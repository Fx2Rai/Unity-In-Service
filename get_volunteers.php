<?php
include 'db_connect.php';

header('Content-Type: application/json');

$query = "SELECT name, city FROM volunteers";
$result = $conn->query($query);

$volunteers = [];
while ($row = $result->fetch_assoc()) {
    $volunteers[] = [
        "name" => $row["name"],
        "city" => $row["city"]
    ];
}

echo json_encode($volunteers);
$conn->close();
?>
