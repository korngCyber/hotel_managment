<?php
require_once 'core_config/db.php';
$conn = db_connect();

if (!$conn) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Fetch all hotels for dropdown
$hotelQuery = "SELECT htId, htName FROM tbHotels";
$hotelResult = $conn->query($hotelQuery);
$hotels = [];
while ($hotel = $hotelResult->fetch_assoc()) {
    $hotels[] = $hotel;
}

// Fetch all rooms from the database
$query = "SELECT r.rId, r.htId, h.htName, r.rName, r.rType, r.rPrice, r.rStatus 
          FROM tbRooms r 
          LEFT JOIN tbHotels h ON r.htId = h.htId";
$result = $conn->query($query);

// Check for database errors
if (!$result) {
    die("Database query failed: " . $conn->error);
}

// Separate rooms into available and booked
$availableRooms = [];
$bookedRooms = [];

while ($row = $result->fetch_assoc()) {
    if ($row['rStatus'] == 'available') {
        $availableRooms[] = $row;
    } else {
        $bookedRooms[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms</title>
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

    .room-container {
        margin-bottom: 2rem;
        padding: 1.5rem;
        border-radius: var(--border-radius);
    }

    .available-container {
        /* background-color: rgba(40, 167, 69, 0.1); */
        border: 1px solidrgba(40, 167, 70, 0.1);
    }

    .booked-container {
        /* background-color: rgba(220, 53, 69, 0.1); */
        border: 1px ssolidrgba(40, 167, 70, 0.1);
    }

    h1 {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    h2 {
        margin-bottom: 1rem;
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

    .hotel-preview {
        margin-top: 8px;
        padding: 8px;
        background-color: #f8f9fa;
        border-radius: 4px;
        font-size: 0.9em;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>Manage Rooms</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addRoomModal">Add New Room</button>

        <div class="room-container available-container">
            <h2>Available Rooms</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Room ID</th>
                        <th>Hotel Name</th>
                        <th>Room Name</th>
                        <th>Room Type</th>
                        <th>Price ($)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($availableRooms as $row) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['rId']) ?></td>
                        <td><?= htmlspecialchars($row['htName']) ?></td>
                        <td><?= htmlspecialchars($row['rName']) ?></td>
                        <td><?= htmlspecialchars($row['rType']) ?></td>
                        <td><?= htmlspecialchars($row['rPrice']) ?></td>
                        <td><span class="badge bg-success"><?= htmlspecialchars(ucfirst($row['rStatus'])) ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-room"
                                data-id="<?= $row['rId'] ?>">Edit</button>
                            <button class="btn btn-sm btn-outline-danger delete-room"
                                data-id="<?= $row['rId'] ?>">Delete</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="room-container booked-container">
            <h2>Booked Rooms</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Room ID</th>
                        <th>Hotel Name</th>
                        <th>Room Name</th>
                        <th>Room Type</th>
                        <th>Price ($)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookedRooms as $row) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['rId']) ?></td>
                        <td><?= htmlspecialchars($row['htName']) ?></td>
                        <td><?= htmlspecialchars($row['rName']) ?></td>
                        <td><?= htmlspecialchars($row['rType']) ?></td>
                        <td><?= htmlspecialchars($row['rPrice']) ?></td>
                        <td><span class="badge bg-danger"><?= htmlspecialchars(ucfirst($row['rStatus'])) ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-room"
                                data-id="<?= $row['rId'] ?>">Edit</button>
                            <button class="btn btn-sm btn-outline-danger delete-room"
                                data-id="<?= $row['rId'] ?>">Delete</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Adding Room -->
    <div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Add New Room
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addRoomForm">
                        <div class="mb-4">
                            <label for="addHotelId" class="form-label">
                                <i class="bi bi-building me-2"></i>Select Hotel
                            </label>
                            <select class="form-control" id="addHotelId" name="htId" required>
                                <option value="">Select a hotel...</option>
                                <?php foreach ($hotels as $hotel) { ?>
                                <option value="<?= htmlspecialchars($hotel['htId']) ?>">
                                    <?= htmlspecialchars($hotel['htName']) ?>
                                </option>
                                <?php } ?>
                            </select>
                            <div id="addHotelPreview" class="hotel-preview"></div>
                        </div>
                        <div class="mb-4">
                            <label for="addRoomName" class="form-label">
                                <i class="bi bi-tag me-2"></i>Room Name
                            </label>
                            <input type="text" class="form-control" id="addRoomName" name="rName" required>
                        </div>
                        <div class="mb-4">
                            <label for="addRoomType" class="form-label">
                                <i class="bi bi-door-closed me-2"></i>Room Type
                            </label>
                            <select class="form-control" id="addRoomType" name="rType" required>
                                <option value="Single">Single</option>
                                <option value="Double">Double</option>
                                <option value="Suite">Suite</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="addPrice" class="form-label">
                                <i class="bi bi-currency-dollar me-2"></i>Price
                            </label>
                            <input type="number" step="0.01" class="form-control" id="addPrice" name="rPrice" required>
                        </div>
                        <div class="mb-4">
                            <label for="addStatus" class="form-label">
                                <i class="bi bi-check-circle me-2"></i>Status
                            </label>
                            <select class="form-control" id="addStatus" name="rStatus" required>
                                <option value="available">Available</option>
                                <option value="booked">Booked</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Add Room
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Editing Room -->
    <div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Room
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editRoomForm">
                        <input type="hidden" id="editRoomId" name="rId">
                        <div class="mb-4">
                            <label for="editHotelId" class="form-label">
                                <i class="bi bi-building me-2"></i>Select Hotel
                            </label>
                            <select class="form-control" id="editHotelId" name="htId" required>
                                <?php foreach ($hotels as $hotel) { ?>
                                <option value="<?= htmlspecialchars($hotel['htId']) ?>">
                                    <?= htmlspecialchars($hotel['htName']) ?>
                                </option>
                                <?php } ?>
                            </select>
                            <div id="editHotelPreview" class="hotel-preview"></div>
                        </div>
                        <div class="mb-4">
                            <label for="editRoomName" class="form-label">
                                <i class="bi bi-tag me-2"></i>Room Name
                            </label>
                            <input type="text" class="form-control" id="editRoomName" name="rName" required>
                        </div>
                        <div class="mb-4">
                            <label for="editRoomType" class="form-label">
                                <i class="bi bi-door-closed me-2"></i>Room Type
                            </label>
                            <select class="form-control" id="editRoomType" name="rType" required>
                                <option value="Single">Single</option>
                                <option value="Double">Double</option>
                                <option value="Suite">Suite</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="editPrice" class="form-label">
                                <i class="bi bi-currency-dollar me-2"></i>Price
                            </label>
                            <input type="number" step="0.01" class="form-control" id="editPrice" name="rPrice" required>
                        </div>
                        <div class="mb-4">
                            <label for="editStatus" class="form-label">
                                <i class="bi bi-check-circle me-2"></i>Status
                            </label>
                            <select class="form-control" id="editStatus" name="rStatus" required>
                                <option value="available">Available</option>
                                <option value="booked">Booked</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Save Changes
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
        // Hotel preview functionality
        function updateHotelPreview(hotelId, previewDivId) {
            if (hotelId) {
                $.get('get_hotel.php', {
                    id: hotelId
                }, function(data) {
                    if (data.success !== false) {
                        $(previewDivId).html(`
                            <strong>Selected Hotel:</strong><br>
                            ${data.htName}<br>
                            ${data.htLocation}
                        `);
                    }
                });
            } else {
                $(previewDivId).empty();
            }
        }

        $('#addHotelId').change(function() {
            updateHotelPreview($(this).val(), '#addHotelPreview');
        });

        $('#editHotelId').change(function() {
            updateHotelPreview($(this).val(), '#editHotelPreview');
        });

        // Add Room
        $('#addRoomForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: 'add_room.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        var res = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (res.success) {
                            alert('Room added successfully');
                            location.reload();
                        } else {
                            alert('Failed to add room: ' + res.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('Unexpected error. Check server response.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Failed to add room. Check the server logs.');
                }
            });
        });

        // Edit Room (fetch)
        $('.edit-room').click(function() {
            var roomId = $(this).data('id');
            $.get('get_room.php', {
                id: roomId
            }, function(data) {
                if (data.success !== false) {
                    $('#editRoomId').val(data.rId);
                    $('#editHotelId').val(data.htId);
                    $('#editRoomName').val(data.rName);
                    $('#editRoomType').val(data.rType);
                    $('#editPrice').val(data.rPrice);
                    $('#editStatus').val(data.rStatus);
                    updateHotelPreview(data.htId, '#editHotelPreview');
                    $('#editRoomModal').modal('show');
                } else {
                    alert(data.message);
                }
            }).fail(function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('Failed to fetch room data. Please try again.');
            });
        });

        // Update Room
        $('#editRoomForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: 'update_room.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert('Room updated successfully');
                        location.reload();
                    } else {
                        alert('Failed to update room: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Failed to update room. Check the server logs.');
                }
            });
        });

        // Delete Room
        $('.delete-room').click(function() {
            if (!confirm('Are you sure you want to delete this room?')) return;

            var roomId = $(this).data('id');

            $.ajax({
                url: 'delete_room.php',
                type: 'POST',
                data: {
                    id: roomId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Room deleted successfully');
                        location.reload();
                    } else {
                        alert('Failed to delete room: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Failed to delete room. Check the server logs.');
                }
            });
        });
    });
    </script>
</body>

</html>