<?php
require_once 'core_config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = db_connect();
    $roomID = $_POST['id'];
    $roomType = $_POST['roomType'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE Rooms SET RoomType = ?, Price = ?, Status = ? WHERE RoomID = ?");
    $stmt->bind_param("sssi", $roomType, $price, $status, $roomID);

    if ($stmt->execute()) {
        echo "Room updated successfully.";
    } else {
        echo "Error updating room: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
