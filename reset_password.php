<?php
include 'db/db.php';
session_start();

// Configuration
const PASSWORD_RESET_EXPIRY_SECONDS = 3600; // 1 hour in seconds
const LOG_DIR = 'logs'; // Logs directory
const LOG_FILE = LOG_DIR . '/reset_attempts.log'; // Log file path

// Ensure logs directory exists and is writable
if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0755, true); // Create logs directory if it doesn't exist
}
if (!file_exists(LOG_FILE)) {
    touch(LOG_FILE); // Create log file if it doesn't exist
    chmod(LOG_FILE, 0644); // Set appropriate permissions
}

// Function to log reset attempts
function logResetAttempt($email, $token, $status, $message) {
    if (is_writable(LOG_FILE)) {
        $logMessage = date('Y-m-d H:i:s') . " | Email: $email | Token: $token | Status: $status | Message: $message\n";
        file_put_contents(LOG_FILE, $logMessage, FILE_APPEND);
    } else {
        error_log("Cannot write to log file: " . LOG_FILE); // Log to PHP error log if file is not writable
    }
}

$error = '';
$success = '';

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = filter_var($_GET['email'], FILTER_SANITIZE_EMAIL);
    $token = filter_var($_GET['token'], FILTER_SANITIZE_STRING);

    // Verify token
    $sql = "SELECT * FROM password_resets WHERE email = ? AND token = ? AND created_at >= ? AND used = 0";
    $stmt = $conn->prepare($sql);
    $expiryTime = date('Y-m-d H:i:s', time() - PASSWORD_RESET_EXPIRY_SECONDS);
    $stmt->bind_param("sss", $email, $token, $expiryTime);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset = $result->fetch_assoc();
    $stmt->close();

    if (!$reset) {
        $error = "Invalid, expired, or already used reset link.";
        logResetAttempt($email, $token, 'FAILURE', $error);
    } else {
        logResetAttempt($email, $token, 'SUCCESS', 'Valid reset link accessed.');
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password']) && isset($_POST['email']) && isset($_POST['token'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $token = filter_var($_POST['token'], FILTER_SANITIZE_STRING);
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // Verify token again
    $sql = "SELECT * FROM password_resets WHERE email = ? AND token = ? AND created_at >= ? AND used = 0";
    $stmt = $conn->prepare($sql);
    $expiryTime = date('Y-m-d H:i:s', time() - PASSWORD_RESET_EXPIRY_SECONDS);
    $stmt->bind_param("sss", $email, $token, $expiryTime);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset = $result->fetch_assoc();
    $stmt->close();

    if ($reset) {
        // Update the user's password
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_password, $email);
        $stmt->execute();
        $stmt->close();

        // Mark token as used
        $sql = "UPDATE password_resets SET used = 1 WHERE email = ? AND token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $stmt->close();

        $success = "Your password has been reset successfully.";
        logResetAttempt($email, $token, 'SUCCESS', 'Password reset successfully.');
    } else {
        $error = "Invalid, expired, or already used reset link.";
        logResetAttempt($email, $token, 'FAILURE', $error);
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
        .password-container {
            position: relative;
            width: 100%;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            width: 20px;
            height: 20px;
        }
    </style>
    <title>EHS | Reset Password</title>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl flex flex-col md:flex-row overflow-hidden">
        <!-- Left Side: School Image -->
        <div class="hidden md:block md:w-1/2 bg-cover bg-center" style="background-image: url('https://the-post-assets.sgp1.digitaloceanspaces.com/2020/08/LETRAN-15.jpg')"></div>
        
        <!-- Right Side: Reset Password Form -->
        <div class="w-full md:w-1/2 p-8 flex flex-col justify-center">
            <div class="flex justify-center mb-6">
                <img src="assets/images/icon.png" alt="School Logo" class="h-16 w-16 object-contain">
            </div>
            <h1 class="text-4xl font-extrabold text-center text-gray-800 mb-2">Letran Calamba</h1>
            <p class="text-lg text-center text-gray-600 mb-6">Reset your password</p>

            <?php if ($error): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6 text-center"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 text-center"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (!$error && !$success): ?>
                <form class="space-y-6" method="POST">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="password-container">
                        <input type="password" name="new_password" id="password" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400" placeholder="New Password" required>
                        <img src="assets/icons/eye-off.png" alt="Show/Hide Password" class="toggle-password" id="togglePassword">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition duration-200 font-semibold">Reset Password</button>
                </form>
            <?php endif; ?>

            <p class="mt-6 text-center text-sm text-gray-600">
                Back to <a href="login.php" class="text-blue-600 hover:underline">Login</a>
            </p>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.src = type === 'password' ? 'assets/icons/eye-off.png' : 'assets/icons/eye-on.png';
        });
    </script>
</body>
</html>