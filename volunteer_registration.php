<?php
include 'db_connect.php'; // Include database connection

$message = '';
$messageType = '';

// Function to handle adding a new volunteer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    try {
        // Get form data
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $aadhaar = trim($_POST['aadhaar']);
        $phone = trim($_POST['phone']);
        $district = trim($_POST['district']);
        $city = trim($_POST['city']);
        $ability = trim($_POST['ability']);
        $password = trim($_POST['password']);
        
        // Validate inputs
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        if (!preg_match("/^\d{12}$/", $aadhaar)) {
            throw new Exception("Aadhaar must be exactly 12 digits");
        }
        
        if (!preg_match("/^\d{10,15}$/", $phone)) {
            throw new Exception("Phone number must be 10-15 digits");
        }
        
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters");
        }
        
        // Hash password
        $hashedPassword = ($password);
        
        // Check if aadhaar already exists using prepared statement
        $stmt = $conn->prepare("SELECT * FROM volunteers WHERE aadhaar = ?");
        $stmt->bind_param("s", $aadhaar);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Aadhaar number already exists!");
        }
        
        // Handle profile image
        $profileImage = "";
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            // Set upload parameters
            $targetDir = "uploads/";
            $fileExtension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
            $newFileName = uniqid('profile_', true) . '.' . $fileExtension;
            $targetFile = $targetDir . $newFileName;
            
            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($fileExtension, $allowedTypes)) {
                throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed");
            }
            
            // Validate file size (max 5MB)
            if ($_FILES["profile_image"]["size"] > 5000000) {
                throw new Exception("File is too large. Maximum size is 5MB");
            }
            
            // Upload file
            if (!move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFile)) {
                throw new Exception("There was an issue uploading the profile image");
            }
            
            $profileImage = $targetFile;
        }
        
        // Insert into database using prepared statement
        $stmt = $conn->prepare("INSERT INTO volunteers (name, email, aadhaar, phone, district, city, ability, password, profile_image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $name, $email, $aadhaar, $phone, $district, $city, $ability, $hashedPassword, $profileImage);
        
        if ($stmt->execute()) {
            $message = "Volunteer added successfully!";
            $messageType = "success";
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Registration</title>
    <style>
        /* Retro Design */
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --dark-bg: #121212;
            --card-bg: #1e1e1e;
            --light-text: #ffffff;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--light-text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background: var(--card-bg);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 600px;
        }
        
        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        form {
            display: flex;
            flex-direction: column;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            font-weight: bold;
            color: var(--light-text);
        }
        
        input[type="text"], input[type="email"], input[type="password"], input[type="file"], input[type="tel"] {
            padding: 10px;
            margin-top: 5px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            color: var(--light-text);
        }
        
        button {
            background: var(--primary-color);
            color: var(--light-text);
            padding: 10px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-weight: bold;
        }
        
        button:hover {
            background: #2980b9;
        }
        
        .message {
            margin: 15px 0;
            padding: 10px;
            background-color: var(--secondary-color);
            color: #fff;
            border-radius: var(--border-radius);
            text-align: center;
        }
        
        .message.error {
            background-color: var(--danger-color);
        }
        
    </style>
</head>
<body>
    <div class="container">
        <h1>Volunteer Registration</h1>
        
        <!-- Display messages -->
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Registration Form -->
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter full name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter email address" required>
            </div>
            <div class="form-group">
                <label for="aadhaar">Aadhaar Number</label>
                <input type="text" id="aadhaar" name="aadhaar" placeholder="12-digit Aadhaar number" pattern="\d{12}" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter phone number" pattern="\d{10,15}" required>
            </div>
            <div class="form-group">
                <label for="district">District</label>
                <input type="text" id="district" name="district" placeholder="Enter district" required>
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" placeholder="Enter city" required>
            </div>
            <div class="form-group">
                <label for="ability">Special Abilities/Skills</label>
                <input type="text" id="ability" name="ability" placeholder="Enter abilities or skills" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" minlength="8" required>
            </div>
            <div class="form-group">
                <label for="profile_image">Profile Image (Optional)</label>
                <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/gif">
            </div>
            <button type="submit">Submit</button>

        </form>
        <a href="userdisplay.html" class="menu-item"><i class="fas fa-sign-out-alt"></i> Home</a>
    </div>
</body>
</html>
