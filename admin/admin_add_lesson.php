<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $module_id = $_GET['module_id'];
  $moduleStmt = $pdo->prepare("SELECT * FROM modules WHERE id = :module_id");
  $moduleStmt->execute([':module_id' => $module_id]);
  $module = $moduleStmt->fetch(PDO::FETCH_ASSOC);

  $module_id = $_GET['module_id'];
  $video_title = filter($_POST['title']);

  if (empty($module_id)) {
    dd('Module ID is not found');
  }

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

  $questions = $_POST['question'] ?? [];
  $options_a = $_POST['option_a'] ?? [];
  $options_b = $_POST['option_b'] ?? [];
  $options_c = $_POST['option_c'] ?? [];
  $options_d = $_POST['option_d'] ?? [];
  $correct_options = $_POST['correct_option'] ?? [];


  foreach ($questions as $index => $question) {
    if (empty(trim($question)) || empty(trim($options_a[$index])) || empty(trim($options_b[$index])) | empty(trim($options_c[$index])) | empty(trim($options_d[$index])) | empty(trim($correct_options[$index]))) {
      $errors['questions'] = 'All questions and options are required';
      break;
    }
  }

  if (!$errors) {
    if (isset($_FILES['video'])) {
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
      }

      try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO lessons (module_id, title, video_url) VALUES (:module_id, :title, :video_url)");
        $stmt->execute([':module_id' => $module_id, ':title' => $video_title, ':video_url' => $destination_path]);
        $lesson_id = $pdo->lastInsertId();

        if (!empty($_POST['question']) && is_array($_POST['question'])) {
          $insertQuestionStmt = $pdo->prepare("INSERT INTO quizzes (lesson_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (:lesson_id, :question, :option_a, :option_b, :option_c, :option_d, :correct_option)");

          foreach ($questions as $i => $q) {
            if (!empty($q)) {
              $params = [
                ':lesson_id' => $lesson_id,
                ':question' => $q,
                ':option_a' => $options_a[$i],
                ':option_b' => $options_b[$i],
                ':option_c' => $options_c[$i],
                ':option_d' => $options_d[$i],
                ':correct_option' => $correct_options[$i]
              ];
              $insertQuestionStmt->execute($params);
            }
          }

          $pdo->commit();
          $errors['none'] = 'Lesson added successfully';
        } else {
          $errors['questions'] = 'Add at least 1 questions';
        }
      } catch (\PDOException $e) {
        $pdo->rollBack();
        $errors['database'] = ("Database Error" . $e->getMessage());
      }
    }
  }




  // dd($_POST);
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

  if (!isset($_GET['module_id'])) {
    dd('Module ID is not set');
  }

  $module_id = $_GET['module_id'];
  $moduleStmt = $pdo->prepare("SELECT * FROM modules WHERE id = :module_id");
  $moduleStmt->execute([':module_id' => $module_id]);
  $module = $moduleStmt->fetch(PDO::FETCH_ASSOC);
  // dd($module);
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
  <link rel="stylesheet" href="../css/admin_add_lesson.css">
  <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
  <title>New Lesson - <?= $module['title'] ?? '' ?></title>
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
        <a href="admin_user_records.php">
          <img src="../assets/icons/assessment.png" alt="Scoreboard Icon">
          <h3>User Score</h3>
        </a>
        <a href="admin_monitoring.php">
          <img src="../assets/icons/monitoring.png" alt="Monitoring Icon">
          <h3>Monitoring</h3>
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
      <h1 class="width-full">ADD NEW LESSON</h1>
      <h3 class="module-title"><?= $module['title'] ?? '' ?> Module</h3>

      <div class="new-module-container">
        <form class="upload-form" method="POST" enctype="multipart/form-data">

          <div class="form-group">
            <?php if (isset($errors['none'])) : ?>
              <p class="sucess-message"><?= $errors['none'] ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="title">Video Title *</label>
            <input type="text" id="title" name="title" placeholder="Enter Video Title" value="<?= isset($errors['none']) ? '' : ($_POST['title'] ?? '') ?>">
            <?php if (isset($errors['title'])) : ?>
              <p class="inputs-error-message"><?= $errors['title'] ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="video">Upload Video * (MP4/WEBM/OGG)</label>
            <input type="file" name="video" class="p-2 border border-black" accept="video/mp4,video/webm,video/ogg">
            <?php if (isset($errors['video'])) : ?>
              <p class="inputs-error-message"><?= $errors['video'] ?></p>
            <?php endif; ?>
            <!-- <img id="thumbnail-preview" class="thumbnail-preview" src="" alt="Thumbnail Preview"> -->
          </div>

          <div id="questions-container" class="flex flex-col gap-y-4">
            <div class="questions-container question-block">
              <h4>Question</h4>
              <textarea name="question[]" class="" placeholder="Question text"></textarea>

              <div class="question-choices">
                <input type="text" name="option_a[]" class="" placeholder="Option A">
                <input type="text" name="option_b[]" class="" placeholder="Option B">
                <input type="text" name="option_c[]" class="" placeholder="Option C">
                <input type="text" name="option_d[]" class="" placeholder="Option D">
              </div>

              <div class="">
                <label class="">Correct Answer:</label>
                <select name="correct_option[]" class="">
                  <option value="" disabled selected>Choose the correct answer</option>
                  <option value="A">A</option>
                  <option value="B">B</option>
                  <option value="C">C</option>
                  <option value="D">D</option>
                </select>
              </div>
            </div>
            <?php if (isset($errors['questions'])) : ?>
              <p class="inputs-error-message"><?= $errors['questions'] ?></p>
            <?php endif; ?>
          </div>

          <div class="add-questions-container">
            <button type="button" id="add-question-btn" class="add-questions-btn">
              &#10010; Add Another Question
            </button>
          </div>

          <div class="button-section">
            <input type="submit" id="add-new-module-btn" class="" value="Add Lesson">
          </div>

        </form>
      </div>
    </main>
  </div>

  <script src="../js/admin.js"></script>
  <script>
    document.getElementById('add-question-btn').addEventListener('click', function() {
      const container = document.getElementById('questions-container');
      const questionBlocks = container.querySelectorAll('.question-block');
      const newQuestion = questionBlocks[0].cloneNode(true);

      // Clear input values
      newQuestion.querySelectorAll('textarea, input, select').forEach(input => input.value = '');

      container.appendChild(newQuestion);


      const addLessonBtn = document.getElementById('add-new-module-btn');
      addLessonBtn.addEventListener('click', () => {
        addLessonBtn.value = 'Please Wait...';
      });

    });
  </script>
</body>

</html>