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

/** 
 * REWARD POINTS LOGIC 
 */
$points_goal = 100;
$current_points = isset($user['points']) ? $user['points'] : 0;
$progress_percent = ($current_points / $points_goal) * 100;
if ($progress_percent > 100) $progress_percent = 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Sit-in Monitoring</title>
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

        /* Main Content */
        .main-content { flex-grow: 1; padding: 40px; overflow-y: auto; }
        .stat-card { background: white; padding: 25px; border-radius: 10px; border-top: 5px solid #1a2fa3; max-width: 400px; margin-bottom: 30px; }
        .progress-container { width: 100%; background: #eee; height: 10px; border-radius: 5px; margin: 10px 0; overflow: hidden; }
        .progress-fill { height: 100%; background: #1a2fa3; transition: width 0.5s; }

        /* --- CUSTOM MULTI-STEP MODAL --- */
        .modal-overlay {
            display: none; 
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 3000;
            align-items: center; justify-content: center;
        }
        .modal-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            width: 400px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }
        .modal-step { display: none; flex-direction: column; gap: 12px; }
        .modal-step.active { display: flex; }

        .modal-btn { padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-option { background: #f8f9fa; border: 1px solid #ddd; color: #333; }
        .btn-option.selected { background: #1a2fa3; color: white; border-color: #1a2fa3; }
        .btn-final-logout { background: #d9534f; color: white; margin-top: 10px; font-size: 16px; }
        .btn-cancel { background: transparent; color: #888; font-size: 13px; margin-top: 5px; }

        /* Announcements */
        .announcement-card { background: white; padding: 20px; border-radius: 10px; border-left: 5px solid #28a745; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        
        footer { background: #2c3e50; color: white; text-align: center; padding: 10px; font-size: 13px; }
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
            <div class="stat-card">
                <span class="label">Reward Progress</span>
                <div style="font-size: 24px; font-weight: bold; color: #1a2fa3;"><?php echo $current_points; ?> pts</div>
                <div class="progress-container">
                    <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%;"></div>
                </div>
            </div>

            <h2 style="color: #1a2fa3;">📢 Announcements</h2>
            <?php while($row = $announcement_query->fetch_assoc()): ?>
                <div class="announcement-card">
                    <strong>Notice:</strong> <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                    <div style="font-size: 11px; color: #aaa; margin-top: 10px;"><?php echo $row['date_posted']; ?></div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- MULTI-STEP LOGOUT MODAL -->
    <div id="logoutModal" class="modal-overlay">
        <div class="modal-box">
            <!-- STEP 1: SELECT STATUS -->
            <div id="step1" class="modal-step active">
                <h3>Step 1: Task Status</h3>
                <p>Did you complete your tasks for today?</p>
                <button type="button" class="modal-btn btn-option" onclick="selectStatus('Completed', this)">Yes, Task Completed</button>
                <button type="button" class="modal-btn btn-option" onclick="selectStatus('Not Completed', this)">No, Incomplete</button>
                <button type="button" class="modal-btn btn-cancel" onclick="closeLogoutModal()">Cancel</button>
            </div>

            <!-- STEP 2: ACTUAL LOGOUT BUTTON -->
            <div id="step2" class="modal-step">
                <h3>Step 2: Finalize</h3>
                <p id="status-preview" style="font-weight: bold; color: #28a745;"></p>
                <button type="button" class="modal-btn btn-final-logout" onclick="finalizeLogout()">Click here to Actual Logout</button>
                <button type="button" class="modal-btn btn-cancel" onclick="resetSteps()">Change Status</button>
            </div>
        </div>
    </div>

    <footer>&copy; 2026 College of Computer Studies</footer>

    <script>
        let selectedStatus = "";

        function openLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
            resetSteps();
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        function selectStatus(status, btnElement) {
            selectedStatus = status;
            
            // Visual feedback: Highlight selection
            document.querySelectorAll('.btn-option').forEach(btn => btn.classList.remove('selected'));
            btnElement.classList.add('selected');

            // Move to Step 2
            setTimeout(() => {
                document.getElementById('step1').classList.remove('active');
                document.getElementById('step2').classList.add('active');
                document.getElementById('status-preview').innerText = "Status: " + status;
            }, 400); // Slight delay for smooth transition
        }

        function resetSteps() {
            document.getElementById('step1').classList.add('active');
            document.getElementById('step2').classList.remove('active');
            selectedStatus = "";
        }

        function finalizeLogout() {
            if (selectedStatus !== "") {
                window.location.href = "logout_handler.php?status=" + selectedStatus;
            }
        }
    </script>
</body>
</html>