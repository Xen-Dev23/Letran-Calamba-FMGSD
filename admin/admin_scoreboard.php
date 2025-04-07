<?php
session_start();
include '../db/db.php';

// Ensure only Admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
}

// Fetch user scores with overall quiz results, attempts, percentage, and status
$result = $conn->query("
    SELECT 
        users.fullname, 
        users.profile_picture,
        quizzes.title, 
        COUNT(results.id) AS total_questions, 
        SUM(results.is_correct) AS correct_answers, 
        COUNT(DISTINCT DATE(results.timestamp)) AS attempts, 
        MAX(results.timestamp) AS last_attempt,
        ROUND((SUM(results.is_correct) / COUNT(results.id)) * 100, 2) AS percentage
    FROM results 
    JOIN users ON results.user_id = users.id 
    JOIN quizzes ON results.quiz_id = quizzes.id 
    GROUP BY users.id, quizzes.id 
    ORDER BY last_attempt DESC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin_scoreboard.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <title>Admin - User Scores</title>
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
                <a href="admin_scoreboard.php" class="active">
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
                    <img src="../assets/icons/video_library.png" alt="Video List Icon">
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

        <!-- Main Content -->
        <main class="users-container">
            <h1>User Scores</h1>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Profile Picture</th>
                        <th>User</th>
                        <th>Quiz Title</th>
                        <th>Overall Score</th>
                        <th>Percentage</th>
                        <th>Status</th>
                        <th>Attempts</th>
                        <th>Last Attempt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $overall_score = "{$row['correct_answers']}/{$row['total_questions']}";
                            $status = ($row['percentage'] >= 70) ? "<span class='status-pass'>Pass</span>" : "<span class='status-fail'>Fail</span>";
                            $profile_pic = $row['profile_picture'] ? htmlspecialchars($row['profile_picture']) : '../assets/images/profile-placeholder.png';
                            $last_attempt = $row['last_attempt'] ? date('Y-m-d h:i A', strtotime($row['last_attempt'])) : 'Never';
                            echo "<tr>
                                <td><img src='$profile_pic' alt='Profile Picture' class='profile-pic' style='width: 50px; height: 50px; border-radius: 50%; object-fit: cover;'></td>
                                <td>" . htmlspecialchars($row['fullname']) . "</td>
                                <td>" . htmlspecialchars($row['title']) . "</td>
                                <td>" . htmlspecialchars($overall_score) . "</td>
                                <td>" . htmlspecialchars($row['percentage']) . "%</td>
                                <td>" . $status . "</td>
                                <td>" . htmlspecialchars($row['attempts']) . "</td>
                                <td>" . htmlspecialchars($last_attempt) . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No scores available yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
        <!-- End of Main Content -->
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>