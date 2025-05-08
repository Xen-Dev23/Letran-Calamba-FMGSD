<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

$lesson_id = $_GET['lesson_id'];
$lessonStmt = $pdo->prepare("SELECT * FROM lessons WHERE id = :lesson_id");
$lessonStmt->execute([':lesson_id' => $lesson_id]);
$lesson = $lessonStmt->fetch(PDO::FETCH_ASSOC);


$quiz_id = $_GET['quiz_id'];
$quizStmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = :quiz_id");
$quizStmt->execute([':quiz_id' => $quiz_id]);
$quiz = $quizStmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // dd($_POST);

  $question = filter($_POST['question']);
  $option_a = filter($_POST['option_a']);
  $option_b = filter($_POST['option_b']);
  $option_c = filter($_POST['option_c']);
  $option_d = filter($_POST['option_d']);
  $correct_option = filter($_POST['correct_option']);
  $status = filter($_POST['status']);

  $errors = [];

  if (empty($question) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d) || empty($correct_option) || empty($status)) {
    $errors['required'] = 'All inputs are required';
  }

  if (!$errors) {
    $stmt = $pdo->prepare("UPDATE quizzes 
                          SET question = :question,
                           option_a = :option_a,
                           option_b = :option_b,
                           option_c = :option_c,
                           option_d = :option_d,
                           correct_option = :correct_option,
                           status = :status
                          WHERE id = :quiz_id");
    $updated = $stmt->execute([
      ':question' => $question,
      ':option_a' => $option_a,
      ':option_b' => $option_b,
      ':option_c' => $option_c,
      ':option_d' => $option_d,
      ':correct_option' => $correct_option,
      ':status' => $status,
      ':quiz_id' => $quiz_id,
    ]);

    if($updated) {
      $_SESSION['success_message'] = 'Quiz updated successfully';
      header("Location: edit-quizzes.php?lesson_id={$lesson_id}");
      exit();
    }
    
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
  <link rel="stylesheet" href="../css/admin_video_list.css">
  <link rel="stylesheet" href="../css/admin_update_quiz.css">
  <link rel="stylesheet" href="../css/loaders.css">
  <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
  <title><?= $lesson['title'] ?></title>
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

        <form action="" method="POST">
          <div class="questions-container question-block">
            <h1>UPDATE QUIZ</h1>

            <?php if (isset($errors['required'])) : ?>
              <p class="error-message"><?= $errors['required'] ?></p>
            <?php endif; ?>

            <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">

            <textarea name="question" class="" placeholder="Question text"><?= $_POST['question'] ?? $quiz['question'] ?></textarea>

            <div class="question-choices">
              <input type="text" name="option_a" class="" placeholder="Option A" value="<?= $_POST['option_a'] ?? $quiz['option_a'] ?>">
              <input type="text" name="option_b" class="" placeholder="Option B" value="<?= $_POST['option_b'] ?? $quiz['option_b'] ?>">
              <input type="text" name="option_c" class="" placeholder="Option C" value="<?= $_POST['option_c'] ?? $quiz['option_c'] ?>">
              <input type="text" name="option_d" class="" placeholder="Option D" value="<?= $_POST['option_d'] ?? $quiz['option_d'] ?>">
            </div>

            <div class="select-section">
              <div class="">
                <label class="">Correct Answer:</label>
                <select name="correct_option" class="">
                  <option value="A" <?= $quiz['correct_option'] === 'A' ? 'selected' : '' ?>>A</option>
                  <option value="B" <?= $quiz['correct_option'] === 'B' ? 'selected' : '' ?>>B</option>
                  <option value="C" <?= $quiz['correct_option'] === 'C' ? 'selected' : '' ?>>C</option>
                  <option value="D" <?= $quiz['correct_option'] === 'D' ? 'selected' : '' ?>>D</option>
                </select>

              </div>

              <div class="question-status">
                <label class="">Status:</label>
                <select name="status" class="">
                  <option value="active" <?= $quiz['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                  <option value="inactive" <?= $quiz['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
              </div>
            </div>

            <input type="submit" id="update-quiz-btn" value="Update">
          </div>
          <?php if (isset($errors['questions'])) : ?>
            <p class="inputs-error-message"><?= $errors['questions'] ?></p>
          <?php endif; ?>

        </form>
      </div>
    </main>
  </div>

  <script src="../js/admin.js"></script>
</body>

</html>