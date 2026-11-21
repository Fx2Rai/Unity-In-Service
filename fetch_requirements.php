<?php
include 'db_connect.php';

header("Content-Type: application/json");

$sql = "SELECT * FROM disaster_requirements ORDER BY id DESC";
$result = $conn->query($sql);

$requirements = [];
while ($row = $result->fetch_assoc()) {
    // Calculate progress as a percentage
    $progress = ($row["needed"] > 0) ? round(($row["collected"] / $row["needed"]) * 100) : 0;

    // Determine status based on progress
    if ($progress >= 100) {
        $status = "Completed";
    } elseif ($progress >= 50) {
        $status = "In Progress";
    } else {
        $status = "Pending";
    }

    $requirements[] = [
        'requirement' => $row['requirement'],
        'disaster_area' => $row['disaster_area'],
        'needed' => (int)$row['needed'],
        'collected' => (int)$row['collected'],
        'latitude' => (float)$row['latitude'],
        'longitude' => (float)$row['longitude']
    ];
}

echo json_encode($requirements);
$conn->close();
?>
