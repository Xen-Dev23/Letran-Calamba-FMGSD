<?php
include 'db/db.php';
session_start();

// Session timeout duration (e.g., 30 minutes)
$timeout_duration = 1800;

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // Check for session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
        // Session has expired, update is_online and status
        $update_sql = "UPDATE users SET is_online = 0, status = 'offline' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $_SESSION['user_id']);
        $update_stmt->execute();
        $update_stmt->close();

        session_unset();
        session_destroy();
        header("Location: login.php?message=session_expired");
        exit();
    }
    // Update last activity time
    $_SESSION['last_activity'] = time();

    // Redirect based on role (if already logged in)
    if ($_SESSION['role'] == "Admin") {
        header("Location: admin/admin_dashboard.php");
    } else {
        header("Location: user/user_dashboard.php");
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Update last_login, is_online, and status fields
        $update_sql = "UPDATE users SET last_login = NOW(), is_online = 1, status = 'online' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $user['id']);
        $update_stmt->execute();
        $update_stmt->close();

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['profile_picture'] = $user['profile_picture'] ?? '../assets/images/profile-placeholder.png';
        $_SESSION['last_activity'] = time(); // Set last activity time

        // Redirect based on role
        if ($user['role'] == "Admin") {
            header("Location: admin/admin_dashboard.php");
        } else {
            header("Location: user/user_dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid email or password.";
    }

    $stmt->close();
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
    <title>EHS | Log In</title>
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
        /* Added error message styling */
        .error-message {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="assets/images/icon.png" alt="School Logo" class="school-logo">
        <p class="title">Login</p>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['message']) && $_GET['message'] == 'session_expired'): ?>
            <div class="error-message">Your session has expired. Please log in again.</div>
        <?php endif; ?>
        <form class="form" method="POST">
            <input type="email" name="email" class="input" placeholder="Email" required>
            <div class="password-container">
                <input type="password" name="password" class="input" id="password" placeholder="Password" required>
                <img src="assets/icons/eye-off.png" alt="Show/Hide Password" class="toggle-password" id="togglePassword">
            </div>
            <p class="page-link">
                <a href="forgot_password.php" class="page-link-label">Forgot Password?</a>
            </p>
            <button class="form-btn" type="submit">Log in</button>
        </form>
        <p class="sign-up-label">
            Don't have an account? <a href="register.php" class="sign-up-link">Register here</a>
        </p>
    </div>
    <script src="./js/tooglepassword.js"></script>
</body>
</html>