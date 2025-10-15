<?php
require_once '../databases/db_con.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('File not specified.');
}

$fileID = (int)$_GET['id'];

global $db;
$query = "SELECT file_name, mime_type, file_data FROM record_files WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $fileID, PDO::PARAM_INT);
$stmt->execute();
$file = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if ($file) {
    $mime = trim($file['mime_type']);
    $filename = basename($file['file_name']);
    $data = $file['file_data'];

    // Fallback for missing MIME
    if (empty($mime) || $mime === 'application/octet-stream') {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];
        $mime = $mimeMap[$ext] ?? 'application/octet-stream';
    }

    header('Content-Type: ' . $mime);
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($data));
    header('Accept-Ranges: bytes');
    header('Cache-Control: public, must-revalidate, max-age=0');
    header('Pragma: public');
    header('Expires: 0');
    header('X-Content-Type-Options: nosniff');

    echo $data;
    exit;
} else {
    http_response_code(404);
    echo 'File not found.';
}
?>
