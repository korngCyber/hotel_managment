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

if (isset($_GET['id'])) {
    $bookingId = $_GET['id'];

    // Join with rooms and guests tables to get more details
    $query = "SELECT b.bId, b.rId, b.gId, b.bCheckIn, b.bCheckout, b.bPrice,
                     r.rName, r.rType, r.rStatus,
                     g.gName, h.htName
              FROM tbBookings b 
              LEFT JOIN tbRooms r ON b.rId = r.rId 
              LEFT JOIN tbGuests g ON b.gId = g.gId 
              LEFT JOIN tbHotels h ON r.htId = h.htId
              WHERE b.bId = ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        $response = ['success' => false, 'message' => 'Prepare failed: ' . $conn->error];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = $result->fetch_assoc();
        $response['success'] = true;
    } else {
        $response = ['success' => false, 'message' => 'Booking not found'];
    }

    $stmt->close();
} else {
    $response = ['success' => false, 'message' => 'Missing booking ID'];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>