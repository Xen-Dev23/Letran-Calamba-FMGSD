<?php
session_start();
include '../db/db.php';

// Ensure only Admins can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
}

// Handle Add Quiz form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_quiz'])) {
    $quiz_title = $conn->real_escape_string($_POST['quiz_title']);
    $question = $conn->real_escape_string($_POST['question']);
    $option_a = $conn->real_escape_string($_POST['option_a']);
    $option_b = $conn->real_escape_string($_POST['option_b']);
    $option_c = $conn->real_escape_string($_POST['option_c']);
    $option_d = $conn->real_escape_string($_POST['option_d']);
    $correct_option = $conn->real_escape_string($_POST['correct_option']);

    $sql = "INSERT INTO quizzes (title, question, option_a, option_b, option_c, option_d, correct_option) 
            VALUES ('$quiz_title', '$question', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_option')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "✅ Quiz added successfully!";
    } else {
        $_SESSION['message'] = "❌ Error adding quiz: " . $conn->error;
    }

    header("Location: admin_add_quiz.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin_add_quiz.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <title>Admin - Add Quiz</title>
</head>
<body>
    <div class="container">
        <!-- Sidebar (Unchanged) -->
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
                <a href="admin_monitoring.php">
                    <img src="../assets/icons/monitoring.png" alt="Monitoring Icon">
                    <h3>Monitoring</h3>
                </a>
                <a href="admin_add_quiz.php" class="active">
                    <img src="../assets/icons/quiz.png" alt="Add Quiz Icon">
                    <h3>Add Quiz</h3>
                </a>
                <a href="admin_video_upload.php">
                    <img src="../assets/icons/video_library.png" alt="Videos Icon">
                    <h3>Training Videos</h3>
                </a>
                <a href="module_list.php">
                    <img src="../assets/icons/video_library.png" alt="Video List Icon">
                    <h3>Module List</h3>
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
        <!-- Main Content (Redesigned Quiz Form Only) -->
        <main>
            <div class="quiz-header">
                <h1>Add New Quiz</h1>
                <?php if (isset($_SESSION['message'])): ?>
                    <p class="message <?php echo strpos($_SESSION['message'], 'Error') !== false ? 'error' : 'success'; ?>">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    </p>
                <?php endif; ?>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="quiz-form">
                <div class="form-group">
                    <label for="quiz_title">Quiz Title</label>
                    <input type="text" id="quiz_title" name="quiz_title" placeholder="Enter quiz title" required>
                </div>

                <div class="form-group">
                    <label for="question">Question</label>
                    <input type="text" id="question" name="question" placeholder="Enter the question" required>
                </div>

                <div class="form-group">
                    <label for="option_a">Option A</label>
                    <input type="text" id="option_a" name="option_a" placeholder="Enter option A" required>
                </div>
                <div class="form-group">
                    <label for="option_b">Option B</label>
                    <input type="text" id="option_b" name="option_b" placeholder="Enter option B" required>
                </div>
                <div class="form-group">
                    <label for="option_c">Option C</label>
                    <input type="text" id="option_c" name="option_c" placeholder="Enter option C" required>
                </div>
                <div class="form-group">
                    <label for="option_d">Option D</label>
                    <input type="text" id="option_d" name="option_d" placeholder="Enter option D" required>
                </div>

                <div class="form-group">
                    <label for="correct_option">Correct Option</label>
                    <select id="correct_option" name="correct_option" required>
                        <option value="" disabled selected>Select correct option</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>

                <button type="submit" name="add_quiz" class="submit-btn">Add Quiz</button>
            </form>
        </main>
    </div>
</body>
</html>