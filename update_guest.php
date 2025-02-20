<?php
require_once 'core_config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = db_connect();
    
    $id = $_POST['id'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE Guests SET FirstName = ?, LastName = ?, Email = ?, Phone = ? WHERE GuestID = ?");
    $stmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $id);

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