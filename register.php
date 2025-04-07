<?php
include 'db/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $fullname = filter_var($_POST['fullname'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Basic validation
    if (empty($fullname) || empty($email) || empty($password) || empty($role)) {
        echo "Error: All fields are required.";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Error: Invalid email format.";
        exit();
    }

    // Check if email already exists
    $sql_check = "SELECT email FROM users WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        echo "Error: Email already exists. Please use a different email.";
        exit();
    }
    $stmt_check->close();

    // Hash the password
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // Set a default profile picture path (since there's no file upload in the form)
    $profile_picture = "../assets/images/profile-placeholder.png";

    // Insert the user into the database
    $sql = "INSERT INTO users (fullname, email, password, role, profile_picture) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $fullname, $email, $password_hashed, $role, $profile_picture);

    if ($stmt->execute()) {
        // Store the fullname in session to pass it to the success page
        $_SESSION['fullname'] = $fullname;
        // Redirect to the success page
        header("Location: registration_success.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/images/favicon.ico">
    <link rel="stylesheet" href="./css/register.css">
    <title>EHS - Register</title>
    <style>
        .password-container {
            position: relative;
            width: 100%;
        }
        .password-container input {
            width: 100%;
            padding-right: 40px;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            width: 20px;
            height: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="assets/images/icon.png" alt="School Logo" class="school-logo">    
        <p class="title">Register</p>
        <form class="form" method="POST">
            <input type="text" name="fullname" class="input" placeholder="User Name" required>
            <input type="email" name="email" class="input" placeholder="Email" required>
            <div class="password-container">
                <input type="password" name="password" class="input" id="password" placeholder="Password" required>
                <img src="assets/icons/eye-off.png" alt="Show/Hide Password" class="toggle-password" id="togglePassword">
            </div>
            <select name="role" class="input">
                <option value="User">User</option>
                <option value="Admin">Admin</option>
            </select>
            <button class="form-btn" type="submit">Sign Up</button>
        </form>
        <p class="sign-up-label">
            Already have an account? <a href="login.php" class="sign-up-link">Log in</a>
        </p>
    </div>
    <script src="./js/tooglepassword.js"></script>
</body>
</html>