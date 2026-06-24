<?php
session_start();
require 'database.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Check if property ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<div class='alert alert-danger text-center'>❌ Error: Invalid Property ID.</div>");
}

$property_id = intval($_GET['id']);

try {
    // Fetch property and owner details
    $stmt = $pdo->prepare("SELECT p.title, p.owner_id, u.fname, u.lname, u.email, u.phone 
                           FROM properties p 
                           JOIN users u ON p.owner_id = u.id 
                           WHERE p.id = ?");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        die("<div class='alert alert-danger text-center'>❌ Error: Property not found.</div>");
    }
} catch (PDOException $e) {
    die("<div class='alert alert-danger text-center'>❌ Database Error: " . $e->getMessage() . "</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Owner - <?= htmlspecialchars($property['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.html">Real Estate Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="available_properties.php">Properties</a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link btn btn-danger text-white" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="card shadow-lg p-4">
        <h2 class="mb-3">Contact Owner for <span class="text-primary"><?= htmlspecialchars($property['title']); ?></span></h2>
        <p><strong>Owner Name:</strong> <?= htmlspecialchars($property['fname'] . ' ' . $property['lname']); ?></p>
        <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($property['email']); ?>"><?= htmlspecialchars($property['email']); ?></a></p>
        <p><strong>Phone:</strong> <a href="tel:<?= htmlspecialchars($property['phone']); ?>"><?= htmlspecialchars($property['phone']); ?></a></p>

        <a href="property_details.php?id=<?= $property_id; ?>" class="btn btn-secondary">Back to Property</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
