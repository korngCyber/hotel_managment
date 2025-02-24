<?php
require_once 'core_config/db.php';
$conn = db_connect();

if (!$conn) {
    die('Database connection failed: ' . $conn->connect_error);
}

$query = "SELECT htId, htName, htAddr, htCon FROM tbHotels";
$result = $conn->query($query);
if (!$result) {
    die('Database query failed: ' . $conn->error);
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

    .btn-outline-danger {
        color: var(--accent-color);
        border-color: var(--accent-color);
    }

    .modal-content {
        border-radius: var(--border-radius);
    }

    .modal-header {
        background: linear-gradient(135deg, #6e8efb, #a777e3);
        color: #ffffff;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        padding: 20px;
    }

    .modal-title {
        font-weight: 600;
        letter-spacing: 1px;
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
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center">Manage Hotels</h1>
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addHotelModal">
            <i class="bi bi-plus-circle me-2"></i>Add New Hotel
        </button>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
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
                        <td><?= htmlspecialchars($row['htId']) ?></td>
                        <td><?= htmlspecialchars($row['htName']) ?></td>
                        <td><?= htmlspecialchars($row['htAddr']) ?></td>
                        <td><?= htmlspecialchars($row['htCon']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-hotel me-2"
                                data-id="<?= $row['htId'] ?>">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-hotel" data-id="<?= $row['htId'] ?>">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Hotel Modal -->
    <div class="modal fade" id="addHotelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Hotel</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addHotelForm">
                        <div class="mb-3">
                            <label for="addName" class="form-label">Hotel Name</label>
                            <input type="text" class="form-control" id="addName" name="htName" required>
                        </div>
                        <div class="mb-3">
                            <label for="addAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="addAddress" name="htAddr" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="addContact" class="form-label">Contact</label>
                            <input type="text" class="form-control" id="addContact" name="htCon">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add Hotel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Hotel Modal -->
    <div class="modal fade" id="editHotelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Hotel</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editHotelForm">
                        <input type="hidden" id="editHotelId" name="htId">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Hotel Name</label>
                            <input type="text" class="form-control" id="editName" name="htName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="editAddress" name="htAddr" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editContact" class="form-label">Contact</label>
                            <input type="text" class="form-control" id="editContact" name="htCon">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Add Hotel
        $('#addHotelForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: 'add_hotel.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        var res = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (res.success) {
                            alert('Hotel added successfully');
                            location.reload();
                        } else {
                            alert('Failed to add hotel: ' + res.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e, response);
                        alert('Unexpected error. Check server response.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Failed to add hotel. Check the server logs.');
                }
            });
        });

        // Edit Hotel
        $('.edit-hotel').click(function() {
            var hotelId = $(this).data('id');
            $.get('get_hotel.php', {
                id: hotelId
            }, function(data) {
                if (data.success !== false) {
                    $('#editHotelId').val(data.htId);
                    $('#editName').val(data.htName);
                    $('#editAddress').val(data.htAddr);
                    $('#editContact').val(data.htCon);
                    $('#editHotelModal').modal('show');
                } else {
                    alert(data.message);
                }
            }).fail(function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('Failed to fetch hotel data. Please try again.');
            });
        });

        // Update Hotel
        $('#editHotelForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: 'update_hotel.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        var res = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (res.success) {
                            alert('Hotel updated successfully');
                            location.reload();
                        } else {
                            alert('Failed to update hotel: ' + res.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('Unexpected error. Please check the server response.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Failed to update hotel. Please try again.');
                }
            });
        });

        // Delete Hotel
        $('.delete-hotel').click(function() {
            var hotelId = $(this).data('id');
            if (confirm('Are you sure you want to delete this hotel? This action cannot be undone.')) {
                $.post('delete_hotel.php', {
                    id: hotelId
                }, function(data) {
                    if (data.success) {
                        alert('Hotel deleted successfully');
                        location.reload();
                    } else {
                        alert('Failed to delete hotel: ' + data.message);
                    }
                }).fail(function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Failed to delete hotel. Please try again.');
                });
            }
        });
    });
    </script>
</body>

</html>