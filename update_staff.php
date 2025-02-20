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

$id = $_POST['id'] ?? '';
$name = $_POST['name'] ?? '';
$position = $_POST['position'] ?? '';
$contact = $_POST['contact'] ?? ''; // Changed from salary to contact

if (empty($id) || empty($name) || empty($position) || empty($contact)) {
    $response = ['success' => false, 'message' => 'Missing required fields'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$query = "UPDATE Staff SET Name = ?, Position = ?, Contact = ? WHERE StaffID = ?"; // Changed Salary to Contact
$stmt = $conn->prepare($query);

if (!$stmt) {
    $response = ['success' => false, 'message' => 'Prepare failed: ' . $conn->error];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$stmt->bind_param("sssi", $name, $position, $contact, $id); // Updated parameter name

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
?>