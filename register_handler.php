<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Collect data from your register.php form
    $Id = $_POST['id_number'];
    $FullName = $_POST['fullname'];
    $UserName = $_POST['username'];
    $CourseLevel = $_POST['course_level'];
    $Course = $_POST['course'];
    $EmailAddress = $_POST['email'];
    
    // --- START OF MODIFIED SECTION ---
    $pass = $_POST['password']; 
    $confirm_pass = $_POST['confirm_password']; // New: captures the second input

    // Check if passwords match
    if ($pass !== $confirm_pass) {
        echo "<script>alert('Passwords do not match! Please type again.'); window.history.back();</script>";
        exit(); // Crucial: prevents the code below from running
    }

    // 2. Check for Minimum Length (Standard is usually 8 characters)
    if (strlen($pass) < 8) {
        echo "<script>alert('Password is too short! Minimum of 8 characters required.'); window.history.back();</script>";
        exit();
    }
    // --- END OF MODIFIED SECTION ---

    // 2. Hash the password for security
    // This creates a long, secure string that cannot be reversed
    $Password = password_hash($pass, PASSWORD_DEFAULT);

    // 3. SQL to insert data into the 'Users' table
    // Note: 'userName' must match the column name in your phpMyAdmin exactly
    $sql = "INSERT INTO Users (Id, UserName, FullName, CourseLevel, Course, EmailAddress, Password) 
            VALUES ('$Id', '$UserName', '$FullName', '$CourseLevel', '$Course', '$EmailAddress', '$Password')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Account Created Successfully!'); window.location='login.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>