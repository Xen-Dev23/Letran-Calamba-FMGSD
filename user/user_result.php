<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all quiz results for the user with lesson, module, and video details
$stmt = $pdo->prepare("
    SELECT qr.*, l.title AS lesson_title, m.title AS module_title
    FROM quiz_results qr
    JOIN lessons l ON qr.lesson_id = l.id
    JOIN modules m ON l.module_id = m.id
    WHERE qr.user_id = :user_id
    ORDER BY qr.taken_at DESC
");
$stmt->execute([':user_id' => $user_id]);
$quiz_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin_video_list.css">
    <link rel="stylesheet" href="../css/assessment_result.css">
    <link rel="stylesheet" href="../css/loaders.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <title>Assessment Results</title>
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
                <a href="user_modules_list.php">
                    <img src="../assets/icons/modules.png" alt="Modules Icon">
                    <h3>Modules</h3>
                </a>
                <a href="user_result.php" class="active">
                    <img src="../assets/icons/assessment.png" alt="Results Icon">
                    <h3>View Results</h3>
                </a>
                <a href="../logout.php">
                    <img src="../assets/icons/logout.png" alt="Logout Icon">
                    <h3>Logout</h3>
                </a>
            </div>
        </aside>
        <!-- End of Sidebar Section -->

        <main class="lesson-container">
            <div class="lesson-quiz-container">
                <h1>ASSESSMENT RESULTS</h1>
                <div id="results-container" class="greetings-container">
                    <?php if (empty($quiz_results)): ?>
                        <h3 class="sub-message">No quiz results found.</h3>
                    <?php else: ?>
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Date Taken</th>
                                    <th>Module</th>
                                    <th>Lesson</th>
                                    <th>Score</th>
                                    <th>Total Items</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($quiz_results as $result): ?>
                                    <tr>
                                        <td><?= date('M d, Y h:i A', strtotime($result['taken_at'])) ?></td>
                                        <td><?= htmlspecialchars($result['module_title'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($result['lesson_title'] ?? 'N/A') ?></td>
                                        <td><?= $result['score'] ?></td>
                                        <td><?= $result['totalItems'] ?></td>
                                        <td>
                                            <span class="<?= $result['isPassed'] ? 'success' : 'failed' ?>">
                                                <?= $result['isPassed'] ? 'Passed' : 'Failed' ?>
                                            </span>
                                        </td>
                                        
                                        <td>
                                            <a href="answers.php?result_id=<?= $result['id'] ?>">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <a href="user_dashboard.php" class="back-to-dashboard">Back to Dashboard</a>
                </div>
            </div>
        </main>
    </div>

    <script src="../js/admin.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timeoutId = setTimeout(() => {
                const loader = document.querySelector('.loader-wrapper');
                if (loader) {
                    loader.style.display = 'none';
                }
                clearTimeout(timeoutId);
            }, 1500);
        });
    </script>
</body>
</html>
