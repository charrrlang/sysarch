<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "ccs_db"; // Use the name shown in your phpMyAdmin


$conn = new mysqli($servername, $username, $password, $dbname);

date_default_timezone_set('Asia/Manila');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>