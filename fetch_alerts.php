<?php
// fetch_alerts.php

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "disaster_volunteering";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch disaster alerts from the database
$sql = "SELECT * FROM disaster_alert ORDER BY reported_at DESC";
$result = $conn->query($sql);

$alerts = [];
while ($row = $result->fetch_assoc()) {
    $alerts[] = $row;
}

echo json_encode($alerts);

$conn->close();
?>
