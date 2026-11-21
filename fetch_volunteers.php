<?php
include 'db_connect.php';

header('Content-Type: application/json'); // Ensure JSON response

$sql = "SELECT id, name, aadhaar, phone, ability, district, city FROM volunteers";
$result = $conn->query($sql);

$volunteers = [];

if ($result) { // Check if query execution was successful
    while ($row = $result->fetch_assoc()) {
        $volunteers[] = $row;
    }
} else {
    echo json_encode(["error" => "Database query failed"]);
    exit;
}

echo json_encode($volunteers, JSON_PRETTY_PRINT); // Pretty-print JSON for readability

$conn->close();
?>
