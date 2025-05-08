<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

$module_id = $_GET['module_id'];
$lesson_id = $_GET['lesson_id'];

$lessonStmt = $pdo->prepare("SELECT * FROM lessons WHERE id = :lesson_id");
$lessonStmt->execute([':lesson_id' => $lesson_id]);
$lesson = $lessonStmt->fetch(PDO::FETCH_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $video_title = filter($_POST['title']);

  if (empty($video_title)) {
    $errors['title'] = 'Video title is required';
  }

  if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES['video']['tmp_name'])) {
    $file_tmp = $_FILES['video']['tmp_name'];
    $file_name = $_FILES['video']['name'];
    $file_type = mime_content_type($file_tmp);

    $allowed_types = ['video/mp4', 'video/webm', 'video/ogg', 'video/x-webm', 'video/x-matroska'];
    if (!in_array($file_type, $allowed_types)) {
      $errors['video'] = 'Only mp4, WEBM, and OGG files are allowed';
    }
  } else {
    $errors['video'] = 'Video is required';
  }

  if (!$errors) {
    if (isset($_FILES['video'])) {
      $existingVideoUrlPath = $lesson['video_url'];

      $video_tmp_path = $_FILES['video']['tmp_name'];
      $origal_filename = $_FILES['video']['name'];

      // Folder to store the uploaded videos
      $uploads_dir = '../uploads/videos/';

      // Add unique name on the .mp4 file
      $unique_name = uniqid('video_', true) . '.mp4';
      $destination_path = $uploads_dir . $unique_name;

      // Convert the uploaded video to MP4 format using FFmpeg
      $ffmpeg_cmd = "ffmpeg -i " . escapeshellarg($video_tmp_path) . " -vcodec libx264 -acodec aac -strict -2 " . escapeshellarg($destination_path) . " 2>&1";
      $output = shell_exec($ffmpeg_cmd);

      // Check if conversion was successful
      if (!file_exists($destination_path)) {
        $errors['video'] = 'Video conversion failed. Check your FFmpeg installation';
      } else {

        try {

          $stmt = $pdo->prepare("UPDATE lessons SET title =:title, video_url = :video_url WHERE id = :lesson_id");
          $stmt->execute([':title' => $video_title, ':video_url' => $destination_path, ':lesson_id' => $lesson_id]);

          header("Location: lessons.php?module_id={$module_id}");
        } catch (\PDOException $e) {

          $errors['database'] = ("Database Error" . $e->getMessage());
        }
      }
    }
  }




  // dd($_POST);
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
  <link rel="stylesheet" href="../css/admin_edit_lesson.css">
  <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
  <title><?= $lesson['title'] ?></title>
</head>

<body>
  <div class="container">
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
        <a href="../logout.php">
          <img src="../assets/icons/logout.png" alt="Logout Icon">
          <h3>Logout</h3>
        </a>
      </div>
    </aside>

    <main class="video-container">
      <h1 class="width-full"><?= $lesson['title'] ?></h1>

      <div class="new-module-container">
        <form class="upload-form" method="POST" enctype="multipart/form-data">

          <div class="form-group">
            <?php if (isset($errors['none'])) : ?>
              <p class="sucess-message"><?= $errors['none'] ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="title">Video Title *</label>
            <input type="text" id="title" name="title" placeholder="Enter Video Title" value="<?= $_POST['title'] ?? $lesson['title'] ?>">
            <?php if (isset($errors['title'])) : ?>
              <p class="inputs-error-message"><?= $errors['title'] ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="video">Upload New Video * (MP4/WEBM/OGG)</label>
            <input type="file" name="video" class="p-2 border border-black" accept="video/mp4,video/webm,video/ogg">
            <?php if (isset($errors['video'])) : ?>
              <p class="inputs-error-message"><?= $errors['video'] ?></p>
            <?php endif; ?>
            <!-- <img id="thumbnail-preview" class="thumbnail-preview" src="" alt="Thumbnail Preview"> -->
          </div>

          <div class="button-section">
            <button type="submit" id="add-new-module-btn" class="">
              Save
            </button>
          </div>

        </form>
      </div>

      <div class="edit-quizzes-container">
        <a href="edit-quizzes.php?lesson_id=<?= $lesson_id ?>" class="edit-quizzes-link">Edit Quizzes</a>
      </div>
    </main>
  </div>

  <script src="../js/admin.js"></script>
</body>

</html>