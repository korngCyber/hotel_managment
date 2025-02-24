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
    $rId = $_POST['id'] ?? 0;

    if (empty($rId)) {
        $response = ['success' => false, 'message' => 'Missing required room ID'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Check if room has any active bookings before deleting
    $checkBookings = $conn->prepare("SELECT COUNT(*) as count FROM tbBookings WHERE rId = ? AND bCheckout > NOW()");
    $checkBookings->bind_param("i", $rId);
    $checkBookings->execute();
    $result = $checkBookings->get_result();
    $bookingCount = $result->fetch_assoc()['count'];
    $checkBookings->close();

    if ($bookingCount > 0) {
        $response = ['success' => false, 'message' => 'Cannot delete room with active bookings'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM tbRooms WHERE rId = ?");
    if (!$stmt) {
        $response = ['success' => false, 'message' => 'Prepare failed: ' . $conn->error];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("i", $rId);

    $response = [];
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Room deleted successfully.";
    } else {
        $response['success'] = false;
        $response['message'] = "Error deleting room: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>