<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'core_config/db.php';
$conn = db_connect();

if (!$conn) {
    $response = ['success' => false, 'message' => 'Database connection failed'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomID = $_POST['id'] ?? 0;
    $roomType = $_POST['roomType'] ?? '';
    $price = $_POST['price'] ?? 0;
    $status = $_POST['status'] ?? '';

    if (empty($roomType) || empty($status) || empty($roomID)) {
        $response = ['success' => false, 'message' => 'Missing required fields'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("UPDATE Rooms SET RoomType = ?, Price = ?, Status = ? WHERE RoomID = ?");
    if (!$stmt) {
        $response = ['success' => false, 'message' => 'Prepare failed: ' . $conn->error];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("sssi", $roomType, $price, $status, $roomID);

    $response = [];
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Room updated successfully.";
    } else {
        $response['success'] = false;
        $response['message'] = "Error updating room: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
