<?php
session_start();
require_once 'db_connection.php'; // Database connection

// Get the property ID from query string
$propertyId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Determine which table to query (rent or sale)
$listingType = $_GET['type'] ?? 'rent';
$table = ($listingType === 'sale') ? 'properties' : 'rent_properties';

try {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = :id");
    $stmt->execute([':id' => $propertyId]);
    $property = $stmt->fetch();

    if (!$property) {
        echo "<h3>❌ Property not found.</h3>";
        exit;
    }
} catch (PDOException $e) {
    echo "Database Error: " . htmlspecialchars($e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Property</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .property-images img { max-width: 300px; margin: 10px; display: inline-block; }
        .property-detail { margin-bottom: 10px; }
    </style>
</head>
<body>

    <h1><?= htmlspecialchars($property['title']) ?></h1>
    <div class="property-detail"><strong>📍 Location:</strong> <?= htmlspecialchars($property['location']) ?></div>
    <div class="property-detail"><strong>💰 Price:</strong> ₹<?= number_format($property['price'], 2) ?></div>
    <div class="property-detail"><strong>📝 Description:</strong><br><?= nl2br(htmlspecialchars($property['description'])) ?></div>

    <?php if (!empty($property['googleMapsUrl'])): ?>
        <div class="property-detail">
            <a href="<?= htmlspecialchars($property['googleMapsUrl']) ?>" target="_blank">🗺️ View on Google Maps</a>
        </div>
    <?php endif; ?>

    <div class="property-detail"><strong>🏗️ Infrastructure:</strong><br><?= nl2br(htmlspecialchars($property['infrastructure'])) ?></div>

    <!-- Property Images (exclude tax_slip and paymentReceipt) -->
    <div class="property-images">
        <h3>📸 Property Images</h3>
        <?php
        $images = json_decode($property['images'], true);
        if (is_array($images)) {
            foreach ($images as $img) {
                // Filter out images that are stored in the 'uploads/receipts' directory (likely tax/payment)
                if (strpos($img, 'uploads/receipts') === false) {
                    echo "<img src='/$img' alt='Property Image'>";
                }
            }
        }
        ?>
    </div>

</body>
</html>
