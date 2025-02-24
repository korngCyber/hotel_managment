<?php
require_once 'core_config/db.php';
$conn = db_connect();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $guestID = $_POST['id'];

    // Fetch the image path
    $query = "SELECT gImage FROM tbGuests WHERE gId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $guestID);
    $stmt->execute();
    $stmt->bind_result($imageURL);
    $stmt->fetch();
    $stmt->close();

    // Delete the record
    $query = "DELETE FROM tbGuests WHERE gId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $guestID);

    if ($stmt->execute()) {
        if (!empty($imageURL) && file_exists($imageURL)) {
            unlink($imageURL); // Delete image file
        }
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }
    $stmt->close();
}
$conn->close();
?>