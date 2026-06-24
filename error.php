<?php
// Start PHP session if needed
session_start();

// Get the error message from URL parameter or session
$errorMessage = isset($_GET['msg']) ? $_GET['msg'] : "Something went wrong.";
$errorMessage = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); // Security to prevent XSS
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error-container {
            text-align: center;
            padding: 50px;
            background-color: #ffe0e0;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(255, 0, 0, 0.2);
            max-width: 500px;
            margin: auto;
        }
        .error-container h2 {
            color: red;
        }
        .error-container p {
            font-size: 18px;
        }
        .error-container a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: red;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .error-container a:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h2>❌ Registration Failed</h2>
        <p><?php echo $errorMessage; ?></p>
        <p><a href="register.html">Try Again</a></p>
    </div>
</body>
</html>
