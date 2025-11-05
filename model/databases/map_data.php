<?php
// model/databases/map_data.php
require_once('db_con.php');

function get_map_data_cached() {
    global $db;
    
    // Check cache (expires after 5 minutes)
    $cache_file = __DIR__ . '/../../cache/map_clusters.json';
    $cache_time = 300; // 5 minutes
    
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        $cached_data = json_decode(file_get_contents($cache_file), true);
        
        // Return the clusters array if it exists, otherwise return as-is
        return isset($cached_data['clusters']) ? $cached_data['clusters'] : $cached_data;
    }
    
    // Cache miss - fetch fresh data
    $result = get_fresh_cluster_data($db);
    
    // Save to cache
    if (!is_dir(__DIR__ . '/../../cache')) {
        mkdir(__DIR__ . '/../../cache', 0755, true);
    }
    file_put_contents($cache_file, json_encode($result));
    
    // Return the clusters array
    return isset($result['clusters']) ? $result['clusters'] : $result;
}

function get_fresh_cluster_data($db) {
    try {
        // Fetch illness data grouped by purok
        $sql = "SELECT
            p.purokName,
            COUNT(CASE WHEN i.illness_name = 'Dengue' THEN 1 END) AS dengue,
            COUNT(CASE WHEN i.illness_name = 'Measles' THEN 1 END) AS measles,
            COUNT(CASE WHEN i.illness_name = 'Flu' THEN 1 END) AS flu,
            COUNT(CASE WHEN i.illness_name = 'Allergies' THEN 1 END) AS allergies,
            COUNT(CASE WHEN i.illness_name = 'Diarrhea' THEN 1 END) AS diarrhea
        FROM purok p
        LEFT JOIN illness_records r ON p.purokID = r.purokID
        LEFT JOIN illnesses i ON i.illness_id = r.illness_id
        -- Uncomment for monthly clustering:
        -- WHERE MONTH(r.record_date) = MONTH(CURDATE()) 
        -- AND YEAR(r.record_date) = YEAR(CURDATE())
        GROUP BY p.purokName
        ORDER BY p.purokName";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure we have data for all puroks
        if (empty($data)) {
            error_log("No purok data found");
            return get_default_cluster_data();
        }
        
        // Send to Flask for clustering
        $json_data = json_encode($data);
        
        
        $ch = curl_init("http://127.0.0.1:5000/cluster");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        // Handle Flask errors
        if ($http_code !== 200 || !$response) {
            error_log("Flask clustering failed: HTTP $http_code, Error: $curl_error");
            error_log("Response: " . substr($response, 0, 500));
            
            // Return data with default cluster assignments
            return [
                'clusters' => array_map(function($d) {
                    return array_merge($d, [
                        'cluster' => 0,
                        'severity' => 'low',
                        'total_cases' => 0
                    ]);
                }, $data),
                'cluster_analysis' => [],
                'summary' => [
                    'total_puroks' => count($data),
                    'total_clusters' => 1,
                    'total_cases' => 0
                ]
            ];
        }
        
        $cluster_result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            return get_default_cluster_data();
        }
        
        // The Flask API now returns: { clusters: [...], cluster_analysis: {...}, summary: {...} }
        // Merge the original data with cluster assignments
        if (isset($cluster_result['clusters'])) {
            $merged = [];
            foreach ($data as $d) {
                foreach ($cluster_result['clusters'] as $c) {
                    if (trim($c['purok']) === trim($d['purokName'])) {
                        $merged[] = array_merge($d, [
                            'cluster' => $c['cluster'],
                            'severity' => $c['severity'] ?? 'low',
                            'total_cases' => $c['total_cases'] ?? 0,
                            'dominant_disease' => $c['dominant_disease'] ?? null
                        ]);
                        break;
                    }
                }
            }
            
            return [
                'clusters' => $merged,
                'cluster_analysis' => $cluster_result['cluster_analysis'] ?? [],
                'summary' => $cluster_result['summary'] ?? []
            ];
        }
        
        return $cluster_result;
        
    } catch (Exception $e) {
        error_log("Cluster data error: " . $e->getMessage());
        return get_default_cluster_data();
    }
}

function get_default_cluster_data() {
    // Return default structure when clustering fails
    $puroks = ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5'];
    $data = [];
    
    foreach ($puroks as $idx => $purok) {
        $data[] = [
            'purokName' => $purok,
            'dengue' => 0,
            'measles' => 0,
            'flu' => 0,
            'allergies' => 0,
            'diarrhea' => 0,
            'cluster' => 0,
            'severity' => 'low',
            'total_cases' => 0
        ];
    }
    
    return [
        'clusters' => $data,
        'cluster_analysis' => [],
        'summary' => [
            'total_puroks' => count($puroks),
            'total_clusters' => 1,
            'total_cases' => 0
        ]
    ];
}

function refresh_cluster_cache() {
    $cache_file = __DIR__ . '/../../cache/map_clusters.json';
    
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }
    
    global $db;
    return get_fresh_cluster_data($db);
}
?>