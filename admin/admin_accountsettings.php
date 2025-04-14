<?php
session_start();
include '../db/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
}

// Check if profile_picture column exists, if not, add it
$check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT '../assets/images/profile-placeholder.png'");
}

// Get current user info
$user_id = $_SESSION['user_id'];
$query = "SELECT fullname, email, profile_picture, password FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission (profile picture, fullname, email, and password)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $update_fields = [];
    $update_params = [];
    $update_types = "";

    // Validate Full Name
    if (!empty($fullname) && $fullname !== $user['fullname']) {
        $update_fields[] = "fullname = ?";
        $update_params[] = $fullname;
        $update_types .= "s";
        $_SESSION['fullname'] = $fullname; // Update session
    }

    // Validate Email
    if (!empty($email) && $email !== $user['email']) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
            $email_check_stmt = $conn->prepare($email_check_query);
            $email_check_stmt->bind_param("si", $email, $user_id);
            $email_check_stmt->execute();
            $email_check_result = $email_check_stmt->get_result();

            if ($email_check_result->num_rows == 0) {
                $update_fields[] = "email = ?";
                $update_params[] = $email;
                $update_types .= "s";
            } else {
                $error_message = "This email is already in use by another user.";
            }
        } else {
            $error_message = "Please enter a valid email address.";
        }
    }

    // Handle Password Change
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "Please fill in all password fields to change your password.";
        } else {
            if (password_verify($current_password, $user['password'])) {
                if ($new_password === $confirm_password) {
                    if (strlen($new_password) >= 8) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_fields[] = "password = ?";
                        $update_params[] = $hashed_password;
                        $update_types .= "s";
                    } else {
                        $error_message = "New password must be at least 8 characters long.";
                    }
                } else {
                    $error_message = "New password and confirmation do not match.";
                }
            } else {
                $error_message = "Current password is incorrect.";
            }
        }
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $target_dir = "../Uploads/profile_pics/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = uniqid() . '_' . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check !== false) {
            if ($_FILES["profile_picture"]["size"] <= 5000000) {
                if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                        if (!empty($user['profile_picture']) && 
                            $user['profile_picture'] != '../assets/images/profile-placeholder.png' &&
                            file_exists($user['profile_picture'])) {
                            unlink($user['profile_picture']);
                        }
                        
                        $update_fields[] = "profile_picture = ?";
                        $update_params[] = $target_file;
                        $update_types .= "s";
                        $_SESSION['profile_picture'] = $target_file;
                        $user['profile_picture'] = $target_file;
                    } else {
                        $error_message = "Sorry, there was an error uploading your file.";
                    }
                } else {
                    $error_message = "Only JPG, JPEG, PNG & GIF files are allowed.";
                }
            } else {
                $error_message = "File is too large. Maximum size is 5MB.";
            }
        } else {
            $error_message = "File is not an image.";
        }
    }

    // Update the database if there are changes
    if (!empty($update_fields) && !isset($error_message)) {
        $update_query = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?";
        $update_params[] = $user_id;
        $update_types .= "i";
        
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param($update_types, ...$update_params);
        $update_stmt->execute();
        
        $success_message = "Profile updated successfully!";
        
        $query = "SELECT fullname, email, profile_picture, password FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } elseif (empty($update_fields) && !isset($error_message)) {
        $error_message = "No changes were made.";
    }
}

$profile_pic = isset($user['profile_picture']) && !empty($user['profile_picture']) && file_exists($user['profile_picture'])
    ? $user['profile_picture']
    : '../assets/images/profile-placeholder.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin_accountsettings.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <title>Account Settings</title>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside>
            <div class="toggle">
                <div class="logo">
                    <img src="../assets/images/favicon.ico" alt="Logo">
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
                <a href="admin_video_list.php">
                    <img src="../assets/icons/video_library.png" alt="Videos Icon">
                    <h3>Video List</h3>
                </a>
                <a href="admin_accountsettings.php" class="active">
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
        <main>
            <h1>Account Settings</h1>
            <div class="profile-section">
                <?php if (isset($success_message)): ?>
                    <div class="message success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if (isset($error_message)): ?>
                    <div class="message error"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="profile-picture">
                    <img src="<?php echo $profile_pic; ?>" alt="Profile Picture">
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="profile_picture">Change Profile Picture</label>
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" name="fullname" id="fullname" placeholder="Enter Full Name" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" placeholder="Email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <!-- Password Change Section -->
                    <div class="password-section">
                        <h3>Change Password</h3>
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <div class="password-container">
                                <input type="password" name="current_password" id="current_password" placeholder="Enter current password">
                                <img src="../assets/icons/eye-off.png" alt="Show/Hide Password" class="toggle-password">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="password-container">
                                <input type="password" name="new_password" id="new_password" placeholder="Enter new password">
                                <img src="../assets/icons/eye-off.png" alt="Show/Hide Password" class="toggle-password">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="password-container">
                                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password">
                                <img src="../assets/icons/eye-off.png" alt="Show/Hide Password" class="toggle-password">
                            </div>
                        </div>
                    </div>
                    <!-- End of Password Change Section -->
                    
                    <div class="form-group">
                        <button type="submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </main>
        <!-- End of Main Content -->
    </div>

    <script>
    // Preview profile picture on file selection
    document.getElementById('profile_picture').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const profilePicForm = document.querySelector('.profile-picture img');
                profilePicForm.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Toggle password visibility
    document.addEventListener('DOMContentLoaded', function() {
        const togglePasswordElements = document.querySelectorAll('.toggle-password');
        
        togglePasswordElements.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                const isPassword = passwordInput.type === 'password';
                
                passwordInput.type = isPassword ? 'text' : 'password';
                this.src = isPassword ? 
                    '../assets/icons/eye-on.png' : 
                    '../assets/icons/eye-off.png';
            });
        });
    });
    </script>
</body>
</html>