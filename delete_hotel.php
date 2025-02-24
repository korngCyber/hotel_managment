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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Use correct table and column names matching other files
    $query = "DELETE FROM tbHotels WHERE htId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $response = ['success' => true];
    } else {
        $response = ['success' => false, 'message' => 'Failed to delete hotel: ' . $stmt->error];
    }

    $stmt->close();
} else {
    $response = ['success' => false, 'message' => 'Invalid request or missing hotel ID'];
}

$conn->close();
echo json_encode($response);
?>