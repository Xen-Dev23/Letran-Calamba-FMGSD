<?php
session_start();
include '../db/db.php';

// Ensure user is logged in as "User"
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "User") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch detailed quiz results
$results_query = $conn->query("
    SELECT q.title, q.question, q.correct_option, r.user_answer, r.is_correct 
    FROM results r 
    JOIN quizzes q ON r.quiz_id = q.id 
    WHERE r.user_id = $user_id
    ORDER BY r.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <title>Quiz Results</title>
    <style>
        .quiz-results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .quiz-results-table th, .quiz-results-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .quiz-results-table th {
            background:rgb(218, 72, 72);
        }
        .correct { color: green; }
        .wrong { color: red; }

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
                <a href="user_dashboard.php">
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
                <a href="user_result.php" class="active">
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
        <main>
            <h1>Your Quiz Results</h1>
            <?php if ($results_query->num_rows > 0): ?>
                <table class="quiz-results-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Question</th>
                            <th>Your Answer</th>
                            <th>Correct Answer</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($result = $results_query->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $result['title']; ?></td>
                                <td><?php echo $result['question']; ?></td>
                                <td><?php echo $result['user_answer']; ?></td>
                                <td><?php echo $result['correct_option']; ?></td>
                                <td>
                                    <?php 
                                        if ($result['is_correct']) {
                                            echo "<span class='correct'>✅ Correct</span>";
                                        } else {
                                            echo "<span class='wrong'>❌ Wrong</span>";
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No quiz results yet. Take a quiz now! 🎯</p>
            <?php endif; ?>
        </main>
        <!-- End of Main Content -->
    </div>

    <!-- Optional: Add JavaScript for sidebar toggle -->
    <script>
        const closeBtn = document.getElementById('close-btn');
        const sidebar = document.querySelector('aside');
        
        closeBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    </script>
</body>
</html>