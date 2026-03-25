<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        /* 1. Global Styles */
        a { text-decoration: none; }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        /* 2. Header Layout */
        header {
            background-color: #86887c;
            display: flex;
            padding: 10px 20px; 
            align-items: center;
            justify-content: space-between; /* Pushes logo-group and nav apart */
            width: 100%; 
            box-sizing: border-box;
        }
        
        .logo-group {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .UC-logo { width: 100px; }

        .system-title {
            margin: 0;
            font-size: 1.3rem;
            color: #1a2fa3;
        }

        /* 3. Navigation & Dropdown */
        .auth-group {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-link, .dropbtn {
            background: transparent !important; /* Forces the background to be invisible */
            border: none;
            color: #1a2fa3; /* This makes the text blue so you can see it on the grey */
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            padding: 0;
            margin: 0;
            display: inline-flex;
            align-items: center;
        }

        .dropdown { position: relative; display: inline-block; }
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
            display: block;
            font-size: 14px;
        }
        .dropdown:hover .dropdown-content { display: block; }

        /* 4. Form Styling */
        .registration-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 0;
        }

        form {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px; 
            display: flex;
            flex-direction: column;
            gap: 15px; 
        }

        input {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        button {
            background-color: #1a2fa3;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        .btn-cancel {
            background-color: #ffffff;
            color: #333;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
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
    
    <div class="registration-wrapper">
        <h2>Create Account</h2>
        
        <form action="register_handler.php" method="POST">
            <input type="text" name="id_number" placeholder="ID Number" inputmode="numeric" pattern="[0-9]*" required>
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="text" name="course_level" placeholder="Course Level" required>
            <input type="text" name="course" placeholder="Course" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            
            <button type="submit">Register</button>
            <a href="welcomepage.php" class="btn-cancel">Cancel</a>
        </form>
    </div>

</body>
</html>