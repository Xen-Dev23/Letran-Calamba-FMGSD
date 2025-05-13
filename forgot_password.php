<?php
include 'db/db.php';
require 'vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists in the database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour

        // Store the token in the database (using password_resets table)
        $sql = "INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE token = ?, created_at = NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $email, $token, $token);
        $stmt->execute();
        $stmt->close();

        // Send email with reset link using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Update with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'opulenciaandrei23@gmail.com'; // Your email
            $mail->Password = 'pkou mbww kqgc hgrh'; // Your app-specific password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('your_email@gmail.com', 'EHS System');
            $mail->addAddress($email);
            $mail->addReplyTo('your_email@gmail.com', 'EHS System Support');

            // Content
            $reset_link = "http://localhost:8000/reset_password.php?email=" . urlencode($email) . "&token=" . $token;
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request for EHS System';
            $mail->Body = "
                <h2>Password Reset Request</h2>
                <p>Dear user,</p>
                <p>We received a request to reset your password for the EHS System.</p>
                <p>Click the link below to reset your password:</p>
                <p><a href='$reset_link'>Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request a password reset, please ignore this email.</p>
                <p>Best regards,<br>EHS System Team</p>
            ";
            $mail->AltBody = "Dear user, we received a request to reset your password for the EHS System. Click the link to reset your password: $reset_link. This link will expire in 1 hour. If you did not request a password reset, please ignore this email. Best regards, EHS System Team";

            $mail->send();
            $success = "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            $error = "Failed to send email. Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/png" href="assets/images/favicon.ico">
    <link rel="stylesheet" href="./css/login.css">
    <title>EHS | Forgot Password</title>
    <style>
        .error-message, .success-message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }
        .error-message {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .success-message {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="assets/images/icon.png" alt="School Logo" class="school-logo">
        <p class="title">Forgot Password</p>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        <form class="form" method="POST" action="forgot_password.php">
            <input type="email" name="email" class="input" placeholder="Enter your email" required>
            <button class="form-btn" type="submit">Send Reset Link</button>
        </form>
        <p class="sign-up-label">
            Back to <a href="login.php" class="sign-up-link">Login</a>
        </p>
    </div>
</body>
</html>