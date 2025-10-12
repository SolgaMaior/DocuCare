<?php
// appointmentdb_con.php
$host = 'localhost';
$port = 3307; // Your new MySQL port
$db   = 'appointmentdb';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected successfully!";
} catch (\PDOException $e) {
    exit('Database connection failed: ' . $e->getMessage());
}
?>
