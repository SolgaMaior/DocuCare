<?php

$host = "localhost";
$user = "root";
$dbname  = "citizensdb";

try {
    $db = new PDO("mysql:host=127.0.0.1;port=3307;dbname=$dbname", "$user", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
