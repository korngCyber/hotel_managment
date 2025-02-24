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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['gName']) && isset($_POST['gMail']) && isset($_FILES['gImage'])) {
        $name = $_POST['gName'];
        $email = $_POST['gMail'];
        $phone = $_POST['gPhone'] ?? '';
        $dob = $_POST['gDob'] ?? '';

        // Handle file upload
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Generate unique filename
        $imageFileType = strtolower(pathinfo($_FILES["gImage"]["name"], PATHINFO_EXTENSION));
        $uniquePrefix = 'guest_' . date('Ymd_His') . '_' . uniqid();
        $targetFile = $targetDir . $uniquePrefix . '.' . $imageFileType;

        // Validate image file
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.']);
            exit;
        }

        if (!getimagesize($_FILES["gImage"]["tmp_name"])) {
            echo json_encode(['success' => false, 'message' => 'Uploaded file is not an image.']);
            exit;
        }

        if (move_uploaded_file($_FILES["gImage"]["tmp_name"], $targetFile)) {
            $stmt = $conn->prepare("INSERT INTO tbGuests (gName, gMail, gPhone, gDob, gImage) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $dob, $targetFile);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                // If database insert fails, delete the uploaded image
                if (file_exists($targetFile)) {
                    unlink($targetFile);
                }
                echo json_encode(['success' => false, 'message' => 'Database insertion failed: ' . $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'File upload failed.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();