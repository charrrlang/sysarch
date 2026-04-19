<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $record_id = $_POST['record_id'];
    $student_id = $_SESSION['id_number'];
    $rating = $_POST['rating'];
    $comment = $conn->real_escape_string($_POST['comment']);

    $sql = "INSERT INTO feedback (record_id, student_id, rating, comment) 
            VALUES ('$record_id', '$student_id', '$rating', '$comment')";

    if ($conn->query($sql)) {
        header("Location: history.php?msg=FeedbackSubmitted");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>