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

$id = $_GET['id'] ?? '';

if (empty($id)) {
    $response = ['success' => false, 'message' => 'Missing ID'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$query = "SELECT * FROM Staff WHERE StaffID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$response = [];

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response = [
        'StaffID' => $row['StaffID'],
        'Name' => $row['Name'],
        'Position' => $row['Position'], // Changed from Role to Position
        'Salary' => $row['Salary']
    ];
} else {
    $response['success'] = false;
    $response['message'] = 'Staff not found';
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>