<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <nav>
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="available_properties.php">Available Properties</a>
        <a href="shortlist.php">Shortlisted Properties</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php">Profile</a>
            <?php if ($_SESSION['user_type'] === 'owner'): ?>
                <a href="add_property.php">Add Property</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
