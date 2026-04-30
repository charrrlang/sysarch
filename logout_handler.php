<?php
session_start();
include 'db_connect.php';
date_default_timezone_set('Asia/Manila');

// 1. Check if ANYONE is logged in (Student or Admin)
if (!isset($_SESSION['id_number']) && !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$current_time = date('Y-m-d H:i:s');

// --- CASE A: ADMIN LOGGING OUT A STUDENT (via sit_in.php) ---
if (isset($_GET['id']) && $_SESSION['role'] === 'Admin') {
    $record_id = $_GET['id'];

    $stmt = $conn->prepare("UPDATE sitin_records SET status = 'Completed', logout_time = ? WHERE id = ?");
    $stmt->bind_param("si", $current_time, $record_id);

    if ($stmt->execute()) {
        header("Location: sit_in.php?msg=logged_out");
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
} 

// --- CASE B: STUDENT LOGGING OUT THEMSELVES (via homepage.php) ---
else if (isset($_GET['status']) && isset($_SESSION['id_number'])) {
    $id_number = $_SESSION['id_number'];
    $status_choice = $_GET['status']; // 'Completed' or 'Not Completed'

    // Update the active record for this specific student
    $stmt = $conn->prepare("UPDATE sitin_records SET status = ?, logout_time = ? WHERE student_id = ? AND status = 'Approved' ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("sss", $status_choice, $current_time, $id_number);
    
    $stmt->execute();
    $stmt->close();

    // Kill session and redirect to login/welcome page
    session_unset();
    session_destroy();
    header("Location: welcomepage.php");
    exit();
} 

// --- DEFAULT: Just a normal logout for someone with no active sit-in ---
else {
    session_unset();
    session_destroy();
    header("Location: welcomepage.php");
}

$conn->close();
?>