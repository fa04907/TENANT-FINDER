<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'db_connection.php'; // your PDO connection file

    // Get form data
    $userType = $_POST['userType'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $country = $_POST['country'];
    $state = $_POST['state'] ?? '';

    // Initialize error array
    $errors = [];

    // ✅ Validation
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@gmail.com')) {
        $errors[] = "Please enter a valid Gmail address ending with @gmail.com.";
    }

    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        $errors[] = "Please enter a valid 10-digit phone number starting with 6, 7, 8, or 9.";
    }

    if ($country == 'India') {
        if (!isset($_FILES['aadhar']) || $_FILES['aadhar']['error'] !== 0) {
            $errors[] = "Aadhar card is required for Indian users.";
        }
        if (empty($state)) {
            $errors[] = "State is required for Indian users.";
        }
    } else {
        if (!isset($_FILES['passport']) || $_FILES['passport']['error'] !== 0) {
            $errors[] = "Passport is required for non-Indian users.";
        }
        if (!empty($state)) {
            $errors[] = "State should not be selected for non-Indian users.";
        }
        if (isset($_FILES['aadhar']) && $_FILES['aadhar']['error'] === 0) {
            $errors[] = "Aadhar should not be uploaded for non-Indian users.";
        }
    }

    

    // ✅ Continue only if no errors
    if (empty($errors)) {
        // File upload paths
        $aadharPath = null;
        $passportPath = null;
        $taxPath = null;

        if ($country === 'India') {
            $aadharPath = 'uploads/' . basename($_FILES['aadhar']['name']);
            move_uploaded_file($_FILES['aadhar']['tmp_name'], $aadharPath);
        } else {
            $passportPath = 'uploads/' . basename($_FILES['passport']['name']);
            move_uploaded_file($_FILES['passport']['tmp_name'], $passportPath);
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users 
                (user_type, fname, lname, username, email, phone, password, country, state, aadhar, passport, tax_payment)
                VALUES
                (:user_type, :fname, :lname, :username, :email, :phone, :password, :country, :state, :aadhar, :passport, :tax_payment)");

            $stmt->execute([
                ':user_type' => $userType,
                ':fname' => $fname,
                ':lname' => $lname,
                ':username' => $username,
                ':email' => $email,
                ':phone' => $phone,
                ':password' => $hashedPassword,
                ':country' => $country,
                ':state' => $country === 'India' ? $state : '',
                ':aadhar' => $aadharPath,
                ':passport' => $passportPath,
                ':tax_payment' => $taxPath
            ]);

            echo "<script>
                alert('Registration successful!');
                window.location.href = 'login.php';
            </script>";
            exit();

        } catch (PDOException $e) {
            echo "<p style='color:red;'>Error saving user: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Real Estate Portal</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-image: url('background.jpg');
            background-size: cover;
            background-position: center;
            font-family: Arial, sans-serif;
            color: white;
        }
        .container {
            width: 40%;
            margin: auto;
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
        }
        .nav-bar {
            background-color: #333;
            padding: 15px;
            text-align: center;
            font-size: 20px;
            color: white;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: none;
        }
        button {
            background-color: blue;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: darkblue;
        }
    </style>
</head>
<body>

<nav style="background-color: #333; padding: 15px;">
    <div class="nav-bar">Register</div>
    <a href="index.php" style="color: white; margin-right: 20px; text-decoration: none;">Home</a>
    <a href="about.php" style="color: white; margin-right: 20px; text-decoration: none;">About</a>
    <a href="contact.php" style="color: white; text-decoration: none;">Contact</a>
</nav>

<script>
    function toggleStateField() {
        var country = document.getElementById('country').value;
        var stateField = document.getElementById('stateField');
        var aadharField = document.getElementById('aadharField');
        var passportField = document.getElementById('passportField');

        if (country === "India") {
            stateField.style.display = 'block';
            aadharField.style.display = 'block';
            passportField.style.display = 'none';
        } else {
            stateField.style.display = 'none';
            aadharField.style.display = 'none';
            passportField.style.display = 'block';
        }
    }

    function validateForm() {
        var email = document.getElementById('email').value;
        if (!email.endsWith('@gmail.com')) {
            alert('Please enter a valid Gmail address ending with @gmail.com');
            return false;
        }
        return true;
    }
</script>
<section class="container">
    <h2>Create an Account</h2>
    <form action="register.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
        
        <!-- User Type with Placeholder -->
<div class="form-group">
    <label for="userType">User Type:</label>
    <select name="userType" id="userType" required>
        <option value="" disabled selected>Select User Type</option>
        <option value="customer" <?php echo (isset($_POST['userType']) && $_POST['userType'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
        <option value="tenant" <?php echo (isset($_POST['userType']) && $_POST['userType'] == 'tenant') ? 'selected' : ''; ?>>Tenant</option>
        <option value="owner" <?php echo (isset($_POST['userType']) && $_POST['userType'] == 'owner') ? 'selected' : ''; ?>>Owner</option>
    </select>
</div>

        <!-- Personal Information -->
        <div class="form-group">
            <label for="fname">First Name:</label>
            <input type="text" id="fname" name="fname" required value="<?php echo isset($_POST['fname']) ? $_POST['fname'] : ''; ?>">
        </div>

        <div class="form-group">
            <label for="lname">Last Name:</label>
            <input type="text" id="lname" name="lname" required value="<?php echo isset($_POST['lname']) ? $_POST['lname'] : ''; ?>">
        </div>

        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>">
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
        </div>

        <div class="form-group">
            <label for="phone">Phone:</label>
            <input type="tel" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>">
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required>
        </div>

        <!-- Country with Placeholder -->
<div class="form-group">
    <label for="country">Country:</label>
    <select id="country" name="country" onchange="toggleStateField()" required>
        <option value="" disabled selected>Select Country</option>
        <option value="India" selected>India</option>
        <option value="USA">USA</option>
        <option value="UK">UK</option>
        <option value="Canada">Canada</option>
        <option value="Australia">Australia</option>
        <option value="Germany">Germany</option>
        <option value="France">France</option>
        <option value="Other">Other</option>
    </select>
</div>

        <!-- Passport Field for non-Indian users -->
        <div class="form-group" id="passportField" style="display: none;">
            <label for="passport">Upload Passport (for non-Indian residents):</label>
            <input type="file" id="passport" name="passport" accept=".pdf, .jpg, .png">
        </div>

        <!-- Aadhar Field for Indian users -->
        <div class="form-group" id="aadharField">
            <label for="aadhar">Upload Aadhar Card:</label>
            <input type="file" id="aadhar" name="aadhar" accept=".pdf, .jpg, .png" required>
        </div>

        
        <!-- State with Placeholder -->
<div class="form-group" id="stateField" style="display: block;">
    <label for="state">State:</label>
    <select id="state" name="state" required>
        <option value="" disabled selected>Select State</option>
        <option value="Andhra Pradesh">Andhra Pradesh</option>
        <option value="Arunachal Pradesh">Arunachal Pradesh</option>
        <option value="Assam">Assam</option>
        <option value="Bihar">Bihar</option>
        <option value="Chhattisgarh">Chhattisgarh</option>
        <option value="Goa">Goa</option>
        <option value="Gujarat">Gujarat</option>
        <option value="Haryana">Haryana</option>
        <option value="Himachal Pradesh">Himachal Pradesh</option>
        <option value="Jharkhand">Jharkhand</option>
        <option value="Karnataka">Karnataka</option>
        <option value="Kerala">Kerala</option>
        <option value="Madhya Pradesh">Madhya Pradesh</option>
        <option value="Maharashtra">Maharashtra</option>
        <option value="Manipur">Manipur</option>
        <option value="Meghalaya">Meghalaya</option>
        <option value="Mizoram">Mizoram</option>
        <option value="Nagaland">Nagaland</option>
        <option value="Odisha">Odisha</option>
        <option value="Punjab">Punjab</option>
        <option value="Rajasthan">Rajasthan</option>
        <option value="Sikkim">Sikkim</option>
        <option value="Tamil Nadu">Tamil Nadu</option>
        <option value="Telangana">Telangana</option>
        <option value="Tripura">Tripura</option>
        <option value="Uttar Pradesh">Uttar Pradesh</option>
        <option value="Uttarakhand">Uttarakhand</option>
        <option value="West Bengal">West Bengal</option>
    </select>
</div>

            <div class="form-group">
                <label for="taxPayment">Upload Recent Tax Payment Slips:</label>
                <input type="file" id="taxPayment" name="taxPayment" accept=".pdf, .jpg, .png">
            </div>
        </div>

        <button type="submit">Register</button>
        <p>Already registered? <a href="login.php">Login here</a></p>
    </form>
</section>
</body>
</html>
