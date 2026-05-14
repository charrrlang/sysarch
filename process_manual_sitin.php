<?php
session_start();
include 'db_connect.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_number = $_POST['id_number'];
    $fullname = $_POST['fullname'];
    $purpose = $_POST['purpose'];
    $lab_room = $_POST['lab_room'];
    $login_time = $_POST['date'] . ' ' . $_POST['time'];
    $pc_no = $_POST['pc_no'];

    // 1. Check if the student has enough sessions left before approving
    $check_sql = "SELECT sessions_remaining FROM users WHERE Id = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("s", $id_number);
    $stmt_check->execute();
    $res = $stmt_check->get_result();
    $user_data = $res->fetch_assoc();

    if ($user_data['sessions_remaining'] <= 0) {
        echo "<script>alert('Error: Student has 0 sessions remaining!'); window.location.href='search_student.php';</script>";
        exit();
    }

    // 2. Insert the sit-in record
    $sql_insert = "INSERT INTO sitin_records (id_number, fullname, purpose, lab_room, login_time, status, pc_no) 
                   VALUES (?, ?, ?, ?, ?, 'Active', ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ssssss", $id_number, $fullname, $purpose, $lab_room, $login_time, $pc_no);

    if ($stmt_insert->execute()) {
        // 3. SUCCESS! Now deduct 1 session from the users table
        $sql_update = "UPDATE users SET sessions_remaining = sessions_remaining - 1 WHERE Id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("s", $id_number);
        $stmt_update->execute();

        header("Location: sit_in.php?msg=Student checked in and session deducted.");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>