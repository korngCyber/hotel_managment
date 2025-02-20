<?php
    require_once 'core_config/db.php';

    $response = ['success' => false]; // Initialize response array

    if (isset($_GET['id'])) {
        $roomID = $_GET['id'];
        $conn = db_connect();

        if (!$conn) {
            $response['message'] = 'Database connection failed';
        } else {
            $stmt = $conn->prepare("SELECT * FROM Rooms WHERE RoomID = ?");
            if ($stmt) {
                $stmt->bind_param("i", $roomID);
                $stmt->execute();
                $result = $stmt->get_result();
                $room = $result->fetch_assoc();

                if ($room) {
                    $response['success'] = true;
                    $response['data'] = $room; // Include room data in response
                } else {
                    $response['message'] = 'Room not found';
                }

                $stmt->close();
            } else {
                $response['message'] = 'Prepare failed: ' . $conn->error;
            }
        }

        $conn->close();
    } else {
        $response['message'] = 'Missing room ID';
    }

    header('Content-Type: application/json'); // Set the content type to JSON
    echo json_encode($response); // Return the response as JSON

?>