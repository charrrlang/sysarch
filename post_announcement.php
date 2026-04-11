<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['content'])) {
    $content = $conn->real_escape_string($_POST['content']);
    
    $sql = "INSERT INTO announcements (content) VALUES ('$content')";
    
    if ($conn->query($sql)) {
        header("Location: admin_dashboard.php?status=success");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>