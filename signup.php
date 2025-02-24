<?php
session_start();
require_once 'core_config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']); 
    $dob = $_POST['dob'];
    $phone = trim($_POST['phone']);

    // File upload handling
    $target_dir = "uploads/";
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

    // Generate unique filename using timestamp and random string
    $timestamp = time();
    $random_string = bin2hex(random_bytes(8));
    $new_filename = $timestamp . '_' . $random_string . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;

    // Check if image file is actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $error = "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["image"]["size"] > 500000) {
        $error = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (
        $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif"
    ) {
        $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        $error = $error ?? "Sorry, your file was not uploaded.";
    } else {
        $conn = db_connect();
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
            
            // Use prepared statement to insert into tbGuests table
            $insert_stmt = $conn->prepare("INSERT INTO tbGuests (gName, gMail, gDob, gPhone, gImage) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssss", $name, $email, $dob, $phone, $image_path);

            if ($insert_stmt->execute()) {
                $response = array('success' => true);
                echo json_encode($response);
                exit();
            } else {
                $error = "Error creating account. Please try again.";
            }
            $insert_stmt->close();
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
        
        db_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
    body {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .signup-container {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        width: 100%;
        max-width: 500px;
    }

    .signup-header {
        background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        color: white;
        padding: 30px;
        text-align: center;
        font-size: 28px;
        font-weight: bold;
    }

    .signup-form {
        padding: 40px;
    }

    .form-control {
        border-radius: 30px;
        padding: 12px 20px;
        border: 2px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #2a5298;
        box-shadow: 0 0 0 0.2rem rgba(42, 82, 152, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        border: none;
        border-radius: 30px;
        padding: 12px;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(0, 0, 0, 0.1);
    }

    .input-group-text {
        background-color: transparent;
        border-right: none;
        border-radius: 30px 0 0 30px;
    }

    .error-message {
        color: #dc3545;
        text-align: center;
        margin-bottom: 20px;
        font-weight: bold;
    }

    .success-message {
        color: #28a745;
        text-align: center;
        margin-bottom: 20px;
        font-weight: bold;
    }

    .login-link {
        text-align: center;
        margin-top: 20px;
    }

    .login-link a {
        color: #2a5298;
        text-decoration: none;
        font-weight: bold;
    }

    .form-label {
        font-weight: 600;
        color: #2a5298;
    }

    .profile-image-container {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        margin: 0 auto 20px;
        border: 4px solid #2a5298;
        position: relative;
        cursor: pointer;
        background-color: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-image-container .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .profile-image-container:hover .overlay {
        opacity: 1;
    }

    .profile-image-container .overlay i,
    .profile-image-container .default-icon {
        color: #2a5298;
        font-size: 40px;
    }

    .profile-image-container .overlay i {
        color: white;
    }

    #image {
        display: none;
    }
    </style>
</head>

<body>
    <div class="signup-container">
        <div class="signup-header">
            <i class="fas fa-hotel me-2"></i>Hotel Management Sign Up
        </div>
        <div class="signup-form">
            <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form action="add_guest.php" method="post" enctype="multipart/form-data">
                <div class="profile-image-container" onclick="document.getElementById('image').click();">
                    <img id="preview" src="/placeholder.svg" alt="Profile Image" style="display: none;">
                    <i class="fas fa-user default-icon"></i>
                    <div class="overlay">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                <input type="file" id="image" name="gImage" accept="image/*" required onchange="previewImage(event)">

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="name" name="gName"
                            placeholder="Enter your full name" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="gMail" placeholder="Enter your email"
                            required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="dob" class="form-label">Date of Birth</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        <input type="date" class="form-control" id="dob" name="gDob" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="tel" class="form-control" id="phone" name="gPhone"
                            placeholder="Enter your phone number" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Sign Up</button>
            </form>
            <div class="login-link mt-4">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('preview');
            output.src = reader.result;
            output.style.display = 'block';
            document.querySelector('.default-icon').style.display = 'none';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const signupForm = document.querySelector("form");
        const signupButton = document.querySelector("button[type='submit']");

        signupForm.addEventListener("submit", function(event) {
            event.preventDefault();
            signupButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing up...';
            signupButton.disabled = true;

            const formData = new FormData(this);

            fetch('add_guest.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'login.php';
                    } else {
                        signupButton.innerHTML = 'Sign Up';
                        signupButton.disabled = false;
                        alert('Signup failed. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    signupButton.innerHTML = 'Sign Up';
                    signupButton.disabled = false;
                    alert('An error occurred. Please try again.');
                });
        });
    });
    </script>
</body>

</html>