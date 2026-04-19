<?php
session_start();
include 'db_connect.php';

// 1. Redirect if not logged in
if (!isset($_SESSION['id_number'])) {
    header("Location: login.php");
    exit();
}

$id_number = $_SESSION['id_number'];

// 2. Fetch Sit-in History using the correct column name: 'id_number'
// We also use a prepared statement for better security
$stmt = $conn->prepare("SELECT * FROM sitin_records WHERE id_number = ? ORDER BY login_time DESC");
$stmt->bind_param("s", $id_number);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sit-in History - CCS Monitoring</title>
    <style>
        html, body { 
            height: 100%; margin: 0; 
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f7f6;
            display: flex; flex-direction: column;
        }

        header { background-color: #b0b1a8; display: flex; padding: 15px 60px; align-items: center; justify-content: space-between; width: 100%; box-sizing: border-box; border-bottom: 1px solid #999; z-index: 1000; }
        .logo-group { display: flex; align-items: center; gap: 20px; }
        .UC-logo { width: 50px; height: auto; }
        .system-title { font-size: 22px; font-weight: bold; color: #1a2fa3; margin: 0; }
        .auth-group { display: flex; gap: 25px; align-items: center; }
        .nav-link { color: #1a2fa3; text-decoration: none; font-weight: bold; font-size: 15px; }

        .app-body { display: flex; flex: 1; overflow: hidden; }

        .sidebar {
            width: 260px; background-color: #ffffff;
            border-right: 2px solid #1a2fa3; padding: 30px 25px;
        }
        .sidebar h3 { color: #1a2fa3; border-bottom: 2px solid #f4f7f6; padding-bottom: 10px; margin-top: 0; }
        .label { font-size: 10px; color: #888; text-transform: uppercase; font-weight: bold; display: block; }
        .value { font-size: 15px; color: #333; font-weight: 600; display: block; margin-bottom: 20px; }

        .main-content { flex-grow: 1; padding: 40px; overflow-y: auto; }
        .history-card {
            background: white; padding: 30px; border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        table { width: 100%; border-collapse: collapse; }
        table th { background-color: #1a2fa3; color: white; text-align: left; padding: 12px; font-size: 14px; }
        table td { padding: 12px; border-bottom: 1px solid #eee; color: #555; font-size: 14px; }
        
        .status-badge { padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-ongoing { background-color: #fff3cd; color: #856404; }
        .logo-group { display: flex; align-items: center; gap: 20px; }
        .UC-logo { width: 50px; height: auto; }
        footer { background-color: #2c3e50; color: white; text-align: center; padding: 12px 0; font-size: 13px; }

        .btn-feedback {
    background-color: #1a2fa3; /* Matches your UC system theme */
    color: white;
    padding: 4px 10px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 11px;
    font-weight: bold;
    margin-left: 10px;
    display: inline-block;
    transition: all 0.3s ease;
}

.btn-feedback:hover {
    background-color: #0d1a70;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Ensure the status cell content stays centered and aligned */
td:last-child {
    display: flex;
    align-items: center;
    justify-content: flex-start;
}

/* Critical for the pop-up to appear */
#notif-toast {
    visibility: hidden;
    min-width: 300px;
    background-color: #1a2fa3;
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
            <a href="homepage.php" class="nav-link">Home</a>
            <a href="editprofile.php" class="nav-link">Edit Profile</a>
            <a href="history.php" class="nav-link" style="text-decoration: underline;">History</a>
            <a href="reservation.php" class="nav-link">Reservation</a>
            <a href="welcomepage.php" class="nav-link" style="color: #d9534f;">Logout</a>
        </div>
    </header>

    <div class="app-body">
        <div class="sidebar">
            <h3>Student Profile</h3>
            <span class="label">ID Number</span>
            <span class="value"><?php echo htmlspecialchars($_SESSION['id_number']); ?></span>
            
            <span class="label">Student Name</span>
            <span class="value"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
        </div>

        <div class="main-content">
            <div class="history-card">
                <h2 style="margin-top:0;">Sit-in History</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Purpose</th>
                            <th>Lab Room</th>
                            <th>Login Time</th>
                            <th>Logout Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php 
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $login_fmt = date('M d, Y - h:i A', strtotime($row['login_time']));
            $logout_fmt = $row['logout_time'] ? date('M d, Y - h:i A', strtotime($row['logout_time'])) : "---";
            
            // Logic to determine if session is finished
            $is_done = ($row['status'] === 'Completed' || !empty($row['logout_time']));
            $status_class = $is_done ? "status-completed" : "status-ongoing";
            $status_text = $is_done ? "Completed" : "Ongoing";
            ?>
            
            <tr>
                <td><?php echo htmlspecialchars($row['purpose'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($row['lab_room'] ?? 'N/A'); ?></td>
                <td><?php echo $login_fmt; ?></td>
                <td><?php echo $logout_fmt; ?></td>
                <td>
                    <span class="status-badge <?php echo $status_class; ?>">
                        <?php echo $status_text; ?>
                    </span>

                    <?php if ($is_done): ?>
                        <a href="feedback.php?id=<?php echo $row['id']; ?>" class="btn-feedback">
                            Feedback
                        </a>
                    <?php endif; ?>
                </td>
            </tr>

            <?php 
        }
    } else {
        echo "<tr><td colspan='5' style='text-align:center;'>No records found for ID: " . htmlspecialchars($id_number) . "</td></tr>";
    }
    ?>
</tbody>
                </table>
            </div>
        </div>
    </div>

    <footer>&copy; 2026 College of Computer Studies</footer>
<?php include 'footer.php'; ?>

</body>
</html>