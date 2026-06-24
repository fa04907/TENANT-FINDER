<?php
session_start();
if (!isset($_SESSION['fname'])) {
    header("Location: index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .success-container {
            text-align: center;
            padding: 50px;
            background-color: #f0fff0;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 128, 0, 0.2);
            max-width: 500px;
            margin: auto;
        }
        .success-container h2 {
            color: green;
        }
        .success-container p {
            font-size: 18px;
        }
        .success-container a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: green;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .success-container a:hover {
            background-color: darkgreen;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <h2>✅ Registration Successful!</h2>
        <p>Welcome, <strong><?= htmlspecialchars($_SESSION['fname']); ?></strong>!</p>
        <p>Your account has been created successfully.</p>
        <p><strong>Next Step:</strong> Please <a href="login.html">Login</a> to continue.</p>
    </div>
</body>
</html>
