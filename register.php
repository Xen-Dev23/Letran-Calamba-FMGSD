<?php
include 'db/db.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // If using Composer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = filter_var($_POST['fullname'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $role = $_POST['role'];

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
    $profile_picture = "../assets/images/profile-placeholder.png";

    $sql = "INSERT INTO users (fullname, email, password, role, profile_picture) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $fullname, $email, $password_hashed, $role, $profile_picture);

    if ($stmt->execute()) {
        $_SESSION['fullname'] = $fullname;

        // Send email
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'opulenciaandrei23@gmail.com'; // Replace with your Gmail
            $mail->Password = 'pkou mbww kqgc hgrh';   // Replace with your App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('yourgmail@gmail.com', 'EHS Registration');
            $mail->addAddress($email, $fullname);

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to EHS!';
            $mail->Body    = "
                <h2>Hello, $fullname!</h2>
                <p>Thank you for registering at EHS.</p>
                <p>You can now log in using your credentials.</p>
                <p><strong>Your Email:</strong> $email</p>
                <p>For your security, never share your password with anyone.</p>
                <p>If you have any questions, feel free to contact us at 
                   <a href='mailto:opulenciaandrei23@gmail.com'>opulenciaandrei23@gmail.com</a>,
                   <a href='mailto:oliveros.sebastiencarl@gmail.com'>oliveros.sebastiencarl@gmail.com</a>,
                </p>
                <br><p>- EHS Team</p>
                <hr>
                <p style='font-size:12px;color:#888;'>This is an automated email. Please do not reply directly to this message.</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }

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
    <title>EHS | Register</title>
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