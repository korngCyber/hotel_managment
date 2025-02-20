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
    $roomType = $_POST['roomType'] ?? '';
    $price = $_POST['price'] ?? 0;
    $status = $_POST['status'] ?? '';
    $hotelId = $_POST['hotelId'] ?? 0;

    if (empty($roomType) || empty($status) || empty($hotelId)) {
        $response = ['success' => false, 'message' => 'Missing required fields'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO Rooms (RoomType, Price, Status, HotelID) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        $response = ['success' => false, 'message' => 'Prepare failed: ' . $conn->error];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("sdsi", $roomType, $price, $status, $hotelId);

    $response = [];
    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['success'] = false;
        $response['message'] = 'Execution failed: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
}

?>