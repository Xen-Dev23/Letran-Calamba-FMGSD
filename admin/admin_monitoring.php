<?php
session_start();
include '../db/db.php';

// Ensure only Admins can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
}

// Get user engagement data (excluding Admins)
$engagement_data = $conn->query("
    SELECT 
        u.fullname,
        COUNT(s.id) as attempts,
        COALESCE(AVG(s.score), 0) as avg_score,
        MAX(s.timestamp) as last_attempt,
        COUNT(DISTINCT DATE(s.timestamp)) as active_days
    FROM users u
    LEFT JOIN scores s ON s.user_id = u.id
    WHERE u.role != 'Admin'
    GROUP BY u.id, u.fullname
    ORDER BY last_attempt DESC
");

if (!$engagement_data) {
    $error_message = "Error fetching data: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin_monitoring.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <title>Admin - Monitoring</title>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside>
            <div class="toggle">
                <div class="logo">
                    <img src="../assets/images/favicon.ico" alt="Logo">
                    <h2>Dashboard<span class="danger">Admin</span></h2>
                </div>
                <div class="close" id="close-btn">
                    <span class="material-icons-sharp">close</span>
                </div>
            </div>
            <div class="sidebar">
                <a href="admin_dashboard.php">
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
                <a href="admin_monitoring.php"  class="active">
                    <img src="../assets/icons/monitoring.png" alt="Monitoring Icon">
                    <h3>Monitoring</h3>
                </a>
                <a href="admin_video_upload.php">
                    <img src="../assets/icons/video_library.png" alt="Videos Icon">
                    <h3>Training Videos</h3>
                </a>
                <a href="module_list.php">
                    <img src="../assets/icons/video_library.png" alt="Videos Icon">
                    <h3>Module List</h3>
                </a>
                <a href="../logout.php">
                    <img src="../assets/icons/logout.png" alt="Logout Icon">
                    <h3>Logout</h3>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main>
            <h1>User Engagement Tracking</h1>

            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php elseif ($engagement_data->num_rows == 0): ?>
                <div class="no-data">No engagement data available yet for non-admin users.</div>
            <?php else: ?>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Attempts</th>
                            <th>Avg Score</th>
                            <th>Active Days</th>
                            <th>Last Attempt</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $engagement_data->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><?php echo $row['attempts']; ?></td>
                            <td><?php echo round($row['avg_score'], 1); ?>%</td>
                            <td><?php echo $row['active_days']; ?></td>
                            <td>
                                <?php 
                                echo ($row['last_attempt']) 
                                    ? date('M d, Y', strtotime($row['last_attempt'])) 
                                    : "No attempts yet";
                                ?>
                            </td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?php echo min(100, $row['avg_score']); ?>%"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Sidebar toggle functionality
        const closeBtn = document.querySelector('#close-btn');
        const sidebar = document.querySelector('aside');
        
        closeBtn.addEventListener('click', () => {
            sidebar.style.display = 'none';
        });

        // Optional: Add a way to show sidebar again (if needed)
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.style.display = 'block';
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>