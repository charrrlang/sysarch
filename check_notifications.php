<?php
session_start();
include 'db_connect.php';

// Set header to JSON so the JavaScript can read it
header('Content-Type: application/json');

$id_number = $_SESSION['id_number'] ?? '';
$response = [
    'new_announcement' => false, 
    'session_update' => false, 
    'message' => ''
];

if (!$id_number) {
    echo json_encode($response);
    exit();
}

// 1. Check for new announcements in the last 30 seconds (more reliable than 10s)
$ann = $conn->query("SELECT content FROM announcements WHERE date_posted > DATE_SUB(NOW(), INTERVAL 30 SECOND) ORDER BY date_posted DESC LIMIT 1");

if ($ann && $ann->num_rows > 0) {
    $row = $ann->fetch_assoc();
    $response['new_announcement'] = true;
    $response['message'] = "New Announcement: " . htmlspecialchars(substr($row['content'], 0, 50)) . "...";
} 
// 2. Only check session update if no announcement (to avoid double pop-up)
else {
    $sess = $conn->query("SELECT id FROM sitin_records WHERE id_number = '$id_number' AND status = 'Completed' AND logout_time > DATE_SUB(NOW(), INTERVAL 30 SECOND) LIMIT 1");
    if ($sess && $sess->num_rows > 0) {
        $response['session_update'] = true;
        $response['message'] = "Your sit-in session has been completed by the Admin.";
    }
}

echo json_encode($response);