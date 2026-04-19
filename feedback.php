<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['id_number'])) {
    header("Location: login.php");
    exit();
}

// Get the record ID from the URL
$record_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$record_id) {
    die("Error: No sit-in record selected for feedback.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit Feedback - CCS Monitoring</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .feedback-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 400px; text-align: center; }
        h2 { color: #1a2fa3; margin-bottom: 20px; }
        .stars { margin-bottom: 20px; }
        .stars input { display: none; }
        .stars label { font-size: 30px; color: #ccc; cursor: pointer; transition: color 0.2s; }
        .stars input:checked ~ label, .stars label:hover, .stars label:hover ~ label { color: #f1c40f; }
        textarea { width: 100%; height: 100px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; resize: none; box-sizing: border-box; }
        .btn-submit { background-color: #1a2fa3; color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; width: 100%; margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="feedback-card">
        <h2>Lab Experience Feedback</h2>
        <form action="submit_feedback.php" method="POST">
            <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($record_id); ?>">
            
            <div class="stars">
                <input type="radio" id="star5" name="rating" value="5" required><label for="star5">★</label>
                <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
            </div>

            <textarea name="comment" placeholder="Tell us about your sit-in session..."></textarea>
            
            <button type="submit" class="btn-submit">Submit Feedback</button>
            <br><br>
            <a href="history.php" style="color: #888; text-decoration: none; font-size: 13px;">Cancel</a>
        </form>
    </div>
</body>
</html>