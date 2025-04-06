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
    <link rel="stylesheet" href="../css/user.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <title>User Dashboard - Training Modules</title>
    <style>
        .video-container {
            margin: 2rem;
            max-width: 1200px;
        }

        .video-list {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .video-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-bar {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-bar span {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        .search-bar input {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 2.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-bar input:focus {
            border-color: #2196F3;
        }

        .filter-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-bar label {
            font-size: 0.9rem;
            color: #333;
        }

        .filter-bar select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            outline: none;
            cursor: pointer;
        }

        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .video-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e5e5e5;
        }

        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .video-card-thumbnail {
            position: relative;
            width: 100%;
            height: 180px;
            overflow: hidden;
        }

        .video-card-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .duration {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 5px;
            font-size: 0.8rem;
        }

        .video-card-content {
            padding: 1rem;
        }

        .video-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .video-card-header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: #333;
            font-weight: 600;
        }

        .category-tag {
            background: #e3f2fd;
            color: #1e88e5;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .video-card-body p {
            margin: 0 0 0.5rem;
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .video-card-body small {
            display: block;
            color: #888;
            font-size: 0.85rem;
        }

        .video-card-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background-color 0.2s;
            cursor: pointer;
        }

        .btn-play {
            background-color: #1e88e5;
            color: white;
        }

        .btn-play:hover {
            background-color: #1565c0;
        }

        .btn-download {
            background-color: #4CAF50;
            color: white;
        }

        .btn-download:hover {
            background-color: #45a049;
        }

        .btn span {
            margin-right: 0.5rem;
        }

        .no-videos {
            text-align: center;
            padding: 3rem;
            color: #666;
            background: #f9f9f9;
            border-radius: 10px;
            border: 1px dashed #ddd;
            margin-top: 1rem;
        }

        .sidebar img {
            width: 24px;
            height: 24px;
            margin-right: 10px;
            vertical-align: middle;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            position: relative;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow: auto;
        }

        .modal-video {
            width: 100%;
            max-height: 70vh;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            color: #333;
            cursor: pointer;
            border: none;
            background: none;
            padding: 0;
        }

        .close-modal:hover {
            color: #f44336;
        }
    </style>
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