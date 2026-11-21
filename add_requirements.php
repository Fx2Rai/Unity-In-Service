<?php
include 'db_connect.php';

$requirement = $_POST['requirement'];
$disaster_area = $_POST['disaster_area'];
$needed = $_POST['needed'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];


$stmt = $conn->prepare("INSERT INTO disaster_requirements 
    (requirement, disaster_area, needed, latitude, longitude) 
    VALUES (?, ?, ?, ?, ?)");

$stmt->bind_param("ssidd", $requirement, $disaster_area, $needed, $latitude, $longitude);

if ($stmt->execute()) {
    header("Location: admin_dashboard.php?success=Requirement added successfully");
} else {
    header("Location: admin_dashboard.php?error=Error adding requirement");
}
$stmt->close();
$conn->close();
?>