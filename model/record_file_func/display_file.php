<?php
require_once '../databases/db_con.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('File not specified.');
}

$fileID = (int)$_GET['id'];

$query = "SELECT file_name, mime_type, file_data FROM record_files WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $fileID, PDO::PARAM_INT);
$stmt->execute();
$file = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if (!$file) {
    http_response_code(404);
    exit('File not found.');
}

$mime = trim($file['mime_type']) ?: 'application/octet-stream';
$data = $file['file_data'];

header('Content-Type: ' . $mime);
header('Content-Length: ' . strlen($data));
header('Cache-Control: public, max-age=3600');
header('Accept-Ranges: bytes');

echo $data;
exit;
