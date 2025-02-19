<?php
require_once 'controller/roomController.php'; // Include the class

$roomController = new RoomController(); // Create an instance
$allRooms = $roomController->getAllRooms(); // Fetch all rooms
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-3">Manage Rooms</h1>

        <!-- Add Room Button -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            Add Room
        </button>

        <!-- Add Room Modal -->
        <div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRoomModalLabel">Add New Room</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addRoomForm">
                            <!-- Hotel ID -->
                            <div class="mb-3">
                                <label for="hotelId" class="form-label">Hotel ID</label>
                                <input type="number" class="form-control" id="hotelId" name="hotelId" required>
                            </div>
                            <!-- Room Type -->
                            <div class="mb-3">
                                <label for="roomType" class="form-label">Room Type</label>
                                <input type="text" class="form-control" id="roomType" name="roomType" required>
                            </div>
                            <!-- Price -->
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                            </div>
                            <!-- Status -->
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="available">Available</option>
                                    <option value="booked">Booked</option>
                                </select>
                            </div>
                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-success">Add Room</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room List Table -->
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
                <?php foreach ($allRooms as $row) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['RoomID']) ?></td>
                        <td><?= htmlspecialchars($row['HotelID']) ?></td>
                        <td><?= htmlspecialchars($row['RoomType']) ?></td>
                        <td><?= htmlspecialchars($row['Price']) ?></td>
                        <td>
                            <span class="badge bg-<?= $row['Status'] == 'Available' ? 'success' : 'danger' ?>">
                                <?= htmlspecialchars($row['Status']) ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
