<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Real Estate Portal</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>Contact Us</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="available_properties.php">Available Properties</a>
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
    <h2>Get in Touch</h2>
    <p>Have questions? Fill out the form below or contact us directly.</p>

    <form action="send_message.php" method="post" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="message">Message:</label>
            <textarea id="message" name="message" rows="5" required></textarea>
        </div>

        <button type="submit">Send Message</button>
    </form>

    <h3>Contact Information</h3>
    <p><strong>Email:</strong> support@realestate.com</p>
    <p><strong>Phone:</strong> +91 98765 43210</p>
    <p><strong>Address:</strong> 123 Real Estate Street, Hyderabad, India</p>
</section>

<footer>
    <p>&copy; 2025 Real Estate Portal. All Rights Reserved.</p>
</footer>

<script>
    function validateForm() {
        var name = document.getElementById("name").value.trim();
        var email = document.getElementById("email").value.trim();
        var message = document.getElementById("message").value.trim();

        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (name === "" || email === "" || message === "") {
            alert("❌ All fields are required.");
            return false;
        }

        if (!emailPattern.test(email)) {
            alert("❌ Please enter a valid email address.");
            return false;
        }

        return true;
    }
</script>

</body>
</html>
