<?php
session_start();
include '../db/db.php';

// Ensure only Admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
}

// Fetch user scores with quiz info
$result = $conn->query("
    SELECT users.fullname, quizzes.title, user_answers.user_answer, user_answers.is_correct, user_answers.timestamp 
    FROM user_answers 
    JOIN users ON user_answers.user_id = users.id 
    JOIN quizzes ON user_answers.quiz_id = quizzes.id 
    ORDER BY user_answers.timestamp DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin_scores.css">
    <title>Admin View User Scores</title>
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
                    <span class="material-icons-sharp">dashboard</span>
                    <h3>Dashboard</h3>
                </a>
                <a href="admin_scoreboard.php">
                    <span class="material-icons-sharp">assessment</span>
                    <h3>Score Board</h3>
                </a>
                <a href="admin_add_quiz.php">
                    <span class="material-icons-sharp">add_circle</span>
                    <h3>Add Quiz</h3>
                </a>
                <a href="../logout.php">
                    <span class="material-icons-sharp">logout</span>
                    <h3>Logout</h3>
                </a>
            </div>
        </aside>

    <!-- Main Content -->
    <div class="main-content">
        <h1> User Scores</h1>
        <table>
            <tr>
                <th>User</th>
                <th>Quiz Title</th>
                <th>User Answer</th>
                <th>Result</th>
                <th>Date</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $result_text = $row['is_correct'] ? "<span class='correct'>✅ Correct</span>" : "<span class='wrong'>❌ Wrong</span>";
                    echo "<tr>
                        <td>{$row['fullname']}</td>
                        <td>{$row['title']}</td>
                        <td>{$row['user_answer']}</td>
                        <td>{$result_text}</td>
                        <td>{$row['timestamp']}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No scores available yet.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>
