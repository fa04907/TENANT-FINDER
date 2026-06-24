<?php
include 'database.php';

$stmt = $pdo->prepare("SELECT name, type, property_id, contact FROM interested_customers");
$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
