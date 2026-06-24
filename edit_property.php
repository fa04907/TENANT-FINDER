<?php
session_start();
include 'db_connection.php'; // Ensure this file sets up $pdo correctly

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<script>alert('Please log in first!'); window.location.href='login.html';</script>");
}

// Check if property ID is provided
if (!isset($_GET['id'])) {
    die("<script>alert('Property ID missing!'); window.history.back();</script>");
}

$property_id = $_GET['id'];
$owner_id = $_SESSION['user_id'];

// Fetch property details
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND owner_id = ?");
$stmt->execute([$property_id, $owner_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    die("<script>alert('Property not found or you do not have permission to edit it!'); window.history.back();</script>");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $textLocation = $_POST['textLocation'] ?? '';
        $googleMapsUrl = $_POST['googleMapsUrl'] ?? '';
        $infrastructure = $_POST['infrastructure'] ?? '';
        $price = $_POST['price'] ?? '';

        // Validate required fields
        if (empty($textLocation) || empty($googleMapsUrl) || empty($infrastructure) || empty($price)) {
            die("<script>alert('Please fill in all required fields.'); window.history.back();</script>");
        }

        // Function to handle file uploads
        function uploadFile($fileInput, $uploadDir) {
            if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] == UPLOAD_ERR_NO_FILE) {
                return null; // Skip if no file uploaded
            }

            $fileName = time() . "_" . basename($_FILES[$fileInput]["name"]);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            $allowedTypes = ["jpg", "png", "jpeg"];

            if (in_array($fileType, $allowedTypes)) {
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                if (move_uploaded_file($_FILES[$fileInput]["tmp_name"], $targetFilePath)) {
                    return $targetFilePath;
                }
            }
            return null; // If upload fails
        }

        // Handle image uploads (Optional)
        $newImagePaths = json_decode($property['images'], true) ?? [];

        foreach ($_FILES as $key => $file) {
            if (!empty($file['name'])) {
                $uploadedFile = uploadFile($key, "uploads/properties/");
                if ($uploadedFile) {
                    $newImagePaths[$key] = $uploadedFile;
                }
            }
        }

        $imagePathsJson = json_encode($newImagePaths);

        // Update property details
        $stmt = $pdo->prepare("UPDATE properties SET textLocation = ?, googleMapsUrl = ?, infrastructure = ?, price = ?, images = ? WHERE id = ? AND owner_id = ?");
        $success = $stmt->execute([$textLocation, $googleMapsUrl, $infrastructure, $price, $imagePathsJson, $property_id, $owner_id]);

        if ($success) {
            echo "<script>alert('✅ Property updated successfully!'); window.location.href='owner_dashboard.html';</script>";
            exit();
        } else {
            echo "<script>alert('❌ Failed to update property!'); window.history.back();</script>";
        }

    } catch (PDOException $e) {
        die("<b>Database Error:</b> " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property</title>
</head>
<body>
    <h2>Edit Property</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label>Location:</label>
        <input type="text" name="textLocation" value="<?= htmlspecialchars($property['textLocation']) ?>" required><br>

        <label>Google Maps URL:</label>
        <input type="text" name="googleMapsUrl" value="<?= htmlspecialchars($property['googleMapsUrl']) ?>" required><br>

        <label>Infrastructure:</label>
        <textarea name="infrastructure" required><?= htmlspecialchars($property['infrastructure']) ?></textarea><br>

        <label>Price (₹):</label>
        <input type="number" name="price" value="<?= htmlspecialchars($property['price']) ?>" required><br>

        <h3>Update Images (Optional)</h3>
        <?php
        $existingImages = json_decode($property['images'], true) ?? [];
        foreach ($existingImages as $key => $imagePath) {
            echo "<label>$key:</label>";
            echo "<img src='$imagePath' width='100'><br>";
            echo "<input type='file' name='$key'><br>";
        }
        ?>

        <button type="submit">Update Property</button>
    </form>
</body>
</html>
