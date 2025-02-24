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
    $rId = $_POST['rId'] ?? 0;
    $gId = $_POST['gId'] ?? 0; 
    $bCheckIn = $_POST['bCheckIn'] ?? '';
    $bCheckout = $_POST['bCheckout'] ?? '';
    $bPrice = $_POST['bPrice'] ?? 0;

    if (empty($rId) || empty($gId) || empty($bCheckIn) || empty($bCheckout) || empty($bPrice)) {
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

    $stmt = $conn->prepare("INSERT INTO tbBookings (rId, gId, bCheckIn, bCheckout, bPrice) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        $response = ['success' => false, 'message' => 'Prepare failed: ' . $conn->error];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("iissd", $rId, $gId, $bCheckIn, $bCheckout, $bPrice);

    $response = [];
    if ($stmt->execute()) {
        // Update room status to booked
        $updateStmt = $conn->prepare("UPDATE tbRooms SET rStatus = 'booked' WHERE rId = ?");
        $updateStmt->bind_param("i", $rId);
        $updateStmt->execute();
        $updateStmt->close();
        
        $response['success'] = true;
        $response['message'] = "Booking added successfully.";
    } else {
        $response['success'] = false;
        $response['message'] = "Error adding booking: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>