<?php
session_start();
include '../db/db.php';

// Ensure user is logged in and has the "User" role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "User") {
    header("Location: login.php");
    exit();
}

// Fetch quizzes from the database
$result = $conn->query("SELECT * FROM quizzes ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css"> <!-- Use same CSS as admin/user dashboard -->
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <title>Take a Quiz</title>
    <style>
        .quiz-card {
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .quiz-card h3 {
            margin: 0 0 10px;
            color: #333;
        }
        .quiz-option {
            display: block;
            margin: 5px 0;
        }
        .choice-letter {
            font-weight: bold;
            margin-right: 5px;
        }
        .submit-all {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .submit-all:hover {
            background: #0056b3;
        }
        .no-quizzes {
            color: #666;
            font-style: italic;
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
                <a href="user_dashboard.php">
                    <img src="../assets/icons/dashboard.png" alt="Dashboard Icon">
                    <h3>Dashboard</h3>
                </a>
                <a href="user_modules.php">
                    <img src="../assets/icons/modules.png" alt="Scoreboard Icon">
                    <h3>Modules</h3>
                </a>
                <a href="user_quiz.php" class="active">
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
        <main>
            <h1>Take a Quiz, <?php echo $_SESSION['fullname']; ?>!</h1>

            <!-- Multiple Quizzes Form -->
            <form method="POST" action="submit_quiz.php">
                <?php
                if ($result->num_rows > 0) {
                    while ($quiz = $result->fetch_assoc()) {
                        echo "<div class='quiz-card'>";
                        echo "<h3>{$quiz['title']}</h3>";
                        echo "<p>{$quiz['question']}</p>";
                        echo "<input type='hidden' name='quiz_ids[]' value='{$quiz['id']}'>";

                        // Display answer options with letters
                        $choices = [
                            'A' => $quiz['option_a'],
                            'B' => $quiz['option_b'],
                            'C' => $quiz['option_c'],
                            'D' => $quiz['option_d']
                        ];

                        foreach ($choices as $letter => $choice) {
                            echo "<label class='quiz-option'>";
                            echo "<input type='radio' name='answers[{$quiz['id']}]' value='{$letter}' required>";
                            echo "<span class='choice-letter'>{$letter}.</span> {$choice}";
                            echo "</label><br>";
                        }

                        echo "</div>";
                    }

                    // Submit All Button
                    echo "<button type='submit' class='submit-all'>Submit</button>";
                } else {
                    echo "<p class='no-quizzes'>🚫 No quizzes available right now.</p>";
                }
                ?>
            </form>
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