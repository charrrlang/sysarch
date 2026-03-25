<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['id_number'])) {
    header("Location: login.php");
    exit();
}

// 1. Prepare User Info (id_number should now be the UserName from login_handler.php)
$id_number = $_SESSION['id_number'];
$full_name = $_SESSION['full_name']; 
$message = "";

// 2. FETCH FRESH BALANCE FROM 'users' TABLE
// We do this every time the page loads to ensure the sidebar is accurate
$stmt_bal = $conn->prepare("SELECT sessions_remaining FROM users WHERE UserName = ?");
$stmt_bal->bind_param("s", $id_number);
$stmt_bal->execute();
$res_bal = $stmt_bal->get_result()->fetch_assoc();

// Fallback to 30 if not found, but use the DB value if it exists
$sessions_left = ($res_bal) ? (int)$res_bal['sessions_remaining'] : 30;
$stmt_bal->close();

// 3. HANDLE RESERVATION SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_res'])) {
    
    if ($sessions_left <= 0) {
        $message = "<div style='color: white; background: #d9534f; padding: 10px; margin-bottom: 15px; border-radius: 5px;'>🚫 No sessions left. Please contact the administrator.</div>";
    } else {
        $purpose = $_POST['purpose'];
        $lab = $_POST['lab'];
        $time = $_POST['time_in'];
        $date = $_POST['date'];

        $conn->begin_transaction();
        try {
            // A. Insert into Sit-in Records
            $stmt = $conn->prepare("INSERT INTO sitin_records (id_number, fullname, purpose, lab_room, date, time, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("ssssss", $id_number, $full_name, $purpose, $lab, $date, $time);
            $stmt->execute();

            // B. THE DEDUCTION: Subtract 1 from 'users' table
            // We use 'UserName' because that is the column in your users table
            $update_stmt = $conn->prepare("UPDATE users SET sessions_remaining = sessions_remaining - 1 WHERE Id = ?");
            $update_stmt->bind_param("s", $id_number);
            $update_stmt->execute();

            // C. VERIFY: Ensure a row was actually changed
            if ($conn->affected_rows > 0) {
                $conn->commit();
                // Redirect to refresh the 'sessions_left' count immediately
                header("Location: reservation.php?success=1");
                exit();
            } else {
                throw new Exception("Deduction failed. Database could not find UserName: $id_number");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<div style='color: white; background: #d9534f; padding: 10px; margin-bottom: 15px; border-radius: 5px;'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Success message from Redirect
if (isset($_GET['success'])) {
    $message = "<div style='color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #c3e6cb;'>✔ Reservation successful! 1 session deducted.</div>";
}

// 4. FETCH HISTORY
$res_query = "SELECT * FROM sitin_records WHERE id_number = '$id_number' ORDER BY date DESC, time DESC";
$res_result = $conn->query($res_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservation | UC Sit-in</title>
    <style>
        html, body { height: 100%; margin: 0; font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; }
        header { background-color: #b0b1a8; padding: 10px 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #999; }
        .wrapper { display: flex; height: calc(100vh - 65px); }
        .sidebar { width: 250px; background: white; border-right: 2px solid #1a2fa3; padding: 40px 30px; }
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .content-box { background: white; border-radius: 12px; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .session-info { margin-top: 20px; padding: 15px; background: #f0f2ff; border-radius: 8px; border-left: 5px solid #1a2fa3; }
        .session-count { font-size: 28px; font-weight: bold; color: #1a2fa3; display: block; }
        .btn-submit { background-color: #1a2fa3; color: white; border: none; padding: 12px 30px; border-radius: 4px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-submit:disabled { background-color: #ccc; cursor: not-allowed; opacity: 0.6; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px; box-sizing: border-box; }
        label { font-size: 11px; font-weight: bold; color: #888; }
        .form-row { display: flex; gap: 20px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #1a2fa3; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        header {
            background-color: #b0b1a8; 
            display: flex;
            padding: 15px 60px; 
            align-items: center;
            justify-content: space-between;
            width: 100%; 
            box-sizing: border-box;
            border-bottom: 1px solid #999;
            z-index: 1000;
        }
        
        .logo-group { display: flex; align-items: center; gap: 20px; }
        .UC-logo { width: 50px; height: auto; }
        .system-title { 
            font-size: 22px; 
            font-weight: bold; 
            color: #1a2fa3; 
            margin: 0; 
        }

        .auth-group { display: flex; gap: 25px; align-items: center; }
        .nav-link { 
            color: #1a2fa3; 
            text-decoration: none; 
            font-weight: bold; 
            font-size: 15px;
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
            <a href="homepage.php" class="nav-link" style="text-decoration: underline;">Home</a>
            <a href="editprofile.php" class="nav-link">Edit Profile</a>
            <a href="history.php" class="nav-link">History</a>
            <a href="reservation.php" class="nav-link">Reservation</a>
            <a href="welcomepage.php" class="nav-link" style="color: #d9534f;">Logout</a>
        </div>
    </header>
<div class="wrapper">
    <div class="sidebar">
        <h3 style="color:#1a2fa3;">Student Profile</h3>
        <p><strong>ID:</strong> <?php echo $id_number; ?></p>
        <p><strong>Name:</strong> <?php echo $full_name; ?></p>
        
    </div>

    <div class="main-content">
        <div class="content-box">
            <h2>Make a Reservation</h2>
            <?php echo $message; ?>

            <form method="POST">
                <div class="form-row">
                    <div style="flex:1;">
                        <label>PURPOSE</label>
                        <input type="text" name="purpose" placeholder="C programming, PHP, etc." required <?php echo ($sessions_left <= 0) ? 'disabled' : ''; ?>>
                    </div>
                    <div style="flex:1;">
                        <label>LABORATORY ROOM</label>
                        <input type="text" name="lab" placeholder="Lab 524" required <?php echo ($sessions_left <= 0) ? 'disabled' : ''; ?>>
                    </div>
                </div>
                <div class="form-row">
                    <div style="flex:1;">
                        <label>TIME IN</label>
                        <input type="time" name="time_in" required <?php echo ($sessions_left <= 0) ? 'disabled' : ''; ?>>
                    </div>
                    <div style="flex:1;">
                        <label>DATE</label>
                        <input type="date" name="date" required <?php echo ($sessions_left <= 0) ? 'disabled' : ''; ?>>
                    </div>
                </div>

                <button type="submit" name="submit_res" class="btn-submit" <?php echo ($sessions_left <= 0) ? 'disabled' : ''; ?>>
                    <?php echo ($sessions_left <= 0) ? "No Sessions Left" : "Confirm Reservation"; ?>
                </button>
            </form>

            <h3 style="margin-top:40px;">My Reservation History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Purpose</th>
                        <th>Lab</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                        <td><?php echo htmlspecialchars($row['lab_room']); ?></td>
                        <td><?php echo $row['date'] . " | " . $row['time']; ?></td>
                        <td style="font-weight:bold; color:#1a2fa3;"><?php echo $row['status']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>