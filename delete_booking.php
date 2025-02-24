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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'])) {
        $response = ['success' => false, 'message' => 'Missing booking ID'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $bookingId = $_POST['id'];

    // Get room ID before deleting booking
    $roomStmt = $conn->prepare("SELECT rId FROM tbBookings WHERE bId = ?");
    $roomStmt->bind_param("i", $bookingId);
    $roomStmt->execute();
    $roomResult = $roomStmt->get_result();
    $room = $roomResult->fetch_assoc();
    $roomId = $room['rId'];
    $roomStmt->close();

    // Delete booking
    $stmt = $conn->prepare("DELETE FROM tbBookings WHERE bId = ?");
    if (!$stmt) {
        $response = ['success' => false, 'message' => 'Prepare failed: ' . $conn->error];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("i", $bookingId);

    if ($stmt->execute()) {
        // Update room status to available
        $updateRoom = $conn->prepare("UPDATE tbRooms SET rStatus = 'available' WHERE rId = ?");
        $updateRoom->bind_param("i", $roomId);
        $updateRoom->execute();
        $updateRoom->close();

        $response['success'] = true;
        $response['message'] = "Booking deleted successfully.";
    } else {
        $response['success'] = false;
        $response['message'] = "Error deleting booking: " . $stmt->error;
    }

    $stmt->close();
} else {
    $response = ['success' => false, 'message' => 'Invalid request method'];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
