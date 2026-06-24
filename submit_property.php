<?php
session_start();
include 'db_connection.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        die("<script>alert('Please log in first!'); window.location.href='login.html';</script>");
    }

    $owner_id = $_SESSION['user_id'];
    $propertyType = $_POST['propertyType'] ?? '';
    $textLocation = $_POST['textLocation'] ?? '';
    $googleMapsUrl = $_POST['googleMapsUrl'] ?? '';
    $infrastructure = $_POST['infrastructure'] ?? '';
    $price = $_POST['price'] ?? '';

    // Validate required fields
    if (empty($propertyType) || empty($textLocation) || empty($googleMapsUrl) || empty($infrastructure) || empty($price)) {
        die("<script>alert('Please fill in all required fields.'); window.history.back();</script>");
    }

    // Function to handle file uploads
    function uploadFile($fileInput, $uploadDir) {
        if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] == UPLOAD_ERR_NO_FILE) {
            return null; // No file uploaded
        }

        $fileName = time() . "_" . basename($_FILES[$fileInput]["name"]);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "png", "jpeg", "pdf"];

        if (in_array($fileType, $allowedTypes)) {
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (move_uploaded_file($_FILES[$fileInput]["tmp_name"], $targetFilePath)) {
                return $targetFilePath;
            }
        }
        return false;
    }

    // Handle property images (multiple uploads)
    $imagePaths = [];
    $uploadDir = "uploads/properties/";

    if (!empty($_FILES["propertyImages"]["name"][0])) {
        foreach ($_FILES["propertyImages"]["tmp_name"] as $key => $tmp_name) {
            $fileName = time() . "_" . basename($_FILES["propertyImages"]["name"][$key]);
            $targetPath = $uploadDir . $fileName;

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (move_uploaded_file($tmp_name, $targetPath)) {
                $imagePaths[] = $targetPath;
            }
        }
    }

    $imagePathsSerialized = implode(",", $imagePaths); // Store as comma-separated paths

    // Upload tax payment slip
    $taxPaymentPath = uploadFile("taxPayment", "uploads/tax/");

    // Upload payment receipt
    $paymentReceiptPath = uploadFile("paymentReceipt", "uploads/receipts/");

    if ($taxPaymentPath === false || $paymentReceiptPath === false) {
        die("<script>alert('Error uploading files. Please try again.'); window.history.back();</script>");
    }

    // Insert data into database
    try {
        $stmt = $conn->prepare("INSERT INTO properties 
            (owner_id, propertyType, textLocation, googleMapsUrl, infrastructure, price, images, taxPayment, paymentReceipt) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("issssssss", $owner_id, $propertyType, $textLocation, $googleMapsUrl, $infrastructure, $price, $imagePathsSerialized, $taxPaymentPath, $paymentReceiptPath);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Property added successfully!'); window.location.href='owner_dashboard.html';</script>";
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
    } catch (Exception $e) {
        die("<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>");
    }

    $stmt->close();
    $conn->close();
}
?>
