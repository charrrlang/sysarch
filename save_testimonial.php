<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['id_number'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback'])) {
    $id_number = $_SESSION['id_number'];
    $feedback = trim($_POST['feedback']);

    if (empty($feedback)) {
        echo json_encode(['status' => 'error', 'message' => 'Feedback cannot be empty']);
        exit();
    }

    // Insert into a testimonials table (Make sure this table exists in your DB)
    // Adjust column names 'id_number' and 'content' to match your database
    $stmt = $conn->prepare("INSERT INTO testimonials (id_number, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $id_number, $feedback);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>