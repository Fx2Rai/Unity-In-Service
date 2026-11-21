<?php
// Database connection
require 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'Image upload failed.']);
        exit;
    }

    $image = $_FILES['image'];
    $severity = $_POST['severity'] ?? 'Unknown';
    $message = $_POST['message'] ?? 'No message';
    
    // Save image to server
    $targetDir = "uploads/";
    $imageName = time() . "_" . basename($image['name']);
    $targetFilePath = $targetDir . $imageName;

    if (!move_uploaded_file($image['tmp_name'], $targetFilePath)) {
        echo json_encode(['error' => 'Failed to save image.']);
        exit;
    }

    // Insert into volunteer_images table
    $stmt = $conn->prepare("INSERT INTO volunteer_images (volunteer_name, images, captions) VALUES (?, ?, ?)");
    $volunteerName = "Anonymous"; // Update with session or actual volunteer name
    $stmt->bind_param("sss", $volunteerName, $imageName, $message);
    $stmt->execute();
    $imageId = $stmt->insert_id;
    $stmt->close();

    // Insert into analyzed_images table
    $stmt = $conn->prepare("INSERT INTO analyzed_images (volunteer_id, image_id, severity_level, damage_percentage, necessity_recommendation) VALUES (?, ?, ?, ?, ?)");
    $volunteerId = 1; // Update with session or actual volunteer ID
    $damagePercentage = ($severity === 'Severe') ? 90 : (($severity === 'Moderate') ? 50 : 20);
    $necessityRecommendation = ($severity === 'Severe') ? "Urgent medical aid, food, shelter needed." : (($severity === 'Moderate') ? "Food, water, temporary shelter." : "Minor assistance required.");
    
    $stmt->bind_param("iisis", $volunteerId, $imageId, $severity, $damagePercentage, $necessityRecommendation);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true, 'severity' => $severity, 'message' => 'Image analyzed successfully']);
} else {
    // Display the HTML form for image upload
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Image Upload</title>
    </head>
    <body>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="image">Select Image:</label>
            <input type="file" name="image" id="image" required><br><br>

            <label for="severity">Severity:</label>
            <select name="severity" id="severity">
                <option value="Severe">Severe</option>
                <option value="Moderate">Moderate</option>
                <option value="Mild">Mild</option>
            </select><br><br>

            <label for="message">Message:</label>
            <textarea name="message" id="message"></textarea><br><br>

            <input type="submit" value="Upload Image">
        </form>
    </body>
    </html>
    <?php
}
?>
