<?php
session_start();
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Check if sessions are actually remaining
    $res = $conn->query("SELECT FullName, SessionsRemaining FROM Users WHERE Id = '$id'");
    $user = $res->fetch_assoc();

    if ($user['SessionsRemaining'] <= 0) {
        echo "<script>alert('Error: This student has 0 sessions left.'); window.location='admin_dashboard.php';</script>";
        exit();
    }

    $name = $user['FullName'];
    $now = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO sitin_records (id_number, fullname, login_time, status) VALUES (?, ?, ?, 'Active')");
    $stmt->bind_param("sss", $id, $name, $now);
    
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?msg=started");
    }
}
?>