<?php
require_once 'core_config/db.php';
$conn = db_connect();

// Fetch all rooms from the database
$query = "SELECT RoomID, HotelID, RoomType, Price, Status FROM Rooms"; // Select only the required fields
$result = $conn->query($query);

// Check for database errors
if (!$result) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-3">Manage Rooms</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addRoomModal">Add New Room</button>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hotel ID</th>
                    <th>Room Type</th>
                    <th>Price ($)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['RoomID']) ?></td>
                        <td><?= htmlspecialchars($row['HotelID']) ?></td>
                        <td><?= htmlspecialchars($row['RoomType']) ?></td>
                        <td><?= htmlspecialchars($row['Price']) ?></td>
                        <td>
                            <span class="badge bg-<?= $row['Status'] == 'available' ? 'success' : 'danger' ?>">
                                <?= htmlspecialchars(ucfirst($row['Status'])) ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-room" data-id="<?= $row['RoomID'] ?>">Edit</button>
                            <button class="btn btn-sm btn-outline-danger delete-room" data-id="<?= $row['RoomID'] ?>">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for Adding Room -->
    <div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addRoomForm">
                        <div class="mb-3">
                            <label for="addHotelId" class="form-label">Hotel ID</label>
                            <input type="number" class="form-control" id="addHotelId" required>
                        </div>
                        <div class="mb-3">
                            <label for="addRoomType" class="form-label">Room Type</label>
                            <input type="text" class="form-control" id="addRoomType" required>
                        </div>
                        <div class="mb-3">
                            <label for="addPrice" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="addPrice" required>
                        </div>
                        <div class="mb-3">
                            <label for="addStatus" class="form-label">Status</label>
                            <select class="form-control" id="addStatus" required>
                                <option value="available">Available</option>
                                <option value="booked">Booked</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Room</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Editing Room -->
    <div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editRoomForm">
                        <input type="hidden" id="editRoomID">
                        <div class="mb-3">
                            <label for="editRoomType" class="form-label">Room Type</label>
                            <input type="text" class="form-control" id="editRoomType" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPrice" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="editPrice" required>
                        </div>
                        <div class="mb-3">
                            <label for="editStatus" class="form-label">Status</label>
                            <select class="form-control" id="editStatus" required>
                                <option value="available">Available</option>
                                <option value="booked">Booked</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            // Add Room
            $('#addRoomForm').submit(function (e) {
                e.preventDefault();
                $.post('add_room.php', {
                    hotelId: $('#addHotelId').val(),
                    roomType: $('#addRoomType').val(),
                    price: $('#addPrice').val(),
                    status: $('#addStatus').val()
                }, function (response) {
                    alert('Room added successfully');
                    location.reload();
                }).fail(function () {
                    alert('Failed to add room.');
                });
            });

            // Edit Room (fetch)
            $('.edit-room').click(function () {
                var roomID = $(this).data('id');
                $.get('get_room.php', { id: roomID }, function (data) {
                    if (data.success) {
                        $('#editRoomID').val(data.data.RoomID);
                        $('#editRoomType').val(data.data.RoomType);
                        $('#editPrice').val(data.data.Price);
                        $('#editStatus').val(data.data.Status);
                        $('#editRoomModal').modal('show');
                    } else {
                        alert('Failed to fetch room details: ' + data.message);
                    }
                }).fail(function () {
                    alert('Failed: Unable to fetch room details.');
                });
            });

            // Update Room
            $('#editRoomForm').submit(function (e) {
                e.preventDefault();
                $.post('update_room.php', {
                    id: $('#editRoomID').val(),
                    roomType: $('#editRoomType').val(),
                    price: $('#editPrice').val(),
                    status: $('#editStatus').val()
                }, function (response) {
                    if (response.success) {
                        alert('Room updated successfully');
                        location.reload();
                    } else {
                        alert('Failed to update room: ' + response.message);
                    }
                }).fail(function () {
                    alert('Failed to update room.');
                });
            });

            // Delete Room
            $('.delete-room').click(function () {
                var roomID = $(this).data('id');
                if (confirm('Are you sure you want to delete this room?')) {
                    $.post('delete_room.php', { id: roomID }, function (data) {
                        if (data.success) {
                            alert('Room deleted successfully');
                            location.reload();
                        } else {
                            alert('Failed to delete room: ' + data.message);
                        }
                    }).fail(function () {
                        alert('Failed: Unable to delete room. Please try again.');
                    });
                }
            });
        });
    </script>
</body>
</html>
