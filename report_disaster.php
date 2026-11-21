<?php
include 'db_connect.php';

$type = $_POST['type'];
$lat = $_POST['lat'];
$lon = $_POST['lon'];

$query = "INSERT INTO disaster_alerts (type, lat, lon, reported_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("sdd", $type, $lat, $lon);
$response = ["success" => $stmt->execute()];

echo json_encode($response);
?>
