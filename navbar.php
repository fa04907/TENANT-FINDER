<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header>
    <h1>Real Estate Portal</h1>
    <nav>
        <a href="index.html">Home</a>
        <a href="available_properties.php">Available Properties</a>
        <a href="admin_details.html">Admin Details</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="profile.php">My Profile</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.html">Login</a>
            <a href="register.html">Register</a>
            <a href="shortlist.php">Shortlisted Properties</a>

        <?php endif; ?>
    </nav>
</header>
