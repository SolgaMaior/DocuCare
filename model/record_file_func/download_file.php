<?php
require_once('model/databases/db_con.php');

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

if (!$file) {
    http_response_code(404);
    exit('File not found.');
}

$mime = trim($file['mime_type']);
$filename = basename($file['file_name']);
$data = base64_encode($file['file_data']);

// Fallback MIME type
if (empty($mime) || $mime === 'application/octet-stream') {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mimeMap = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
    ];
    $mime = $mimeMap[$ext] ?? 'application/octet-stream';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($filename) ?></title>
  <link rel="icon" type="image/svg+xml" href="../../resources/images/Logo.svg">
  <style>
    body { margin: 0; display: flex; justify-content: center; align-items: center; background: #f9f9f9; height: 100vh; }
    img { max-width: 90%; max-height: 90vh; object-fit: contain; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.2); }
    embed { width: 100%; height: 100vh; border: none; }
  </style>
</head>
<body>
  <?php if (strpos($mime, 'image/') === 0): ?>
    <img src="data:<?= $mime ?>;base64,<?= $data ?>" alt="<?= htmlspecialchars($filename) ?>">
  <?php elseif ($mime === 'application/pdf'): ?>
    <embed src="data:<?= $mime ?>;base64,<?= $data ?>" type="<?= $mime ?>" />
  <?php else: ?>
    <p>Cannot preview this file type. <a href="data:<?= $mime ?>;base64,<?= $data ?>" download="<?= htmlspecialchars($filename) ?>">Download</a></p>
  <?php endif; ?>
</body>
</html>
