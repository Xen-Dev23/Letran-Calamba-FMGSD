<?php
session_start();
include '../db/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header('HTTP/1.0 403 Forbidden');
    exit();
}

// Get unread count
$notif_query = $conn->query("SELECT COUNT(*) as unread_count FROM scores WHERE is_read = 0");
$notif_data = $notif_query->fetch_assoc();

// Get all notifications
$result = $conn->query("
    SELECT scores.id, users.fullname, scores.score, scores.timestamp, scores.is_read 
    FROM scores 
    JOIN users ON scores.user_id = users.id 
    ORDER BY scores.timestamp DESC
");

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'unread_count' => $notif_data['unread_count'],
    'notifications' => $notifications
]);
?>