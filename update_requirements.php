<?php
include 'db_connect.php';

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug: Check received data
    if (!isset($_POST['id']) || !isset($_POST['collected'])) {
        echo json_encode(["success" => false, "error" => "Missing required fields"]);
        exit;
    }

    $id = $_POST['id'];
    $collected = $_POST['collected'];

    if (!is_numeric($id) || !is_numeric($collected)) {
        echo json_encode(["success" => false, "error" => "Invalid input"]);
        exit;
    }

    // Prepare the query
    $sql = "UPDATE disaster_requirements SET collected = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        echo json_encode(["success" => false, "error" => "SQL prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("ii", $collected, $id);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?success=updated succesfully");
        exit(); // Ensure script stops execution after redirection
    } else {
        echo "Error: " . $stmt->error;
    }
    

    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
}

$conn->close();
?>
