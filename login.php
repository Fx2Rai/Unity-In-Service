<?php
session_start();

// Include database connection
include 'db_connect.php';

// If the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data
    $email = trim($_POST['username'] ?? '');  // Using 'username' for email input
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? ''; // Get role from the form
    
    // Validate input
    if (empty($email) || empty($password) || empty($role)) {
        echo "<script>alert('All fields are required');</script>";
        exit;
    }

    try {
        // Determine which table to query based on the role
        if ($role === 'admin') {
            // Query the admins table
            $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
        } else if ($role === 'volunteer') {
            // Query the volunteers table
            $stmt = $conn->prepare("SELECT id, password FROM volunteers WHERE email = ?");
        } else {
            echo "<script>alert('Invalid role');</script>";
            exit;
        }

        // Bind parameters and execute
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the user exists and verify password
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($password === $user['password']) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;

                // Redirect to the respective dashboard
                if ($role === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: volunteer_dashboard.php");
                }
                exit;
            } else {
                header("Location: login.php");
            }
        } else {
            header("Location: login.php");
        }

        $stmt->close();
    } catch (Exception $e) {
        echo "<script>alert('Authentication failed');</script>";
    }

    $conn->close();
    exit; // Prevent the rest of the page from loading when submitting the form
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYSTEM LOGIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@400;600;700&display=swap');
        
        :root {
            --alert-red: #ff3333;
            --emergency-orange: #ff6600;
            --command-blue: #0066ff;
            --system-white: #e6e6e6;
            --terminal-green: #33ff33;
        }
        
        body {
            background-color: #0a0a12;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(255, 51, 51, 0.15) 0%, transparent 25%),
                radial-gradient(circle at 80% 70%, rgba(0, 102, 255, 0.15) 0%, transparent 25%);
            color: var(--system-white);
            font-family: 'Rajdhani', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            overflow: hidden;
        }
        
        .emergency-panel {
            width: 420px;
            background: rgba(10, 10, 20, 0.9);
            border: 3px solid var(--alert-red);
            border-radius: 5px;
            box-shadow: 
                0 0 25px rgba(255, 51, 51, 0.5),
                inset 0 0 15px rgba(255, 51, 51, 0.3);
            padding: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .panel-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--alert-red);
            padding-bottom: 15px;
            position: relative;
        }
        
        .panel-header::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--alert-red), transparent);
        }
        
        .panel-header h1 {
            font-family: 'Orbitron', sans-serif;
            color: var(--alert-red);
            font-size: 1.8rem;
            letter-spacing: 3px;
            margin: 0;
            text-transform: uppercase;
            text-shadow: 0 0 10px rgba(255, 51, 51, 0.7);
        }
        
        .panel-header p {
            color: var(--system-white);
            margin-top: 10px;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }
        
        .alert-strip {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: repeating-linear-gradient(
                45deg,
                var(--alert-red),
                var(--alert-red) 10px,
                #000 10px,
                #000 20px
            );
            animation: alertFlash 1s linear infinite;
        }
        
        @keyframes alertFlash {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: var(--command-blue);
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: 1px;
            font-size: 0.95rem;
        }
        
        input{
            width: 92%;
            padding: 12px 15px;
            background: rgba(0, 0, 30, 0.8);
            border: 2px solid var(--command-blue);
            color: var(--system-white);
            font-family: 'Rajdhani', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        select {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0, 0, 30, 0.8);
            border: 2px solid var(--command-blue);
            color: var(--system-white);
            font-family: 'Rajdhani', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%230066ff'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--emergency-orange);
            box-shadow: 0 0 15px rgba(255, 102, 0, 0.5);
        }
        
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to right, var(--alert-red), var(--emergency-orange));
            border: none;
            color: white;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
        }
        
        .login-btn:hover {
            background: linear-gradient(to right, #ff0000, #ff5500);
            box-shadow: 0 0 25px rgba(255, 51, 51, 0.7);
        }
        
        .login-btn::after {
            content: "⚠";
            position: absolute;
            right: -20px;
            transition: all 0.3s;
        }
        
        .login-btn:hover::after {
            right: 20px;
        }
        
        .status-message {
            padding: 12px;
            border-radius: 4px;
            margin: 20px 0;
            text-align: center;
            font-weight: 600;
            display: none;
        }
        
        .alert {
            background-color: rgba(255, 51, 51, 0.2);
            border: 1px solid var(--alert-red);
            color: var(--alert-red);
            display: block;
        }
        
        .success {
            background-color: rgba(0, 255, 0, 0.2);
            border: 1px solid var(--terminal-green);
            color: var(--terminal-green);
            display: block;
        }
    </style>
</head>
<body>
    <div class="emergency-panel">
        <div class="panel-header">
            <h1>SYSTEM LOGIN</h1>
            <p>ACCESS RESTRICTED TO AUTHORIZED PERSONNEL</p>
        </div>
        
        <form method="POST" id="login-form">
            <div class="form-group">
                <label for="role">USER TYPE:</label>
                <select id="role" name="role">
                    <option value="admin">ADMINISTRATOR</option>
                    <option value="volunteer">VOLUNTEER</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="username">USERNAME:</label>
                <input type="text" id="username" name="username" placeholder="ENTER ACCESS CODE" required>
            </div>
            
            <div class="form-group">
                <label for="password">PASSWORD:</label>
                <input type="password" id="password" name="password" placeholder="ENTER ENCRYPTION KEY" required>
            </div>
            
            <button type="submit" class="login-btn">AUTHENTICATE</button>
            <div class="status-message" id="errorMessage"></div><br><br>
            <a href="userdisplay.html" class="menu-item"><i class="fas fa-sign-out-alt"></i> Home</a>
        </form>
    </div>
</body>
</html>
