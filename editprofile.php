<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['id_number'])) {
    header("Location: login.php");
    exit();
}

$id_number = $_SESSION['id_number'];
$message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current user data
    $query = $conn->prepare("SELECT Password, profile_picture FROM users WHERE Id = ?");
    $query->bind_param("s", $id_number);
    $query->execute();
    $result = $query->get_result();
    $user_data = $result->fetch_assoc();

    $can_update = true;
    $final_password = $user_data['Password']; 

    // Password Change Logic
    if (!empty($old_password)) {
        // Verify current password
        if (!password_verify($old_password, $user_data['Password']) && $old_password !== $user_data['Password']) {
            $error_message = "Old password does not match our records.";
            $can_update = false;
        }

        if ($can_update) {
            if (strlen($new_password) < 8) {
                $error_message = "New password must be at least 8 characters long.";
                $can_update = false;
            } elseif ($new_password !== $confirm_password) {
                $error_message = "New passwords do not match.";
                $can_update = false;
            } else {
                // Securely hash the new password
                $final_password = password_hash($new_password, PASSWORD_DEFAULT);
            }
        }
    }

    // Profile Picture Logic
    $profile_pic = $user_data['profile_picture'];
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        
        $file_ext = pathinfo($_FILES["profile_img"]["name"], PATHINFO_EXTENSION);
        $new_filename = "profile_" . $id_number . "_" . time() . "." . $file_ext;
        if (move_uploaded_file($_FILES["profile_img"]["tmp_name"], $target_dir . $new_filename)) {
            $profile_pic = $new_filename;
        }
    }

    // Execute Update
    if ($can_update) {
        $update_sql = "UPDATE users SET FullName=?, EmailAddress=?, profile_picture=?, Password=? WHERE Id=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssss", $fullname, $email, $profile_pic, $final_password, $id_number);
        
        if ($update_stmt->execute()) {
            $_SESSION['full_name'] = $fullname;
            $message = "Profile updated successfully!";
        }
    }
}

// Fetch fresh data for the form
$user_query = $conn->query("SELECT * FROM users WHERE Id = '$id_number'");
$user = $user_query->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - CCS Sit-in Monitoring</title>
    <style>
        html, body { height: 100%; margin: 0; font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; }
        header { background-color: #b0b1a8; display: flex; padding: 15px 60px; align-items: center; justify-content: space-between; width: 100%; box-sizing: border-box; border-bottom: 1px solid #999; z-index: 1000; }
        .logo-group { display: flex; align-items: center; gap: 20px; }
        .UC-logo { width: 50px; height: auto; }
        .system-title { font-size: 22px; font-weight: bold; color: #1a2fa3; margin: 0; }
        .auth-group { display: flex; gap: 25px; align-items: center; }
        .nav-link { color: #1a2fa3; text-decoration: none; font-weight: bold; font-size: 15px; }
        
        .wrapper { display: flex; min-height: calc(100vh - 65px); }
        .sidebar { width: 250px; background: white; border-right: 2px solid #1a2fa3; padding: 40px 30px; text-align: center; }
        .profile-img-container { width: 120px; height: 120px; border-radius: 50%; border: 3px solid #1a2fa3; margin: 0 auto 20px; overflow: hidden; background: #eee; }
        .profile-img-container img { width: 100%; height: 100%; object-fit: cover; }

        .main-content { flex: 1; padding: 40px; }
        .content-box { background: white; border-radius: 12px; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 700px; margin: 0 auto; }
        
        .section-label { color: #1a2fa3; font-weight: bold; font-size: 11px; margin: 25px 0 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; text-transform: uppercase; }
        .form-row { display: flex; gap: 20px; margin-bottom: 15px; }
        .form-group { flex: 1; }
        label { display: block; font-size: 11px; font-weight: bold; color: #999; text-transform: uppercase; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        input[readonly] { background-color: #f9f9f9; }

        .btn-update { background-color: #1a2fa3; color: white; border: none; padding: 15px; border-radius: 4px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 20px; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; font-size: 14px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

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
            <a href="homepage.php" class="nav-link" style="text-decoration: underline;">Home</a>
            <a href="editprofile.php" class="nav-link">Edit Profile</a>
            <a href="history.php" class="nav-link">History</a>
            <a href="reservation.php" class="nav-link">Reservation</a>
            <a href="welcomepage.php" class="nav-link" style="color: #d9534f;">Logout</a>
        </div>
</header>

<div class="wrapper">
    <div class="sidebar">
        <div class="profile-img-container">
            <img src="uploads/<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default.png'; ?>" alt="Profile">
        </div>
        <h3 style="color: #1a2fa3;"><?php echo htmlspecialchars($user['FullName']); ?></h3>
    </div>

    <div class="main-content">
        <div class="content-box">
            <h2 style="color: #1a2fa3; margin-top:0;">Edit Profile</h2>
            
            <?php if($message) echo "<div class='alert success'>$message</div>"; ?>
            <?php if($error_message) echo "<div class='alert error'>$error_message</div>"; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="section-label">General Information</div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_img" accept="image/*">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>ID Number</label>
                        <input type="text" value="<?php echo $user['Id']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['FullName']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['EmailAddress']); ?>" required>
                </div>

                <div class="section-label">Security (Password Change)</div>
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="old_password" placeholder="Verify old password to make changes">
                </div>
                
                <div class="form-row" style="margin-top: 15px;">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="Minimum 8 characters">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Re-type new password">
                    </div>
                </div>

                <button type="submit" name="update_profile" class="btn-update">Save All Changes</button>
            </form>
        </div>
    </div>
</div>

    <footer>&copy; 2026 College of Computer Studies</footer>

<?php include 'footer.php'; ?>

</body>
</html>