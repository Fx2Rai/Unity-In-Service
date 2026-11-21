<?php
include 'db_connect.php'; // Include the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"])) {
    $volunteer_name = $_POST["volunteer_name"];
    $caption = $_POST["caption"];
    $target_dir = "uploads/";
    
    // Ensure the uploads directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($_FILES["image"]["name"]);

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO volunteer_images (volunteer_name, images, captions) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $volunteer_name, $target_file, $caption);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Image uploaded successfully"]);
        } else {
            echo json_encode(["error" => "Database error: " . $stmt->error]);
        }
    } else {
        echo json_encode(["error" => "Failed to upload image"]);
    }
}

$conn->close();
?>
