<?php
session_start();
include 'database.php'; // Ensure database connection

// Check if user is logged in and is an owner
$isOwner = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Owner';

// Handle property submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $isOwner) {
    try {
        $title = trim($_POST['title']);
        $location = trim($_POST['location']);
        $price = trim($_POST['price']);
        $description = trim($_POST['description']);
        $owner_id = $_SESSION['user_id']; // Get owner ID from session

        // Image Upload
        $uploadDir = "uploads/";
        $imagePath = null;

        if (isset($_FILES["image"]["name"]) && $_FILES["image"]["size"] > 0) {
            $imagePath = $uploadDir . "Property_" . time() . "_" . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
        }

        // Insert property into database
        $stmt = $pdo->prepare("INSERT INTO properties (owner_id, title, location, price, description, image, type) VALUES (?, ?, ?, ?, ?, ?, 'Sell')");
        if ($stmt->execute([$owner_id, $title, $location, $price, $description, $imagePath])) {
            echo "<script>alert('Property listed for sale successfully!');</script>";
        } else {
            echo "<script>alert('Failed to list property for sale!');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Database Error: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Properties</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
        }
        .property {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            background: #fff;
        }
        .property img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
        }
        .add-property {
            background-color: #002147;
            color: white;
            padding: 15px;
            border-radius: 5px;
            display: <?php echo $isOwner ? 'block' : 'none'; ?>;
        }
        .add-property form {
            display: flex;
            flex-direction: column;
        }
        input, textarea {
            margin-top: 10px;
            padding: 10px;
        }
        button {
            margin-top: 10px;
            padding: 10px;
            background-color: #002147;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Available Properties for Sale</h2>

    <!-- Show available properties for sale -->
    <?php
    $stmt = $pdo->query("SELECT * FROM properties WHERE type='Sell'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<div class='property'>";
        if ($row['image']) {
            echo "<img src='" . $row['image'] . "' alt='Property Image'>";
        }
        echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
        echo "<p><strong>Location:</strong> " . htmlspecialchars($row['location']) . "</p>";
        echo "<p><strong>Price:</strong> ₹" . htmlspecialchars($row['price']) . "</p>";
        echo "<p>" . htmlspecialchars($row['description']) . "</p>";
        echo "</div>";
    }
    ?>

    <!-- Owner-Only Section: Add Property for Sale -->
    <?php if ($isOwner): ?>
        <div class="add-property">
            <h2>List Property for Sale</h2>
            <form action="sell_properties.php" method="post" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Property Title" required>
                <input type="text" name="location" placeholder="Location" required>
                <input type="number" name="price" placeholder="Selling Price (₹)" required>
                <textarea name="description" placeholder="Property Description" required></textarea>
                <input type="file" name="image" required>
                <button type="submit">List Property</button>
            </form>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
