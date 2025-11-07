<?php

require_once('../databases/db_con.php');
require_once('../databases/citizensdb.php');

header('Content-Type: application/json');

$searchTerm = trim($_GET['search'] ?? '');
$purokID = $_GET['purokID'] ?? 'all';
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;

if (empty($searchTerm)) {
    echo json_encode(['error' => 'Search term required']);
    exit;
}

try {
    $citizens = search_citizens($searchTerm, $purokID, $page, 15);
    $totalCount = get_search_count($searchTerm, $purokID);
    
    echo json_encode([
        'success' => true,
        'citizens' => $citizens,
        'total' => $totalCount,
        'page' => $page,
        'totalPages' => ceil($totalCount / 15)
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>