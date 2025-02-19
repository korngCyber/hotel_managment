<?php
require_once 'core_config/db.php';
$conn = db_connect();

$name = $_POST['name'];
$address = $_POST['address'];
$contact = $_POST['contact'];

$query = "INSERT INTO Hotels (Name, Address, Contact) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $name, $address, $contact);

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