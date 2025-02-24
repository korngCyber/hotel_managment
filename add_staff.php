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
    if (isset($_POST['sName']) && isset($_POST['sPos']) && isset($_FILES['sImage'])) {
        $name = $_POST['sName'];
        $position = $_POST['sPos'];
        $contact = $_POST['sCon'] ?? '';
        $address = $_POST['sAddr'] ?? '';
        
        // Fix sWork value checking
        $work = isset($_POST['sWork']) && $_POST['sWork'] === '1' ? 1 : 0;

        // Handle file upload
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Generate unique filename
        $imageFileType = strtolower(pathinfo($_FILES["sImage"]["name"], PATHINFO_EXTENSION));
        $uniquePrefix = 'staff_' . date('Ymd_His') . '_' . uniqid();
        $targetFile = $targetDir . $uniquePrefix . '.' . $imageFileType;

        // Validate image file
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.']);
            exit;
        }

        if (!getimagesize($_FILES["sImage"]["tmp_name"])) {
            echo json_encode(['success' => false, 'message' => 'Uploaded file is not an image.']);
            exit;
        }

        if (move_uploaded_file($_FILES["sImage"]["tmp_name"], $targetFile)) {
            $stmt = $conn->prepare("INSERT INTO tbStaffs (sName, sPos, sCon, sAddr, sImage, sWork) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $name, $position, $contact, $address, $targetFile, $work);

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
?>