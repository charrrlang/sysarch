<?php
session_start();
include 'db_connect.php';

// Fetch all feedback with student names and session details
$sql = "SELECT f.*, u.FullName, s.lab_room, s.purpose 
        FROM feedback f 
        JOIN users u ON f.student_id = u.Id 
        JOIN sitin_records s ON f.record_id = s.id 
        ORDER BY f.date_submitted DESC";

$report_query = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Feedback Reports - Admin Panel</title>
    <style>
        /* Reusing your clean, professional UI aesthetics */
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
                .report-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #1a2fa3; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .rating-star { color: #f1c40f; font-weight: bold; }
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
        <a href="feedback_reports.php">Feedback Reports</a>
        <a href="welcomepage.php" class="btn-logout">Logout</a>
    </nav>
</header>
    <div class="report-container">
        <h2>Student Feedback Reports</h2>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Lab & Purpose</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $report_query->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['FullName']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['lab_room'] . " (" . $row['purpose'] . ")"); ?></td>
                    <td class="rating-star"><?php echo str_repeat('★', $row['rating']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($row['comment'])); ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['date_submitted'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html><?php

