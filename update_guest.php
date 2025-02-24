<?php
require_once 'core_config/db.php';
$conn = db_connect();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate required fields
    if (!isset($_POST['guestID']) || !isset($_POST['gName']) || !isset($_POST['gMail'])) {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit;
    }

    $guestID = $_POST['guestID'];
    $gName = $_POST['gName'];
    $email = $_POST['gMail'];
    $phone = $_POST['gPhone'] ?? '';
    $dateOfBirth = $_POST['gDob'] ?? '';

    // Fetch existing image URL
    $query = "SELECT gImage FROM tbGuests WHERE gId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $guestID);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingData = $result->fetch_assoc();
    $existingImageURL = $existingData['gImage'] ?? '';
    $stmt->close();

    // Handle new image upload if provided
    if (isset($_FILES['gImage']) && $_FILES['gImage']['error'] === UPLOAD_ERR_OK) {
        $imageDir = 'uploads/';
        if (!is_dir($imageDir)) {
            mkdir($imageDir, 0777, true);
        }

        // Generate unique filename
        $imageExtension = strtolower(pathinfo($_FILES['gImage']['name'], PATHINFO_EXTENSION));
        $uniquePrefix = 'guest_' . date('Ymd_His') . '_' . uniqid();
        $imagePath = $imageDir . $uniquePrefix . '.' . $imageExtension;

        // Validate image type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageExtension, $allowedTypes)) {
            echo json_encode(["success" => false, "message" => "Only JPG, JPEG, PNG & GIF files are allowed"]);
            exit;
        }

        if (move_uploaded_file($_FILES['gImage']['tmp_name'], $imagePath)) {
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

    // Update guest information
    $query = "UPDATE tbGuests SET gName = ?, gMail = ?, gPhone = ?, gDob = ?";
    $params = [$gName, $email, $phone, $dateOfBirth];
    $types = "ssss";

    // Only include image in update if it exists
    if (!empty($existingImageURL)) {
        $query .= ", gImage = ?";
        $params[] = $existingImageURL;
        $types .= "s";
    }

    $query .= " WHERE gId = ?";
    $params[] = $guestID;
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
