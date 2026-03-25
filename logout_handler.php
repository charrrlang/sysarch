<?php
session_start();
include 'db_connect.php';

// Security: Only allow Admin to perform this action
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $record_id = $_GET['id'];
    $current_time = date('Y-m-d H:i:s');

    // Update the record: Set status to 'Completed' and set logout_time
    $stmt = $conn->prepare("UPDATE sitin_records SET status = 'Completed', logout_time = ? WHERE id = ?");
    $stmt->bind_param("si", $current_time, $record_id);

    if ($stmt->execute()) {
        // Redirect back to sit_in.php with a success message
        header("Location: sit_in.php?msg=logged_out");
    } else {
        echo "Error updating record: " . $conn->error;
    }
    
    $stmt->close();
} else {
    header("Location: sit_in.php");
}
$conn->close();
?>