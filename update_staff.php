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
    if (!isset($_POST['sId']) || !isset($_POST['sName']) || !isset($_POST['sPos'])) {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit;
    }

    $staffId = $_POST['sId'];
    $name = $_POST['sName'];
    $position = $_POST['sPos'];
    $contact = $_POST['sCon'] ?? '';
    $address = $_POST['sAddr'] ?? '';
    $work = isset($_POST['sWork']) ? 1 : 0;

    // Fetch existing image URL
    $query = "SELECT sImage FROM tbStaffs WHERE sId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $staffId);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingData = $result->fetch_assoc();
    $existingImageURL = $existingData['sImage'] ?? '';
    $stmt->close();

    // Handle new image upload if provided
    if (isset($_FILES['sImage']) && $_FILES['sImage']['error'] === UPLOAD_ERR_OK) {
        $imageDir = 'uploads/';
        if (!is_dir($imageDir)) {
            mkdir($imageDir, 0777, true);
        }

        // Generate unique filename
        $imageExtension = strtolower(pathinfo($_FILES['sImage']['name'], PATHINFO_EXTENSION));
        $uniquePrefix = 'staff_' . date('Ymd_His') . '_' . uniqid();
        $imagePath = $imageDir . $uniquePrefix . '.' . $imageExtension;

        // Validate image type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageExtension, $allowedTypes)) {
            echo json_encode(["success" => false, "message" => "Only JPG, JPEG, PNG & GIF files are allowed"]);
            exit;
        }

        if (move_uploaded_file($_FILES['sImage']['tmp_name'], $imagePath)) {
            // Delete old image if exists
            if (!empty($existingImageURL) && file_exists($existingImageURL) && $existingImageURL !== $imagePath) {
                unlink($existingImageURL);
            }
            $existingImageURL = $imagePath;
        } else {
            echo json_encode(["success" => false, "message" => "Failed to upload image"]);
            exit;
        }
    }

    // Update staff information
    $query = "UPDATE tbStaffs SET sName = ?, sPos = ?, sCon = ?, sAddr = ?, sWork = ?";
    $params = [$name, $position, $contact, $address, $work];
    $types = "ssssi";

    // Only include image in update if it exists
    if (!empty($existingImageURL)) {
        $query .= ", sImage = ?";
        $params[] = $existingImageURL;
        $types .= "s";
    }

    $query .= " WHERE sId = ?";
    $params[] = $staffId;
    $types .= "i";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        // If update fails and we uploaded a new image, delete it
        if (isset($imagePath) && file_exists($imagePath) && $imagePath !== $existingImageURL) {
            unlink($imagePath);
        }
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }
    $stmt->close();
}

$conn->close();
