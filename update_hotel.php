<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'core_config/db.php';

header('Content-Type: application/json');

$conn = db_connect();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate required fields
    if (!isset($_POST['htId']) || !isset($_POST['htName'])) {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit;
    }

    $hotelId = $_POST['htId'];
    $name = $_POST['htName'];
    $address = $_POST['htAddr'] ?? '';
    $contact = $_POST['htCon'] ?? '';

    // Update hotel information
    $query = "UPDATE tbHotels SET htName = ?, htAddr = ?, htCon = ? WHERE htId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $name, $address, $contact, $hotelId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>