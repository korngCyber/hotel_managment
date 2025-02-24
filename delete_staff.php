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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing staff ID']);
        exit;
    }

    $staffId = $_POST['id'];

    // First, get the image path
    $query = "SELECT sImage FROM tbStaffs WHERE sId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $staffId);
    $stmt->execute();
    $result = $stmt->get_result();
    $imageData = $result->fetch_assoc();
    $imagePath = $imageData['sImage'] ?? '';
    $stmt->close();

    // Delete the record
    $query = "DELETE FROM tbStaffs WHERE sId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $staffId);

    if ($stmt->execute()) {
        // If record deletion successful, delete the image file
        if (!empty($imagePath) && file_exists($imagePath)) {
            unlink($imagePath);
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete staff: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
