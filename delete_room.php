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
    $rId = $_POST['id'] ?? 0;

    if (empty($rId)) {
        $response = ['success' => false, 'message' => 'Missing required room ID'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM tbRooms WHERE rId = ?");
    if (!$stmt) {
        $response = ['success' => false, 'message' => 'Prepare failed: ' . $conn->error];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("i", $rId);

    $response = [];
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Room deleted successfully.";
    } else {
        $response['success'] = false;
        $response['message'] = "Error deleting room: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>