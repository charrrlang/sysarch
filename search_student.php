<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$search_results = null;
$search_query = "";

if (isset($_POST['search'])) {
    $search_query = $_POST['search_input'];
    
    // Search for Students and include their sessions_remaining
    $sql = "SELECT Id, FullName, sessions_remaining FROM users WHERE (Id LIKE ? OR FullName LIKE ?) AND role = 'Student'";
    
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
    <title>Admin - Search Student</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        
        /* Fixed Header Spacing */
        header { 
            background-color: #b0b1a8; 
            padding: 10px 50px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid #999; 
        }
        .logo-group { display: flex; align-items: center; gap: 15px; }
        .logo-group img { width: 40px; }
        .system-title { color: #1a2fa3; font-weight: bold; font-size: 20px; margin: 0; }

        .nav-links { display: flex; align-items: center; }
        .nav-links a { color: #1a2fa3; text-decoration: none; font-weight: bold; margin-left: 25px; font-size: 13px; }
        .nav-links a:hover { text-decoration: underline; }

        .container { padding: 40px; max-width: 1200px; margin: 0 auto; }
        .search-box { 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .search-form { display: flex; gap: 10px; }
        input[type="text"], select { padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 15px; }
        input[type="text"] { flex: 1; }
        .btn-search { background: #1a2fa3; color: white; border: none; padding: 0 25px; border-radius: 4px; cursor: pointer; font-weight: bold; }

        /* Results Table Styling */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        th { background-color: #1a2fa3; color: white; padding: 15px; text-align: left; font-size: 12px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .btn-action { background: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-action:hover { background: #0056b3; }
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
        <a href="welcomepage.php" class="btn-logout">Logout</a>
    </nav>
</header>

<div class="container">
    <div class="search-box">
        <h2 style="color: #1a2fa3; margin-top: 0;">Search Student</h2>
        <form method="POST" class="search-form">
            <input type="text" name="search_input" placeholder="Search by ID Number or Full Name..." value="<?php echo htmlspecialchars($search_query); ?>" required>
            <button type="submit" name="search" class="btn-search">Search</button>
        </form>
    </div>

    <?php if ($search_results): ?>
    <form action="process_sitin.php" method="POST">
        <table>
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Student Name</th>
                    <th>Purpose</th>
                    <th>Lab</th>
                    <th>Remaining Sessions</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($search_results->num_rows > 0): ?>
                    <?php while($row = $search_results->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight: bold; color: #1a2fa3;">
                            <?php echo htmlspecialchars($row['Id']); ?>
                            <input type="hidden" name="id_number" value="<?php echo $row['Id']; ?>">
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['FullName']); ?>
                            <input type="hidden" name="fullname" value="<?php echo $row['FullName']; ?>">
                        </td>
                        <td>
                            <select name="purpose" required>
                                <option value="C Programming">C Programming</option>
                                <option value="Java">Java</option>
                                <option value="PHP">PHP</option>
                                <option value="Networking">Networking</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="lab" placeholder="e.g. 524" required style="width: 80px;">
                        </td>
                        <td style="text-align: center; font-weight: bold;">
                            <?php echo htmlspecialchars($row['sessions_remaining']); ?>
                            <input type="hidden" name="remaining" value="<?php echo $row['sessions_remaining']; ?>">
                        </td>
                        <td>
                            <button type="submit" class="btn-action">SIT IN</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; color: #888; padding: 20px;">No students found for "<?php echo htmlspecialchars($search_query); ?>".</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
    <?php endif; ?>
</div>

</body>
</html>