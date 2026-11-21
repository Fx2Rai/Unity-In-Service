<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Save new message
    if (isset($_POST['message'], $_POST['volunteer_name'], $_POST['city'])) {
        $volunteer_name = trim($_POST['volunteer_name']);
        $message = trim($_POST['message']);
        $city = trim($_POST['city']);

        if (empty($volunteer_name) || empty($message) || empty($city)) {
            echo json_encode(["status" => "error", "message" => "All fields are required."]);
            exit;
        }

        $query = "INSERT INTO chat_messages (volunteer_name, city, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $volunteer_name, $city, $message);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Message sent successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to send message."]);
        }

        $stmt->close();
        exit;
    }
}

// Fetch messages for a specific city
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $city = isset($_GET['city']) ? trim($_GET['city']) : "";

    if (empty($city)) {
        echo json_encode(["status" => "error", "message" => "City is required"]);
        exit;
    }

    $query = "SELECT volunteer_name, city, message, timestamp FROM chat_messages WHERE city = ? ORDER BY timestamp ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $city);
    $stmt->execute();
    $result = $stmt->get_result();

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
    $stmt->close();
}

$conn->close();
?>
