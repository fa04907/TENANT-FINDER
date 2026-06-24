<?php
session_start();
require 'database.php'; // Ensure this file correctly connects to your database

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); // Exit if the user is not logged in
}

// Validate property ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<div class='alert alert-danger text-center'>❌ Error: Invalid Property ID.</div>");
}

$property_id = intval($_GET['id']);

try {
    // Fetch property details
    $stmt = $pdo->prepare("SELECT id, title, description, location, price, views, images FROM properties WHERE id = ?");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        die("<div class='alert alert-danger text-center'>❌ Error: Property not found.</div>");
    }

    // Increment views count
    $update_views = $pdo->prepare("UPDATE properties SET views = views + 1 WHERE id = ?");
    $update_views->execute([$property_id]);

    // Extract details safely
    $title = htmlspecialchars($property['title'] ?? 'N/A');

    
    $description = htmlspecialchars($property['description'] ?? 'No description available');
    $googleMapsUrl = $property['googleMapsUrl'] ?? '';
    $location = htmlspecialchars($property['location'] ?? 'Unknown location');
    $price = number_format($property['price'] ?? 0);
    $views = $property['views'] ?? 0;
    $images = json_decode($property['images'], true); // Convert JSON string to PHP array

} catch (PDOException $e) {
    die("<div class='alert alert-danger text-center'>❌ Database Error: " . $e->getMessage() . "</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Details</title>
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
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="available_properties.php">Properties</a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link btn btn-danger text-white" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="card shadow-lg p-4">
        <h2 class="mb-3"><?= $title; ?></h2>
        <p><strong>Location:</strong> <?= $location; ?></p>
        <p><strong>Price:</strong> ₹<?= $price; ?></p>
        <p><strong>Description:</strong> <?= $description; ?></p>
        <p><strong>Views:</strong> <?= $views; ?></p>

        <?php if (!empty($images) && is_array($images)): ?>
    <div class="row">
        <?php foreach ($images as $image): ?>
            <div class="col-md-4">
                <img src="<?= htmlspecialchars($image); ?>" class="img-fluid rounded mb-3" alt="Property Image">
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No images available</p>
<?php endif; ?>



        <div class="mt-4">
            <a href="contact_owner.php?id=<?= $property_id; ?>" class="btn btn-primary">Contact Owner</a>
            <form action="shortlist_property.php" method="POST" class="d-inline">
                <input type="hidden" name="property_id" value="<?= $property_id; ?>">
                <button type="submit" class="btn btn-warning">Shortlist</button>
            </form>
            <a href="available_properties.php" class="btn btn-secondary">Back to Listings</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
