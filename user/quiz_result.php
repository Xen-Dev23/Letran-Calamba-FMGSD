<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

if(!isset($_SESSION['quiz-result'])) {
  header ("Location: user_dashboard.php");
}

$result_id = $_GET['result_id'];
$stmt = $pdo->prepare("SELECT * FROM quiz_results WHERE id = :result_id");
$stmt->execute([':result_id' => $result_id]);
$quiz_result = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/admin_video_list.css">
  <link rel="stylesheet" href="../css/quiz_result.css">
  <link rel="stylesheet" href="../css/loaders.css">
  <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
  <title>Quiz Result</title>
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
       
        <!-- loaders -->
        <!-- <div class="loader-wrapper">
          <div class="loader"></div>
        </div> -->

        <h1>QUIZ RESULT</h1>

        <div id="quiz-container" class="greetings-container">
          <h1 class="greetings-title <?= $quiz_result['isPassed'] ? 'success' : 'failed' ?>"><?= $quiz_result['isPassed'] ? 'Congratulations' : 'Failed' ?></h1>
          <h3 class="sub-message"><?= $quiz_result['isPassed'] ? 'You Passed' : 'Try Again' ?></h3>
          <h3 class="score-items"><?= $quiz_result['score'] ?> / <?= $quiz_result['totalItems'] ?></h3>
          <a href="user_dashboard.php">Go Back to DarshBoard</a>
          <?php unset($_SESSION['quiz-result']) ?>
        </div>
      </div>
    </main>
  </div>

  <script src="../js/admin.js"></script>
  <script>
    // window.addEventListener('load', () => {
    //   document.querySelector('.loader-wrapper').style.display = 'none';
    // });

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