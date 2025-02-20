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

$name = $_POST['name'] ?? '';
$position = $_POST['position'] ?? '';
$contact = $_POST['contact'] ?? ''; // Changed from salary to contact

if (empty($name) || empty($position) || empty($contact)) {
    $response = ['success' => false, 'message' => 'Missing required fields'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$query = "INSERT INTO Staff (Name, Position, Contact) VALUES (?, ?, ?)"; // Changed Salary to Contact
$stmt = $conn->prepare($query);

if (!$stmt) {
    $response = ['success' => false, 'message' => 'Prepare failed: ' . $conn->error];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$stmt->bind_param("sss", $name, $position, $contact); // Updated parameter name

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