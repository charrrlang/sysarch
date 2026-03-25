<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo "<script>alert('Please fill in all fields'); window.history.back();</script>";
        exit();
    }

    // This ignores the database entirely for these specific words
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['role'] = 'Admin'; // Set role to distinguish from students
        $_SESSION['full_name'] = 'System Administrator';
        $_SESSION['id_number'] = 'ADMIN-01';

        // REDIRECT TO ADMIN DASHBOARD INSTEAD
        echo "<script>alert('Welcome Admin!'); window.location='admin_dashboard.php';</script>";
        exit();
    }
    
    // Search for the user
    $sql = "SELECT * FROM Users WHERE userName = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify hashed password
        if (password_verify($password, $row['Password'])) {
            
            // SAVE TO SESSION (This makes the data available on the homepage)
            $_SESSION['id_number']   = $row['Id'];
            $_SESSION['full_name']   = $row['FullName'];
            $_SESSION['course']      = $row['Course'];
            $_SESSION['course_lvl']  = $row['CourseLevel'];
            $_SESSION['user_name']   = $row['userName'];
            $_SESSION['email_address']  = $row['EmailAddress'];

            echo "<script>alert('Login Successful!'); window.location='homepage.php';</script>";
        } else {
            echo "<script>alert('Incorrect Password'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('User not found'); window.history.back();</script>";
    }
    $conn->close();
}
?>