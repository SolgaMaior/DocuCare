<?php

$host = "localhost";
$user = "docuuser";
$dbname  = "citizensdb";

try {
    $db = new PDO("mysql:host=$host;port=3306;dbname=$dbname", "$user", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
