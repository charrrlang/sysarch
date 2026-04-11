<?php
session_start();
include 'db_connect.php';

// Restricted to Admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Fetch only ACTIVE sit-in sessions (those currently in the lab)
$sql = "SELECT * FROM sitin_records WHERE status = 'Active' ORDER BY login_time DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Sit-ins | Admin Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        
        /* --- ADMIN DASHBOARD HEADER STYLE --- */
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

        /* --- CONTENT --- */
        .container { padding: 40px; max-width: 1200px; margin: 0 auto; }
        
        .card { 
            background: white; 
            border-radius: 12px; 
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .card-header { 
            padding: 25px; 
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
        }

        .card-title { font-size: 20px; font-weight: 700; color: #1a2fa3; margin: 0; }

        /* --- TABLE STYLE --- */
        table { width: 100%; border-collapse: collapse; }
        thead th { 
            background-color: #1a2fa3; 
            color: white; 
            padding: 15px 20px; 
            text-align: left; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
        }
        tbody td { padding: 16px 20px; border-bottom: 1px solid #f1f1f1; font-size: 14px; color: #444; }
        tbody tr:hover { background-color: #f9f9ff; }
        
        .id-badge { 
            color: #1a2fa3; 
            font-weight: 700; 
            background: #eef1ff;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .status-pill {
            background-color: #e6ffed;
            color: #28a745;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #c6f6d5;
        }

        .btn-logout-row {
            background-color: #d9534f;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-logout-row:hover { background-color: #c9302c; box-shadow: 0 2px 8px rgba(217, 83, 79, 0.3); }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #c3e6cb;
            font-weight: 500;
        }
    </style>
</head>
<body>

<header>
    <div class="logo-group">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/University_of_Cebu_Logo.png/960px-University_of_Cebu_Logo.png" alt="UC Logo">
        <h1 class="system-title">College of Computer Studies Sit-in Monitoring</h1>
    </div>
    <nav class="nav-links">
        <a href="admin_dashboard.php">Home</a>
        <a href="search_student.php">Search</a>
        <a href="view_students.php">Students</a>
        <a href="sit_in.php" class="active">Sit-in</a>
        <a href="view_sitin_records.php">Records</a>
        <a href="reservation_admin.php">Reservation</a>
        <a href="feedback_reports.php">Feedback Repoerts</a>
        <a href="welcomepage.php" class="btn-logout">Logout</a>
    </nav>
</header>

<div class="container">
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'logged_out'): ?>
        <div class="alert-success">
            ✔ Student has been successfully logged out and session moved to records.
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Current Lab Sessions</h2>
            <span class="status-pill">● <?php echo $result->num_rows; ?> Students Active</span>
        </div>
        
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Student Name</th>
                        <th>Lab Room</th>
                        <th>Purpose</th>
                        <th>Login Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><span class="id-badge"><?php echo htmlspecialchars($row['id_number']); ?></span></td>
                            <td style="font-weight: 500;"><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><strong>Lab <?php echo htmlspecialchars($row['lab_room']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                            <td style="color: #666;"><?php echo date('h:i A', strtotime($row['login_time'])); ?></td>
                            <td>
                                <a href="logout_handler.php?id=<?php echo $row['id']; ?>" 
                                   class="btn-logout-row" 
                                   onclick="return confirm('End sit-in session for this student?')">
                                    Logout Student
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                                No students are currently sitting in.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>