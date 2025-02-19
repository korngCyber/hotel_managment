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
$position = $_POST['position'] ?? ''; // Changed from role to position
$salary = $_POST['salary'] ?? '';

if (empty($id) || empty($name) || empty($position) || empty($salary)) {
    $response = ['success' => false, 'message' => 'Missing required fields'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$query = "UPDATE Staff SET Name = ?, Position = ?, Salary = ? WHERE StaffID = ?"; // Changed Role to Position
$stmt = $conn->prepare($query);

if (!$stmt) {
    $response = ['success' => false, 'message' => 'Prepare failed: ' . $conn->error];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$stmt->bind_param("sssi", $name, $position, $salary, $id); // Updated parameter name

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