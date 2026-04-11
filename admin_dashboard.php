<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in (using role for security)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Dynamic Stats
$total_students = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$active_sitin = $conn->query("SELECT COUNT(*) as count FROM sitin_records WHERE status = 'Approved'")->fetch_assoc()['count'];
$total_sitin_records = $conn->query("SELECT COUNT(*) as count FROM sitin_records")->fetch_assoc()['count'];

// Pie Chart Data
$chart_sql = $conn->query("SELECT purpose, COUNT(*) as count FROM sitin_records GROUP BY purpose");
$purposes = []; $counts = [];
while($row = $chart_sql->fetch_assoc()) {
    $purposes[] = $row['purpose'];
    $counts[] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        
        /* Consistent Header Style */
        header { 
            background-color: #b0b1a8; 
            padding: 10px 50px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid #999; 
        }
        .logo-group { display: flex; align-items: center; gap: 10px; }
        .logo-group img { width: 40px; }
        .system-title { color: #1a2fa3; font-weight: bold; font-size: 20px; margin: 0; }

        /* Navigation */
        .nav-links { display: flex; align-items: center; }
        .nav-links a { 
            color: #1a2fa3; 
            text-decoration: none; 
            font-size: 13px; 
            margin-left: 20px; 
            font-weight: bold; 
        }
        .nav-links a:hover { text-decoration: underline; }
        .btn-logout { color: #d9534f !important; }

        /* Dashboard Layout */
        .container { padding: 40px; max-width: 1200px; margin: 0 auto; }
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        
        .card { 
            background: white; 
            border-radius: 10px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
            overflow: hidden; 
            border: none;
        }
        .card-header { 
            background: #1a2fa3; 
            color: white; 
            padding: 15px 20px; 
            font-weight: bold; 
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-body { padding: 25px; }

        /* Stats Styling */
        .stat-item { 
            display: flex; 
            justify-content: space-between; 
            padding: 10px 0; 
            border-bottom: 1px solid #edf2f7; 
        }
        .stat-label { font-weight: 600; color: #4a5568; }
        .stat-value { font-weight: bold; color: #1a2fa3; }

        textarea { 
            width: 100%; 
            height: 100px; 
            padding: 12px; 
            border: 1px solid #cbd5e0; 
            border-radius: 6px; 
            resize: none; 
            box-sizing: border-box; 
            margin-bottom: 15px;
        }
        .btn-submit { 
            background: #28a745; 
            color: white; 
            border: none; 
            padding: 10px 25px; 
            border-radius: 6px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.2s;
        }
        .btn-submit:hover { background: #218838; }
    </style>
</head>
<body>

<header>
    <div class="logo-group">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/University_of_Cebu_Logo.png/960px-University_of_Cebu_Logo.png" alt="UC Logo">
        <h1 class="system-title">College of Computer Studies Sit-in Monitoring</h1>
    </div>

    <nav class="nav-links">
        <a href="admin_dashboard.php" style="text-decoration: underline;">Home</a>
        <a href="search_student.php">Search</a>
        <a href="view_students.php">Students</a>
        <a href="sit_in.php">Sit-in</a>
        <a href="view_sitin_records.php">Records</a>
        <a href="reservation_admin.php">Reservation</a>
        <a href="feedback_reports.php">Feedback Repoerts</a>
        <a href="welcomepage.php" class="btn-logout">Logout</a>
    </nav>
</header>

<div class="container">
    <div class="dashboard-grid">
        <div class="card">
            <div class="card-header">📊 System Statistics</div>
            <div class="card-body">
                <div class="stat-item">
                    <span class="stat-label">Students Registered</span>
                    <span class="stat-value"><?php echo $total_students; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Current Active Sit-ins</span>
                    <span class="stat-value"><?php echo $active_sitin; ?></span>
                </div>
                
                <div class="stat-item" style="border-bottom: none; margin-bottom: 20px;">
                    <span class="stat-label">Total Historical Records</span>
                    <span class="stat-value"><?php echo $total_sitin_records; ?></span>
                </div>
                <canvas id="purposeChart" style="max-height: 250px;"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">📢 Post Announcement</div>
            <div class="card-body">
                <form action="post_announcement.php" method="POST">
                    <label style="display:block; margin-bottom: 10px; font-weight:600; color:#4a5568;">What's the news today?</label>
                    <textarea name="content" placeholder="Type your announcement here..." required></textarea>
                    <button type="submit" class="btn-submit">Post Announcement</button>
                </form>
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
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

</body>
</html>