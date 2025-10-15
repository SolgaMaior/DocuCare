<?php
require_once 'databases/db_con.php'; // adjust the path if needed

if (!isset($_GET['id'])) {
    die("File not specified.");
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
    header("Content-Type: " . $file['mime_type']);
    header("Content-Disposition: inline; filename=\"" . $file['file_name'] . "\"");
    echo $file['file_data'];
} else {
    echo "File not found.";
}
?>  