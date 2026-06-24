<?php
session_start();
include 'database.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must be logged in to sell a property.'); window.location.href='login.html';</script>";
    exit();
}

$user_id = $_SESSION['user_id']; // Logged-in user ID

// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_id'])) {
    $property_id = $_POST['property_id'];

    try {
        // Fetch property details and verify ownership
        $stmt = $pdo->prepare("SELECT p.title, p.owner_id, u.email FROM properties p 
                               JOIN users u ON p.owner_id = u.id WHERE p.id = ? AND p.owner_id = ?");
        $stmt->execute([$property_id, $user_id]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($property) {
            // Mark the property as sold
            $updateStmt = $pdo->prepare("UPDATE properties SET status = 'sold' WHERE id = ?");
            if ($updateStmt->execute([$property_id])) {
                // Send email notification to owner
                $to = $property['email'];
                $subject = "Your Property Has Been Sold!";
                $message = "Dear Owner,\n\nYour property '" . htmlspecialchars($property['title']) . "' has been sold.\n\nThank you for using our platform!\n\n- Real Estate Team";
                $headers = "From: noreply@realestate.com\r\nReply-To: support@realestate.com";

                mail($to, $subject, $message, $headers);

                echo "<script>alert('Property marked as sold! Notification sent to owner.'); window.location.href='owner_dashboard.php';</script>";
            } else {
                echo "<script>alert('Error updating property status.'); window.location.href='owner_dashboard.php';</script>";
            }
        } else {
            echo "<script>alert('Property not found or unauthorized access!'); window.location.href='owner_dashboard.php';</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "'); window.location.href='owner_dashboard.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='owner_dashboard.php';</script>";
}
?>
