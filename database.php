<?php
$host = "localhost";
$dbname = "real_estate";
$username = "root";
$password = "root";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Enable exceptions for errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch as associative array by default
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Prevent SQL injection risk
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . htmlspecialchars($e->getMessage()));
}
?>
