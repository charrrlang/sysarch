<?php
session_start();
include 'db_connect.php';
date_default_timezone_set('Asia/Manila');

if (isset($_POST['send_feedback'])) {
    $id_number = $_SESSION['id_number'];
    $message = $_POST['feedback_message'];

    // Insert feedback into database
    $sql = "INSERT INTO feedbacks (id_number, message, date_submitted) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $id_number, $message);
    
    if ($stmt->execute()) {
        // Redirect back to homepage with a success alert
        echo "<script>alert('Feedback submitted successfully!'); window.location.href='homepage.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>