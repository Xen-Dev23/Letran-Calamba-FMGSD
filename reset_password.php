<?php
include 'db/db.php';
session_start();

$error = '';
$success = '';

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    // Verify token
    $sql = "SELECT * FROM password_resets WHERE email = ? AND token = ? AND created_at >= NOW() - INTERVAL 1 HOUR";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset = $result->fetch_assoc();
    $stmt->close();

    if (!$reset) {
        $error = "Invalid or expired reset link.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password']) && isset($_POST['email']) && isset($_POST['token'])) {
    $email = $_POST['email'];
    $token = $_POST['token'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // Verify token again
    $sql = "SELECT * FROM password_resets WHERE email = ? AND token = ? AND created_at >= NOW() - INTERVAL 1 HOUR";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $token);
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

        // Delete the reset token
        $sql = "DELETE FROM password_resets WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        $success = "Your password has been reset successfully.";
    } else {
        $error = "Invalid or expired reset link.";
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
    <title>EHS | Reset Password</title>
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
        <p class="title">Reset Password</p>
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (!$error && !$success): ?>
            <form class="form" method="POST">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="password-container">
                    <input type="password" name="new_password" class="input" id="password" placeholder="New Password" required>
                    <img src="assets/icons/eye-off.png" alt="Show/Hide Password" class="toggle-password" id="togglePassword">
                </div>
                <button class="form-btn" type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
        <p class="sign-up-label">
            Back to <a href="login.php" class="sign-up-link">Login</a>
        </p>
    </div>
    <script src="./js/tooglepassword.js"></script>
</body>
</html>