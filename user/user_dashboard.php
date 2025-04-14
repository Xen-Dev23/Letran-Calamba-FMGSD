<?php
session_start();
include '../db/db.php';

// Ensure user is logged in as "User"
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "User") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info from the database
$query = "SELECT fullname, profile_picture, last_active FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$fullname = $user['fullname'];
$profile_picture = $user['profile_picture'] ?: '../assets/images/profile-placeholder.png';
// Determine online status (active within the last 5 minutes)
$is_online = (strtotime($user['last_active']) > time() - 300) ? true : false;

// Update last_active timestamp on page load
$update_query = "UPDATE users SET last_active = NOW() WHERE id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("i", $user_id);
$update_stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/user_dashboard.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <title>User Dashboard</title>
</head>
<body>
    <div class="container">
        <!-- Sidebar Section -->
        <aside>
            <div class="toggle">
                <div class="logo">
                    <img src="../assets/images/favicon.ico">
                    <h2>Dashboard<span class="danger">User</span></h2>
                </div>
                <div class="close" id="close-btn">
                    <span class="material-icons-sharp">close</span>
                </div>
            </div>

            <div class="sidebar">
                <a href="user_dashboard.php" class="active">
                    <img src="../assets/icons/dashboard.png" alt="Dashboard Icon">
                    <h3>Dashboard</h3>
                </a>
                <a href="user_modules.php">
                    <img src="../assets/icons/modules.png" alt="Modules Icon">
                    <h3>Modules</h3>
                </a>
                <a href="user_quiz.php">
                    <img src="../assets/icons/quiz.png" alt="Quiz Icon">
                    <h3>Take Quiz</h3>
                </a>
                <a href="user_result.php">
                    <img src="../assets/icons/assessment.png" alt="Results Icon">
                    <h3>View Results</h3>
                </a>
                <a href="user_accountsettings.php">
                    <img src="../assets/icons/settings.png" alt="Settings Icon">
                    <h3>Account Settings</h3>
                </a>
                <a href="../logout.php">
                    <img src="../assets/icons/logout.png" alt="Logout Icon">
                    <h3>Logout</h3>
                </a>
            </div>
        </aside>
        <!-- End of Sidebar Section -->

        <!-- Content Wrapper (Right Section + Main Content) -->
        <div class="content-wrapper">
            <!-- Right Section -->
            <div class="right-section">
                <div class="nav">
                    <button id="menu-btn">
                        <span class="material-icons-sharp">menu</span>
                    </button>
                </div>

                <!-- Search Bar Section -->
                <div class="search-container">
                    <input type="text" placeholder="Search..." />
                    <div class="profile">
                        <span class="profile-name"><?= htmlspecialchars($fullname) ?></span>
                        <div class="profile-pic">
                            <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture" style="width: 40px; height: 40px; border-radius: 50%;">
                            <div class="status-dot <?= $is_online ? '' : 'offline' ?>" id="status-dot"></div>
                        </div>
                        <!-- Profile Dropdown -->
                        <div class="profile-dropdown" id="profile-dropdown">
                            <div class="profile-info">
                                <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture">
                                <div class="profile-info-text">
                                    <p><?= htmlspecialchars($fullname) ?></p>
                                    <small>User</small>
                                </div>
                            </div>
                            <div class="profile-actions">
                                <a href="user_accountsettings.php">
                                    <span class="material-icons-sharp">settings</span>
                                    Account Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End of Right Section -->

            <!-- Main Content Section -->
            <main class="modules-container">
                <div class="welcome-section">
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</h1>
                    <p>Explore modules, take quizzes, and track your progress.</p>
                </div>
            </main>
        </div>
        <!-- End of Content Wrapper -->
    </div>

    <script>
        // Toggle menu script
        const menuBtn = document.getElementById('menu-btn');
        const sidebar = document.querySelector('.sidebar');
        const profile = document.querySelector('.profile');
        const profileDropdown = document.querySelector('#profile-dropdown');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Profile dropdown toggle
        profile.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!profile.contains(e.target)) {
                profileDropdown.style.display = 'none';
            }
        });
    </script>
</body>
</html>