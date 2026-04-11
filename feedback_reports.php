<?php
session_start();
include 'db_connect.php';

// Security: Only allow Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Fetch feedback joined with user details to see WHO sent the message
$sql = "SELECT f.*, u.FullName, u.Course, u.CourseLevel 
        FROM feedbacks f 
        JOIN users u ON f.id_number = u.Id 
        ORDER BY f.date_submitted DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Reports | Admin</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
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
        
        .container { padding: 40px; max-width: 1100px; margin: 0 auto; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #dee2e6; }
        .card-header { padding: 25px; border-bottom: 1px solid #dee2e6; background: #fafafa; }
        .card-title { font-size: 20px; font-weight: 700; color: #1a2fa3; margin: 0; }

        table { width: 100%; border-collapse: collapse; }
        thead th { background-color: #1a2fa3; color: white; padding: 15px 20px; text-align: left; font-size: 11px; text-transform: uppercase; }
        tbody td { padding: 16px 20px; border-bottom: 1px solid #f1f1f1; font-size: 14px; vertical-align: top; }
        
        .student-info { font-weight: bold; color: #333; display: block; }
        .student-sub { font-size: 11px; color: #888; }
        .message-text { line-height: 1.5; color: #444; background: #f9f9f9; padding: 10px; border-radius: 6px; border-left: 3px solid #1a2fa3; }
        .btn-logout { color: #d9534f !important; }
        .date-text { font-size: 12px; color: #666; font-style: italic; }
    </style>
</head>
<body>

<header>
    <div class="logo-group">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/University_of_Cebu_Logo.png/960px-University_of_Cebu_Logo.png" alt="UC Logo">
        <h1 class="system-title">CCS Sit-in Monitoring | Admin</h1>
    </div>
    <nav class="admin-navbar">
        <div class="nav-links">
        <a href="admin_dashboard.php">Home</a>
        <a href="search_student.php">Search</a>
        <a href="view_students.php">Students</a>
        <a href="sit_in.php">Sit-in</a>
        <a href="view_sitin_records.php">Records</a>
        <a href="reservation_admin.php">Reservation</a>
        <a href="feedback_reports.php">Feedback Repoerts</a>
        <a href="welcomepage.php" class="btn-logout">Log out</a>
        </div>
    </nav>
</header>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Student Feedback Reports</h2>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th width="25%">Student Details</th>
                    <th width="55%">Feedback Message</th>
                    <th width="20%">Date Submitted</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <span class="student-info"><?php echo htmlspecialchars($row['FullName']); ?></span>
                            <span class="student-sub"><?php echo htmlspecialchars($row['id_number']); ?></span><br>
                            <span class="student-sub"><?php echo htmlspecialchars($row['Course']); ?> - <?php echo $row['CourseLevel']; ?></span>
                        </td>
                        <td>
                            <div class="message-text">
                                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                            </div>
                        </td>
                        <td>
                            <span class="date-text">
                                <?php echo date('M d, Y', strtotime($row['date_submitted'])); ?><br>
                                <?php echo date('h:i A', strtotime($row['date_submitted'])); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 50px; color: #999;">No feedback messages received yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>