<?php
session_start();
include '../db/db.php';

// Ensure user is logged in as "User"
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "User") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get profile picture from session or use default
$profile_pic = isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture']) 
    ? $_SESSION['profile_picture'] 
    : '../assets/images/profile-placeholder.png';

// Fetch latest score
$score_result = $conn->query("SELECT score FROM scores WHERE user_id = $user_id ORDER BY timestamp DESC LIMIT 1");
$score = $score_result->fetch_assoc();
$latest_score = $score ? $score['score'] : "No score yet";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <title>User Dashboard</title>
    <style>
        .modules-container {
            margin: 2rem;
            max-width: 1200px;
        }
        .welcome-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .right-section {
            padding: 1rem;
        }
        .nav {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 1rem;
        }
        .profile {
            position: relative;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .profile-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            margin-left: 15px;
        }
        .profile-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 50px;
            background: white;
            min-width: 200px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            border-radius: 5px;
            z-index: 1000;
        }
        .profile-info {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }
        .profile-info p {
            margin: 0;
            color: #333;
        }
        .profile-info small {
            color: #777;
        }
        .profile-actions {
            padding: 5px 0;
        }
        .profile-actions a {
            display: flex;
            align-items: center;
            padding: 8px 15px;
            text-decoration: none;
            color: #666;
        }
        .profile-actions a:hover {
            background: #f5f5f5;
            color: #000;
        }
        .profile-actions span {
            margin-right: 10px;
        }

        /* Png Image for dashboard icon */
        .sidebar img {
        width: 24px; /* Adjust size as needed */
        height: 24px;
        margin-right: 10px; /* Spacing between icon and text */
        vertical-align: middle;
        }
    </style>
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
                    <img src="../assets/icons/modules.png" alt="Scoreboard Icon">
                    <h3>Modules</h3>
                </a>
                <a href="user_quiz.php">
                    <img src="../assets/icons/quiz.png" alt="Scoreboard Icon">
                    <h3>Take Quiz</h3>
                </a>
                <a href="user_result.php">
                    <img src="../assets/icons/assessment.png" alt="Scoreboard Icon">
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

        <!-- Main Content -->
        <main class="modules-container">
            <div class="welcome-section">
                <h1>Welcome, <?php echo $_SESSION['fullname']; ?>!</h1>
            </div>
        </main>
        <!-- End of Main Content -->

        <!-- Right Section -->
        <div class="right-section">
            <div class="nav">
                <div class="profile">
                    <div class="profile-photo">
                        <img src="<?php echo $profile_pic; ?>" alt="Profile" id="profile-pic">
                    </div>
                    <div class="profile-dropdown" id="profile-dropdown">
                        <div class="profile-info">
                            <p><b><?php echo $_SESSION['fullname']; ?></b></p>
                            <small>User</small>
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
    </div>

    <script>
        // Sidebar toggle
        const closeBtn = document.getElementById('close-btn');
        const sidebar = document.querySelector('aside');
        
        closeBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Profile Dropdown Toggle on Click
        const profile = document.querySelector('.profile');
        const profileDropdown = document.querySelector('#profile-dropdown');

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