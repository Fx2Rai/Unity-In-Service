<?php
include 'db_connect.php';
include 'sidebar1.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $response = [];
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $city = trim($_POST['city']);
    $district = trim($_POST['district']);
    $ability = trim($_POST['ability']); // New ability field
    $password = !empty($_POST['password']) ? ($_POST['password']) : null;

    // Update query
    if ($password) {
        $stmt = $conn->prepare("UPDATE volunteers SET name=?, phone=?, city=?, district=?, ability=?, password=? WHERE id=?");
        $stmt->bind_param("ssssssi", $name, $phone, $city, $district, $ability, $password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE volunteers SET name=?, phone=?, city=?, district=?, ability=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $phone, $city, $district, $ability, $user_id);
    }

    if ($stmt->execute()) {
        $response['success'] = 'Profile updated successfully!';
    } else {
        $response['error'] = 'Error updating profile: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    echo json_encode($response);
    exit();
}

// Fetch user details
$stmt = $conn->prepare("SELECT name, phone, city, district, ability FROM volunteers WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Update</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6c5ce7;
            --primary-hover: #5649c9;
            --background: #121212;
            --card-bg: #1e1e1e;
            --text: #e0e0e0;
            --text-muted: #a0a0a0;
            --accent: #00d2d3;
            --success: #00b894;
            --input-bg: #2d2d2d;
            --input-border: #444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background);
            color: var(--text);
            line-height: 1.6;
            padding: 0;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 550px;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        h2 {
            font-size: 28px;
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 8px;
        }

        .subheader {
            color: var(--text-muted);
            font-size: 16px;
        }

        #statusMessage {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            border-radius: 6px;
            font-weight: 500;
            display: none;
        }

        #statusMessage.success {
            background-color: rgba(0, 184, 148, 0.2);
            color: var(--success);
            border: 1px solid rgba(0, 184, 148, 0.3);
            display: block;
        }

        #statusMessage.error {
            background-color: rgba(255, 82, 82, 0.2);
            color: #ff5252;
            border: 1px solid rgba(255, 82, 82, 0.3);
            display: block;
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            padding: 30px;
            margin-bottom: 30px;
        }

        .form-group {
            position: relative;
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--accent);
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        input {
            width: 100%;
            padding: 14px 14px 14px 42px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            background-color: var(--input-bg);
            color: var(--text);
            font-size: 15px;
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
        }

        input::placeholder {
            color: var(--text-muted);
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
        }

        button {
            background-color: var(--primary);
            color: white;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        button:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .footer {
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 20px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }
            .card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Update Your Profile</h2>
            <p class="subheader">Change your personal information below</p>
        </div>
        
        <div id="statusMessage"></div>
        
        <div class="card">
            <form id="profile-form">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" value="<?= e($user['name']) ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="phone" name="phone" pattern="\d{10}" value="<?= e($user['phone']) ?>" placeholder="10 digit number" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="city">City</label>
                    <div class="input-icon">
                        <i class="fas fa-city"></i>
                        <input type="text" id="city" name="city" value="<?= e($user['city']) ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="district">District</label>
                    <div class="input-icon">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" id="district" name="district" value="<?= e($user['district']) ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="ability">Ability</label>
                    <div class="input-icon">
                        <i class="fas fa-cogs"></i>
                        <input type="text" id="ability" name="ability" value="<?= e($user['ability']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">New Password (Leave blank to keep current)</label>
                    <div class="input-icon password-container">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password">
                        <div class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                </div>
                
                <button type="submit" id="submit-btn">
                    Update Profile
                    <div class="spinner" id="spinner"></div>
                </button>
            </form>
        </div>
        
        <div class="footer">
            All information is secured and encrypted
        </div>
    </div>
    <script>
        // Password visibility toggle
        document.getElementById("togglePassword").addEventListener("click", function() {
            const passwordInput = document.getElementById("password");
            const icon = this.querySelector("i");
            
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        });

        // Form submission
        document.getElementById("profile-form").addEventListener("submit", async (e) => {
            e.preventDefault();
            
            const form = new FormData(e.target);
            const statusMessage = document.getElementById("statusMessage");
            const submitBtn = document.getElementById("submit-btn");
            const spinner = document.getElementById("spinner");
            
            // Show loading state
            submitBtn.disabled = true;
            spinner.style.display = "block";
            statusMessage.className = "";
            statusMessage.style.display = "none";
            
            try {
                const response = await fetch("", {
                    method: "POST",
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: form
                });
                
                const data = await response.json();
                
                if (data.success) {
                    statusMessage.innerText = data.success;
                    statusMessage.className = "success";
                } else if (data.error) {
                    statusMessage.innerText = data.error;
                    statusMessage.className = "error";
                }
                
                statusMessage.style.display = "block";
                
            } catch (error) {
             
                statusMessage.style.display = "block";
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                spinner.style.display = "none";
            }
        });
    </script>
</body>
</html>