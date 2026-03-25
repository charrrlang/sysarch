<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Welcome Page</title> 

    <style>
        /* 1. Reset and Global Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        /* 2. Header Layout Fix */
        header {
            background-color: #86887c;
            display: flex;
            padding: 10px 20px; 
            align-items: center;
            justify-content: space-between; /* Pushes logo-group and auth-group apart */
            width: 100%;
            box-sizing: border-box;
        }

        /* NEW: Container to keep Logo and Title together on the left */
        .logo-group {
            display: flex;
            align-items: center;
            gap: 15px; /* Distance between logo and text */
        }

        .UC-logo {
            width: 100px;
        }

        .system-title {
            margin: 0;
            font-size: 1.3rem;
            color: #1a2fa3; /* Your blue theme color */
        }

        /* 3. Navigation Links and Dropdown */
        .auth-group {
            display: flex;
            gap: 25px; 
            align-items: center;
        }

        .nav-link, .dropbtn {
            text-decoration: none;
            color: #1a2fa3;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #ffffff;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 4px;
            top: 100%; 
            left: 0;
        }

        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-size: 14px;
            transition: background 0.2s;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
            color: #1a2fa3;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        /* Invisible bridge to fix the hover gap */
        .dropdown-content::before {
            content: "";
            position: absolute;
            top: -15px;
            left: 0;
            width: 100%;
            height: 15px;
        }

        .footer {
        background-color: #343a40; /* Dark gray to match professional UI */
        padding: 20px 0;
        margin-top: 40px; /* Space between content and footer */
        width: 100%;
        border-top: 4px solid #004a99; /* UC Blue accent line */
        text-align: center;
        }

        .footer span {
        font-size: 0.9rem;
        font-family: 'Arial', sans-serif;
        letter-spacing: 0.5px;
        }

        .footer {
        margin-top: auto;
        }
        body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
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
            <a href="welcomepage.php" class="nav-link">Home</a>

            <div class="dropdown">
                <button class="dropbtn">Community ▼</button>
                <div class="dropdown-content">
                    <a href="#">School Info</a>
                    <a href="#">Rules</a>
                    <a href="#">Contact</a>
                </div>
            </div>

            <a href="about.php" class="nav-link">About</a>
            <a href="login.php" class="nav-link">Login</a>
            <a href="register.php" class="nav-link">Register</a>
        </div>
    </header>

    <div style="max-width: 900px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
    <h2 style="color: #1a2fa3; text-align: center;">Laboratory Rules</h2>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px;">
        <div style="padding: 15px; border-left: 5px solid #1a2fa3; background: #f9f9f9;">
            <h4 style="margin: 0 0 10px 0;">Proper Attire</h4>
            <p style="font-size: 0.9rem; color: #555;">Students must follow the university dress code. Wearing of caps, sleeveless shirts, or shorts is strictly prohibited.</p>
        </div>
        
        <div style="padding: 15px; border-left: 5px solid #1a2fa3; background: #f9f9f9;">
            <h4 style="margin: 0 0 10px 0;">Cleanliness</h4>
            <p style="font-size: 0.9rem; color: #555;">No eating or drinking inside the laboratory. Dispose of your trash properly before leaving.</p>
        </div>

        <div style="padding: 15px; border-left: 5px solid #1a2fa3; background: #f9f9f9;">
            <h4 style="margin: 0 0 10px 0;">PC Usage</h4>
            <p style="font-size: 0.9rem; color: #555;">Use only the PC number assigned to you. Do not move or swap peripherals like mice or keyboards.</p>
        </div>

        <div style="padding: 15px; border-left: 5px solid #1a2fa3; background: #f9f9f9;">
            <h4 style="margin: 0 0 10px 0;">Accountability</h4>
            <p style="font-size: 0.9rem; color: #555;">You are responsible for any hardware damage during your session. Report issues to the supervisor immediately.</p>
        </div>
    </div>
</div>

</body>

<footer class="footer">
        <div class = "container">
            <span class=" text-white "> © 2024 College of Computer Studies

            </span>
        </div>
</footer>
</html>