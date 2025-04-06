<?php
session_start();
include '../db/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
}

// Get profile picture from session or use default
$profile_pic = isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture']) 
    ? $_SESSION['profile_picture'] 
    : '../assets/images/profile-placeholder.png';

// Handle actions
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'read' && isset($_GET['id'])) {
        $conn->query("UPDATE scores SET is_read = 1 WHERE id = " . (int)$_GET['id']);
        header("Location: admin_dashboard.php");
        exit();
    }
    if ($_GET['action'] == 'read_all') {
        $conn->query("UPDATE scores SET is_read = 1 WHERE is_read = 0");
        header("Location: admin_dashboard.php");
        exit();
    }
    if ($_GET['action'] == 'delete' && isset($_GET['id'])) {
        $conn->query("DELETE FROM scores WHERE id = " . (int)$_GET['id']);
        header("Location: admin_dashboard.php");
        exit();
    }
    if ($_GET['action'] == 'delete_all') {
        $conn->query("DELETE FROM scores");
        header("Location: admin_dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <title>Admin Dashboard</title>
</head>
<body>
    <div class="container">
        <!-- Sidebar Section -->
        <aside>
    <div class="toggle">
        <div class="logo">
            <img src="../assets/images/favicon.ico">
            <h2>Dashboard<span class="danger">Admin</span></h2>
        </div>
        <div class="close" id="close-btn">
            <span class="material-icons-sharp">close</span>
        </div>
    </div>

    <div class="sidebar">
        <a href="admin_dashboard.php" class="active">
            <img src="../assets/icons/dashboard.png" alt="Dashboard Icon">
            <h3>Dashboard</h3>
        </a>
        <a href="admin_users.php">
            <img src="../assets/icons/users.png" alt="Users Icon">
            <h3>Users</h3>
        </a>
        <a href="admin_scoreboard.php">
            <img src="../assets/icons/assessment.png" alt="Scoreboard Icon">
            <h3>User Score</h3>
        </a>
        <a href="admin_monitoring.php">
            <img src="../assets/icons/monitoring.png" alt="Monitoring Icon">
            <h3>Monitoring</h3>
        </a>
        <a href="admin_add_quiz.php">
            <img src="../assets/icons/quiz.png" alt="Add Quiz Icon">
            <h3>Add Quiz</h3>
        </a>
        <a href="admin_video_upload.php">
            <img src="../assets/icons/video_library.png" alt="Videos Icon">
            <h3>Training Videos</h3>
        </a>
        <a href="admin_video_list.php">
            <img src="../assets/icons/video_library.png" alt="Videos Icon">
            <h3>Video List</h3>
        </a>
        <a href="admin_accountsettings.php">
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
            <p>Manage quizzes, view scores, and oversee user activities.</p>
        </div>
        </main>
        <!-- End of Main Content -->

        <!-- Right Section -->
        <div class="right-section">
            <div class="nav">
                <button id="menu-btn">
                    <span class="material-icons-sharp">menu</span>
                </button>
                
                <!-- Notification Section -->
                <div class="notification">
                    <img src="../assets/icons/notification-icon.png" alt="Notifications">
                    <span class="badge" id="notification-count">0</span>
                    <div class="notification-dropdown" id="notification-dropdown">
                        <!-- Notifications will be dynamically loaded here -->
                    </div>
                </div>
                <!-- End of Notification Section -->

                <!-- Profile Section -->
                <div class="profile">
                    <div class="profile-photo">
                        <img src="<?php echo $profile_pic; ?>" alt="Profile" id="profile-pic">
                    </div>
                    <div class="profile-dropdown" id="profile-dropdown">
                        <div class="profile-info">
                            <p><b><?php echo $_SESSION['fullname']; ?></b></p>
                            <small>Admin</small>
                        </div>
                        <div class="profile-actions">
                            <a href="admin_accountsettings.php">
                                <span class="material-icons-sharp">settings</span>
                                Account Settings
                            </a>
                        </div>
                    </div>
                </div>
                <!-- End of Profile Section -->
            </div>
        </div>
        <!-- End of Right Section -->
    </div>

    <script>
        const profile = document.querySelector('.profile');
    const profileDropdown = document.querySelector('#profile-dropdown');

    profile.addEventListener('click', (e) => {
        e.stopPropagation();
        profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', (e) => {
        if (!profile.contains(e.target)) {
            profileDropdown.style.display = 'none';
        }
    });

    // Modified Notification Script
    let previousUnreadCount = 0;
    const notificationSound = new Audio('../assets/sounds/notification.mp3');
    
    // Add error handling and preload
    notificationSound.preload = 'auto';
    notificationSound.onerror = function() {
        console.error('Error loading audio file. Check if ../assets/sounds/notification.mp3 exists');
    };
    notificationSound.onloadeddata = function() {
        console.log('Audio file loaded successfully');
    };

    function playNotificationSound() {
        console.log('Attempting to play notification sound');
        notificationSound.currentTime = 0;
        const playPromise = notificationSound.play();
        
        if (playPromise !== undefined) {
            playPromise
                .then(() => {
                    console.log('Notification sound played successfully');
                })
                .catch(error => {
                    console.error('Playback failed:', error);
                    // Common fix for autoplay issues
                    if (error.name === 'NotAllowedError') {
                        console.log('Autoplay blocked. Sound will play after user interaction');
                    }
                });
        }
    }

    function updateNotifications() {
        fetch('get_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.unread_count > previousUnreadCount && previousUnreadCount !== 0) {
                    console.log('New notification detected, attempting to play sound');
                    playNotificationSound();
                }
                previousUnreadCount = data.unread_count;

                const notificationCount = document.getElementById('notification-count');
                notificationCount.textContent = data.unread_count;
                if (data.unread_count > 0) {
                    notificationCount.style.display = 'block';
                } else {
                    notificationCount.style.display = 'none';
                }

                const dropdown = document.getElementById('notification-dropdown');
                let html = '';
                
                if (data.notifications.length > 0) {
                    data.notifications.forEach(notif => {
                        html += `
                            <div class="notification-item ${notif.is_read ? '' : 'unread'}">
                                <div>
                                    ${notif.fullname} scored ${notif.score}
                                    <br>
                                    <small>${notif.timestamp}</small>
                                </div>
                                <div class="notification-actions">
                                    ${!notif.is_read ? `
                                        <a href="?action=read&id=${notif.id}" title="Mark as Read">
                                            <span class="material-icons-sharp">done</span>
                                        </a>
                                    ` : ''}
                                    <a href="?action=delete&id=${notif.id}" title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this notification?');">
                                        <span class="material-icons-sharp">delete</span>
                                    </a>
                                </div>
                            </div>
                        `;
                    });
                    html += `
                        <div class="read-all">
                            ${data.unread_count > 0 ? '<a href="?action=read_all">Mark All as Read</a>' : ''}
                            <a href="?action=delete_all" onclick="return confirm('Are you sure you want to delete all notifications?');">Delete All</a>
                        </div>
                    `;
                } else {
                    html = '<div class="notification-item">No notifications</div>';
                }
                
                dropdown.innerHTML = html;
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    // Initial update
    updateNotifications();
    setInterval(updateNotifications, 5000);

    // Test function
    window.testNotificationSound = function() {
        console.log('Testing notification sound');
        playNotificationSound();
    };
</script>
</body>
</html>