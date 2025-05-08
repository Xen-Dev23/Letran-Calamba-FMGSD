<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';


$lesson_count = 1;

$module_id = $_GET['module_id'];
$moduleStmt = $pdo->prepare("SELECT * FROM modules WHERE id = :module_id");
$moduleStmt->execute([':module_id' => $module_id]);
$module = $moduleStmt->fetch(PDO::FETCH_ASSOC);



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
  <link rel="stylesheet" href="../css/admin_lessons_list.css">
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
        <a href="../logout.php">
          <img src="../assets/icons/logout.png" alt="Logout Icon">
          <h3>Logout</h3>
        </a>
      </div>
    </aside>
    <!-- End of Sidebar Section -->

    <main class="video-container">

      <div class="video-list">

        <!-- loaders -->
        <div class="loader-wrapper">
          <div class="loader"></div>
        </div>

        <div class="edit-module">
          <div class="add-lesson">
            <h2>
              <a href="module_edit.php?module_id=<?= $module['id'] ?>" class="edit-module-link">
                <?= $module['title'] ?>
              </a>
            </h2>
            <a href="admin_add_lesson.php?module_id=<?= $module_id ?>" class="add-lesson-link">Add Lesson</a>
          </div>
        </div>
        <?php if ($rowCount > 0): ?>
          <div class="lesson-container">
            <?php foreach ($lessons as $lesson): ?>

              <div class="lesson-card">
                <div class="lesson">
                  Lesson <?= $lesson_count ?> - <?= $lesson['title'] ?>
                </div>
                <a href="lesson_edit.php?lesson_id=<?= $lesson['id'] ?>&module_id=<?= $module_id ?>" class="edit-lesson-link">
                  Edit
                </a>
              </div>
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