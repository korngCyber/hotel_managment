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
    $bId = $_POST['bId'] ?? 0;
    $rId = $_POST['rId'] ?? 0;
    $gId = $_POST['gId'] ?? 0;
    $bCheckIn = $_POST['bCheckIn'] ?? '';
    $bCheckout = $_POST['bCheckout'] ?? '';
    $bPrice = $_POST['bPrice'] ?? 0;

    if (empty($bId) || empty($rId) || empty($gId) || empty($bCheckIn) || empty($bCheckout) || empty($bPrice)) {
        $response = ['success' => false, 'message' => 'Missing required fields'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Validate dates
    $checkIn = new DateTime($bCheckIn);
    $checkout = new DateTime($bCheckout);

    if ($checkout <= $checkIn) {
        $response = ['success' => false, 'message' => 'Check-out date must be after check-in date'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Get current room ID before update
    $oldRoomStmt = $conn->prepare("SELECT rId FROM tbBookings WHERE bId = ?");
    $oldRoomStmt->bind_param("i", $bId);
    $oldRoomStmt->execute();
    $oldRoomResult = $oldRoomStmt->get_result();
    $oldRoom = $oldRoomResult->fetch_assoc();
    $oldRoomId = $oldRoom['rId'];
    $oldRoomStmt->close();

    // Update booking
    $stmt = $conn->prepare("UPDATE tbBookings SET rId = ?, gId = ?, bCheckIn = ?, bCheckout = ?, bPrice = ? WHERE bId = ?");
    if (!$stmt) {
        $response = ['success' => false, 'message' => 'Prepare failed: ' . $conn->error];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("iissdi", $rId, $gId, $bCheckIn, $bCheckout, $bPrice, $bId);

    if ($stmt->execute()) {
        // If room changed, update room statuses
        if ($oldRoomId != $rId) {
            // Set old room to available
            $updateOldRoom = $conn->prepare("UPDATE tbRooms SET rStatus = 'available' WHERE rId = ?");
            $updateOldRoom->bind_param("i", $oldRoomId);
            $updateOldRoom->execute();
            $updateOldRoom->close();

            // Set new room to booked
            $updateNewRoom = $conn->prepare("UPDATE tbRooms SET rStatus = 'booked' WHERE rId = ?");
            $updateNewRoom->bind_param("i", $rId);
            $updateNewRoom->execute();
            $updateNewRoom->close();
        }

        $response['success'] = true;
        $response['message'] = "Booking updated successfully.";
    } else {
        $response['success'] = false;
        $response['message'] = "Error updating booking: " . $stmt->error;
    }

    $stmt->close();
} else {
    $response = ['success' => false, 'message' => 'Invalid request method'];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
