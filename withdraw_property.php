<?php
session_start();
include 'database.php'; // Ensure this file establishes $pdo connection

// Ensure the request method is POST and data is received
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['property_id']) || !isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Invalid request."]);
        exit;
    }

    $property_id = intval($data['property_id']);
    $owner_id = $_SESSION['user_id'];

    try {
        // Ensure the property belongs to the logged-in owner before deleting
        $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ? AND owner_id = ?");
        if ($stmt->execute([$property_id, $owner_id])) {
            echo json_encode(["success" => true, "message" => "Property withdrawn successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to withdraw property."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}
?>
 