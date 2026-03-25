<?php
session_start();
include 'db_connect.php';

if (isset($_GET['id'])) {
    $record_id = $_GET['id'];

    // 1. Get the student's ID from the reservation record
    $get_student = $conn->prepare("SELECT id_number FROM sitin_records WHERE id = ?");
    $get_student->bind_param("i", $record_id);
    $get_student->execute();
    $result = $get_student->get_result();
    $row = $result->fetch_assoc();
    
    if ($row) {
        $student_id = $row['id_number'];

        // 2. Start Transaction (Ensures both updates happen or none at all)
        $conn->begin_transaction();

        try {
            // Step A: Set reservation to Active
            $update_stmt = $conn->prepare("UPDATE sitin_records SET status = 'Active', login_time = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $record_id);
            $update_stmt->execute();

            // Step B: Subtract 1 session from the student
            $deduct_stmt = $conn->prepare("UPDATE users SET sessions_remaining = sessions_remaining - 1 WHERE UserName = ? AND sessions_remaining > 0");
            $deduct_stmt->bind_param("s", $student_id);
            $deduct_stmt->execute();

            $conn->commit();
            header("Location: reservation_admin.php?status=success");
        } catch (Exception $e) {
            $conn->rollback();
            header("Location: reservation_admin.php?status=error");
        }
    }
}
?>