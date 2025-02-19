<?php
require_once 'core_config/db.php';

if (isset($_GET['id'])) {
    $roomID = $_GET['id'];
    $conn = db_connect();

    $stmt = $conn->prepare("SELECT * FROM Rooms WHERE RoomID = ?");
    $stmt->bind_param("i", $roomID);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();

    echo json_encode($room);

    $stmt->close();
    $conn->close();
}
?>
