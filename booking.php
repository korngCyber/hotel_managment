<?php
require_once 'core_config/db.php';
$conn = db_connect();

// Fetch all bookings from the database
$query = "SELECT * FROM Bookings";
$result = $conn->query($query);

if (!$result) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-3">Manage Bookings</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBookingModal">Add New Booking</button>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Room ID</th>
                    <th>Customer Name</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['BookingID']) ?></td>
                        <td><?= htmlspecialchars($row['RoomID']) ?></td>
                        <td><?= htmlspecialchars($row['CustomerName']) ?></td>
                        <td><?= htmlspecialchars($row['CheckInDate']) ?></td>
                        <td><?= htmlspecialchars($row['CheckOutDate']) ?></td>
                        <td>
                            <span class="badge bg-<?= $row['Status'] == 'Confirmed' ? 'success' : 'warning' ?>">
                                <?= htmlspecialchars($row['Status']) ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-booking" data-id="<?= $row['BookingID'] ?>">Edit</button>
                            <button class="btn btn-sm btn-outline-danger delete-booking" data-id="<?= $row['BookingID'] ?>">Delete</button>
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
                    <h5 class="modal-title">Add New Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addBookingForm">
                        <div class="mb-3">
                            <label for="roomID" class="form-label">Room ID</label>
                            <input type="number" class="form-control" id="roomID" required>
                        </div>
                        <div class="mb-3">
                            <label for="customerName" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" id="customerName" required>
                        </div>
                        <div class="mb-3">
                            <label for="checkIn" class="form-label">Check-in Date</label>
                            <input type="date" class="form-control" id="checkIn" required>
                        </div>
                        <div class="mb-3">
                            <label for="checkOut" class="form-label">Check-out Date</label>
                            <input type="date" class="form-control" id="checkOut" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#addBookingForm').submit(function (e) {
                e.preventDefault();
                var roomID = $('#roomID').val();
                var customerName = $('#customerName').val();
                var checkIn = $('#checkIn').val();
                var checkOut = $('#checkOut').val();
                $.ajax({
                    url: 'add_booking.php',
                    type: 'POST',
                    data: { roomID, customerName, checkIn, checkOut },
                    success: function () {
                        alert('Booking added successfully');
                        location.reload();
                    },
                    error: function () {
                        alert('Failed to add booking.');
                    }
                });
            });
        });
    </script>
</body>
</html>
