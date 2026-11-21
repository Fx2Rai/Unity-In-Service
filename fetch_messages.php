<?php
include 'db_connect.php';

header('Content-Type: application/json');

$query = "SELECT volunteer_name, city, message, timestamp FROM chat_messages ORDER BY timestamp ASC";
$result = $conn->query($query);

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        "volunteer_name" => $row["volunteer_name"],
        "city" => $row["city"],
        "message" => $row["message"],
        "timestamp" => $row["timestamp"]
    ];
}

echo json_encode(["status" => "success", "messages" => $messages]);

$conn->close();
?>
