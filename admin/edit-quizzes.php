<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

$lesson_id = $_GET['lesson_id'];
$lessonStmt = $pdo->prepare("SELECT * FROM lessons WHERE id = :lesson_id");
$lessonStmt->execute([':lesson_id' => $lesson_id]);
$lesson = $lessonStmt->fetch(PDO::FETCH_ASSOC);

$quizzesStmt = $pdo->prepare("SELECT * FROM quizzes WHERE lesson_id = :lesson_id ORDER BY id DESC");
$quizzesStmt->execute([':lesson_id' => $lesson_id]);
$quizzes = $quizzesStmt->fetchAll(PDO::FETCH_ASSOC);

$itemNumber = 1;
// dd($quizzes);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/admin_video_list.css">
  <link rel="stylesheet" href="../css/admin_edit_quizzes.css">
  <link rel="stylesheet" href="../css/loaders.css">
  <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
  <title>Quizzes List</title>
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
        <a href="admin_add_quiz.php">
          <img src="../assets/icons/quiz.png" alt="Add Quiz Icon">
          <h3>Add Quiz</h3>
        </a>
        <a href="admin_video_upload.php">
          <img src="../assets/icons/video_library.png" alt="Videos Icon">
          <h3>Training Videos</h3>
        </a>
        <a href="module_list.php" class="active">
          <img src="../assets/icons/video_library.png" alt="Videos Icon">
          <h3>Module List</h3>
        </a>
        <div class="log-out-link">
          <a href="../logout.php">
            <img src="../assets/icons/logout.png" alt="Logout Icon">
            <h3>Logout</h3>
          </a>
        </div>
      </div>
    </aside>
    <!-- End of Sidebar Section -->

    <main class="video-container">

      <h1><?= $lesson['title'] ?></h1>

      <div class="video-list">
        <h1>QUIZ LIST</h1>

        <?php if(isset($_SESSION['success_message'])) : ?>
          <p class="success-message"><?= $_SESSION['success_message'] ?></p>
          <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php foreach ($quizzes as $quiz) : ?>
          <div class="quiz-container">
            <div class="edit-link-container">
              <p class="quiz-question"><?= $itemNumber ?>. <?= $quiz['question'] ?></p>
              <a href="update-quiz.php?quiz_id=<?= $quiz['id'] ?>&lesson_id=<?= $lesson_id ?>" class="edit-link">edit</a>
            </div>
            <div class="quiz-options">
              <p class="optionA">A. <?= $quiz['option_a'] ?></p>
              <p class="optionB">B. <?= $quiz['option_b'] ?></p>
              <p class="optionC">C. <?= $quiz['option_c'] ?></p>
              <p class="optionD">D. <?= $quiz['option_d'] ?></p>
            </div>
            <div class="correct-answer-container">
              <p class="quiz-correct-answer">Correct Answer: <?= $quiz['correct_option'] ?></p>
            </div>
            <?php $itemNumber++ ?>
          </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>

  <script src="../js/admin.js"></script>
</body>

</html>