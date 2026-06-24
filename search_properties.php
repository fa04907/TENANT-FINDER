<?php
// search_properties.php - Search Properties
include 'database.php';

header('Content-Type: application/json'); // Set JSON response type

try {
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $query = isset($_GET['query']) ? trim($_GET['query']) : '';

        $stmt = $pdo->prepare("SELECT * FROM properties WHERE title LIKE :query OR location LIKE :query");
        $stmt->execute(['query' => "%$query%"]);
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'properties' => $properties]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
