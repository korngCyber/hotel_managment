<?php
require_once 'core_config/db.php';
$conn = db_connect();

$id = $_POST['id'];

$query = "DELETE FROM Staff WHERE StaffID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

$response = []; // Initialize response array

if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['success'] = false;
    $response['message'] = $stmt->error; // Capture the error message
}

$stmt->close();
$conn->close();

header('Content-Type: application/json'); // Set the content type to JSON
echo json_encode($response); // Return the response as JSON
?>