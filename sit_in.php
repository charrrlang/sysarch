<?php
session_start();
include 'db_connect.php';

// Restricted to Admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Fetch only ACTIVE sit-in sessions
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
        header { background-color: #b0b1a8; padding: 10px 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #999; }
        .logo-group { display: flex; align-items: center; gap: 10px; }
        .logo-group img { width: 40px; }
        .system-title { color: #1a2fa3; font-weight: bold; font-size: 20px; margin: 0; }
        .nav-links { display: flex; align-items: center; }
        .nav-links a { color: #1a2fa3; text-decoration: none; font-size: 13px; margin-left: 20px; font-weight: bold; }
        .nav-links a:hover { text-decoration: underline; }
        .btn-logout { color: #d9534f !important; }

        .container { padding: 40px; max-width: 1200px; margin: 0 auto; }
        .card { background: white; border-radius: 12px; border: 1px solid #dee2e6; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
        .card-header { padding: 25px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; background: #fafafa; }
        .card-title { font-size: 20px; font-weight: 700; color: #1a2fa3; margin: 0; }

        table { width: 100%; border-collapse: collapse; }
        thead th { background-color: #1a2fa3; color: white; padding: 15px 20px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        tbody td { padding: 16px 20px; border-bottom: 1px solid #f1f1f1; font-size: 14px; color: #444; }
        
        .id-badge { color: #1a2fa3; font-weight: 700; background: #eef1ff; padding: 4px 10px; border-radius: 6px; }
        .status-pill { background-color: #e6ffed; color: #28a745; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: 700; border: 1px solid #c6f6d5; }

        .btn-logout-row { background-color: #d9534f; color: white; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-size: 12px; font-weight: 600; transition: 0.2s; }
        .btn-logout-row:hover { background-color: #c9302c; }

        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #c3e6cb; font-weight: 500; }
        
        /* Modal Style */
        #logoutModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center; }
        .modal-content { background:white; padding:30px; border-radius:12px; width:400px; box-shadow:0 10px 25px rgba(0,0,0,0.2); text-align:center; }
   
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
        <a href="sit_in.php" style="text-decoration: underline;">Sit-in</a>
        <a href="view_sitin_records.php">Records</a>
        <a href="reservation_admin.php">Reservation</a>
        <a href="feedback_reports.php">Feedback Reports</a>
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
                        <th>PC No.</th>
                        
    

                    
                </thead>
                
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><span class="id-badge"><?php echo htmlspecialchars($row['id_number']); ?></span></td>
                            <td style="font-weight: 500;"><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><strong>Lab <?php echo htmlspecialchars($row['lab_room']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                            <td><?php echo date('h:i A', strtotime($row['login_time'])); ?></td>
                            <td>
                                <button type="button" class="btn-logout-row" 
                                        onclick="openLogoutModal('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['fullname']); ?>')">
                                    Logout Student
                                </button>
                            <td><?php echo htmlspecialchars($row['pc_no']); ?></td>

                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #999;">No students are currently sitting in.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="logoutModal">
    <div class="modal-content">
        <h2 style="color:#1a2fa3; margin-top:0;">End Session</h2>
        <p>Ending sit-in for: <br><strong id="modalStudentName"></strong></p>
        <p style="font-size: 14px; color: #666; margin-bottom: 20px;">Did the student complete their lab task?</p>
        
        <form action="logout_handler.php" method="GET" style="display:flex; flex-direction:column; gap:12px;">
            <input type="hidden" name="id" id="modalRecordId">
            
            <button type="submit" name="task_status" value="Completed" 
                    style="background:#28a745; color:white; border:none; padding:12px; border-radius:6px; cursor:pointer; font-weight:bold;">
                ✔ Task Completed
            </button>
            
            <button type="submit" name="task_status" value="Not Completed" 
                    style="background:#f0ad4e; color:white; border:none; padding:12px; border-radius:6px; cursor:pointer; font-weight:bold;">
                ✖ Not Completed
            </button>
            
            <button type="button" onclick="closeLogoutModal()" style="background:none; border:none; color:#777; cursor:pointer; margin-top:10px;">Cancel</button>
        </form>
    </div>
</div>

<script>
function openLogoutModal(id, name) {
    document.getElementById('modalRecordId').value = id;
    document.getElementById('modalStudentName').innerText = name;
    document.getElementById('logoutModal').style.display = 'flex';
}

function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
}

window.onclick = function(event) {
    let modal = document.getElementById('logoutModal');
    if (event.target == modal) { closeLogoutModal(); }
}
</script>

</body>
</html>