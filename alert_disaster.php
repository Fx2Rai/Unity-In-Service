<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Get JSON input
    $json = file_get_contents('php://input');
    if (empty($json)) {
        throw new Exception("No input received");
    }
    
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON format");
    }

    // Validate required fields
    $required = ['user_name', 'disaster_type', 'latitude', 'longitude'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate coordinates
    if (!is_numeric($data['latitude']) || !is_numeric($data['longitude'])) {
        throw new Exception("Invalid coordinates");
    }

    // Prepare and execute statement
    $stmt = $conn->prepare("INSERT INTO disaster_alert
        (user_name, disaster_type, latitude, longitude) 
        VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Database preparation failed: " . $conn->error);
    }

    $stmt->bind_param("ssdd", 
        $data['user_name'],
        $data['disaster_type'],
        $data['latitude'],
        $data['longitude']
    );

    if (!$stmt->execute()) {
        throw new Exception("Database execution failed: " . $stmt->error);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>