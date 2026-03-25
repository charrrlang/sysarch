<?php
session_start();
include 'db_connect.php'; // Ensure your database connection is included

if (!isset($_SESSION['id_number'])) {
    header("Location: login.php");
    exit();
}

$id_number = $_SESSION['id_number'];

// Fetch the latest user data including the profile picture
$query = $conn->query("SELECT * FROM users WHERE Id = '$id_number'");
$user = $query->fetch_assoc();

// Fallback to a default image if no profile picture is set
$profile_pic = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default.png';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Sit-in Monitoring</title>
    <style>
        /* Global Reset */
        body { 
            font-family: 'Segoe UI', sans-serif; 
            margin: 0; 
            background-color: #f4f7f6; 
            height: 100vh;
            display: flex;
            flex-direction: column; 
        }
        
        /* Header Styling */
        header {
            background-color: #b0b1a8; 
            display: flex;
            padding: 15px 60px; 
            align-items: center;
            justify-content: space-between;
            width: 100%; 
            box-sizing: border-box;
            border-bottom: 1px solid #999;
            z-index: 1000;
        }
        
        .logo-group { display: flex; align-items: center; gap: 20px; }
        .UC-logo { width: 50px; height: auto; }
        .system-title { 
            font-size: 22px; 
            font-weight: bold; 
            color: #1a2fa3; 
            margin: 0; 
        }

        .auth-group { display: flex; gap: 25px; align-items: center; }
        .nav-link { 
            color: #1a2fa3; 
            text-decoration: none; 
            font-weight: bold; 
            font-size: 15px;
        }
        
        /* Layout Wrapper */
        .app-body {
            display: flex;
            flex-grow: 1; 
            overflow: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 280px;
            background-color: #ffffff;
            border-right: 2px solid #1a2fa3;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
            align-items: center; /* Center the profile picture */
        }

        /* Profile Picture Styling */
        .profile-pic-container {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #1a2fa3;
            overflow: hidden;
            margin-bottom: 20px;
            background-color: #eee;
        }

        .profile-pic-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sidebar h3 { 
            color: #1a2fa3; 
            border-bottom: 1px solid #eee; 
            padding-bottom: 10px; 
            margin-top: 0; 
            width: 100%;
            text-align: left;
        }

        .detail-box { margin-bottom: 20px; width: 100%; text-align: left; }
        .label { font-size: 11px; color: #888; text-transform: uppercase; font-weight: bold; display: block; }
        .value { font-size: 16px; color: #333; font-weight: 600; }

        /* Main Content */
        .main-content {
            flex-grow: 1;
            padding: 50px;
            overflow-y: auto; 
        }

        .welcome-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 10px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <header>
        <div class="logo-group">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/University_of_Cebu_Logo.png/960px-University_of_Cebu_Logo.png" alt="UC logo" class="UC-logo">
            <h1 class="system-title">College of Computer Studies Sit-in Monitoring</h1>
        </div>

        <div class="auth-group">
            <a href="homepage.php" class="nav-link" style="text-decoration: underline;">Home</a>
            <a href="editprofile.php" class="nav-link">Edit Profile</a>
            <a href="history.php" class="nav-link">History</a>
            <a href="reservation.php" class="nav-link">Reservation</a>
            <a href="welcomepage.php" class="nav-link" style="color: #d9534f;">Logout</a>
        </div>
    </header>

    <div class="app-body">
        <div class="sidebar">
            <div class="profile-pic-container">
                <img src="uploads/<?php echo $profile_pic; ?>" alt="Profile Picture">
            </div>

            <h3>Student Profile</h3>
            
            <div class="detail-box">
                <span class="label">ID Number</span>
                <span class="value"><?php echo htmlspecialchars($user['Id']); ?></span>
            </div>

            <div class="detail-box">
                <span class="label">Student Name</span>
                <span class="value"><?php echo htmlspecialchars($user['FullName']); ?></span>
            </div>

            <div class="detail-box">
                <span class="label">Course & Year</span>
                <span class="value"><?php echo htmlspecialchars($user['Course'] . " - " . $user['CourseLevel']); ?></span>
            </div>

            <div class="detail-box">
                <span class="label">Email Address</span>
                <span class="value"><?php echo htmlspecialchars($user['EmailAddress']); ?></span>
            </div>
        </div>

        <div class="main-content">
            <div class="welcome-card">
                <h1>Welcome, <?php echo htmlspecialchars($user['FullName']); ?>!</h1>
                <p>Select an option from the sidebar to manage your sit-in sessions.</p>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2024 College of Computer Studies
    </footer>

</body>
</html>