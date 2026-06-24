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
    $stmt = $pdo->prepare("SELECT fname, lname, username, email, phone, country, state FROM users WHERE id = ?");
    $stmt->execute([intval($_SESSION['user_id'])]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error_message = "User not found.";
    }

    // Handle profile update
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $fname = htmlspecialchars(trim($_POST['fname']));
        $lname = htmlspecialchars(trim($_POST['lname']));
        $username = htmlspecialchars(trim($_POST['username']));
        $email = htmlspecialchars(trim($_POST['email']));
        $phone = htmlspecialchars(trim($_POST['phone']));
        $country = htmlspecialchars(trim($_POST['country']));
        $state = htmlspecialchars(trim($_POST['state']));
        $password = htmlspecialchars(trim($_POST['password']));
        $confirmPassword = htmlspecialchars(trim($_POST['confirmPassword']));

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email address.";
        }

        // Password matching check
        if ($password !== $confirmPassword) {
            $error_message = "Passwords do not match.";
        }

        // Hash the password if it's set
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET fname = ?, lname = ?, username = ?, email = ?, phone = ?, country = ?, state = ?, password = ? WHERE id = ?");
            $stmt->execute([$fname, $lname, $username, $email, $phone, $country, $state, $hashedPassword, $_SESSION['user_id']]);
        } else {
            // Update profile without changing the password
            $stmt = $pdo->prepare("UPDATE users SET fname = ?, lname = ?, username = ?, email = ?, phone = ?, country = ?, state = ? WHERE id = ?");
            $stmt->execute([$fname, $lname, $username, $email, $phone, $country, $state, $_SESSION['user_id']]);
        }

        // If update was successful
        $success_message = "Profile updated successfully!";
        // Redirect to the profile page to see updated information
        header("Location: profile.php");
        exit();

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
    <title>Edit Profile - Real Estate Portal</title>
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
    <h2>Edit Profile</h2>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <div class="card p-4 shadow-lg">
        <form action="edit_profile.php" method="POST">

            <div class="mb-3">
                <label for="fname" class="form-label">First Name</label>
                <input type="text" class="form-control" id="fname" name="fname" value="<?= htmlspecialchars($user['fname']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="lname" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lname" name="lname" value="<?= htmlspecialchars($user['lname']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="country" class="form-label">Country</label>
                <input type="text" class="form-control" id="country" name="country" value="<?= htmlspecialchars($user['country']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="state" class="form-label">State</label>
                <input type="text" class="form-control" id="state" name="state" value="<?= htmlspecialchars($user['state']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>

            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
            </div>

            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
