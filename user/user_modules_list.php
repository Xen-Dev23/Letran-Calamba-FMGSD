<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "User") {
  header("Location: login.php");
  exit();
}

$stmt = $pdo->prepare("SELECT * FROM modules");
$stmt->execute();
$modules = $stmt->fetchAll();
$rowCount = count($modules);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/admin_video_list.css">
  <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
  <title>User - Modules List</title>
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
      <h1>Training Module List</h1>

      <div class="video-list">
        <div class="video-controls">
          <div class="search-bar">
            <span class="material-icons-sharp">search</span>
            <input type="text" id="video-search" placeholder="Search videos by title or description..." oninput="filterVideos()">
          </div>
          <div class="filter-bar-combined">
            <div class="filter-item">
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
            <div class="filter-item">
              <label for="date-filter">Filter by Date:</label>
              <input type="date" id="date-filter" onchange="filterVideos()">
            </div>
          </div>
        </div>

        <div class="add-new-module">
          <h2>Uploaded Modules</h2>
        </div>
        <?php if ($rowCount > 0): ?>
          <div class="video-grid" id="video-grid">
            <?php
            foreach ($modules as $module):
              $category = $module['category'];
              $thumbnail = $module['thumbnail'];
              $title = $module['title'];
              $description = $module['description'];
            ?>
              <a href="lessons.php?module_id=<?= $module['id'] ?>" class="video-card" data-category="<?= $category; ?>" data-title="<?php echo $title; ?>" data-description="<?= $description; ?>">
                <div class="video-card-thumbnail">
                  <img src="<?= $thumbnail; ?>" alt="Video Thumbnail">
                </div>
                <div class="video-card-content">
                  <div class="video-card-header">
                    <h3><?php echo $title; ?></h3>
                    <span class="category-tag"><?php echo $category; ?></span>
                  </div>
                  <div class="video-card-body">
                    <p><?php echo $description; ?></p>
                    <small>Uploaded: <?php echo date('M d, Y h:i A', strtotime($module['created_at'])); ?></small>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="no-videos">
            <p>No module uploaded yet.</p>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script src="../js/admin.js"></script>
  <script>
    function filterVideos() {
      const searchInput = document.getElementById('video-search').value.trim().toLowerCase();
      const categoryFilter = document.getElementById('category-filter').value.toLowerCase();
      const dateFilter = document.getElementById('date-filter').value; // format: yyyy-mm-dd
      const videoCards = document.querySelectorAll('.video-card');

      videoCards.forEach(card => {
        const title = (card.getAttribute('data-title') || '').toLowerCase();
        const description = (card.getAttribute('data-description') || '').toLowerCase();
        const category = (card.getAttribute('data-category') || 'uncategorized').toLowerCase();
        const uploadedText = card.querySelector('.video-card-body small')?.textContent || '';
        const uploadedDateMatch = uploadedText.match(/Uploaded: (.+)/);
        let matchDate = true;

        if (dateFilter && uploadedDateMatch) {
          const uploadedDate = new Date(uploadedDateMatch[1]);
          const uploadedDateStr = `${uploadedDate.getFullYear()}-${String(uploadedDate.getMonth() + 1).padStart(2, '0')}-${String(uploadedDate.getDate()).padStart(2, '0')}`;
          matchDate = uploadedDateStr === dateFilter;
        }

        const matchesSearch = title.includes(searchInput) || description.includes(searchInput);
        const matchesCategory = categoryFilter === 'all' || category === categoryFilter;

        card.style.display = (matchesSearch && matchesCategory && matchDate) ? '' : 'none';
      });

      const videoGrid = document.getElementById('video-grid');
      const noVideosMessage = document.querySelector('.no-videos');
      const visibleCards = Array.from(videoCards).filter(card => card.style.display !== 'none');

      if (visibleCards.length === 0 && !noVideosMessage) {
        const message = document.createElement('div');
        message.className = 'no-videos';
        message.innerHTML = '<p>No videos match your search or filter.</p>';
        videoGrid.insertAdjacentElement('afterend', message);
      } else if (visibleCards.length > 0 && noVideosMessage) {
        noVideosMessage.remove();
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      filterVideos();

      const videoCards = document.querySelectorAll('.video-card');
      videoCards.forEach(card => {
        const videoUrl = card.querySelector('.btn-play').href;
        const durationSpan = card.querySelector('.duration');

        if (durationSpan.textContent === 'N/A') {
          const video = document.createElement('video');
          video.preload = 'metadata';
          video.src = videoUrl;

          video.onloadedmetadata = function() {
            const duration = video.duration;
            const minutes = Math.floor(duration / 60);
            const seconds = Math.floor(duration % 60);
            durationSpan.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
          };
        }
      });
    });
  </script>
</body>

</html>