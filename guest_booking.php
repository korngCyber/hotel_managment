<?php
session_start();
require_once 'core_config/db.php';

// Check if user is logged in and is a guest
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'guest') {
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

// Fetch all guests
$guestsQuery = "SELECT gId, gName FROM tbGuests ORDER BY gName ASC";
$guestsResult = $conn->query($guestsQuery);
$guests = [];
while ($guest = $guestsResult->fetch_assoc()) {
    $guests[] = $guest;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
    :root {
        --primary-color: #4a90e2;
        --secondary-color: #f5f7fa;
        --accent-color: #ff6b6b;
        --text-color: #333;
        --border-radius: 8px;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: var(--secondary-color);
        color: var(--text-color);
    }

    .container {
        background-color: #ffffff;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        margin-top: 2rem;
    }

    h1 {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 8px;
    }

    .form-control {
        border-radius: 8px;
        padding: 12px;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #6e8efb, #a777e3);
        border: none;
        border-radius: 8px;
        padding: 12px;
        font-weight: 600;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: 45px;
        padding: 8px;
        border: 1px solid #ced4da;
        border-radius: 8px;
    }

    .btn-logout {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background-color: var(--accent-color);
        color: white;
        border: none;
        border-radius: var(--border-radius);
        padding: 8px 16px;
    }
    </style>
</head>

<body>
    <a href="logout.php" class="btn btn-logout">
        <i class="bi bi-box-arrow-right me-2"></i>Logout
    </a>

    <div class="container">
        <h1>Make a Booking</h1>
        <form id="bookingForm">
            <input type="hidden" name="gId" value="<?= htmlspecialchars($guestId) ?>">

            <div class="mb-4">
                <label for="roomId" class="form-label">
                    <i class="bi bi-door-closed me-2"></i>Select Room
                </label>
                <select class="form-control" id="roomId" name="rId" required>
                    <option value="">Select a room...</option>
                    <?php foreach ($roomsResult as $room) { ?>
                    <option value="<?= htmlspecialchars($room['rId']) ?>"
                        data-price="<?= htmlspecialchars($room['rPrice']) ?>">
                        <?= htmlspecialchars($room['htName']) ?> -
                        <?= htmlspecialchars($room['rName']) ?> -
                        <?= htmlspecialchars($room['rType']) ?>
                        ($<?= htmlspecialchars(number_format($room['rPrice'], 2)) ?>)
                    </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="checkIn" class="form-label">
                    <i class="bi bi-calendar-event me-2"></i>Check-in Date
                </label>
                <input type="date" class="form-control" id="checkIn" name="bCheckIn" required>
            </div>

            <div class="mb-4">
                <label for="checkOut" class="form-label">
                    <i class="bi bi-calendar-event me-2"></i>Check-out Date
                </label>
                <input type="date" class="form-control" id="checkOut" name="bCheckout" required>
            </div>

            <div class="mb-4">
                <label for="price" class="form-label">
                    <i class="bi bi-currency-dollar me-2"></i>Price
                </label>
                <input type="number" step="0.01" class="form-control" id="price" name="bPrice" readonly>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Make Booking
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Set minimum date for check-in to today
        var today = new Date().toISOString().split('T')[0];
        $('#checkIn').attr('min', today);

        // Update check-out minimum date when check-in changes
        $('#checkIn').change(function() {
            $('#checkOut').attr('min', $(this).val());
            if ($('#checkOut').val() < $(this).val()) {
                $('#checkOut').val($(this).val());
            }
            updatePrice();
        });

        $('#checkOut').change(updatePrice);
        $('#roomId').change(updatePrice);

        function updatePrice() {
            var selectedOption = $('#roomId').find('option:selected');
            var price = selectedOption.data('price');
            var checkIn = new Date($('#checkIn').val());
            var checkOut = new Date($('#checkOut').val());

            if (!isNaN(checkIn.getTime()) && !isNaN(checkOut.getTime())) {
                var nights = (checkOut - checkIn) / (1000 * 60 * 60 * 24);
                var totalPrice = price * nights;
                $('#price').val(totalPrice.toFixed(2));
            } else {
                $('#price').val(price || '');
            }
        }

        $('#bookingForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: 'add_booking.php',
                type: 'POST',
                data: Object.fromEntries(formData),
                success: function(response) {
                    try {
                        var res = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (res.success) {
                            alert('Booking added successfully');
                            location.reload();
                        } else {
                            alert('Failed to add booking: ' + res.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('Unexpected error. Check server response.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Failed to add booking. Check the server logs.');
                }
            });
        });
    });
    </script>
</body>

</html>