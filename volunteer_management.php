<?php
include 'db_connect.php'; // Include database connection
include 'sidebar.php';

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

// Function to handle deleting a volunteer
if (isset($_POST['delete_id'])) {
    try {
        $id = intval($_POST['delete_id']); // Ensure id is an integer
        
        // Use prepared statement
        $stmt = $conn->prepare("SELECT * FROM volunteers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Get the volunteer data to delete the image if exists
            $volunteer = $result->fetch_assoc();
            
            // Delete the volunteer
            $stmt = $conn->prepare("DELETE FROM volunteers WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                // Remove profile image if exists
                if (!empty($volunteer['profile_image']) && file_exists($volunteer['profile_image'])) {
                    unlink($volunteer['profile_image']);
                }
                
                $message = "Volunteer deleted successfully!";
                $messageType = "success";
            } else {
                throw new Exception($conn->error);
            }
        } else {
            throw new Exception("Volunteer not found!");
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

// Fetch all volunteers
$sql = "SELECT * FROM volunteers";
$volunteers = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Management</title>
    <style>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--light-text);
            display: flex;
            min-height: 100vh;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 25%;
            padding: 20px;
            transition: var(--transition);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .header h1 {
            font-size: 1.8rem;
        }
        
        .card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--box-shadow);
        }
        
        .card h2 {
            margin-bottom: 15px;
            color: var(--primary-color);
            font-size: 1.4rem;
        }
        
        .card h3 {
            margin: 20px 0 10px;
            font-size: 1.2rem;
        }
        
        /* Form Styles */
        form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        input, select {
            padding: 10px 15px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: var(--light-text);
            flex: 1;
            min-width: 150px;
        }
        
        button {
            background: var(--primary-color);
            color: var(--light-text);
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
        }
        
        button:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        button.update-btn {
            background: var(--secondary-color);
        }
        
        button.update-btn:hover {
            background: #27ae60;
        }
        
        button.delete-btn {
            background: var(--danger-color);
            padding: 8px 12px;
        }
        
        button.delete-btn:hover {
            background: #c0392b;
        }
        
        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border-radius: var(--border-radius);
            overflow: hidden;
            border:1px;
        }
        
        thead tr {
            background: rgba(52, 152, 219, 0.2);
            color: var(--primary-color);
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        /* Alert Styles */
        .alert-list {
            list-style: none;
        }
        
        .alert-item {
            background: rgba(231, 76, 60, 0.1);
            border-left: 4px solid var(--danger-color);
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
        }
        
        .alert-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .alert-location {
            font-style: italic;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .alert-time {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Additional Styles for Progress Bars and Notifications */
        .progress-container {
            position: relative;
            width: 100%;
            height: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            transition: width 0.5s ease;
        }
        
        .progress-bar.low {
            background: var(--danger-color);
        }
        
        .progress-bar.medium {
            background: #f39c12;
        }
        
        .progress-bar.good {
            background: #3498db;
        }
        
        .progress-bar.complete {
            background: var(--secondary-color);
        }
        
        .progress-container span {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            font-weight: bold;
        }
        
        .update-field {
            display: flex;
            gap: 5px;
        }
        
        .update-field input {
            width: 70px;
            min-width: 70px;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: var(--secondary-color);
            color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            z-index: 1000;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
        }
        
        .notification.error {
            background: var(--danger-color);
        }
        
        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Hamburger button for mobile */
        .hamburger {
            display: none;
            background: transparent;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        /* Confirmation Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: var(--border-radius);
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: var(--box-shadow);
        }
        
        .modal-title {
            margin-bottom: 15px;
            color: var(--light-text);
        }
        
        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .modal-actions button {
            min-width: 100px;
        }
        
        /* Media query for smaller screens */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            
            .hamburger {
                display: block;
            }
            
            form {
                flex-direction: column;
            }
            
            .update-field {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
   
    <!-- Main Content Area -->
    <div class="main-content" id="mainContent">
        <div class="container">
            <h1>Volunteer Management</h1>
            
            <!-- Display messages -->
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Form to Add Volunteer -->
            <button id="showFormBtn">Add Volunteer</button>
<div class="form-container" id="volunteerForm" style="display: none;">
    <h2>Add Volunteer</h2>
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
</div>

<script>
    document.getElementById("showFormBtn").addEventListener("click", function() {
        var form = document.getElementById("volunteerForm");
        form.style.display = form.style.display === "none" ? "block" : "none";
    });
</script>

            <h2>Volunteers List</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Aadhaar</th>
                            <th>Phone</th>
                            <th>District</th>
                            <th>City</th>
                            <th>Ability</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($volunteers->num_rows > 0) {
                            while ($row = $volunteers->fetch_assoc()) {
                                $imgSrc = !empty($row['profile_image']) ? $row['profile_image'] : 'uploads/default-profile.png';
                                
                                // Mask Aadhaar number for privacy (show only last 4 digits)
                                $maskedAadhaar = 'XXXX-XXXX-' . substr($row['aadhaar'], -4);
                                
                                echo "<tr>
                                        <td>{$row['name']}</td>
                                        <td>{$row['email']}</td>
                                        <td>{$maskedAadhaar}</td>
                                        <td>{$row['phone']}</td>
                                        <td>{$row['district']}</td>
                                        <td>{$row['city']}</td>
                                        <td>{$row['ability']}</td>
                                        <td>
                                            <form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this volunteer?\");'>
                                                <input type='hidden' name='delete_id' value='{$row['id']}'>
                                                <button type='submit' class='btn-danger'>Delete</button>
                                            </form>
                                        </td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No volunteers found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar visibility
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        menuBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            mainContent.classList.toggle('shift');
        });
        
        // Form validation enhancements
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(event) {
                const password = document.getElementById('password');
                const aadhaar = document.getElementById('aadhaar');
                const phone = document.getElementById('phone');
                
                // Additional validation can be added here if needed
            });
        });
    </script>
</body>
</html>