<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        /* 1. Reset and Center Everything */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6; /* Light grey background */
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full screen height */
        }

        /* 2. Style the form container */
        form {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Soft shadow */
            width: 320px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }

        /* 3. Style the inputs */
        input {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #1a2fa3;
            outline: none;
        }

        /* 4. Style the Login Button */
        .btn-login {
            background-color: #1a2fa3;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .btn-login:hover {
            background-color: #142382;
        }

        /* 5. Style the Sign Up Link */
        .signup-link {
            text-align: center;
            text-decoration: none;
            color: #1a2fa3;
            font-size: 14px;
            margin-top: 5px;
        }

        .signup-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<form method="POST" action="login_handler.php">
    <h2>Login</h2>
    
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    
    <button type="submit" class="btn-login">Login</button>
    
    <a href="register.php" class="signup-link">Don't have an account? Sign Up</a>
</form>

</body>
</html>