<?php
session_start();

// ✅ Ensure session variable exists and user is an owner
$isOwner = isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'owner';

// ✅ Database credentials (adjust as per your setup)
$host = "localhost";
$dbname = "real_estate";
$username = "root";
$password = "root"; // Set to your actual MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("❌ DB Connection Failed: " . htmlspecialchars($e->getMessage()));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isOwner) {
    try {
        $title = htmlspecialchars(trim($_POST['title']));
        $location = htmlspecialchars(trim($_POST['location']));
        $price = htmlspecialchars(trim($_POST['price']));
        $description = htmlspecialchars(trim($_POST['description']));
        $owner_id = $_SESSION['user_id'];

        $imagePaths = [];
        $paymentReceiptPath = null;
        $uploadDir = 'uploads/properties/';

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // ✅ Handle property images
        if (!empty($_FILES['images']['name'][0])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
                    $fileType = mime_content_type($tmpName);
                    $fileSize = $_FILES['images']['size'][$index];

                    if (in_array($fileType, $allowedTypes) && $fileSize <= 2 * 1024 * 1024) {
                        $uniqueName = 'Property_' . time() . '_' . uniqid() . '_' . basename($_FILES['images']['name'][$index]);
                        $targetPath = $uploadDir . $uniqueName;
                        move_uploaded_file($tmpName, $targetPath);
                        $imagePaths[] = $targetPath;
                    }
                }
            }
        }

        // ✅ Handle payment receipt
        if (isset($_FILES['paymentReceipt']) && $_FILES['paymentReceipt']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            $fileType = mime_content_type($_FILES['paymentReceipt']['tmp_name']);
            $fileSize = $_FILES['paymentReceipt']['size'];

            if (in_array($fileType, $allowedTypes) && $fileSize <= 5 * 1024 * 1024) {
                $receiptName = 'PaymentReceipt_' . time() . '_' . uniqid() . '_' . basename($_FILES['paymentReceipt']['name']);
                $paymentReceiptPath = $uploadDir . $receiptName;
                move_uploaded_file($_FILES['paymentReceipt']['tmp_name'], $paymentReceiptPath);
            } else {
                echo "<script>alert('Invalid file type or size for payment receipt.');</script>";
            }
        }

        // ✅ Prepare JSON or NULL
        $imagesJson = !empty($imagePaths) ? json_encode($imagePaths, JSON_UNESCAPED_SLASHES) : null;
        $paymentReceiptJson = $paymentReceiptPath ? json_encode([$paymentReceiptPath], JSON_UNESCAPED_SLASHES) : null;

        // ✅ Insert into DB
        $stmt = $pdo->prepare("INSERT INTO properties (
            owner_id, title, location, price, description, images, status, paymentReceipt
        ) VALUES (
            :owner_id, :title, :location, :price, :description, :images, 'active', :paymentReceipt
        )");

        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':images', $imagesJson);
        $stmt->bindParam(':paymentReceipt', $paymentReceiptJson);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Property added successfully!'); window.location.href = 'owner_dashboard.php';</script>";
        } else {
            echo "<script>alert('❌ Failed to add property.');</script>";
        }

    } catch (PDOException $e) {
        echo "<script>alert('❌ Database Error: " . htmlspecialchars($e->getMessage()) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Properties</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { width: 80%; margin: auto; padding: 20px; background: white; border-radius: 5px; }
        .property-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .property { border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #fff; }
        .property img { width: 100%; height: 200px; object-fit: cover; border-radius: 5px; }
        .add-property { display: <?php echo $isOwner ? 'block' : 'none'; ?>; margin-top: 20px; padding: 15px; background-color: #002147; color: white; border-radius: 5px; }
        .add-property form { display: flex; flex-direction: column; gap: 10px; }
        input, textarea, select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px; background-color: #002147; color: white; border: none; cursor: pointer; border-radius: 4px; }
        .filter { margin-bottom: 20px; display: flex; gap: 10px; }
        nav a {
            color: white;
            margin-right: 15px;
            text-decoration: none;
            font-size: 18px;
        }
        nav a:hover {
            color: #ff8c00; /* Hover effect color */
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav style="background-color: #002147; padding: 10px 0; color: white;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
        <a href="index.php" style="color: white; text-decoration: none; font-size: 20px; font-weight: bold;">Real Estate</a>
        
        <div>
            <a href="rent_properties.php" style="color: white;">Rent Properties</a>
            <a href="owner_dashboard.php" style="color: white;">Owner Dashboard</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" style="color: white;">Logout</a>
            <?php else: ?>
                <a href="login.php" style="color: white;">Login</a>
                <a href="signup.php" style="color: white;">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Container for Properties -->
<div class="container">
    <h2>Available Rental Properties</h2>

    <!-- Filter Section -->
    <div class="filter">
        <form method="get">
            <input type="text" name="search" placeholder="Search by location">
            <button type="submit">Search</button>
        </form>
    </div>

    <?php
    try {
        $search = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : '';
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE status = 'active' AND location LIKE ?");
        $stmt->execute(['%' . $search . '%']);

        echo '<div class="property-grid">';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<div class='property'>";
            if (!empty($row['images'])) {
                $images = json_decode($row['images'], true);
            
                if (is_array($images) && count($images) > 0) {
                    foreach ($images as $img) {
                        echo "<img src='/house/" . htmlspecialchars($img) . "' alt='Property Image' style='max-width: 300px; margin: 10px;'>";
                    }
                } else {
                    echo "No property images available.";
                }
            } else {
                echo "No images uploaded for this property.";
            }
            
            echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
            echo "<p><strong>Location:</strong> " . htmlspecialchars($row['location']) . "</p>";
            echo "<p><strong>Price:</strong> ₹" . htmlspecialchars($row['price']) . "</p>";
            echo "<a href='view_property.php?id=" . $row['id'] . "'>View Details</a>";
            echo "</div>";
        }
        echo '</div>';
    } catch (PDOException $e) {
        echo "<p>Error fetching properties: " . $e->getMessage() . "</p>";
    }
    ?>

    <!-- Owner-Only Section: Add Property -->
    <?php if ($isOwner): ?>
        <div class="add-property">
            <h2>Add Rental Property</h2>
            <form action="rent_properties.php" method="post" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Property Title" required>
                <input type="text" name="location" placeholder="Location" required>
                <input type="number" name="price" placeholder="Rental Price (₹)" required>
                <textarea name="description" placeholder="Property Description" required></textarea>

                <!-- Multiple image upload -->
                <input type="file" name="images[]" accept="image/jpeg,image/png" multiple required>
                
                <!-- Add payment receipt file input -->
                <input type="file" name="paymentReceipt" accept="application/pdf,image/jpeg,image/png">

                <button type="submit">Add Property</button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
