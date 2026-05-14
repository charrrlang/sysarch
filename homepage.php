<?php
session_start();
include 'db_connect.php'; 

if (!isset($_SESSION['id_number'])) {
    header("Location: login.php");
    exit();
}

$id_number = $_SESSION['id_number'];

// 1. Fetch user data
// Note: Changed 'Id' to '$id_number' based on your session logic
$query = $conn->query("SELECT * FROM users WHERE Id = '$id_number'");
$user = $query->fetch_assoc();

// 2. Fetch announcements
$announcement_query = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC");

$profile_pic = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default.png';

/** * REWARD POINTS LOGIC 
 */
$points_goal = 100;
$current_points = isset($user['points']) ? $user['points'] : 0;
$progress_percent = ($current_points / $points_goal) * 100;
if ($progress_percent > 100) $progress_percent = 100;

/** * SIT-IN SUMMARY CALCULATION 
 */
$summary_query = $conn->query("
    SELECT 
        COUNT(*) as total_sessions,
        SUM(TIMESTAMPDIFF(MINUTE, login_time, logout_time)) as total_minutes,
        AVG(TIMESTAMPDIFF(MINUTE, login_time, logout_time)) as avg_minutes,
        MAX(TIMESTAMPDIFF(MINUTE, login_time, logout_time)) as longest_minutes
    FROM sitin_records 
    WHERE id_number = '$id_number' AND logout_time IS NOT NULL
");
$summary = $summary_query->fetch_assoc();

$total_hrs = $summary['total_minutes'] ? round($summary['total_minutes'] / 60, 1) : 0;
$session_count = $summary['total_sessions'] ?? 0;
$avg_duration = $summary['avg_minutes'] ? round($summary['avg_minutes']) . " mins" : "0 mins";
$longest_session = $summary['longest_minutes'] ? round($summary['longest_minutes']) . " mins" : "0 mins";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard | CCS Sit-in</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background-color: #f4f7f6; height: 100vh; display: flex; flex-direction: column; }
        
        header { background-color: #b0b1a8; display: flex; padding: 15px 60px; align-items: center; justify-content: space-between; border-bottom: 1px solid #999; }
        .logo-group { display: flex; align-items: center; gap: 20px; }
        .UC-logo { width: 50px; }
        .system-title { font-size: 22px; font-weight: bold; color: #1a2fa3; margin: 0; }
        .nav-link { color: #1a2fa3; text-decoration: none; font-weight: bold; cursor: pointer; margin-left: 20px; }

        .app-body { display: flex; flex-grow: 1; overflow: hidden; }

        /* Sidebar */
        .sidebar { width: 280px; background: white; border-right: 2px solid #1a2fa3; padding: 30px 20px; display: flex; flex-direction: column; align-items: center; }
        .profile-pic-container { width: 120px; height: 120px; border-radius: 50%; border: 3px solid #1a2fa3; overflow: hidden; margin-bottom: 20px; }
        .profile-pic-container img { width: 100%; height: 100%; object-fit: cover; }
        .detail-box { margin-bottom: 15px; width: 100%; }
        .label { font-size: 11px; color: #888; text-transform: uppercase; font-weight: bold; }
        .value { font-size: 15px; color: #333; font-weight: 600; display: block; }

        /* Main Content Area */
        .main-content { flex-grow: 1; padding: 40px; overflow-y: auto; }

        /* Summary Grid */
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .summary-card { background: white; padding: 20px; border-radius: 10px; border: 1px solid #dee2e6; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .summary-card .stat-val { font-size: 22px; font-weight: bold; color: #1a2fa3; display: block; margin-top: 5px; }

        /* Reward Card */
        .stat-card { background: white; padding: 25px; border-radius: 10px; border-top: 5px solid #1a2fa3; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .progress-container { width: 100%; background: #eee; height: 12px; border-radius: 6px; margin: 15px 0; overflow: hidden; }
        .progress-fill { height: 100%; background: #1a2fa3; transition: width 0.8s ease-in-out; }

        /* Announcements */
        .announcement-card { background: white; padding: 20px; border-radius: 10px; border-left: 5px solid #28a745; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        
        /* Notification Toast */
        #notif-toast {
            visibility: hidden; min-width: 300px; background-color: #333; color: white; padding: 16px;
            position: fixed; right: 30px; bottom: 30px; z-index: 9999; border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3); border-left: 5px solid #f1c40f;
        }
        #notif-toast.show { visibility: visible; animation: fadein 0.5s; }
        @keyframes fadein { from { bottom: 0; opacity: 0; } to { bottom: 30px; opacity: 1; } }

        footer { background: #2c3e50; color: white; text-align: center; padding: 15px; font-size: 13px; margin-top: auto; }
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
                <img src="uploads/<?php echo $profile_pic; ?>" alt="Profile">
            </div>
            <h3>Student Profile</h3>
            <div class="detail-box"><span class="label">ID Number</span><span class="value"><?php echo htmlspecialchars($user['Id']); ?></span></div>
            <div class="detail-box"><span class="label">Name</span><span class="value"><?php echo htmlspecialchars($user['FullName']); ?></span></div>
            <div class="detail-box"><span class="label">Course</span><span class="value"><?php echo htmlspecialchars($user['Course']); ?></span></div>
            <div class="detail-box"><span class="label">Sessions</span><span class="value"><?php echo htmlspecialchars($user['sessions_remaining']); ?> left</span></div>
        </div>

        <div class="main-content">
            <h2 style="color: #1a2fa3; margin-top: 0;">📊 My Lab Performance</h2>
            <div class="summary-grid">
                <div class="summary-card">
                    <span class="label">Total Hours</span>
                    <span class="stat-val"><?php echo $total_hrs; ?> hrs</span>
                </div>
                <div class="summary-card">
                    <span class="label">Total Sessions</span>
                    <span class="stat-val"><?php echo $session_count; ?></span>
                </div>
                <div class="summary-card">
                    <span class="label">Avg Duration</span>
                    <span class="stat-val"><?php echo $avg_duration; ?></span>
                </div>
                <div class="summary-card">
                    <span class="label">Longest Sit-in</span>
                    <span class="stat-val"><?php echo $longest_session; ?></span>
                </div>
            </div>

            <div class="stat-card">
                <span class="label">Reward Progress</span>
                <div id="points-display" style="font-size: 28px; font-weight: bold; color: #1a2fa3;"><?php echo $current_points; ?> pts</div>
                <div class="progress-container">
                    <div id="progress-bar" class="progress-fill" style="width: <?php echo $progress_percent; ?>%;"></div>
                </div>
                <p style="font-size: 12px; color: #666; margin: 0;">Goal: 100 points for Lab Certification</p>
            </div>

            

            <h2 style="color: #1a2fa3;">📢 Announcements</h2>
            <?php if ($announcement_query->num_rows > 0): ?>
                <?php while($row = $announcement_query->fetch_assoc()): ?>
                    <div class="announcement-card">
                        <strong>Notice:</strong> <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                        <div style="font-size: 11px; color: #aaa; margin-top: 10px;"><?php echo date('M d, Y - h:i A', strtotime($row['date_posted'])); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #999;">No current announcements.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="notif-toast"></div>

    <footer>&copy; 2026 College of Computer Studies | Sit-in Monitoring System</footer>

    <script>
        // Store current points in a JS variable for the listener
        let currentPointsJS = <?php echo $current_points; ?>;

        function checkUpdates() {
            // Call the background script every 10 seconds
            fetch(`check_notifications.php?current_points=${currentPointsJS}`)
                .then(response => response.json())
                .then(data => {
                    if (data.message !== "") {
                        
                        // Set Toast Style based on type
                        const toast = document.getElementById('notif-toast');
                        toast.innerHTML = `<strong>Notification</strong><br>${data.message}`;
                        
                        if (data.points_update) {
                            toast.style.backgroundColor = "#28a745"; // Success Green
                            // Update the UI Live
                            currentPointsJS = data.new_total;
                            document.getElementById('points-display').innerText = data.new_total + " pts";
                            document.getElementById('progress-bar').style.width = (data.new_total > 100 ? 100 : data.new_total) + "%";
                        } else if (data.session_update) {
                            toast.style.backgroundColor = "#d9534f"; // Alert Red
                            setTimeout(() => { window.location.href = "welcomepage.php"; }, 4000);
                        } else {
                            toast.style.backgroundColor = "#1a2fa3"; // Info Blue
                        }

                        // Show the Toast
                        toast.classList.add('show');
                        setTimeout(() => { toast.classList.remove('show'); }, 6000);
                    }
                })
                .catch(err => console.log("Update check failed:", err));
        }

        // Start the polling
        setInterval(checkUpdates, 10000);
    </script>
</body>
</html>