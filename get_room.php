<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'core_config/db.php';

header('Content-Type: application/json');

$conn = db_connect();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query = "SELECT rId, htId, rName, rType, rPrice, rStatus FROM tbRooms WHERE rId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = $result->fetch_assoc();
    } else {
        $response = ['success' => false, 'message' => 'Room not found'];
    }

    $stmt->close();
} else {
    $response = ['success' => false, 'message' => 'Missing ID parameter'];
}

$conn->close();
echo json_encode($response);
?>