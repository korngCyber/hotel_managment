<?php
require_once 'core_config/db.php';

$conn = db_connect();

// Fetch all hotels from the database
$query = "SELECT * FROM Hotels";
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
    <title>Manage Hotels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-3">Manage Hotels</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addHotelModal">Add New Hotel</button>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['HotelID']) ?></td>
                        <td><?= htmlspecialchars($row['Name']) ?></td>
                        <td><?= htmlspecialchars($row['Address']) ?></td>
                        <td><?= htmlspecialchars($row['Contact']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-hotel" data-id="<?= $row['HotelID'] ?>">Edit</button>
                            <button class="btn btn-sm btn-outline-danger delete-hotel" data-id="<?= $row['HotelID'] ?>">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for Adding Hotel -->
    <div class="modal fade" id="addHotelModal" tabindex="-1" aria-labelledby="addHotelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addHotelForm">
                        <div class="mb-3">
                            <label for="addHotelName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="addHotelName" required>
                        </div>
                        <div class="mb-3">
                            <label for="addHotelAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="addHotelAddress" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="addHotelContact" class="form-label">Contact</label>
                            <input type="text" class="form-control" id="addHotelContact" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Hotel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Editing Hotel -->
    <div class="modal fade" id="editHotelModal" tabindex="-1" aria-labelledby="editHotelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editHotelForm">
                        <input type="hidden" id="editHotelID">
                        <div class="mb-3">
                            <label for="editHotelName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="editHotelName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editHotelAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="editHotelAddress" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editHotelContact" class="form-label">Contact</label>
                            <input type="text" class="form-control" id="editHotelContact" required>
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
        // Restore scroll position
        if (localStorage.getItem('scrollPosition')) {
            window.scrollTo(0, localStorage.getItem('scrollPosition'));
        }

        // Add Hotel
        $('#addHotelForm').submit(function (e) {
            e.preventDefault();
            var name = $('#addHotelName').val();
            var address = $('#addHotelAddress').val();
            var contact = $('#addHotelContact').val();
            
            $.ajax({
                url: 'add_hotel.php',
                type: 'POST',
                data: { name: name, address: address, contact: contact },
                success: function (response) {
                    if (response.success) {
                        alert('Success: Hotel added successfully');
                        localStorage.setItem('scrollPosition', window.scrollY); // Save scroll position
                        location.reload();
                    } else {
                        alert('Failed: ' + (response.message || 'An unknown error occurred.'));
                    }
                },
                error: function () {
                    alert('Failed: Unable to add hotel. Please try again.');
                }
            });
        });

        // Edit Hotel
        $('.edit-hotel').click(function () {
            var hotelID = $(this).data('id');
            $.ajax({
                url: 'get_hotel.php',
                type: 'GET',
                data: { id: hotelID },
                dataType: 'json',
                success: function (data) {
                    $('#editHotelID').val(data.HotelID);
                    $('#editHotelName').val(data.Name);
                    $('#editHotelAddress').val(data.Address);
                    $('#editHotelContact').val(data.Contact);
                    $('#editHotelModal').modal('show');
                },
                error: function () {
                    alert('Failed: Unable to fetch hotel details.');
                }
            });
        });

        $('#editHotelForm').submit(function (e) {
            e.preventDefault();
            var hotelID = $('#editHotelID').val();
            var name = $('#editHotelName').val();
            var address = $('#editHotelAddress').val();
            var contact = $('#editHotelContact').val();
            
            $.ajax({
                url: 'update_hotel.php',
                type: 'POST',
                data: { id: hotelID, name: name, address: address, contact: contact },
                success: function (response) {
                    if (response.success) {
                        alert('Success: Hotel updated successfully');
                        localStorage.setItem('scrollPosition', window.scrollY); // Save scroll position
                        location.reload();
                    } else {
                        alert('Failed: ' + (response.message || 'An unknown error occurred.'));
                    }
                },
                error: function () {
                    alert('Failed: Unable to update hotel. Please try again.');
                }
            });
        });

        // Delete Hotel
        $('.delete-hotel').click(function () {
            var hotelID = $(this).data('id');
            if (confirm('Are you sure you want to delete this hotel?')) {
                $.ajax({
                    url: 'delete_hotel.php',
                    type: 'POST',
                    data: { id: hotelID },
                    success: function (response) {
                        if (response.success) {
                            alert('Success: Hotel deleted successfully');
                            localStorage.setItem('scrollPosition', window.scrollY); // Save scroll position
                            location.reload();
                        } else {
                            alert('Failed: ' + (response.message || 'An unknown error occurred.'));
                        }
                    },
                    error: function () {
                        alert('Failed: Unable to delete hotel. Please try again.');
                    }
                });
            }
        });

        // Save scroll position before unload
        $(window).on('beforeunload', function () {
            localStorage.setItem('scrollPosition', window.scrollY);
        });
    });
    </script>
</body>
</html>