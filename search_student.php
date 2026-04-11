<?php
session_start();
include 'db_connect.php';

// Set timezone for the default time values
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['id_number'])) {
    header("Location: login.php");
    exit();
}

$search_results = null;
$search_query = "";

if (isset($_POST['search'])) {
    $search_query = $_POST['search_input'];
    
    // Search for ANY student in the users table, regardless of reservation status
    $sql = "SELECT Id, FullName FROM users WHERE (Id LIKE ? OR FullName LIKE ?) AND role = 'Student'";
    $stmt = $conn->prepare($sql);
    $term = "%$search_query%";
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    $search_results = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Check-in Student</title>
    <style>
        /* Keeping your existing styles... */
        html, body { height: 100%; margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; color: #333; }
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
        .container { padding: 40px; max-width: 1300px; margin: 0 auto; }
        h2 { color: #1a2fa3; font-size: 24px; margin-bottom: 25px; }
        
        .search-box { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 30px; display: flex; gap: 10px; }
        .search-box input { flex: 1; padding: 12px; border: 1px solid #edf2f7; border-radius: 6px; background: #f8fafc; }
        .btn-search { background: #1a2fa3; color: white; border: none; padding: 0 30px; border-radius: 6px; cursor: pointer; font-weight: bold; }

        /* Table & Form Inputs */
        .table-container { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1a2fa3; color: white; padding: 18px 15px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { padding: 12px 15px; border-bottom: 1px solid #edf2f7; font-size: 13px; }
        
        select, input[type="text"].table-input, input[type="date"], input[type="time"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        .btn-approve { background-color: #28a745; color: white; padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; font-weight: bold; width: 100%; }
        .btn-approve:hover { background-color: #218838; }
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
    <h2>Search Student to Check-in</h2>
    
    <div class="search-box">
        <form method="POST" style="display:contents;">
            <input type="text" name="search_input" placeholder="Enter Student Name or ID Number..." value="<?php echo htmlspecialchars($search_query); ?>" required>
            <button type="submit" name="search" class="btn-search">Search</button>
        </form>
    </div>

    <?php if ($search_results): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th width="10%">ID NUMBER</th>
                    <th width="15%">STUDENT NAME</th>
                    <th width="15%">PURPOSE</th>
                    <th width="10%">LABORATORY</th>
                    <th width="12%">DATE</th>
                    <th width="12%">TIME</th>
                    <th width="10%">STATUS</th>
                    <th width="10%">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($search_results->num_rows > 0): ?>
                    <?php while($row = $search_results->fetch_assoc()): ?>
                    <tr>
                        <form action="process_manual_sitin.php" method="POST">
                            <input type="hidden" name="id_number" value="<?php echo $row['Id']; ?>">
                            <input type="hidden" name="fullname" value="<?php echo $row['FullName']; ?>">

                            <td style="font-weight: bold; color: #1a2fa3;"><?php echo htmlspecialchars($row['Id']); ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($row['FullName']); ?></td>
                            
                            <td>
                                <select name="purpose" required>
                                    <option value="">Select Purpose</option>
                                    <option value="C++ Programming">C++ Programming</option>
                                    <option value="Java Programming">Java Programming</option>
                                    <option value="PHP Programming">PHP Programming</option>
                                    <option value="Research">Research</option>
                                    <option value="Exam">Exam</option>
                                </select>
                            </td>
                            
                            <td>
                                <select name="lab_room" required>
                                    <option value="">Lab...</option>
                                    <option value="524">Lab 524</option>
                                    <option value="526">Lab 526</option>
                                    <option value="530">Lab 530</option>
                                    <option value="542">Lab 542</option>
                                </select>
                            </td>

                            <td><input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required></td>
                            <td><input type="time" name="time" value="<?php echo date('H:i'); ?>" required></td>
                            
                            <td><b style="color: #b7791f;">PENDING</b></td>
                            
                            <td>
                                <button type="submit" class="btn-approve">Approve</button>
                            </td>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #999;">No registered student found with that Name or ID.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

</body>
</html>