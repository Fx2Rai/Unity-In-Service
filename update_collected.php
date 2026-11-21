<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $collected = $_POST["collected"];

    $stmt = $conn->prepare("UPDATE requirements SET collected = ? WHERE id = ?");
    $stmt->bind_param("ii", $collected, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }

    $stmt->close();
    $conn->close();
}
?>
