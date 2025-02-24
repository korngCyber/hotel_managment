<?php
require_once 'core_config/db.php';
$conn = db_connect();

// Fetch all bookings with guest and room details
$query = "SELECT b.bId, b.rId, b.gId, g.gName, b.bCheckIn, b.bCheckout, b.bPrice, r.rStatus, r.rType, h.htName 
          FROM tbBookings b
          LEFT JOIN tbGuests g ON b.gId = g.gId
          LEFT JOIN tbRooms r ON b.rId = r.rId
          LEFT JOIN tbHotels h ON r.htId = h.htId";
$result = $conn->query($query);

if (!$result) {
    die("Database query failed: " . $conn->error);
}

// Fetch all available rooms
$roomsQuery = "SELECT r.rId, r.rType, h.htName 
               FROM tbRooms r
               LEFT JOIN tbHotels h ON r.htId = h.htId 
               WHERE r.rStatus = 'available'";
$roomsResult = $conn->query($roomsQuery);
$availableRooms = [];
while ($room = $roomsResult->fetch_assoc()) {
    $availableRooms[] = $room;
}

// Fetch all guests
$guestsQuery = "SELECT gId, gName FROM tbGuests";
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
    <title>Manage Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

    .modal-content {
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        background: linear-gradient(135deg, #6e8efb, #a777e3);
        border-radius: 15px 15px 0 0;
        padding: 20px;
    }

    .modal-title {
        font-weight: 600;
        letter-spacing: 1px;
        color: #ffffff;
    }

    .modal-body {
        padding: 30px;
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

    .form-control:focus {
        box-shadow: 0 0 0 3px rgba(110, 142, 251, 0.1);
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

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(110, 142, 251, 0.4);
    }

    .booking-preview {
        margin-top: 8px;
        padding: 8px;
        background-color: #f8f9fa;
        border-radius: 4px;
        font-size: 0.9em;
    }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-3">
            <i class="bi bi-calendar-check me-2"></i>Manage Bookings
        </h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBookingModal">
            <i class="bi bi-plus-circle me-2"></i>Add New Booking
        </button>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Room Details</th>
                    <th>Guest Name</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Price</th>
                    <th>Room Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['bId']) ?></td>
                    <td>
                        <?= htmlspecialchars($row['htName']) ?> -
                        <?= htmlspecialchars($row['rType']) ?>
                        (ID: <?= htmlspecialchars($row['rId']) ?>)
                    </td>
                    <td><?= htmlspecialchars($row['gName']) ?></td>
                    <td><?= htmlspecialchars($row['bCheckIn']) ?></td>
                    <td><?= htmlspecialchars($row['bCheckout']) ?></td>
                    <td>$<?= htmlspecialchars(number_format($row['bPrice'], 2)) ?></td>
                    <td>
                        <span class="badge bg-<?= $row['rStatus'] == 'available' ? 'success' : 'warning' ?>">
                            <?= htmlspecialchars($row['rStatus']) ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary edit-booking" data-id="<?= $row['bId'] ?>">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-booking" data-id="<?= $row['bId'] ?>">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for Adding Booking -->
    <div class="modal fade" id="addBookingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-plus me-2"></i>Add New Booking
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addBookingForm">
                        <div class="mb-3">
                            <label for="roomId" class="form-label">
                                <i class="bi bi-door-closed me-2"></i>Select Room
                            </label>
                            <select class="form-control" id="roomId" name="rId" required>
                                <option value="">Select a room...</option>
                                <?php foreach ($availableRooms as $room) { ?>
                                <option value="<?= htmlspecialchars($room['rId']) ?>">
                                    <?= htmlspecialchars($room['htName']) ?> -
                                    <?= htmlspecialchars($room['rType']) ?>
                                    (ID: <?= htmlspecialchars($room['rId']) ?>)
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="guestId" class="form-label">
                                <i class="bi bi-person me-2"></i>Select Guest
                            </label>
                            <select class="form-control" id="guestId" name="gId" required>
                                <option value="">Select a guest...</option>
                                <?php foreach ($guests as $guest) { ?>
                                <option value="<?= htmlspecialchars($guest['gId']) ?>">
                                    <?= htmlspecialchars($guest['gName']) ?>
                                    (ID: <?= htmlspecialchars($guest['gId']) ?>)
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="checkIn" class="form-label">
                                <i class="bi bi-calendar-event me-2"></i>Check-in Date
                            </label>
                            <input type="date" class="form-control" id="checkIn" name="bCheckIn" required>
                        </div>
                        <div class="mb-3">
                            <label for="checkOut" class="form-label">
                                <i class="bi bi-calendar-event me-2"></i>Check-out Date
                            </label>
                            <input type="date" class="form-control" id="checkOut" name="bCheckout" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">
                                <i class="bi bi-currency-dollar me-2"></i>Price
                            </label>
                            <input type="number" step="0.01" class="form-control" id="price" name="bPrice" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Add Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Add Booking
        $('#addBookingForm').submit(function(e) {
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

        // Edit Booking (fetch)
        $('.edit-booking').click(function() {
            var bookingId = $(this).data('id');
            $.get('get_booking.php', {
                id: bookingId
            }, function(data) {
                if (data.success !== false) {
                    $('#editBookingId').val(data.bId);
                    $('#editRoomId').val(data.rId);
                    $('#editGuestId').val(data.gId);
                    $('#editCheckIn').val(data.bCheckIn);
                    $('#editCheckOut').val(data.bCheckout);
                    $('#editPrice').val(data.bPrice);
                    $('#editBookingModal').modal('show');
                } else {
                    alert(data.message);
                }
            }).fail(function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('Failed to fetch booking data. Please try again.');
            });
        });

        // Delete Booking
        $('.delete-booking').click(function() {
            if (!confirm('Are you sure you want to delete this booking?')) return;

            var bookingId = $(this).data('id');

            $.ajax({
                url: 'delete_booking.php',
                type: 'POST',
                data: {
                    id: bookingId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Booking deleted successfully');
                        location.reload();
                    } else {
                        alert('Failed to delete booking: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Failed to delete booking. Check the server logs.');
                }
            });
        });
    });
    </script>
</body>

</html>