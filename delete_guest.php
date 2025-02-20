<?php
require_once 'core_config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = db_connect();
    
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM Guests WHERE GuestID = ?");
    $stmt->bind_param("i", $id);

    $response = [];
    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['success'] = false;
        $response['message'] = $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
}
?> 