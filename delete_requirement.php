<?php
// Include database connection
require_once 'db_connect.php';

header('Content-Type: application/json');

// Check if ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'Requirement ID is required']);
    exit;
}

$id = intval($_POST['id']);  // Sanitize input by casting to integer

try {
    // Prepare and execute the delete statement
    $stmt = $conn->prepare("DELETE FROM disaster_requirements WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Check if a row was actually deleted
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
            // Redirect to admin_dashboard.php after successful deletion
            header("Location: admin_dashboard.php");
            exit; // Important to stop further script execution after redirect
        } else {
            echo json_encode(['success' => false, 'error' => 'Requirement not found']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
} finally {
    // Ensure connection is closed even if an exception occurs
    $conn->close();
}
?>
