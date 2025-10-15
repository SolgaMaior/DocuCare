<?php
require_once '../databases/db_con.php';

if (!isset($_GET['citID'])) {
    exit('No citizen ID specified.');
}

$citID = intval($_GET['citID']);

$query = "SELECT profile_image, profile_image_type FROM citizens WHERE citID = :citID";
$stmt = $db->prepare($query);
$stmt->bindValue(':citID', $citID, PDO::PARAM_INT);
$stmt->execute();
$image = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if ($image && !empty($image['profile_image'])) {
    header("Content-Type: " . $image['profile_image_type']);
    echo $image['profile_image'];
} else {
    header("Content-Type: image/png");
    readfile("../../resources/defaultprofile.png"); // fixed path
}
?>
