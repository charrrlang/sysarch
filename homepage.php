<?php
    session_start();
    include 'db_connect.php'; 

    if (!isset($_SESSION['id_number'])) {
        header("Location: login.php");
        exit();
    }

    $id_number = $_SESSION['id_number'];

    // 1. Fetch user data
    $query = $conn->query("SELECT * FROM users WHERE Id = '$id_number'");
    $user = $query->fetch_assoc();

    // 2. Fetch announcements
    $announcement_query = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC");

    $profile_pic = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default.png';

    /** * REWARD POINTS LOGIC */
    $points_goal = 100;
    $current_points = isset($user['points']) ? $user['points'] : 0;
    $progress_percent = ($current_points / $points_goal) * 100;
    if ($progress_percent > 100) $progress_percent = 100;

    /** * SIT-IN SUMMARY CALCULATION */
    $summary_query = $conn->query("
        SELECT 
            COUNT(*) as total_sessions,
            SUM(TIMESTAMPDIFF(MINUTE, login_time, logout_time)) as total_minutes,
            AVG(TIMESTAMPDIFF(MINUTE, login_time, logout_time)) as avg_minutes,
            MAX(TIMESTAMPDIFF(MINUTE, login_time, logout_time)) as longest_minutes
        FROM sitin_records 
        WHERE id_number = '$id_number' AND logout_time IS NOT NULL
    ");
    $summary = $summary_query->fetch_assoc();

    $total_hrs = $summary['total_minutes'] ? round($summary['total_minutes'] / 60, 1) : 0;
    $session_count = $summary['total_sessions'] ?? 0;
    $avg_duration = $summary['avg_minutes'] ? round($summary['avg_minutes']) . " mins" : "0 mins";
    $longest_session = $summary['longest_minutes'] ? round($summary['longest_minutes']) . " mins" : "0 mins";
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Student Dashboard | CCS Sit-in</title>
        <style>
            body { font-family: 'Segoe UI', sans-serif; margin: 0; background-color: #f4f7f6; height: 100vh; display: flex; flex-direction: column; }
            header { background-color: #b0b1a8; display: flex; padding: 15px 60px; align-items: center; justify-content: space-between; border-bottom: 1px solid #999; }
            .logo-group { display: flex; align-items: center; gap: 20px; }
            .UC-logo { width: 50px; }
            .system-title { font-size: 22px; font-weight: bold; color: #1a2fa3; margin: 0; }
            .nav-link { color: #1a2fa3; text-decoration: none; font-weight: bold; cursor: pointer; margin-left: 20px; }
            .app-body { display: flex; flex-grow: 1; overflow: hidden; }
            .sidebar { width: 280px; background: white; border-right: 2px solid #1a2fa3; padding: 30px 20px; display: flex; flex-direction: column; align-items: center; }
            .profile-pic-container { width: 120px; height: 120px; border-radius: 50%; border: 3px solid #1a2fa3; overflow: hidden; margin-bottom: 20px; }
            .profile-pic-container img { width: 100%; height: 100%; object-fit: cover; }
            .detail-box { margin-bottom: 15px; width: 100%; }
            .label { font-size: 11px; color: #888; text-transform: uppercase; font-weight: bold; }
            .value { font-size: 15px; color: #333; font-weight: 600; display: block; }
            .main-content { flex-grow: 1; padding: 40px; overflow-y: auto; }
            .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 30px; }
            .summary-card { background: white; padding: 20px; border-radius: 10px; border: 1px solid #dee2e6; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
            .summary-card .stat-val { font-size: 22px; font-weight: bold; color: #1a2fa3; display: block; margin-top: 5px; }
            .stat-card { background: white; padding: 25px; border-radius: 10px; border-top: 5px solid #1a2fa3; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
            .progress-container { width: 100%; background: #eee; height: 12px; border-radius: 6px; margin: 15px 0; overflow: hidden; }
            .progress-fill { height: 100%; background: #1a2fa3; transition: width 0.8s ease-in-out; }
            .announcement-card { background: white; padding: 20px; border-radius: 10px; border-left: 5px solid #28a745; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
            #notif-toast { visibility: hidden; min-width: 300px; background-color: #333; color: white; padding: 16px; position: fixed; right: 30px; bottom: 30px; z-index: 9999; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); border-left: 5px solid #f1c40f; }
            #notif-toast.show { visibility: visible; animation: fadein 0.5s; }
            @keyframes fadein { from { bottom: 0; opacity: 0; } to { bottom: 30px; opacity: 1; } }
            footer { background: #2c3e50; color: white; text-align: center; padding: 15px; font-size: 13px; margin-top: auto; }
            .theme-toggle-btn { background: transparent; border: none; font-size: 1.3rem; cursor: pointer; margin-left: 20px; padding: 5px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; transition: transform 0.2s ease, background-color 0.2s; vertical-align: middle; }
            .theme-toggle-btn:hover { background-color: rgba(255, 255, 255, 0.2); transform: scale(1.1); }
            .auth-group { display: flex; align-items: center; }

            /* DARK MODE TECH AESTHETIC */
            [data-theme="dark"] body { background-color: #0f111a; color: #e2e8f0; }
            [data-theme="dark"] header { background-color: #1e2230; border-bottom: 1px solid #242936; }
            [data-theme="dark"] .system-title { color: #4d61fc; }
            [data-theme="dark"] .nav-link:not([style*="color: #d9534f"]) { color: #8fa0ff; }
            [data-theme="dark"] .sidebar { background: #161925; border-right: 2px solid #4d61fc; }
            [data-theme="dark"] .profile-pic-container { border-color: #4d61fc; }
            [data-theme="dark"] .value { color: #e2e8f0; }
            [data-theme="dark"] .label { color: #718096; }
            [data-theme="dark"] h2 { color: #4d61fc !important; }
            [data-theme="dark"] .summary-card { background: #161925; border: 1px solid #242936; box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
            [data-theme="dark"] .summary-card .stat-val { color: #4d61fc; }
            [data-theme="dark"] .stat-card { background: #161925; border-top: 5px solid #4d61fc; box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
            [data-theme="dark"] #points-display { color: #4d61fc !important; }
            [data-theme="dark"] .progress-container { background: #242936; }
            [data-theme="dark"] .progress-fill { background: #4d61fc; }
            [data-theme="dark"] .announcement-card { background: #161925; box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
            [data-theme="dark"] footer { background: #090a0f; border-top: 1px solid #161925; }

            /* Testimonial Styling */
            .testimonial-form-container { background: white; padding: 25px; border-radius: 10px; border: 1px solid #dee2e6; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
            .testimonial-form-container textarea { width: 100%; height: 100px; padding: 12px; border: 1px solid #dee2e6; border-radius: 6px; font-family: inherit; font-size: 14px; resize: vertical; box-sizing: border-box; margin-top: 5px; margin-bottom: 15px; background-color: #fff; color: #333; }
            .submit-testimonial-btn { background-color: #1a2fa3; color: white; border: none; padding: 10px 20px; font-size: 14px; font-weight: bold; border-radius: 5px; cursor: pointer; transition: background-color 0.2s; }
            .submit-testimonial-btn:hover { background-color: #13227a; }
            [data-theme="dark"] .testimonial-form-container { background: #161925; border-color: #242936; box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
            [data-theme="dark"] .testimonial-form-container textarea { background-color: #0f111a; border-color: #242936; color: #e2e8f0; }
            [data-theme="dark"] .submit-testimonial-btn { background-color: #4d61fc; }
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
                <button id="theme-toggle" class="theme-toggle-btn"><span id="toggle-icon">🌙</span></button>
            </div>
        </header>

        <div class="app-body">
            <div class="sidebar">
                <div class="profile-pic-container">
                    <img src="uploads/<?php echo $profile_pic; ?>" alt="Profile">
                </div>
                <h3>Student Profile</h3>
                <div class="detail-box"><span class="label">ID Number</span><span class="value"><?php echo htmlspecialchars($user['Id']); ?></span></div>
                <div class="detail-box"><span class="label">Name</span><span class="value"><?php echo htmlspecialchars($user['FullName']); ?></span></div>
                <div class="detail-box"><span class="label">Course</span><span class="value"><?php echo htmlspecialchars($user['Course']); ?></span></div>
                <div class="detail-box"><span class="label">Sessions</span><span class="value"><?php echo htmlspecialchars($user['sessions_remaining']); ?> left</span></div>
            </div>

            <div class="main-content">
                <h2 style="color: #1a2fa3; margin-top: 0;">📊 My Lab Performance</h2>
                <div class="summary-grid">
                    <div class="summary-card"><span class="label">Total Hours</span><span class="stat-val"><?php echo $total_hrs; ?> hrs</span></div>
                    <div class="summary-card"><span class="label">Total Sessions</span><span class="stat-val"><?php echo $session_count; ?></span></div>
                    <div class="summary-card"><span class="label">Avg Duration</span><span class="stat-val"><?php echo $avg_duration; ?></span></div>
                    <div class="summary-card"><span class="label">Longest Sit-in</span><span class="stat-val"><?php echo $longest_session; ?></span></div>
                </div>

                <div class="stat-card">
                    <span class="label">Reward Progress</span>
                    <div id="points-display" style="font-size: 28px; font-weight: bold; color: #1a2fa3;"><?php echo $current_points; ?> pts</div>
                    <div class="progress-container">
                        <div id="progress-bar" class="progress-fill" style="width: <?php echo $progress_percent; ?>%;"></div>
                    </div>
                    <p style="font-size: 12px; color: #666; margin: 0;">Goal: 100 points for Lab Certification</p>
                </div>

                <h2 style="color: #1a2fa3; margin-top: 40px;" id="testimonials-heading">💬 Share Your Experience</h2>
                <div class="testimonial-form-container">
                    <form id="testimonialForm">
                        <div class="form-group">
                            <label for="testimonialInput" class="label">Your Feedback / Testimonial</label>
                            <textarea id="testimonialInput" placeholder="How has your experience been?" required></textarea>
                        </div>
                        <button type="submit" class="submit-testimonial-btn">Submit Testimonial</button>
                    </form>
                    <div id="form-status-msg" style="margin-top: 10px; font-size: 13px; font-weight: bold;"></div>
                </div>

                <h2 style="color: #1a2fa3; margin-top: 40px;">📢 Announcements</h2>
                <?php if ($announcement_query->num_rows > 0): ?>
                    <?php while($row = $announcement_query->fetch_assoc()): ?>
                        <div class="announcement-card">
                            <strong>Notice:</strong> <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                            <div style="font-size: 11px; color: #aaa; margin-top: 10px;"><?php echo date('M d, Y - h:i A', strtotime($row['date_posted'])); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #999;">No current announcements.</p>
                <?php endif; ?>
            </div>
        </div>

        <div id="notif-toast"></div>
        <footer>&copy; 2026 College of Computer Studies | Sit-in Monitoring System</footer>

        <script>
            // Theme Logic
            (function () {
                const savedTheme = localStorage.getItem('theme');
                if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            })();

            document.addEventListener('DOMContentLoaded', function() {
                // Testimonial Submission logic
                const tForm = document.getElementById('testimonialForm');
                const tInput = document.getElementById('testimonialInput');
                const statusMsg = document.getElementById('form-status-msg');

                if (tForm) {
                    tForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const feedbackText = tInput.value.trim();
                        
                        if (feedbackText === "") return;

                        statusMsg.style.color = "#1a2fa3";
                        statusMsg.innerText = "⏳ Sending...";

                        fetch('save_testimonial.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'feedback=' + encodeURIComponent(feedbackText)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.status === 'success') {
                                statusMsg.style.color = "#28a745";
                                statusMsg.innerText = "✨ Success! Testimonial shared.";
                                tInput.value = "";
                            } else {
                                statusMsg.style.color = "#d9534f";
                                statusMsg.innerText = "❌ Error: " + data.message;
                            }
                        })
                        .catch(err => {
                            statusMsg.style.color = "#d9534f";
                            statusMsg.innerText = "❌ Connection error.";
                        });
                    });
                }

                // Theme Toggle
                const toggleBtn = document.getElementById('theme-toggle');
                const toggleIcon = document.getElementById('toggle-icon');
                if (document.documentElement.getAttribute('data-theme') === 'dark') toggleIcon.textContent = '☀️';

                toggleBtn.addEventListener('click', () => {
                    let currentTheme = document.documentElement.getAttribute('data-theme');
                    if (currentTheme === 'dark') {
                        document.documentElement.removeAttribute('data-theme');
                        localStorage.setItem('theme', 'light');
                        toggleIcon.textContent = '🌙';
                    } else {
                        document.documentElement.setAttribute('data-theme', 'dark');
                        localStorage.setItem('theme', 'dark');
                        toggleIcon.textContent = '☀️';
                    }
                });
            });

            // Polling for updates
            let currentPointsJS = <?php echo $current_points; ?>;
            function checkUpdates() {
                fetch(`check_notifications.php?current_points=${currentPointsJS}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.message !== "") {
                            const toast = document.getElementById('notif-toast');
                            toast.innerHTML = `<strong>Notification</strong><br>${data.message}`;
                            if (data.points_update) {
                                toast.style.backgroundColor = "#28a745";
                                currentPointsJS = data.new_total;
                                document.getElementById('points-display').innerText = data.new_total + " pts";
                                document.getElementById('progress-bar').style.width = (data.new_total > 100 ? 100 : data.new_total) + "%";
                            } else if (data.session_update) {
                                toast.style.backgroundColor = "#d9534f";
                                setTimeout(() => { window.location.href = "welcomepage.php"; }, 4000);
                            }
                            toast.classList.add('show');
                            setTimeout(() => { toast.classList.remove('show'); }, 6000);
                        }
                    }).catch(err => console.log("Update check failed", err));
            }
            setInterval(checkUpdates, 10000);
        </script>
    </body>
    </html>