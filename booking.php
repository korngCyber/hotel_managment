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

// Fetch all available rooms and currently booked rooms for editing
$roomsQuery = "SELECT r.rId, r.rName, r.rType, r.rPrice, h.htName 
               FROM tbRooms r
               LEFT JOIN tbHotels h ON r.htId = h.htId 
               WHERE r.rStatus = 'available' 
               OR r.rId IN (SELECT rId FROM tbBookings)";  // Include rooms that are currently booked
$roomsResult = $conn->query($roomsQuery);
$availableRooms = [];
while ($room = $roomsResult->fetch_assoc()) {
    $availableRooms[] = $room;
}

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
    <title>Manage Bookings</title>
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

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: darken(var(--primary-color), 10%);
        border-color: darken(var(--primary-color), 10%);
    }

    .table {
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .table thead th {
        background-color: var(--primary-color);
        color: #ffffff;
        border: none;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .btn-sm {
        border-radius: 20px;
        padding: 0.25rem 0.75rem;
    }

    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
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

    .select2-container {
        width: 100% !important;
    }

    /* Additional Select2 Styling */
    .select2-container--default .select2-selection--single {
        height: 45px;
        padding: 8px;
        border: 1px solid #ced4da;
        border-radius: 8px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 43px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
    }

    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 8px;
    }

    .select2-search--dropdown .select2-search__field {
        padding: 8px;
        border-radius: 4px;
    }

    .select2-results__option {
        padding: 8px;
    }

    .guest-search-box {
        margin-bottom: 10px;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>Manage Bookings</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBookingModal">Add New
            Booking</button>

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
                    <td><span
                            class="badge bg-<?= $row['rStatus'] == 'available' ? 'success' : 'warning' ?>"><?= htmlspecialchars($row['rStatus']) ?></span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary edit-booking"
                            data-id="<?= $row['bId'] ?>">Edit</button>
                        <button class="btn btn-sm btn-outline-danger delete-booking"
                            data-id="<?= $row['bId'] ?>">Delete</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for Adding Booking -->
    <div class="modal fade" id="addBookingModal" tabindex="-1" aria-labelledby="addBookingModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-plus me-2"></i>Add New Booking
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addBookingForm">
                        <div class="mb-4">
                            <label for="roomId" class="form-label">
                                <i class="bi bi-door-closed me-2"></i>Select Room
                            </label>
                            <select class="form-control" id="roomId" name="rId" required>
                                <option value="">Select a room...</option>
                                <?php foreach ($availableRooms as $room) { ?>
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
                            <label for="guestId" class="form-label">
                                <i class="bi bi-person me-2"></i>Select Guest
                            </label>
                            <select class="form-control select2" id="guestId" name="gId" required>
                                <option value="">Search for a guest...</option>
                                <?php foreach ($guests as $guest) { ?>
                                <option value="<?= htmlspecialchars($guest['gId']) ?>">
                                    <?= htmlspecialchars($guest['gName']) ?>
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
                                <i class="bi bi-check-circle me-2"></i>Add Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Editing Booking -->
    <div class="modal fade" id="editBookingModal" tabindex="-1" aria-labelledby="editBookingModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-check me-2"></i>Edit Booking
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editBookingForm">
                        <input type="hidden" id="editBookingId" name="bId">
                        <div class="mb-4">
                            <label for="editRoomId" class="form-label">
                                <i class="bi bi-door-closed me-2"></i>Select Room
                            </label>
                            <select class="form-control" id="editRoomId" name="rId" required>
                                <option value="">Select a room...</option>
                                <?php foreach ($availableRooms as $room) { ?>
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
                            <label for="editGuestId" class="form-label">
                                <i class="bi bi-person me-2"></i>Select Guest
                            </label>
                            <select class="form-control select2" id="editGuestId" name="gId" required>
                                <option value="">Search for a guest...</option>
                                <?php foreach ($guests as $guest) { ?>
                                <option value="<?= htmlspecialchars($guest['gId']) ?>">
                                    <?= htmlspecialchars($guest['gName']) ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="editCheckIn" class="form-label">
                                <i class="bi bi-calendar-event me-2"></i>Check-in Date
                            </label>
                            <input type="date" class="form-control" id="editCheckIn" name="bCheckIn" required>
                        </div>
                        <div class="mb-4">
                            <label for="editCheckOut" class="form-label">
                                <i class="bi bi-calendar-event me-2"></i>Check-out Date
                            </label>
                            <input type="date" class="form-control" id="editCheckOut" name="bCheckout" required>
                        </div>
                        <div class="mb-4">
                            <label for="editPrice" class="form-label">
                                <i class="bi bi-currency-dollar me-2"></i>Price
                            </label>
                            <input type="number" step="0.01" class="form-control" id="editPrice" name="bPrice" readonly>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Update Booking
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
        // Initialize Select2 for guest selection
        $('.select2').select2({
            placeholder: 'Search for a guest...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#addBookingModal')
        });

        $('#editGuestId').select2({
            placeholder: 'Search for a guest...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#editBookingModal')
        });

        // Set price when room is selected (for both add and edit forms)
        $('#roomId, #editRoomId').change(function() {
            var selectedOption = $(this).find('option:selected');
            var price = selectedOption.data('price');
            var priceInput = $(this).closest('form').find('[name="bPrice"]');
            priceInput.val(price || '');
        });

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
            console.log('Editing booking ID:', bookingId); // Debug

            $.ajax({
                url: 'get_booking.php',
                type: 'GET',
                data: {
                    id: bookingId
                },
                dataType: 'json',
                success: function(data) {
                    console.log('Received data:', data); // Debug
                    console.log('Available room options:', $('#editRoomId option')
                        .length); // Debug

                    if (data.success !== false) {
                        $('#editBookingId').val(data.bId);

                        // Debug room selection
                        console.log('Setting room ID:', data.rId);
                        console.log('Available options:', $('#editRoomId option').map(
                            function() {
                                return {
                                    value: this.value,
                                    text: this.text
                                };
                            }).get());

                        $('#editRoomId').val(data.rId).trigger('change');
                        $('#editGuestId').val(data.gId).trigger('change');
                        $('#editCheckIn').val(data.bCheckIn);
                        $('#editCheckOut').val(data.bCheckout);
                        $('#editPrice').val(data.bPrice);
                        $('#editBookingModal').modal('show');
                    } else {
                        alert(data.message || 'Failed to fetch booking data');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Failed to fetch booking data. Please try again.');
                }
            });
        });

        // Update Booking
        $('#editBookingForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: 'update_booking.php',
                type: 'POST',
                data: Object.fromEntries(formData),
                success: function(response) {
                    try {
                        var res = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (res.success) {
                            alert('Booking updated successfully');
                            location.reload();
                        } else {
                            alert('Failed to update booking: ' + res.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('Unexpected error. Check server response.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Failed to update booking. Check the server logs.');
                }
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