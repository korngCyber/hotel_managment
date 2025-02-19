<?php
require_once 'core_config/db.php';
$conn = db_connect();

$id = $_GET['id'];

$query = "SELECT * FROM Hotels WHERE HotelID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$response = []; // Initialize response array

if ($row = $result->fetch_assoc()) {
    $response = $row; // Return the hotel data
} else {
    $response['error'] = 'Hotel not found'; // Capture the error message
}

$stmt->close();
$conn->close();

header('Content-Type: application/json'); // Set the content type to JSON
echo json_encode($response); // Return the response as JSON
?>