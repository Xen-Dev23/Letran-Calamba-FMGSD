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
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email already exists
        $sql_check = "SELECT email FROM users WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already exists. Please use a different email.";
        } else {
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
                $error = "Error: " . $stmt->error;
            }
        }
        $stmt_check->close();
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <title>EHS | Register</title>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl flex flex-col md:flex-row overflow-hidden">
        <!-- Left Side: School Image -->
        <div class="hidden md:block md:w-1/2 bg-cover bg-center" style="background-image: url('https://the-post-assets.sgp1.digitaloceanspaces.com/2020/08/LETRAN-15.jpg')"></div>
        
        <!-- Right Side: Register Form -->
        <div class="w-full md:w-1/2 p-8 flex flex-col justify-center">
            <div class="flex justify-center mb-6">
                <img src="assets/images/icon.png" alt="School Logo" class="h-16 w-16 object-contain">
            </div>
            <h1 class="text-4xl font-extrabold text-center text-gray-800 mb-2">Letran Calamba</h1>
            <p class="text-lg text-center text-gray-600 mb-6">Create your student portal account</p>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6 text-center"><?php echo $error; ?></div>
            <?php endif; ?>

            <form class="space-y-6" method="POST">
                <div>
                    <input type="text" name="fullname" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400" placeholder="Full Name" required>
                </div>
                <div>
                    <input type="email" name="email" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400" placeholder="Email" required>
                </div>
                <div class="relative">
                    <input type="password" name="password" id="password" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400" placeholder="Password" required>
                    <img src="assets/icons/eye-off.png" alt="Show/Hide Password" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 cursor-pointer" id="togglePassword">
                </div>
                <div>
                    <select name="role" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400">
                        <option value="User">User</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition duration-200 font-semibold">Sign Up</button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Log in here</a>
            </p>
        </div>
    </div>
    <script src="./js/tooglepassword.js"></script>
</body>
</html>