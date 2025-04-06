<?php
session_start();
include '../db/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
}

// Fetch all users from the database (initial load)
$query = "SELECT id, fullname, email, profile_picture, last_login, is_online FROM users";
$result = mysqli_query($conn, $query);

// Function to format the last login date
function formatLastLogin($last_login) {
    if (!$last_login) {
        return 'Never';
    }
    // Format as "Y-m-d h:i A" to get 12-hour format with AM/PM
    return date('Y-m-d h:i A', strtotime($last_login)); // e.g., "2025-04-04 8:31 PM"
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
<link rel="stylesheet" href="../css/admin.css">
<link rel="stylesheet" href="../css/admin_users.css">
<link rel="icon" type="image/png" href="../assets/images/favicon.ico">
<title>Admin - Manage Users</title>
</head>
<body>
    <div class="container">
        <!-- Sidebar Section -->
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
                <a href="admin_users.php" class="active">
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
        <main class="users-container">
            <h1>Manage Users</h1>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Profile</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Last Login</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">
                    <?php while ($row = mysqli_fetch_assoc($result)) { 
                        $status_class = $row['is_online'] ? 'online' : 'offline';
                        $status_text = $row['is_online'] ? 'Online' : 'Offline';
                        $profile_pic = $row['profile_picture'] ?: '../assets/images/profile-placeholder.png';
                    ?>
                        <tr data-user-id="<?php echo $row['id']; ?>">
                            <td>
                                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-pic">
                            </td>
                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <span class="status-dot <?php echo $status_class; ?>"></span>
                                <span class="status-text"><?php echo $status_text; ?></span>
                            </td>
                            <td class="last-login"><?php echo formatLastLogin($row['last_login']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </main>
        <!-- End of Main Content -->
    </div>

    <script src="../js/admin.js"></script>
    <script>
        // Function to format the last login date on the client side
        function formatLastLogin(last_login) {
            if (!last_login) {
                return 'Never';
            }
            // Parse the date and format it as "YYYY-MM-DD h:mm AM/PM"
            const date = new Date(last_login);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-based
            const day = String(date.getDate()).padStart(2, '0');
            let hours = date.getHours();
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12; // Convert to 12-hour format
            return `${year}-${month}-${day} ${hours}:${minutes} ${ampm}`; // Year-Month-Day Hour:Minute AM/PM
        }

        // Function to fetch and update user data
        function updateUserTable() {
            fetch('fetch_users.php')
                .then(response => response.json())
                .then(users => {
                    const tableBody = document.getElementById('users-table-body');
                    // Loop through each user and update the corresponding row
                    users.forEach(user => {
                        const row = tableBody.querySelector(`tr[data-user-id="${user.id}"]`);
                        if (row) {
                            // Update status
                            const statusCell = row.cells[3]; // Status column
                            statusCell.innerHTML = `
                                <span class="status-dot ${user.status_class}"></span>
                                <span class="status-text">${user.status}</span>
                            `;
                            // Update last login
                            const lastLoginCell = row.cells[4]; // Last Login column
                            lastLoginCell.textContent = formatLastLogin(user.last_login);
                        }
                    });
                })
                .catch(error => console.error('Error fetching user data:', error));
        }

        // Initial call to update the table
        updateUserTable();

        // Set interval to update the table every 5 seconds
        setInterval(updateUserTable, 5000); // 5000 ms = 5 seconds
    </script>
</body>
</html>