<?php
session_start();
include 'db_connect.php';

// Check if we received the Session ID (id) and Student ID (sid)
if (isset($_GET['id']) && isset($_GET['sid'])) {
    $session_id = $_GET['id'];
    $student_id = $_GET['sid'];
    $logout_time = date("Y-m-d H:i:s");

    // 1. Mark the session as 'Completed'
    $update_sql = "UPDATE sitin_records SET logout_time = ?, status = 'Completed' WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $logout_time, $session_id);

    if ($stmt->execute()) {
        // 2. REQUIREMENT: Automatically deduct one session from the student
        $deduct_sql = "UPDATE Users SET SessionsRemaining = SessionsRemaining - 1 WHERE Id = ?";
        $deduct_stmt = $conn->prepare($deduct_sql);
        $deduct_stmt->bind_param("s", $student_id);
        
        if ($deduct_stmt->execute()) {
            // 3. Send back to dashboard with a success message
            header("Location: admin_dashboard.php?msg=session_ended");
            exit();
        } else {
            die("Error deducting session: " . $conn->error);
        }
    } else {
        die("Error updating sit-in record: " . $conn->error);
    }
} else {
    die("Error: No ID provided in the URL.");
}
?>