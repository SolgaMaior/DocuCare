<?php
require_once('db_con.php');

function get_map_data_cached() {
    global $db;
    
    // Check cache (expires after 5 minutes)
    $cache_file = __DIR__ . '/../../cache/map_clusters.json';
    $cache_time = 300; // 5 minutes
    
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        return json_decode(file_get_contents($cache_file), true);
    }
    
    // Cache miss - fetch fresh data
    $merged = get_fresh_cluster_data($db);
    
    // Save to cache
    if (!is_dir(__DIR__ . '/../../cache')) {
        mkdir(__DIR__ . '/../../cache', 0755, true);
    }
    file_put_contents($cache_file, json_encode($merged));
    
    return $merged;
}

function get_fresh_cluster_data($db) {
    // Fetch illness data
    $sql = "SELECT
        p.purokName,
        COUNT(CASE WHEN i.illness_name = 'Dengue' THEN 1 END) AS dengue,
        COUNT(CASE WHEN i.illness_name = 'Measles' THEN 1 END) AS measles,
        COUNT(CASE WHEN i.illness_name = 'Flu' THEN 1 END) AS flu,
        COUNT(CASE WHEN i.illness_name = 'Allergies' THEN 1 END) AS allergies,
        COUNT(CASE WHEN i.illness_name = 'Diarrhea' THEN 1 END) AS diarrhea
    FROM illness_records r
    JOIN purok p ON p.purokID = r.purokID
    JOIN illnesses i ON i.illness_id = r.illness_id
    GROUP BY p.purokName";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Send to Flask for clustering
    $json_data = json_encode($data);
    $ch = curl_init("http://127.0.0.1:5000/cluster");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Handle Flask errors
    if ($http_code !== 200 || !$response) {
        error_log("Flask clustering failed: HTTP $http_code");
        // Return data with default cluster 0
        return array_map(function($d) {
            return array_merge($d, ['cluster' => 0]);
        }, $data);
    }
    
    $clusters = json_decode($response, true);
    
    // Merge cluster results
    $merged = [];
    foreach ($data as $d) {
        foreach ($clusters as $c) {
            if (trim($c['purok']) === trim($d['purokName'])) {
                $merged[] = array_merge($d, ['cluster' => $c['cluster']]);
                break;
            }
        }
    }
    
    return $merged;
}
?>