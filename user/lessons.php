<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "User") {
  header("Location: login.php");
  exit();
}


$user_id = $_SESSION['user_id'];
$lesson_count = 1;

$module_id = $_GET['module_id'];
$lessonStmt = $pdo->prepare("SELECT * FROM lessons WHERE module_id = :module_id ORDER BY created_at DESC");
$lessonStmt->execute([':module_id' => $module_id]);
$lessons = $lessonStmt->fetchAll(PDO::FETCH_ASSOC);
$rowCount = count($lessons);


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
  <link rel="stylesheet" href="../css/user_lessons_list.css">
  <link rel="stylesheet" href="../css/loaders.css">
  <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
  <title>Lessons List</title>
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

    <main class="video-container">

      <div class="video-list">
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
        <!-- <div class="loader-wrapper">
          <div class="loader">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
          </div>
        </div> -->

        <div class="add-new-module">
          <h2>Lessons Available</h2>
        </div>
        <?php if ($rowCount > 0): ?>
          <div class="lesson-container">
            <?php foreach ($lessons as $lesson): ?>

              <?php
              $watchedLessonStmt = $pdo->prepare("SELECT * FROM quiz_results WHERE user_id = :user_id AND id = :lesson_id");
              $watchedLessonStmt->execute([':user_id' => $user_id, ':lesson_id' => $lesson['id']]);
              $watched_lesson = $watchedLessonStmt->fetch(PDO::FETCH_ASSOC);
              ?>

              <?php if ($watched_lesson && !empty($watched_lesson['isWatched']) && $watched_lesson['isWatched'] == 1): ?>
                <a href="lesson.php?lesson_id=<?= $lesson['id'] ?>" class="watched-lesson-card">
                  <div class="lesson">
                    Lesson <?= $lesson_count ?> - <?= $lesson['title'] ?>
                  </div>
                </a>
              <?php else : ?>
                <a href="lesson.php?lesson_id=<?= $lesson['id'] ?>" class="lesson-card">
                  <div class="lesson">
                    Lesson <?= $lesson_count ?> - <?= $lesson['title'] ?>
                  </div>
                </a>
              <?php endif; ?>


              <?php $lesson_count++ ?>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="no-videos">
            <p>No lesson uploaded yet for this module.</p>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script src="../js/admin.js"></script>
  <script>
    window.addEventListener('load', () => {
      document.querySelector('.loader-wrapper').style.display = 'none';
    });
  </script>
</body>

</html>