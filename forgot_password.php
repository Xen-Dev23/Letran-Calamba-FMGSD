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
    <link rel="icon" type="image/png" href="assets/images/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <title>EHS | Forgot Password</title>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl flex flex-col md:flex-row overflow-hidden">
        <!-- Left Side: School Image -->
        <div class="hidden md:block md:w-1/2 bg-cover bg-center" style="background-image: url('https://the-post-assets.sgp1.digitaloceanspaces.com/2020/08/LETRAN-15.jpg')"></div>
        
        <!-- Right Side: Forgot Password Form -->
        <div class="w-full md:w-1/2 p-8 flex flex-col justify-center">
            <div class="flex justify-center mb-6">
                <img src="assets/images/icon.png" alt="School Logo" class="h-16 w-16 object-contain">
            </div>
            <h1 class="text-4xl font-extrabold text-center text-gray-800 mb-2">Letran Calamba</h1>
            <p class="text-lg text-center text-gray-600 mb-6">Reset your password</p>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6 text-center"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 text-center"><?php echo $success; ?></div>
            <?php endif; ?>

            <form class="space-y-6" method="POST" action="forgot_password.php">
                <div>
                    <input type="email" name="email" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400" placeholder="Enter your email" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition duration-200 font-semibold">Send Reset Link</button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Back to <a href="login.php" class="text-blue-600 hover:underline">Login</a>
            </p>
        </div>
    </div>
</body>
</html>