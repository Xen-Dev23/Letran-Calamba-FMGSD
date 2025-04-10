<?php
session_start();
include '../db/db.php';

// Authentication check - allow only logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

class VideoManager {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function getAllVideos() {
        $sql = "SELECT * FROM training_videos ORDER BY created_at DESC";
        return $this->conn->query($sql);
    }
}

$videoManager = new VideoManager($conn);
$videos = $videoManager->getAllVideos();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/user_modules.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <title>User Dashboard - Training Modules</title>
</head>
<body>
    <div class="container">
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
                <a href="user_modules.php" class="active">
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

        <main class="video-container">
            <h1>Training Modules</h1>

            <div class="video-list">
                <div class="video-controls">
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
                            <option value="Uncategorized">Uncategorized</option>
                        </select>
                    </div>
                </div>

                <h2>Available Training Videos</h2>
                <?php if ($videos->num_rows > 0): ?>
                    <div class="video-grid" id="video-grid">
                        <?php while ($video = $videos->fetch_assoc()): 
                            $category = isset($video['category']) ? htmlspecialchars($video['category']) : 'Uncategorized';
                            $duration = isset($video['duration']) ? htmlspecialchars($video['duration']) : 'N/A';
                            $thumbnail = isset($video['thumbnail']) ? htmlspecialchars($video['thumbnail']) : '../assets/images/default_thumbnail.jpg';
                            $title = htmlspecialchars($video['title'] ?? 'Untitled');
                            $description = htmlspecialchars($video['description'] ?? 'No description');
                        ?>
                            <div class="video-card" data-category="<?php echo $category; ?>" data-title="<?php echo $title; ?>" data-description="<?php echo $description; ?>">
                                <div class="video-card-thumbnail">
                                    <img src="<?php echo $thumbnail; ?>" alt="Video Thumbnail">
                                    <span class="duration"><?php echo $duration; ?></span>
                                </div>
                                <div class="video-card-content">
                                    <div class="video-card-header">
                                        <h3><?php echo $title; ?></h3>
                                        <span class="category-tag"><?php echo $category; ?></span>
                                    </div>
                                    <div class="video-card-body">
                                        <p><?php echo $description; ?></p>
                                        <small>Uploaded: <?php echo date('M d, Y h:i A', strtotime($video['created_at'])); ?></small>
                                    </div>
                                    <div class="video-card-actions">
                                        <a href="<?php echo htmlspecialchars($video['file_path']); ?>" class="btn btn-play">
                                            <span class="material-icons-sharp">visibility</span> Watch
                                        </a>
                                        <a href="<?php echo htmlspecialchars($video['file_path']); ?>" download class="btn btn-download">
                                            <span class="material-icons-sharp">download</span> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-videos">
                        <p>No training videos available yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div id="videoModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal()">×</button>
            <video id="modalVideo" class="modal-video" controls>
                <source src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    </div>

    <script src="../js/user.js"></script>
    <script>
        function openModal(videoUrl) {
            const modal = document.getElementById('videoModal');
            const video = document.getElementById('modalVideo');
            const source = video.getElementsByTagName('source')[0];
            
            source.src = videoUrl;
            video.load();
            modal.style.display = 'flex';
        }

        function closeModal() {
            const modal = document.getElementById('videoModal');
            const video = document.getElementById('modalVideo');
            
            video.pause();
            video.currentTime = 0;
            modal.style.display = 'none';
        }

        document.querySelectorAll('.video-card-actions .btn-play').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const videoUrl = this.getAttribute('href');
                openModal(videoUrl);
            });
        });

        document.getElementById('videoModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        function filterVideos() {
            const searchInput = document.getElementById('video-search').value.trim().toLowerCase();
            const categoryFilter = document.getElementById('category-filter').value.toLowerCase();
            const videoCards = document.querySelectorAll('.video-card');

            videoCards.forEach(card => {
                const title = (card.getAttribute('data-title') || '').toLowerCase();
                const description = (card.getAttribute('data-description') || '').toLowerCase();
                const category = (card.getAttribute('data-category') || 'uncategorized').toLowerCase();

                const matchesSearch = title.includes(searchInput) || description.includes(searchInput);
                const matchesCategory = categoryFilter === 'all' || category === categoryFilter;

                card.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
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