<?php
session_start();
require_once 'core_config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$conn = db_connect();

// Get logged in guest info
$guestId = $_SESSION['user_id'];
$guestQuery = "SELECT gId, gName FROM tbGuests WHERE gId = ?";
$guestStmt = $conn->prepare($guestQuery);
$guestStmt->bind_param("i", $guestId);
$guestStmt->execute();
$guestResult = $guestStmt->get_result();
$guest = $guestResult->fetch_assoc();

if (!$guest) {
    die("Error: Guest not found");
}

// Fetch available rooms with hotel info
$roomsQuery = "SELECT r.rId, r.rName, r.rType, r.rPrice, h.htName 
               FROM tbRooms r
               LEFT JOIN tbHotels h ON r.htId = h.htId 
               WHERE r.rStatus = 'available'";
$roomsResult = $conn->query($roomsQuery);

// Only process POST requests for the API endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api'])) {
    header('Content-Type: application/json');

    try {
        $rId = $_POST['rId'];
        $checkIn = $_POST['bCheckIn'];
        $checkOut = $_POST['bCheckout'];
        $price = $_POST['bPrice'];

        // Basic Validation
        if (empty($rId) || empty($checkIn) || empty($checkOut) || empty($price)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }

        // Check if room is available
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM tbBookings 
            WHERE rId = ? AND (
                (bCheckIn <= ? AND bCheckout >= ?) OR 
                (bCheckIn <= ? AND bCheckout >= ?) OR
                (bCheckIn >= ? AND bCheckout <= ?)
            )");
        $checkStmt->execute([$rId, $checkIn, $checkIn, $checkOut, $checkOut, $checkIn, $checkOut]);
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Room is not available for selected dates']);
            exit;
        }

        // Insert booking
        $stmt = $conn->prepare("INSERT INTO tbBookings (rId, gId, bCheckIn, bCheckout, bPrice) 
            VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $success = $stmt->execute([$rId, $guestId, $checkIn, $checkOut, $price]);
        if (!$success) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f4f7f9;
        padding: 20px;
    }

    .container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        color: #555;
        font-weight: bold;
    }

    input,
    select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    input[type="number"] {
        appearance: textfield;
    }

    .submit-btn {
        width: 100%;
        padding: 12px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s;
    }

    .submit-btn:hover {
        background-color: #45a049;
    }

    #statusMessage {
        margin-top: 20px;
        padding: 10px;
        border-radius: 4px;
        text-align: center;
        display: none;
    }

    .success {
        background-color: #dff0d8;
        color: #3c763d;
    }

    .error {
        background-color: #f2dede;
        color: #a94442;
    }
    </style>

</head>

<body>
    <div class="container">
        <h1>Book a Room</h1>
        <form id="bookingForm">
            <input type="hidden" name="api" value="1">
            <div class="form-group mb-3">
                <label for="roomId" class="form-label">Select Room:</label>
                <select class="form-select" id="roomId" name="rId" required>
                    <option value="">Choose a room...</option>
                    <?php while ($room = $roomsResult->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($room['rId']) ?>"
                        data-price="<?= htmlspecialchars($room['rPrice']) ?>">
                        <?= htmlspecialchars($room['htName']) ?> -
                        <?= htmlspecialchars($room['rName']) ?>
                        (<?= htmlspecialchars($room['rType']) ?>) -
                        $<?= htmlspecialchars(number_format($room['rPrice'], 2)) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Guest:</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($guest['gName']) ?>" readonly>
            </div>

            <div class="form-group mb-3">
                <label for="checkIn" class="form-label">Check-In Date:</label>
                <input type="date" class="form-control" id="checkIn" name="bCheckIn" required>
            </div>

            <div class="form-group mb-3">
                <label for="checkOut" class="form-label">Check-Out Date:</label>
                <input type="date" class="form-control" id="checkOut" name="bCheckout" required>
            </div>

            <div class="form-group mb-3">
                <label for="price" class="form-label">Price:</label>
                <input type="number" class="form-control" id="price" name="bPrice" step="0.01" readonly required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Create Booking</button>
        </form>
        <div id="statusMessage" class="mt-3"></div>
    </div>
    <div class="text-center mt-3">
        <a href="logout.php" class="btn btn-danger w-100">Logout</a>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Set minimum date for check-in to today
        var today = new Date().toISOString().split('T')[0];
        $('#checkIn').attr('min', today);

        // Update check-out minimum date when check-in is selected
        $('#checkIn').change(function() {
            $('#checkOut').attr('min', $(this).val());
            if ($('#checkOut').val() < $(this).val()) {
                $('#checkOut').val($(this).val());
            }
        });

        // Set price when room is selected
        $('#roomId').change(function() {
            var selectedOption = $(this).find('option:selected');
            var price = selectedOption.data('price');
            $('#price').val(price || '');
        });

        // Form submission
        $('#bookingForm').submit(function(e) {
            e.preventDefault();

            // Validate dates
            var checkIn = new Date($('#checkIn').val());
            var checkOut = new Date($('#checkOut').val());

            if (checkOut <= checkIn) {
                showMessage('Check-out date must be after check-in date', 'error');
                return;
            }

            var formData = new FormData(this);

            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: Object.fromEntries(formData),
                success: function(response) {
                    try {
                        if (response.success) {
                            showMessage('Booking created successfully!', 'success');
                            $('#bookingForm')[0].reset();
                        } else {
                            showMessage(response.message || 'Error creating booking',
                                'error');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        showMessage('Unexpected error occurred', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    showMessage('Failed to create booking', 'error');
                }
            });
        });

        function showMessage(message, type) {
            var statusDiv = $('#statusMessage');
            statusDiv.removeClass('alert-success alert-danger')
                .addClass(type === 'success' ? 'alert alert-success' : 'alert alert-danger')
                .html(message)
                .show();

            setTimeout(function() {
                statusDiv.fadeOut();
            }, 5000);
        }
    });
    </script>
</body>

</html>