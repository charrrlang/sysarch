<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_number = $_POST['id_number'];
    $fullname = $_POST['fullname'];
    $purpose = $_POST['purpose'];
    $lab_room = $_POST['lab'];
    $remaining = (int)$_POST['remaining'];

    // 1. Validation: Check if student has enough sessions
    if ($remaining <= 0) {
        header("Location: search_student.php?error=no_sessions");
        exit();
    }

    // 2. Deduct 1 session from the 'users' table
    $new_total = $remaining - 1;
    $update_sql = "UPDATE users SET sessions_remaining = ? WHERE UserName = ?";
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("is", $new_total, $id_number);

    if ($stmt_update->execute()) {
        // 3. Create the active sit-in record in 'sitin_records'
        // Status is 'Active' because they just logged in
        $insert_sql = "INSERT INTO sitin_records (id_number, fullname, purpose, lab_room, login_time, status) 
                       VALUES (?, ?, ?, ?, NOW(), 'Active')";
        $stmt_insert = $conn->prepare($insert_sql);
        $stmt_insert->bind_param("ssss", $id_number, $fullname, $purpose, $lab_room);
        
        if ($stmt_insert->execute()) {
            // Success! Redirect to the Active Sit-ins page
            header("Location: sit_in.php?msg=success");
        } else {
            echo "Error creating record: " . $conn->error;
        }
    } else {
        echo "Error updating sessions: " . $conn->error;
    }
}
?>