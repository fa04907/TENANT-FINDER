<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'database.php'; // Ensure the path is correct

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    echo "📥 Form submitted via POST method.<br>";  // Debugging line

    // Required fields
    $requiredFields = ['userType', 'fname', 'lname', 'username', 'email', 'phone', 'password', 'confirmPassword', 'country', 'state'];

    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo "<script>
                alert('$field is required.');
                window.history.back();
            </script>";
            exit();
        }
    }

    // Sanitize input
    function cleanInput($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    $userType = cleanInput($_POST['userType']);
    $fname = cleanInput($_POST['fname']);
    $lname = cleanInput($_POST['lname']);
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $phone = cleanInput($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $country = cleanInput($_POST['country']);
    $state = cleanInput($_POST['state']);
    $gstId = isset($_POST['gstId']) ? cleanInput($_POST['gstId']) : null;

    // Check if username or email already exists
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            echo "<script>
                alert('Username or Email already exists.');
                window.history.back();
            </script>";
            exit();
        }
    } catch (PDOException $e) {
        die("❌ Error checking existing users: " . $e->getMessage());
    }

    // Password checks
    if (strlen($password) < 8 || !preg_match('/\d/', $password) || !preg_match('/[@$!%*?&]/', $password)) {
        echo "<script>alert('Password must be at least 8 characters, with a number & special character.'); window.history.back();</script>";
        exit();
    }

    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert into database
    try {
        $stmt = $pdo->prepare("INSERT INTO users (user_type, fname, lname, username, email, phone, password, country, state, gst_id)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userType, $fname, $lname, $username, $email, $phone, $hashedPassword, $country, $state, $gstId]);

        echo "✅ Registration successful!<br>";  // Debugging line

        // SweetAlert for success
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Registration Successful!',
                text: 'Redirecting to login page...',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'login.html';
            });
        </script>";

    } catch (PDOException $e) {
        die("❌ Error inserting data: " . $e->getMessage());
    }
} else {
    echo "❌ Invalid request method.";
}
?>
