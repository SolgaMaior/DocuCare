<?php
// --- Step 1: Include PDO connection
require_once('db_con.php'); // this gives you $db

// --- Step 2: Fetch data using PDO
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
$json_data = json_encode($data);

// --- Step 3: Send to Flask AI clustering
$ch = curl_init("http://127.0.0.1:5000/cluster");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$clusters = json_decode($response, true);

// --- Step 4: Merge cluster results
$merged = [];
if (!empty($clusters)) {
    foreach ($data as $d) {
        foreach ($clusters as $c) { // no ['clusters']
            if (trim($c['purok']) === trim($d['purokName'])) {
                $merged[] = array_merge($d, ['cluster' => $c['cluster']]);
            }
        }
    }
}
?>