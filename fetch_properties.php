<?php
session_start();
include 'database.php';

$owner_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id, location, price, status FROM properties WHERE owner_id = ?");
$stmt->execute([$owner_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
