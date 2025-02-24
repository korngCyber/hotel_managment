<?php
require_once 'core_config/db.php';

if (isset($_GET['id'])) {
    $conn = db_connect();
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT gId, gName, gMail, gPhone, gDob, gImage FROM tbGuests WHERE gId = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $response = [];
    if ($row = $result->fetch_assoc()) {
        $response = $row;
    } else {
        $response['success'] = false;
        $response['message'] = 'Guest not found';
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>