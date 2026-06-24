<?php
session_start();
$_SESSION = []; // Clear all session variables
session_unset(); // Unset all session variables (extra safety)
session_destroy(); // Destroy the session
setcookie(session_name(), '', time() - 3600, '/'); // Destroy session cookie
header("Location: index.php"); // Redirect to home page
exit();
?>
