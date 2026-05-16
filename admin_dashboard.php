<?php
// 1. START OUTPUT BUFFERING (Fixes the "Header already sent" error)
ob_start(); 
session_start();
include 'db_connect.php';

// 2. SECURITY CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// 3. --- HANDLE REWARD SUBMISSION (PRG PATTERN) ---
if (isset($_POST['grant_reward'])) {
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $points_to_add = intval($_POST['amount']);
    
    $update = $conn->query("UPDATE users SET points = points + $points_to_add WHERE Id = '$student_id'");
    
    if ($update) { 
        $_SESSION['success_msg'] = "Successfully added $points_to_add points to $student_id!";
        header("Location: admin_dashboard.php");
        exit(); 
    }
}

$msg = "";
if (isset($_SESSION['success_msg'])) {
    $msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

// 4. --- ANALYTICS DATA FETCHING ---

// Reward Points
$points_stats = $conn->query("SELECT SUM(points) as total, AVG(points) as avg FROM users")->fetch_assoc();
$total_points_issued = $points_stats['total'] ?? 0;

// Sit-in Hours
$hours_data = $conn->query("SELECT SUM(TIMESTAMPDIFF(MINUTE, login_time, logout_time) / 60) as total_hrs 
                            FROM sitin_records 
                            WHERE status = 'Completed'")->fetch_assoc();
$total_hours = $hours_data['total_hrs'] ?? 0;

// Task Completion
$total_records = $conn->query("SELECT COUNT(*) as count FROM sitin_records")->fetch_assoc()['count'];
$completed_records = $conn->query("SELECT COUNT(*) as count FROM sitin_records WHERE status = 'Completed'")->fetch_assoc()['count'];
$task_percent = ($total_records > 0) ? round(($completed_records / $total_records) * 100) : 0;

// General Stats
$total_students = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$active_sitin = $conn->query("SELECT COUNT(*) as count FROM sitin_records WHERE status = 'Approved'")->fetch_assoc()['count'];

// Pie Chart Data
$chart_sql = $conn->query("SELECT purpose, COUNT(*) as count FROM sitin_records GROUP BY purpose");
$purposes = []; $counts = [];
while($row = $chart_sql->fetch_assoc()) {
    $purposes[] = $row['purpose'];
    $counts[] = $row['count'];
}

// Leaderboard
$leaderboard = $conn->query("SELECT FullName, points, Course FROM users ORDER BY points DESC LIMIT 5");

/**
 * 🆕 FIXED FETCH FOR TESTIMONIALS
 * Uses JOIN to link the id_number from testimonials to the Id in users.
 * This allows us to pull the real name and course.
 */
$testimonials_query = $conn->query("
    SELECT t.content, t.date_submitted, u.FullName, u.Course 
    FROM testimonials t 
    JOIN users u ON t.id_number = u.Id 
    ORDER BY t.date_submitted DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | UC Sit-in Monitoring</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        header { background-color: #b0b1a8; padding: 10px 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #999; }
        .logo-group { display: flex; align-items: center; gap: 10px; }
        .logo-group img { width: 40px; }
        .system-title { color: #1a2fa3; font-weight: bold; font-size: 20px; margin: 0; }
        .nav-links a { color: #1a2fa3; text-decoration: none; font-size: 13px; margin-left: 20px; font-weight: bold; }
        .btn-logout { color: #d9534f !important; }
        .container { padding: 30px; max-width: 1400px; margin: 0 auto; }
        .analytics-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .analytics-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 5px solid #1a2fa3; }
        .ana-label { font-size: 12px; color: #888; font-weight: bold; text-transform: uppercase; }
        .ana-value { font-size: 28px; font-weight: bold; color: #1a2fa3; margin: 5px 0; }
        .ana-sub { font-size: 11px; color: #28a745; font-weight: bold; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; align-items: start; }
        .card { background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 25px; overflow: hidden; }
        .card-header { background: #1a2fa3; color: white; padding: 15px; font-weight: bold; font-size: 15px; }
        .card-body { padding: 20px; }
        input, select, textarea { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #cbd5e0; border-radius: 6px; box-sizing: border-box; }
        .btn-action { background: #28a745; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; }
        .btn-reward { background: #1a2fa3; }
        .leader-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f1f1; }
        .rank { font-weight: bold; color: #f1c40f; margin-right: 10px; }
        .stat-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
        .testimonial-row { padding: 12px 0; border-bottom: 1px solid #f8f9fa; }
        .testimonial-text { font-style: italic; font-size: 13.5px; color: #4a5568; margin: 0 0 5px 0; line-height: 1.4; }
        .testimonial-meta { font-size: 11px; font-weight: bold; color: #718096; display: flex; justify-content: space-between; }
    </style>
</head>
<body>

<header>
    <div class="logo-group">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/University_of_Cebu_Logo.png/960px-University_of_Cebu_Logo.png" alt="UC logo">
        <h1 class="system-title">College of Computer Studies Sit-in Monitoring</h1>
    </div>
    <nav class="admin-navbar">
        <div class="nav-links">
            <a href="admin_dashboard.php">Home</a>
            <a href="search_student.php">Search</a>
            <a href="view_students.php">Students</a>
            <a href="sit_in.php">Sit-in</a>
            <a href="view_sitin_records.php">Records</a>
            <a href="reservation_admin.php">Reservation</a>
            <a href="feedback_reports.php">Feedback Reports</a>
            <a href="welcomepage.php" class="btn-logout">Log out</a>
        </div>
    </nav>
</header>

<div class="container">
    <?php if($msg): ?>
        <div class="alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="analytics-row">
        <div class="analytics-card" style="border-left-color: #1a2fa3;">
            <div class="ana-label">Total Rewards Issued (60%)</div>
            <div class="ana-value"><?php echo number_format($total_points_issued); ?> pts</div>
            <div class="ana-sub">Avg per Student: <?php echo round($points_stats['avg'], 1); ?></div>
        </div>
        <div class="analytics-card" style="border-left-color: #f1c40f;">
            <div class="ana-label">Total Sit-in Hours (20%)</div>
            <div class="ana-value"><?php echo number_format($total_hours, 1); ?> hrs</div>
            <div class="ana-sub">Lab Usage Duration</div>
        </div>
        <div class="analytics-card" style="border-left-color: #28a745;">
            <div class="ana-label">Task Completion (20%)</div>
            <div class="ana-value"><?php echo $task_percent; ?>%</div>
            <div class="ana-sub"><?php echo $completed_records; ?> sessions finalized</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="col">
            <div class="card">
                <div class="card-header">📊 Lab Usage</div>
                <div class="card-body">
                    <div class="stat-item"><span>Registered Users</span><span><?php echo $total_students; ?></span></div>
                    <div class="stat-item"><span>Active Sessions</span><span><?php echo $active_sitin; ?></span></div>
                    <canvas id="purposeChart" style="margin-top:20px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-header">🎁 Grant Reward Points</div>
                <div class="card-body">
                    <form method="POST">
                        <select name="student_id" required>
                            <option value="">-- Select Student --</option>
                            <?php
                            $students = $conn->query("SELECT Id, FullName FROM users");
                            while($s = $students->fetch_assoc()) {
                                echo "<option value='".$s['Id']."'>".$s['Id']." - ".$s['FullName']."</option>";
                            }
                            ?>
                        </select>
                        <input type="number" name="amount" placeholder="Points to add..." required>
                        <button type="submit" name="grant_reward" class="btn-action btn-reward">Update Points</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">📢 Lab Announcement</div>
                <div class="card-body">
                    <form action="post_announcement.php" method="POST">
                        <textarea name="content" placeholder="Broadcast to all students..." required></textarea>
                        <button type="submit" class="btn-action">Post Notice</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-header">🏆 Top Performers</div>
                <div class="card-body">
                    <?php 
                    $rank = 1;
                    while($row = $leaderboard->fetch_assoc()): ?>
                        <div class="leader-row">
                            <span><span class="rank">#<?php echo $rank++; ?></span> <?php echo $row['FullName']; ?></span>
                            <span style="font-weight:bold; color:#1a2fa3;"><?php echo $row['points']; ?> pts</span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">💬 Latest Student Testimonials</div>
                <div class="card-body" style="max-height: 380px; overflow-y: auto;">
                    <?php if ($testimonials_query && $testimonials_query->num_rows > 0): ?>
                        <?php while($t = $testimonials_query->fetch_assoc()): ?>
                            <div class="testimonial-row">
                                <p class="testimonial-text">"<?php echo htmlspecialchars($t['content']); ?>"</p>
                                <div class="testimonial-meta">
                                    <span style="color: #1a2fa3;">- <?php echo htmlspecialchars($t['FullName']); ?> (<?php echo htmlspecialchars($t['Course']); ?>)</span>
                                    <span style="color: #aaa; font-weight: normal;"><?php echo date('M d', strtotime($t['date_submitted'])); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color: #999; text-align: center; font-size: 13px; padding: 10px 0;">No student testimonials submitted yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    new Chart(document.getElementById('purposeChart'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($purposes); ?>,
            datasets: [{ 
                data: <?php echo json_encode($counts); ?>, 
                backgroundColor: ['#1a2fa3','#f1c40f','#28a745','#d9534f','#6c757d'],
                borderWidth: 1
            }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });
</script>

</body>
</html>
<?php 
ob_end_flush(); 
?>