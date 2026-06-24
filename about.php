<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Real Estate Portal</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>About Us</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="available_properties.php">Available Properties</a>
        <a href="contact.php">Contact</a>
        <?php
        if (isset($_SESSION['user_id'])) {
            echo '<a href="profile.php">Profile</a>';
            echo '<a href="logout.php">Logout</a>';
        } else {
            echo '<a href="login.php">Login</a>';
        }
        ?>
    </nav>
</header>

<section class="container">
    <h2>Welcome to Our Real Estate Portal</h2>
    <p>Our platform helps buyers, tenants, and property owners connect easily. We provide verified listings, secure transactions, and an efficient property management system.</p>

    <h3>Why Choose Us?</h3>
    <ul>
        <li>✔ Verified Properties</li>
        <li>✔ Secure Transactions</li>
        <li>✔ Advanced Search & Filtering</li>
        <li>✔ Dedicated Support</li>
    </ul>

    <h3>Our Mission</h3>
    <p>We aim to create a transparent, efficient, and user-friendly real estate marketplace for all.</p>

    <h3>Contact Us</h3>
    <p>Email: support@realestate.com</p>
    <p>Phone: +91 98765 43210</p>
</section>

<footer>
    <p>&copy; 2025 Real Estate Portal. All Rights Reserved.</p>
</footer>

</body>
</html>
