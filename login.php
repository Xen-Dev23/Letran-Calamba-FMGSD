<?php
include 'db/db.php';
session_start();

// Session timeout duration (e.g., 30 minutes)
$timeout_duration = 1800;

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
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
    $_SESSION['last_activity'] = time();

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
        $update_sql = "UPDATE users SET last_login = NOW(), is_online = 1, status = 'online' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $user['id']);
        $update_stmt->execute();
        $update_stmt->close();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['profile_picture'] = $user['profile_picture'] ?? '../assets/images/profile-placeholder.png';
        $_SESSION['last_activity'] = time();

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
    <link rel="icon" type="image/png" href="assets/images/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <title>EHS | Log In</title>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl flex flex-col md:flex-row overflow-hidden">
        <!-- Left Side: School Image -->
        <div class="hidden md:block md:w-1/2 bg-cover bg-center" style="background-image: url('https://the-post-assets.sgp1.digitaloceanspaces.com/2020/08/LETRAN-15.jpg')"></div>
        
        <!-- Right Side: Login Form -->
        <div class="w-full md:w-1/2 p-8 flex flex-col justify-center">
            <div class="flex justify-center mb-6">
                <img src="assets/images/icon.png" alt="School Logo" class="h-16 w-16 object-contain">
            </div>
            <h1 class="text-4xl font-extrabold text-center text-gray-800 mb-2">Letran Calamba</h1>
            <p class="text-lg text-center text-gray-600 mb-6">Log in to your student portal</p>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6 text-center"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['message']) && $_GET['message'] == 'session_expired'): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6 text-center">Your session has expired. Please log in again.</div>
            <?php endif; ?>

            <form class="space-y-6" method="POST">
                <div>
                    <input type="email" name="email" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400" placeholder="Email" required>
                </div>
                <div class="relative">
                    <input type="password" name="password" id="password" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400" placeholder="Password" required>
                    <img src="assets/icons/eye-off.png" alt="Show/Hide Password" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 cursor-pointer" id="togglePassword">
                </div>
                <div class="text-right">
                    <a href="forgot_password.php" class="text-sm text-blue-600 hover:underline">Forgot Password?</a>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition duration-200 font-semibold">Log In</button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Don't have an account? <a href="register.php" class="text-blue-600 hover:underline">Register here</a>
            </p>
        </div>
    </div>
    <script src="./js/tooglepassword.js"></script>
</body>
</html>
