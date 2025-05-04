<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "User") {
  header("Location: login.php");
  exit();
}

$lesson_id = $_GET['lesson_id'];
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = :lesson_id");
$stmt->execute([':lesson_id' => $lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);
$rowCount = count($lesson);

$quizStmt = $pdo->prepare("SELECT * FROM quizzes WHERE lesson_id = :lesson_id");
$quizStmt->execute([':lesson_id' => $lesson_id]);
$questions = $quizStmt->fetchAll(PDO::FETCH_ASSOC);

$totalQuestions = count($questions);

$shuffleQuestions = $questions;
shuffle($shuffleQuestions);

// dd($questions);
// dd($rowCount);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/admin_video_list.css">
  <link rel="stylesheet" href="../css/user_lesson.css">
  <link rel="stylesheet" href="../css/loaders.css">
  <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
  <title><?= $lesson['title'] ?? 'Lesson' ?></title>
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
        <a href="user_modules_list.php" class="active">
          <img src="../assets/icons/modules.png" alt="Modules Icon">
          <h3>Modules</h3>
        </a>
        <a href="user_quiz.php">
          <img src="../assets/icons/quiz.png" alt="Quiz Icon">
          <h3>Take Quiz</h3>
        </a>
        <a href="user_result.php">
          <img src="../assets/icons/assessment.png" alt="Results Icon">
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

    <main class="lesson-container">

      <div class="lesson-quiz-container">
        <!-- SEARCH AND FILTERING -->
        <!-- <div class="video-controls">
          <div class="search-bar">
            <span class="material-icons-sharp">search</span>
            <input type="text" id="video-search" placeholder="Search videos by title or description..." oninput="filterVideos()">
          </div>
          <div class="filter-bar">
            <label for="category-filter">Filter by Category:</label>
            <select id="category-filter" onchange="filterVideos()">
              <option value="all">All Categories</option>
              <option value="Safety">Safety</option>
              <option value="Environment">Environment</option>
              <option value="Health">Health</option>
              <option value="Certification">Certification</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div> -->

        <!-- loaders -->
        <div class="loader-wrapper">
          <div class="loader"></div>
        </div>

        <h2><?= $lesson['title'] ?></h2>

        <div class="video-container">
          <?php if ($rowCount > 0): ?>
            <video id="lesson_video" controls>
              <source src="<?= htmlspecialchars($lesson['video_url']) ?>" type="video/mp4">
              Your browser does not support the video tag.
            </video>
          <?php else: ?>
            <div class="no-lesson">
              <p>No lesson uploaded yet for this module.</p>
            </div>
          <?php endif; ?>
        </div>

        <div id="quiz-container" class="quiz-container hidden">

            <h3 class="total-no-of-questions"><?= $totalQuestions ?> Questions</h3>

          <form action="handle_quiz_submit.php" method="POST" id="quiz-form" class="">
            <input type="hidden" name="lesson_id" value="<?= $lesson_id ?>">

            <?php foreach ($shuffleQuestions as $index => $question) : ?>
              <div class="questions-container">
                <p class="question"><?= ($index + 1) . '. ' . $question['question'] ?></p>
                <?php foreach (['A', 'B', 'C', 'D'] as $letter) : ?>
                  <label class="option">
                    <input type="radio" name="answers[<?= $question['id'] ?>]" value="<?= $letter ?>">
                    <?= $letter ?>. <?= ($question["option_" . strtolower($letter)]) ?>
                  </label>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>

            <button type="button" id="fake-submit-button">
              Submit
            </button>

            <!-- Modal -->
            <div id="confirmation-modal" class="modal hidden">
              <div class="modal-content">
                <p class="modal-text">Are you sure you want to submit your answers?</p>
                <div class="modal-buttons">
                  <button type="submit" form="quiz-form">Yes, Submit</button>
                  <button type="button" id="cancel-modal">Cancel</button>
                </div>
              </div>
            </div>


          </form>
        </div>
      </div>

      <div id="waiting" class="text-center font-semibold text-gray-700 mt-4">
        Please watch the video. Quiz will appear after video ends.
      </div>
    </main>
  </div>

  <script src="../js/admin.js"></script>
  <script>
    // window.addEventListener('load', () => {
    //   document.querySelector('.loader-wrapper').style.display = 'none';
    // });

    let timeoutId;

    document.addEventListener('DOMContentLoaded', function() {
      const video = document.getElementById('lesson_video');
      const videoContainer = document.querySelector('.video-container');
      const quizForm = document.getElementById('quiz-container');
      const waitingMsg = document.getElementById('waiting');

      video.addEventListener('ended', () => {

        timeoutId = setTimeout(() => {
          videoContainer.classList.add("hidden");
          quizForm.classList.remove("hidden");
          waitingMsg.classList.add("hidden");
        }, 1000);
      });





      timeoutId = setTimeout(() => {
        const loader = document.querySelector('.loader-wrapper');
        if (loader) {
          loader.style.display = 'none';
        }
        clearTimeout(timeoutId);
      }, 1500);



      const submitBtn = document.getElementById('fake-submit-button');
      const modal = document.getElementById('confirmation-modal');
      const cancelBtn = document.getElementById('cancel-modal');

      submitBtn.addEventListener('click', () => {
        modal.classList.remove('hidden');
      });

      cancelBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
      });






      // Select all radio buttons inside questions-container
      const radioButtons = document.querySelectorAll('.questions-container input[type="radio"]');

      radioButtons.forEach(radio => {
        radio.addEventListener('change', () => {
          const container = radio.closest('.questions-container');

          // Remove 'active' class from all options in this container
          container.querySelectorAll('.option').forEach(label => {
            label.classList.remove('active');
          });

          // Add 'active' class to the selected one
          radio.parentElement.classList.add('active');
        });
      });



    });
  </script>
</body>

</html>