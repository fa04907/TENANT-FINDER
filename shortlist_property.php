<?php
session_start();
include 'db_connection.php'; // Ensure this file sets up $pdo correctly

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<script>alert('Please log in first!'); window.location.href='login.html';</script>");
}

// Fetch shortlisted properties for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT p.id, p.title AS property_name, p.location, p.price
    FROM shortlisted s
    INNER JOIN properties p ON s.property_id = p.id
    WHERE s.user_id = ?
");
$stmt->execute([$user_id]);
$shortlisted_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shortlisted Properties</title>
</head>
<body>
    <h1>My Shortlisted Properties</h1>
    
    <?php if (count($shortlisted_properties) > 0): ?>
        <ul>
            <?php foreach ($shortlisted_properties as $property): ?>
                <li>
                    <strong><?php echo $property['property_name']; ?></strong>
                    <p>Location: <?php echo $property['location']; ?></p>
                    <p>Price: ₹<?php echo $property['price']; ?></p>
                    <a href="property_details.php?id=<?php echo $property['id']; ?>">View Details</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You have no shortlisted properties.</p>
    <?php endif; ?>
</body>
</html>
