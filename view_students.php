<?php
session_start();
include 'db_connect.php';

// Check if user is logged in as Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Fetch all students ordered by name
$sql = "SELECT Id, FullName, Course, CourseLevel FROM users WHERE role = 'Student' ORDER BY FullName ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Students | Admin</title>
    <style>
        :root {
            --primary-blue: #1a2fa3;
            --bg-light: #f4f7f6;
            --white: #ffffff;
            --border: #dee2e6;
            --text-gray: #6c757d;
        }

        body { 
            font-family: 'Inter', 'Segoe UI', sans-serif; 
            background-color: var(--bg-light); 
            margin: 0; 
            color: #333;
        }

        /* --- ADMIN DASHBOARD HEADER STYLE --- */
        header { 
            background-color: #b0b1a8; 
            padding: 10px 50px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid #999; 
            height: 70px;
            box-sizing: border-box;
        }

        .logo-group { display: flex; align-items: center; gap: 10px; }
        .logo-group img { width: 40px; }
        .system-title { color: var(--primary-blue); font-weight: bold; font-size: 20px; margin: 0; }

        .nav-links { display: flex; align-items: center; }
        .nav-links a { 
            color: var(--primary-blue); 
            text-decoration: none; 
            font-size: 13px; 
            margin-left: 20px; 
            font-weight: bold; 
            transition: 0.3s;
        }
        .nav-links a:hover { text-decoration: underline; }
        .nav-links a.active { text-decoration: underline; }
        .btn-logout { color: #d9534f !important; }

        /* --- MAIN CONTENT --- */
        .container { padding: 40px; max-width: 1200px; margin: 0 auto; }
        
        .dashboard-card { 
            background: var(--white); 
            border-radius: 12px; 
            border: 1px solid var(--border);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            overflow: hidden;
        }

        .card-header { 
            padding: 25px; 
            border-bottom: 1px solid var(--border); 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            background: #fafafa;
        }

        .card-title { font-size: 20px; font-weight: 700; color: var(--primary-blue); margin: 0; }
        .student-count { font-size: 13px; color: var(--text-gray); font-weight: 500; }

        /* --- TABLE STYLE --- */
        table { width: 100%; border-collapse: collapse; }
        
        thead th { 
            background-color: #fff; 
            color: var(--text-gray); 
            padding: 15px 20px; 
            text-align: left; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            border-bottom: 2px solid #f1f1f1;
        }

        tbody td { 
            padding: 18px 20px; 
            border-bottom: 1px solid #f9f9f9; 
            font-size: 14px; 
            color: #444;
        }

        tbody tr:hover { background-color: #fcfcff; }

        .id-cell { font-weight: 700; color: var(--primary-blue); }
        .course-tag { 
            background: #eef1ff; 
            color: var(--primary-blue); 
            padding: 5px 10px; 
            border-radius: 6px; 
            font-size: 12px; 
            font-weight: 600; 
        }
        .year-text { color: #555; font-weight: 500; }

        @media (max-width: 768px) {
            header { padding: 0 20px; }
            .nav-links { display: none; } 
        }
    </style>
</head>
<body>

<header>
    <div class="logo-group">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/University_of_Cebu_Logo.png/960px-University_of_Cebu_Logo.png" alt="UC logo">
        <h1 class="system-title">College of Computer Studies Sit-in Monitoring</h1>
    </div>
    <nav class="nav-links">
        <a href="admin_dashboard.php">Home</a>
        <a href="search_student.php">Search</a>
        <a href="view_students.php" class="active">Students</a>
        <a href="sit_in.php">Sit-in</a>
        <a href="view_sitin_records.php">Records</a>
        <a href="reservation_admin.php">Reservation</a>
        <a href="welcomepage.php" class="btn-logout">Log out</a>
    </nav>
</header>

<div class="container">
    <div class="dashboard-card">
        <div class="card-header">
            <h2 class="card-title">Registered Students</h2>
            <span class="student-count">Total Registered: <strong><?php echo $result->num_rows; ?></strong></span>
        </div>
        
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Full Name</th>
                        <th>Course</th>
                        <th>Year Level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="id-cell"><?php echo htmlspecialchars($row['Id']); ?></td>
                            <td style="font-weight: 500;"><?php echo htmlspecialchars($row['FullName']); ?></td>
                            <td><span class="course-tag"><?php echo htmlspecialchars($row['Course']); ?></span></td>
                            <td class="year-text"><?php echo htmlspecialchars($row['CourseLevel']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px; color: #888;">
                                No students found in the database.
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