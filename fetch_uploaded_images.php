<?php
include 'db_connect.php'; // Include the database connection

$sql = "SELECT volunteer_name, images, captions FROM volunteer_images ORDER BY id DESC";
$result = $conn->query($sql);

$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}

echo json_encode($images);

$conn->close();
?>
