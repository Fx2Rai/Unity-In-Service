<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $aadhaar = trim($_POST['aadhaar']);
    $phone = trim($_POST['phone']);
    $ability = trim($_POST['ability']);
    $password = (trim($_POST['password']));
    $district = trim($_POST['district']);
    $city = trim($_POST['city']);

    // Check if Aadhaar already exists
    $checkQuery = "SELECT id FROM volunteers WHERE aadhaar = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $aadhaar);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Aadhaar ID already exists."]);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Insert new volunteer data
    $query = "INSERT INTO volunteers (name, aadhaar, phone, ability, password, district, city) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssss", $name, $aadhaar, $phone, $ability, $password, $district, $city);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id; // Store user ID in session
        $_SESSION['volunteer_name'] = $name; // Store volunteer name for later use

        // Redirect to the volunteer dashboard
        header("Location: volunteer_dashboard.html");
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Registration failed."]);
    }

    $stmt->close();
    $conn->close();
}
?>
