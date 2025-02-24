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
    $rType = $_POST['rType'] ?? '';
    $rPrice = $_POST['rPrice'] ?? 0;
    $rStatus = $_POST['rStatus'] ?? '';
    $htId = $_POST['htId'] ?? 0;

    if (empty($rType) || empty($rStatus) || empty($htId)) {
        $response = ['success' => false, 'message' => 'Missing required fields'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Validate enum values
    $validTypes = ['Single', 'Double', 'Suite'];
    $validStatuses = ['available', 'booked'];

    if (!in_array($rType, $validTypes) || !in_array($rStatus, $validStatuses)) {
        $response = ['success' => false, 'message' => 'Invalid room type or status'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO tbRooms (htId, rType, rPrice, rStatus) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        $response = ['success' => false, 'message' => 'Prepare failed: ' . $conn->error];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("isds", $htId, $rType, $rPrice, $rStatus);

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