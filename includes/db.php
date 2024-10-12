<?php
// db.php
$host = 'localhost';
$db = 'event_registration';
$user = 'root';  // Ganti dengan username MySQL Anda
$pass = '';      // Ganti dengan password MySQL Anda

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
