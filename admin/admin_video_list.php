<?php
session_start();
include '../db/db.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "Admin") {
    header("Location: login.php");
    exit();
}

class VideoManager
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    public function deleteVideo($id)
    {
        $sql = "SELECT file_path, thumbnail FROM training_videos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($video = $result->fetch_assoc()) {
            $file_path = $video['file_path'];
            $thumbnail = $video['thumbnail'];

            if (!file_exists($file_path)) {
                return ['success' => false, 'message' => 'Video file does not exist: ' . $file_path];
            }

            if (!is_writable($file_path)) {
                return ['success' => false, 'message' => 'Permission denied: Cannot delete the video file ' . $file_path];
            }

            if (unlink($file_path)) {
                if ($thumbnail && file_exists($thumbnail)) {
                    unlink($thumbnail);
                }

                $sql = "DELETE FROM training_videos WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    return ['success' => true, 'message' => 'Video deleted successfully!'];
                } else {
                    return ['success' => false, 'message' => 'Failed to delete video record from database.'];
                }
            } else {
                return ['success' => false, 'message' => 'Failed to delete the video file.'];
            }
        }
        return ['success' => false, 'message' => 'Video not found in the database.'];
    }

    public function getAllVideos()
    {
        $sql = "SELECT * FROM training_videos ORDER BY created_at ASC"; // Changed to ASC for oldest first
        return $this->conn->query($sql);
    }

    public function updateVideo($id, $title, $description, $category, $newThumbnail = null, $newVideoFile = null)
    {
        $currentVideo = $this->getVideoById($id);
        if (!$currentVideo) {
            return ['success' => false, 'message' => 'Video not found.'];
        }

        $filePath = $currentVideo['file_path'];
        $thumbnail = $currentVideo['thumbnail'];

        // Handle video file update
        if ($newVideoFile && $newVideoFile['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/videos/';
            $newFileName = uniqid() . '_' . basename($newVideoFile['name']);
            $newFilePath = $uploadDir . $newFileName;

            if (move_uploaded_file($newVideoFile['tmp_name'], $newFilePath)) {
                if (file_exists($filePath)) {
                    unlink($filePath); // Delete old video file
                }
                $filePath = $newFilePath;
            } else {
                return ['success' => false, 'message' => 'Failed to upload new video file.'];
            }
        }

        // Handle thumbnail update
        if ($newThumbnail && $newThumbnail['error'] === UPLOAD_ERR_OK) {
            $thumbDir = '../uploads/thumbnails/';
            $newThumbName = uniqid() . '_' . basename($newThumbnail['name']);
            $newThumbnailPath = $thumbDir . $newThumbName;

            if (move_uploaded_file($newThumbnail['tmp_name'], $newThumbnailPath)) {
                if ($thumbnail && file_exists($thumbnail)) {
                    unlink($thumbnail); // Delete old thumbnail
                }
                $thumbnail = $newThumbnailPath;
            } else {
                return ['success' => false, 'message' => 'Failed to upload new thumbnail.'];
            }
        }

        $sql = "UPDATE training_videos SET title = ?, description = ?, category = ?, file_path = ?, thumbnail = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssi", $title, $description, $category, $filePath, $thumbnail, $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Video updated successfully!'];
        } else {
            return ['success' => false, 'message' => 'Failed to update video: ' . $this->conn->error];
        }
    }

    public function getVideoById($id)
    {
        $sql = "SELECT * FROM training_videos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

$videoManager = new VideoManager($conn);
$message = null;

if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $message = $videoManager->deleteVideo($_GET['delete_id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_video'])) {
    $id = $_POST['video_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $newThumbnail = isset($_FILES['thumbnail']) ? $_FILES['thumbnail'] : null;
    $newVideoFile = isset($_FILES['video_file']) ? $_FILES['video_file'] : null;

    $message = $videoManager->updateVideo($id, $title, $description, $category, $newThumbnail, $newVideoFile);
}

$videos = $videoManager->getAllVideos();
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
    <title>Admin Dashboard - Video List</title>
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
            <h1>Training Videos List</h1>

            <?php if ($message): ?>
                <div class="message <?php echo $message['success'] ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message['message']); ?>
                </div>
            <?php endif; ?>

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
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="add-new-module">
                    <h2>Uploaded Videos</h2>

                    <a class="add-new-module-btn" href="admin_add_new_module.php">&#10010; Add New Module</a>
                </div>
                <?php if ($videos->num_rows > 0): ?>
                    <div class="video-grid" id="video-grid">
                        <?php
                        while ($video = $videos->fetch_assoc()):
                            $category = isset($video['category']) ? htmlspecialchars($video['category']) : 'Uncategorized';
                            $duration = isset($video['duration']) ? htmlspecialchars($video['duration']) : 'N/A';
                            $thumbnail = isset($video['thumbnail']) ? htmlspecialchars($video['thumbnail']) : '../assets/images/default_thumbnail.jpg';
                            $title = htmlspecialchars($video['title'] ?? 'Untitled');
                            $description = htmlspecialchars($video['description'] ?? 'No description');
                        ?>
                            <div class="video-card" data-category="<?= $category; ?>" data-title="<?php echo $title; ?>" data-description="<?= $description; ?>">
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
                                            <span class="material-icons-sharp">visibility</span> Play
                                        </a>
                                        <button class="btn btn-edit" onclick='openEditModal(<?php echo json_encode($video); ?>)'>
                                            <span class="material-icons-sharp">edit</span> Edit
                                        </button>
                                        <a href="?delete_id=<?php echo $video['id']; ?>"
                                            onclick="return confirm('Are you sure you want to delete this video?')" class="btn btn-delete">
                                            <span class="material-icons-sharp">delete</span> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-videos">
                        <p>No videos uploaded yet.</p>
                        <a href="admin_video_upload.php" class="btn btn-upload">Upload a Video</a>
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

    <div id="editModal" class="edit-modal">
        <div class="edit-modal-content">
            <button class="close-modal" onclick="closeEditModal()">×</button>
            <h2>Edit Video</h2>
            <form class="edit-form" method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="video_id" id="edit_video_id">
                <div>
                    <label for="edit_title">Title</label>
                    <input type="text" name="title" id="edit_title" placeholder="Enter Video Title" required>
                </div>
                <div>
                    <label for="edit_description">Description</label>
                    <textarea name="description" id="edit_description" placeholder="Enter Description Here" required></textarea>
                </div>
                <div>
                    <label for="edit_category">Category</label>
                    <select name="category" id="edit_category" required>
                        <option value="" disabled selected>Select a category</option>
                        <option value="Safety">Safety</option>
                        <option value="Environment">Environment</option>
                        <option value="Health">Health</option>
                        <option value="Certification">Certification</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label for="edit_thumbnail">Thumbnail (Leave blank to keep current)</label>
                    <input type="file" name="thumbnail" id="edit_thumbnail" accept="image/*">
                    <img id="current_thumbnail" src="" alt="Current Thumbnail" style="max-width: 100px; margin-top: 10px; display: none;">
                </div>
                <div>
                    <label for="edit_video_file">Video File (Leave blank to keep current)</label>
                    <input type="file" name="video_file" id="edit_video_file" accept="video/mp4,video/avi,video/mkv">
                </div>
                <div class="edit-form-buttons">
                    <button type="submit" name="edit_video" class="btn btn-save">Save Changes</button>
                    <button type="button" class="btn btn-delete" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/admin.js"></script>
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

        function openEditModal(video) {
            const modal = document.getElementById('editModal');
            document.getElementById('edit_video_id').value = video.id;
            document.getElementById('edit_title').value = video.title || '';
            document.getElementById('edit_description').value = video.description || '';
            document.getElementById('edit_category').value = video.category || 'Uncategorized';

            // Show current thumbnail
            const currentThumbnail = document.getElementById('current_thumbnail');
            if (video.thumbnail) {
                currentThumbnail.src = video.thumbnail;
                currentThumbnail.style.display = 'block';
            } else {
                currentThumbnail.style.display = 'none';
            }

            // Reset file inputs
            document.getElementById('edit_thumbnail').value = '';
            document.getElementById('edit_video_file').value = '';

            modal.style.display = 'flex';
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
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

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        document.querySelector('.edit-form').addEventListener('submit', function(e) {
            e.stopPropagation();
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