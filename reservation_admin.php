<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['id_number'])) {
    header("Location: login.php");
    exit();
}

// Fetch pending records ordered by the closest date first
$query = "SELECT * FROM sitin_records WHERE status = 'Pending' ORDER BY date ASC, time ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Reservation List</title>
    <style>
        html, body { height: 100%; margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; color: #333; }
        
        /* Fixed Header to match student view */
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

        /* Navigation Links */
        .admin-navbar { display: flex; align-items: center; }
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

        /* Table and Content Styling */
        .container { padding: 40px; max-width: 1200px; margin: 0 auto; }
        h2 { color: #1a2fa3; font-size: 24px; margin-bottom: 25px; }
        .table-container { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1a2fa3; color: white; padding: 18px 15px; text-align: left; font-size: 12px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #edf2f7; font-size: 14px; }
        tr:hover { background-color: #f8fafc; }
        
        .btn-approve { 
            background-color: #28a745; 
            color: white; 
            padding: 8px 16px; 
            border-radius: 6px; 
            text-decoration: none; 
            font-size: 12px; 
            font-weight: bold; 
        }
        .badge-pending { background: #fffaf0; color: #b7791f; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; border: 1px solid #fbd38d; }
       
        .no-data { text-align: center; color: #718096; padding: 50px; font-style: italic; }
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
        <a href="feedback_reports.php">Feedback Repoerts</a>
        <a href="welcomepage.php" class="btn-logout">Log out</a>
        </div>
    </nav>
</header>

<div class="container">
    <h2>Pending Reservations</h2>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Student Name</th>
                    <th>Purpose</th>
                    <th>Laboratory</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight: bold; color: #1a2fa3;"><?php echo htmlspecialchars($row['id_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['fullname'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                        <td><span style="background: #edf2f7; padding: 3px 8px; border-radius: 4px;">Lab <?php echo htmlspecialchars($row['lab_room']); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                        <td><?php echo date('h:i A', strtotime($row['time'])); ?></td>
                        <td><span class="badge-pending">PENDING</span></td>
                        <td>
                            <a href="approve_handler.php?id=<?php echo $row['id']; ?>" 
                               class="btn-approve" 
                               onclick="return confirm('Approve this reservation for <?php echo htmlspecialchars($row['fullname']); ?>?')">
                               Approve
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-data">No pending reservations found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>