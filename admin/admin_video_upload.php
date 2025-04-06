<?php
session_start();
include '../db/db.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "Admin") {
    header("Location: login.php");
    exit();
}

class VideoUploader {
    private $conn;
    private $uploadDir = "../uploads/videos/";
    private $thumbnailDir = "../uploads/thumbnails/";
    
    public function __construct($connection) {
        $this->conn = $connection;
        // Create directories if they don't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
        if (!is_dir($this->thumbnailDir)) {
            mkdir($this->thumbnailDir, 0777, true);
        }
    }
    
    public function uploadVideo($title, $description, $category, $videoFile, $thumbnailFile) {
        // Validate inputs
        if (empty($title) || empty($description) || empty($category)) {
            return ['success' => false, 'message' => 'Please fill in all required fields.'];
        }

        // Validate video file
        if ($videoFile['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error uploading video file.'];
        }

        $allowedVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];
        $videoType = mime_content_type($videoFile['tmp_name']);
        if (!in_array($videoType, $allowedVideoTypes)) {
            return ['success' => false, 'message' => 'Invalid video format. Only MP4, WebM, and OGG are allowed.'];
        }

        // Validate thumbnail file (if provided)
        $thumbnailPath = null;
        if ($thumbnailFile['error'] === UPLOAD_ERR_OK) {
            $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $thumbnailType = mime_content_type($thumbnailFile['tmp_name']);
            if (!in_array($thumbnailType, $allowedImageTypes)) {
                return ['success' => false, 'message' => 'Invalid thumbnail format. Only JPEG, PNG, and GIF are allowed.'];
            }

            $thumbnailExt = pathinfo($thumbnailFile['name'], PATHINFO_EXTENSION);
            $thumbnailName = uniqid('thumb_') . '.' . $thumbnailExt;
            $thumbnailPath = $this->thumbnailDir . $thumbnailName;

            if (!move_uploaded_file($thumbnailFile['tmp_name'], $thumbnailPath)) {
                return ['success' => false, 'message' => 'Failed to upload thumbnail.'];
            }
        }

        // Handle video upload
        $videoExt = pathinfo($videoFile['name'], PATHINFO_EXTENSION);
        $videoName = uniqid('video_') . '.' . $videoExt;
        $videoPath = $this->uploadDir . $videoName;

        if (!move_uploaded_file($videoFile['tmp_name'], $videoPath)) {
            // If thumbnail was uploaded, remove it to avoid orphaned files
            if ($thumbnailPath && file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
            return ['success' => false, 'message' => 'Failed to upload video.'];
        }

        // Get video duration using FFmpeg (optional, requires FFmpeg installed on the server)
        $duration = 'N/A';
        if (extension_loaded('ffmpeg')) {
            $ffmpeg = FFMpeg\FFMpeg::create();
            $video = $ffmpeg->open($videoPath);
            $duration = gmdate("i:s", $video->getDuration());
        }

        // Insert into database
        $sql = "INSERT INTO training_videos (title, description, category, file_path, thumbnail, duration, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssss", $title, $description, $category, $videoPath, $thumbnailPath, $duration);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Video uploaded successfully!'];
        } else {
            // Clean up uploaded files if database insertion fails
            if (file_exists($videoPath)) {
                unlink($videoPath);
            }
            if ($thumbnailPath && file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
            return ['success' => false, 'message' => 'Failed to save video to database.'];
        }
    }
}

$videoUploader = new VideoUploader($conn);
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $videoFile = $_FILES['video_file'];
    $thumbnailFile = $_FILES['thumbnail_file'];

    $message = $videoUploader->uploadVideo($title, $description, $category, $videoFile, $thumbnailFile);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin_video_upload.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <title>Admin Dashboard - Upload Training Video</title>
</head>
<body>
    <div class="container">
        <!-- Sidebar Section (unchanged from your previous setup) -->
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
                <a href="admin_video_upload.php" class="active">
                    <img src="../assets/icons/video_library.png" alt="Videos Icon">
                    <h3>Training Videos</h3>
                </a>
                <a href="admin_video_list.php">
                    <img src="../assets/icons/video_library.png" alt="Videos Icon">
                    <h3>Video List</h3>
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
        <!-- End of Sidebar Section -->

        <!-- Main Content -->
        <main class="upload-container">
            <h1>Upload Training Video</h1>

            <?php if ($message): ?>
                <div class="message <?php echo $message['success'] ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message['message']); ?>
                </div>
            <?php endif; ?>

            <form class="upload-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Video Title *</label>
                    <input type="text" id="title" name="title" placeholder="Enter Video Title"required>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" placeholder="Enter Description Here" required></textarea>
                </div>

                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="" disabled selected>Select a category</option>
                        <option value="Safety">Safety</option>
                        <option value="Environment">Environment</option>
                        <option value="Health">Health</option>
                        <option value="Certification">Certification</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="video_file">Video File * (MP4, WebM, OGG)</label>
                    <input type="file" id="video_file" name="video_file" accept="video/mp4,video/webm,video/ogg" required>
                </div>

                <div class="form-group">
                    <label for="thumbnail_file">Thumbnail (Optional, JPEG/PNG/GIF)</label>
                    <input type="file" id="thumbnail_file" name="thumbnail_file" accept="image/jpeg,image/png,image/gif">
                    <img id="thumbnail-preview" class="thumbnail-preview" src="" alt="Thumbnail Preview">
                </div>

                <button type="submit" class="upload-btn">
                    <span class="material-icons-sharp">upload</span> Upload Video
                </button>
            </form>
        </main>
    </div>

    <script src="../js/admin.js"></script>
    <script>
        // Thumbnail preview functionality
        const thumbnailInput = document.getElementById('thumbnail_file');
        const thumbnailPreview = document.getElementById('thumbnail-preview');

        thumbnailInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    thumbnailPreview.src = e.target.result;
                    thumbnailPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                thumbnailPreview.style.display = 'none';
            }
        });
    </script>
</body>
</html>