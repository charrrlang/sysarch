<?php
session_start();
include 'db_connect.php'; 

if (!isset($_SESSION['id_number'])) {
    header("Location: login.php");
    exit();
}

$id_number = $_SESSION['id_number'];

// Fetch user data
$query = $conn->query("SELECT * FROM users WHERE Id = '$id_number'");
$user = $query->fetch_assoc();

// Fetch announcements
$announcement_query = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC");

$profile_pic = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default.png';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Sit-in Monitoring</title>
    <style>
        /* ... Keep all your existing CSS ... */
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background-color: #f4f7f6; height: 100vh; display: flex; flex-direction: column; }
        header { background-color: #b0b1a8; display: flex; padding: 15px 60px; align-items: center; justify-content: space-between; width: 100%; box-sizing: border-box; border-bottom: 1px solid #999; z-index: 1000; }
        .logo-group { display: flex; align-items: center; gap: 20px; }
        .UC-logo { width: 50px; height: auto; }
        .system-title { font-size: 22px; font-weight: bold; color: #1a2fa3; margin: 0; }
        .auth-group { display: flex; gap: 25px; align-items: center; }
        .nav-link { color: #1a2fa3; text-decoration: none; font-weight: bold; font-size: 15px; }
        .app-body { display: flex; flex-grow: 1; overflow: hidden; }
        .sidebar { width: 280px; background-color: #ffffff; border-right: 2px solid #1a2fa3; padding: 30px 20px; display: flex; flex-direction: column; box-shadow: 2px 0 5px rgba(0,0,0,0.05); align-items: center; }
        .profile-pic-container { width: 120px; height: 120px; border-radius: 50%; border: 3px solid #1a2fa3; overflow: hidden; margin-bottom: 20px; background-color: #eee; }
        .profile-pic-container img { width: 100%; height: 100%; object-fit: cover; }
        .sidebar h3 { color: #1a2fa3; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0; width: 100%; text-align: left; }
        .detail-box { margin-bottom: 20px; width: 100%; text-align: left; }
        .label { font-size: 11px; color: #888; text-transform: uppercase; font-weight: bold; display: block; }
        .value { font-size: 16px; color: #333; font-weight: 600; }

        .main-content { flex-grow: 1; padding: 50px; overflow-y: auto; }

        .announcement-card { background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; border-left: 6px solid #f1c40f; margin-bottom: 25px; }
        .announcement-card:first-of-type { border-left-color: #28a745; }
        .announcement-header { background-color: #fdfdfd; padding: 15px 25px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .announcement-title { font-weight: bold; color: #1a2fa3; font-size: 18px; }
        .announcement-date { font-size: 12px; color: #888; }
        .announcement-body { padding: 25px; color: #444; line-height: 1.6; }

        /* FEEDBACK STYLES */
        .feedback-section { background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); padding: 30px; border-top: 5px solid #1a2fa3; margin-top: 40px; }
        .feedback-section h3 { color: #1a2fa3; margin-top: 0; }
        .feedback-form textarea { width: 100%; height: 120px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; resize: none; margin-bottom: 15px; box-sizing: border-box; }
        .btn-submit { background-color: #1a2fa3; color: white; border: none; padding: 12px 30px; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background-color: #0d1a6d; }
        
        footer { background-color: #2c3e50; color: white; text-align: center; padding: 10px 0; font-size: 14px; }

        #notif-toast {
    visibility: hidden;
    min-width: 300px;
    background-color: #1a2fa3; /* Matches your UI */
    color: white;
    padding: 16px;
    position: fixed;
    right: 30px;
    bottom: 30px;
    z-index: 9999;
    border-radius: 8px;
    border-left: 5px solid #f1c40f;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}

/* This is the class the JavaScript adds */
#notif-toast.show {
    visibility: visible;
    animation: fadein 0.5s;
}

@keyframes fadein {
    from { bottom: 0; opacity: 0; }
    to { bottom: 30px; opacity: 1; }
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
            <div class="detail-box"><span class="label">ID Number</span><span class="value"><?php echo htmlspecialchars($user['Id']); ?></span></div>
            <div class="detail-box"><span class="label">Student Name</span><span class="value"><?php echo htmlspecialchars($user['FullName']); ?></span></div>
            <div class="detail-box"><span class="label">Course & Year</span><span class="value"><?php echo htmlspecialchars($user['Course'] . " - " . $user['CourseLevel']); ?></span></div>
            <div class="detail-box"><span class="label">Email Address</span><span class="value"><?php echo htmlspecialchars($user['EmailAddress']); ?></span></div>
            <div class="detail-box"><span class="label">Remaining Sessions</span><span class="value" style="color: #1a2fa3; font-size: 20px;"><?php echo htmlspecialchars($user['sessions_remaining']); ?></span></div>
        </div> 

        <div class="main-content">
            <h2 style="color: #1a2fa3; margin-top: 0;">📢 Announcements</h2>
            <?php if ($announcement_query->num_rows > 0): ?>
                <?php while($row = $announcement_query->fetch_assoc()): ?>
                    <div class="announcement-card">
                        <div class="announcement-header">
                            <span class="announcement-title">Notice</span>
                            <span class="announcement-date">Posted on: <?php echo date('M d, Y | h:i A', strtotime($row['date_posted'])); ?></span>
                        </div>
                        <div class="announcement-body"><?php echo nl2br(htmlspecialchars($row['content'])); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="announcement-card" style="border-left: 6px solid #ccc;">
                    <div class="announcement-body"><p style="color: #888; font-style: italic; text-align: center;">No current announcements.</p></div>
                </div>
            <?php endif; ?>

            
        </div>
    </div>

    <footer>&copy; 2026 College of Computer Studies</footer>

   
<?php include 'footer.php'; ?>

</body>
</html>