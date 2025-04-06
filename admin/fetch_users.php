<?php
include '../db/db.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Fetch all users
$query = "SELECT id, fullname, email, profile_picture, last_login, is_online FROM users";
$result = mysqli_query($conn, $query);

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = [
        'id' => $row['id'],
        'fullname' => htmlspecialchars($row['fullname']),
        'email' => htmlspecialchars($row['email']),
        'profile_picture' => $row['profile_picture'] ?: '../assets/images/profile-placeholder.png',
        'status' => $row['is_online'] ? 'Online' : 'Offline',
        'status_class' => $row['is_online'] ? 'online' : 'offline',
        'last_login' => $row['last_login'] ? htmlspecialchars($row['last_login']) : 'Never'
    ];
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($users);
exit();
?>