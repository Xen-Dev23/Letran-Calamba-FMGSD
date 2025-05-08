<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

$module_id = $_GET['module_id'];
$moduleStmt = $pdo->prepare("SELECT * FROM modules WHERE id = :module_id");
$moduleStmt->execute([':module_id' => $module_id]);
$module = $moduleStmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // dd($_POST);

  $errors = [];

  $module_title = filter($_POST['title']);
  $module_description = filter($_POST['description']);
  // $module_category = filter($_POST['category']);
  $module_category = isset($_POST['category']) ? filter($_POST['category']) : '';


  if (empty($module_title)) {
    $errors['title'] = 'Module title is required';
  }

  if (empty($module_description)) {
    $errors['description'] = 'Module description is required';
  }

  if (empty($module_category)) {
    $errors['category'] = 'Module category is required';
  }

  if (
    isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES['thumbnail']['tmp_name'])
  ) {
    $existingThumbnailPath = $module['thumbnail'] ?? null;
    // dd($existingThumbnailPath);

    $file_tmp = $_FILES['thumbnail']['tmp_name'];
    $file_name = basename($_FILES['thumbnail']['name']);
    $file_type = mime_content_type($file_tmp);

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file_type, $allowed_types)) {
      $errors['thumbnail'] = 'Only JPEG, PNG, and GIF files are allowed.';
    }
  } else {
    $errors['thumbnail'] = 'Module thumbnail is required.';
  }


  if (!$errors) {

    $destination = '../uploads/thumbnails/' . $file_name;
    if (move_uploaded_file($file_tmp, $destination)) {
      // delete the old thumbnail file if it exists
      if ($existingThumbnailPath && file_exists($existingThumbnailPath)) {
        unlink($existingThumbnailPath);
      }

      $stmt = $pdo->prepare("UPDATE modules SET title = :title, description = :description, thumbnail = :thumbnail, category = :category WHERE id = :module_id");
      $stmt->execute([
        ':title' => $module_title,
        ':description' => $module_description,
        'thumbnail' => $destination,
        ':category' => $module_category,
        ':module_id' => $module_id
      ]);

      header("Location: lessons.php?module_id={$module_id}");
      exit();
    } else {
      $errors['thumbnail'] = 'Failed to upload the thumbnail.';
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
  <link rel="stylesheet" href="../css/admin_edit_module.css">
  <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
  <title>Admin Dashboard - Edit Module</title>
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

    <main class="video-container">
      <h1>EDIT MODULE</h1>

      <div class="new-module-container">
        <form class="upload-form" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="title">Module Title *</label>
            <input type="text" id="title" name="title" placeholder="Enter Module Title" value="<?= ($_POST['title'] ?? $module['title']) ?? '' ?>">
            <?php if (isset($errors['title'])) : ?>
              <p class="inputs-error-message"><?= $errors['title'] ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" placeholder="Enter Module Description"><?= ($_POST['description'] ?? $module['description']) ?? '' ?></textarea>
            <?php if (isset($errors['description'])) : ?>
              <p class="inputs-error-message"><?= $errors['description'] ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" name="category">
              <option value="" disabled <?= empty($_POST['category']) ? 'selected' : '' ?>>Select a category</option>
              <option value="Safety" <?= ($_POST['category'] ?? $module['category']) === 'Safety' ? 'selected' : '' ?>>Safety</option>
              <option value="Environment" <?= ($_POST['category'] ?? $module['category']) === 'Environment' ? 'selected' : '' ?>>Environment</option>
              <option value="Health" <?= ($_POST['category'] ?? $module['category']) === 'Health' ? 'selected' : '' ?>>Health</option>
              <option value="Certification" <?= ($_POST['category'] ?? $module['category']) === 'Certification' ? 'selected' : '' ?>>Certification</option>
              <option value="Other" <?= ($_POST['category'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>

            <?php if (isset($errors['category'])) : ?>
              <p class="inputs-error-message"><?= $errors['category'] ?></p>
            <?php endif; ?>

          </div>

          <div class="form-group">
            <label for="thumbnail_file">Upload New Thumbnail * (JPEG/PNG/GIF)</label>
            <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/gif">
            <?php if (isset($errors['thumbnail'])) : ?>
              <p class="inputs-error-message"><?= $errors['thumbnail'] ?></p>
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
    </main>
  </div>

  <script src="../js/admin.js"></script>
  <script>
  </script>
</body>

</html>