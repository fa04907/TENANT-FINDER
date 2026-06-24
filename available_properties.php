<?php
session_start();
require 'database.php';

// Fetch available properties
$stmt = $pdo->prepare("SELECT * FROM properties WHERE status = 'active'");
$stmt->execute();
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Properties - Real Estate Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header class="bg-dark text-white p-3">
        <div class="container d-flex justify-content-between">
            <h1>Available Properties</h1>
            <nav>
                <a href="index.php" class="text-white mx-2">Home</a>
                <a href="profile.php" class="text-white mx-2">Profile</a>
                <a href="logout.php" class="text-white mx-2">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container mt-4">
        <h2>Find Your Ideal Property</h2>

        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label">Property Type:</label>
                <select id="propertyType" class="form-select" onchange="filterProperties()">
                    <option value="all">All</option>
                    <option value="flat">Flat</option>
                    <option value="house">House</option>
                    <option value="2bhk">2BHK</option>
                    <option value="3bhk">3BHK</option>
                    <option value="land">Land</option>
                    <option value="agriculture">Agricultural Land</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Location:</label>
                <input type="text" id="location" class="form-control" onkeyup="filterProperties()" placeholder="Search by location...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Min Price:</label>
                <input type="number" id="minPrice" class="form-control" onkeyup="filterProperties()" placeholder="₹">
            </div>
            <div class="col-md-3">
                <label class="form-label">Max Price:</label>
                <input type="number" id="maxPrice" class="form-control" onkeyup="filterProperties()" placeholder="₹">
            </div>
        </div>

        <!-- Google Map Embed -->
        <div class="mb-4">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m10!1m8!1m3!1d61294.660852037385!2d80.5697824!3d16.224742!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sin!4v1742457214220!5m2!1sen!2sin" 
                width="100%" 
                height="400" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>

        <!-- Property Listings -->
        <div class="row">
            <?php foreach ($properties as $property): ?>
                <div class="col-md-4 mb-4 property-item" 
                     data-type="<?= isset($property['type']) ? strtolower($property['type']) : 'unknown'; ?>" 
                     data-location="<?= isset($property['location']) ? strtolower($property['location']) : 'unknown'; ?>" 
                     data-price="<?= isset($property['price']) ? $property['price'] : '0'; ?>">
                    <div class="card">
                        <?php 
                            $firstImage = isset($property['image']) && !empty($property['image']) 
                                ? htmlspecialchars($property['image']) 
                                : 'default.jpg';
                        ?>
                        
                        <div class="card-body">
                    
                            <h5 class="card-title"><?= htmlspecialchars($property['title'] ?? 'Untitled'); ?></h5>
                            <p><strong>Details:</strong> <?= htmlspecialchars($property['description'] ?? 'No description available'); ?></p>
                            <p><strong>Location:</strong> <?= htmlspecialchars($property['location']); ?></p>
                            <p><strong>Price:</strong> ₹<?= number_format($property['price']); ?></p>
                            <a href="property_details.php?id=<?= $property['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Filtering Script -->
    <script>
        function filterProperties() {
            var type = document.getElementById("propertyType").value.toLowerCase();
            var location = document.getElementById("location").value.toLowerCase();
            var minPrice = parseInt(document.getElementById("minPrice").value) || 0;
            var maxPrice = parseInt(document.getElementById("maxPrice").value) || Infinity;

            var properties = document.querySelectorAll(".property-item");

            properties.forEach(property => {
                var propType = property.getAttribute("data-type").toLowerCase();
                var propLocation = property.getAttribute("data-location").toLowerCase();
                var propPrice = parseInt(property.getAttribute("data-price"));

                if ((type === "all" || propType.includes(type)) &&
                    (location === "" || propLocation.includes(location)) &&
                    (propPrice >= minPrice && propPrice <= maxPrice)) {
                    property.style.display = "block";
                } else {
                    property.style.display = "none";
                }
            });
        }
    </script>
</body>
</html>
