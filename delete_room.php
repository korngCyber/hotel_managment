<?php
require_once 'core_config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = db_connect();
    $roomID = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM Rooms WHERE RoomID = ?");
    $stmt->bind_param("i", $roomID);

    if ($stmt->execute()) {
        echo "Room deleted successfully.";
    } else {
        echo "Error deleting room: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
