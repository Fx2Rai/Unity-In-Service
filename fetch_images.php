<?php
include 'db_connect.php'; // Include the database connection

$sql = "SELECT images FROM volunteer_images ORDER BY id DESC";
$result = $conn->query($sql);

$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row["images"];
}

echo json_encode($images);

$conn->close();
?>
