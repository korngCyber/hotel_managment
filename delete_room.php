<?php
require_once 'core_config/db.php';

$response = ['success' => false]; // Initialize response array

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = db_connect();
    $roomID = $_POST['id'] ?? 0; // Use null coalescing operator for safety

    if ($roomID > 0) { // Check if roomID is valid
        $stmt = $conn->prepare("DELETE FROM Rooms WHERE RoomID = ?");
        $stmt->bind_param("i", $roomID);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Room deleted successfully.";
        } else {
            $response['message'] = "Error deleting room: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $response['message'] = 'Invalid room ID.';
    }

    $conn->close();
}

header('Content-Type: application/json'); // Set the content type to JSON
echo json_encode($response); // Return the response as JSON

?>