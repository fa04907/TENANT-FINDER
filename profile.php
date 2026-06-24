<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Secure the session
session_regenerate_id(true);

// Include database connection
require 'database.php';

try {
    // Fetch user details
    $stmt = $pdo->prepare("SELECT fname, lname, username, email, phone, country, state, user_type FROM users WHERE id = ?");
    $stmt->execute([intval($_SESSION['user_id'])]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error_message = "User not found.";
    }
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Real Estate Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Real Estate Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="available_properties.php">Properties</a></li>
                <li class="nav-item"><a class="nav-link btn btn-danger text-white" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2>Your Profile</h2>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message); ?></div>
    <?php else: ?>
        <div class="card p-4 shadow-lg">
            <h3 class="mb-3">Welcome, <?= htmlspecialchars($user['fname'] . " " . $user['lname']); ?>!</h3>
            <ul class="list-group">
                <li class="list-group-item"><strong>Username:</strong> <?= htmlspecialchars($user['username']); ?></li>
                <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></li>
                <li class="list-group-item"><strong>Phone:</strong> <?= htmlspecialchars($user['phone']); ?></li>
                <li class="list-group-item"><strong>Country:</strong> <?= htmlspecialchars($user['country']); ?></li>
                <li class="list-group-item"><strong>State:</strong> <?= htmlspecialchars($user['state']); ?></li>
                <li class="list-group-item"><strong>Account Type:</strong> <?= ucfirst(htmlspecialchars($user['user_type'])); ?></li>
            </ul>
            <div class="mt-3">
                <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
