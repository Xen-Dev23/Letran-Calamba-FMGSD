<?php
session_start();
include '../db/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "User") {
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

// Handle form submission (profile picture, full name, email, and password)
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
            // Check if email already exists in the database (excluding current user)
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
        // All password fields must be filled
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "Please fill in all password fields to change your password.";
        } else {
            // Verify current password
            if (password_verify($current_password, $user['password'])) {
                // Check if new password matches confirmation
                if ($new_password === $confirm_password) {
                    // Validate new password (e.g., minimum length)
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
        $target_dir = "../uploads/profile_pics/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = uniqid() . '_' . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check !== false) {
            // Check file size (5MB max)
            if ($_FILES["profile_picture"]["size"] <= 5000000) {
                // Allow certain file formats
                if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                        // Delete old profile picture if it exists and isn't the default
                        if (
                            !empty($user['profile_picture']) &&
                            $user['profile_picture'] != '../assets/images/profile-placeholder.png' &&
                            file_exists($user['profile_picture'])
                        ) {
                            unlink($user['profile_picture']);
                        }

                        $update_fields[] = "profile_picture = ?";
                        $update_params[] = $target_file;
                        $update_types .= "s";
                        $_SESSION['profile_picture'] = $target_file; // Update session
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

        // Refresh user data
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

// Set profile picture with fallback
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
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/user_accountsettings.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <title>Account Settings</title>
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
                <a href="user_modules_list.php">
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
                <a href="user_accountsettings.php" class="active">
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

                    <div class="form-group">
                        <button type="submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </main>
        <!-- End of Main Content -->

        <!-- Right Section -->
        <div class="right-section">
            <div class="nav">
                <button id="menu-btn">
                    <span class="material-icons-sharp">menu</span>
                </button>
                <div class="profile">
                    <div class="profile-photo">
                        <img src="<?php echo $profile_pic; ?>" alt="Profile" id="profile-pic">
                    </div>
                    <div class="profile-dropdown" id="profile-dropdown">
                        <div class="profile-info">
                            <p><b><?php echo $_SESSION['fullname']; ?></b></p>
                            <small>User</small>
                        </div>
                        <div class="profile-actions">
                            <a href="user_accountsettings.php">
                                <span class="material-icons-sharp">settings</span>
                                Account Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Right Section -->
    </div>

    <script>
        // Sidebar toggle
        const closeBtn = document.getElementById('close-btn');
        const sidebar = document.querySelector('aside');

        closeBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Profile Dropdown Toggle on Click
        const profile = document.querySelector('.profile');
        const profileDropdown = document.querySelector('#profile-dropdown');

        profile.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', (e) => {
            if (!profile.contains(e.target)) {
                profileDropdown.style.display = 'none';
            }
        });

        // Preview profile picture on file selection
        document.getElementById('profile_picture').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Update the profile picture in the form
                    const profilePicForm = document.querySelector('.profile-picture img');
                    profilePicForm.src = e.target.result;

                    // Update the profile picture in the top-right nav
                    const profilePicNav = document.getElementById('profile-pic');
                    profilePicNav.src = e.target.result;
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