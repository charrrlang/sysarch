<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "ccs_db"; // Use the name shown in your phpMyAdmin

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>