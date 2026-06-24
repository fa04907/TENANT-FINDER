<?php
session_start();
include 'database.php'; // Ensure correct DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim(htmlspecialchars($_POST['username']));
    $password = trim($_POST['password']);

    try {
        // Fetch user details
        $stmt = $pdo->prepare("SELECT id, username, user_type, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];

            // Redirect based on user type
            header("Location: " . ($user['user_type'] === 'owner' ? "owner_dashboard.php" : "index.php")); 
            exit();
        } else {
            $_SESSION['error'] = "❌ Invalid username or password!";
            header("Location: login.php"); // Redirect back to login
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Database Error: " . $e->getMessage();
        header("Location: login.php");
        exit();
    }
}
?>
