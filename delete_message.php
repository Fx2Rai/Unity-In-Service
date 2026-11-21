<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to delete a message.";
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'volunteer'; // Default to 'volunteer'

// Check if the request is a POST request and if message ID is provided
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message_id']) && isset($_POST['chat_id'])) {
    $message_id = $_POST['chat_id'];
    $sender_id = $_POST['message_id'];
    
    // Allow only the sender of the message or an admin to delete
    if ($sender_id == $user_id || $user_role == 'admin') {
        // Prepare the delete query
        $stmt = $conn->prepare("DELETE FROM volunteer_chat WHERE id = ? AND sender_id = ?");
        $stmt->bind_param("ii", $message_id, $sender_id);
        
        if ($stmt->execute()) {
            echo "Message deleted successfully.";
        } else {
            echo "Error deleting message.";
        }
    } else {
        echo "You do not have permission to delete this message.";
    }
}
?>
