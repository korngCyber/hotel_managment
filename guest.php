<?php
require_once 'core_config/db.php';
$conn = db_connect();

if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

$query = "SELECT gId, gName, gMail, gPhone, gDob, gImage FROM tbGuests";
$result = $conn->query($query);
if (!$result) {
    die(json_encode(['success' => false, 'message' => 'Database query failed: ' . $conn->error]));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Guests</title>
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
        background-color: var(--primary-color);
        color: #ffffff;
        border-top-left-radius: var(--border-radius);
        border-top-right-radius: var(--border-radius);
    }

    .modal-title {
        font-weight: 600;
    }

    .form-control {
        border-radius: var(--border-radius);
    }

    .form-label {
        font-weight: 500;
    }

    img {
        border-radius: 50%;
        object-fit: cover;
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

    #imagePreview {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        transition: all 0.3s ease;
    }

    #preview {
        max-height: 200px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    #addPreview,
    #editPreview {
        width: 100px;
        height: 100px;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center">Manage Guests</h1>
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addGuestModal">
            <i class="bi bi-plus-circle me-2"></i>Add New Guest
        </button>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Date of Birth</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['gId']) ?></td>
                        <td><?= htmlspecialchars($row['gName']) ?></td>
                        <td><?= htmlspecialchars($row['gMail']) ?></td>
                        <td><?= htmlspecialchars($row['gPhone']) ?></td>
                        <td><?= htmlspecialchars($row['gDob']) ?></td>
                        <td><img src="<?= htmlspecialchars($row['gImage']) ?>" alt="Guest Image" width="50" height="50">
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-guest me-2" data-id="<?= $row['gId'] ?>">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-guest" data-id="<?= $row['gId'] ?>">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Guest Modal -->
    <div class="modal fade" id="addGuestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-person-plus-fill me-2"></i>Add New Guest
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addGuestForm" enctype="multipart/form-data" method="post" action="add_guest.php">
                        <div class="mb-4">
                            <label for="addName" class="form-label">
                                <i class="bi bi-person me-2"></i>Name
                            </label>
                            <input type="text" class="form-control" id="addName" name="gName" required
                                placeholder="Enter guest's full name">
                        </div>
                        <div class="mb-4">
                            <label for="addEmail" class="form-label">
                                <i class="bi bi-envelope me-2"></i>Email
                            </label>
                            <input type="email" class="form-control" id="addEmail" name="gMail" required
                                placeholder="Enter guest's email address">
                        </div>
                        <div class="mb-4">
                            <label for="addPhone" class="form-label">
                                <i class="bi bi-telephone me-2"></i>Phone
                            </label>
                            <input type="tel" class="form-control" id="addPhone" name="gPhone"
                                placeholder="Enter guest's phone number">
                        </div>
                        <div class="mb-4">
                            <label for="addDateOfBirth" class="form-label">
                                <i class="bi bi-calendar-event me-2"></i>Date of Birth
                            </label>
                            <input type="date" class="form-control" id="addDateOfBirth" name="gDob">
                        </div>
                        <div class="mb-4">
                            <label for="addImage" class="form-label">
                                <i class="bi bi-image me-2"></i>Profile Image
                            </label>
                            <input type="file" class="form-control" id="addImage" name="gImage" accept="image/*"
                                required onchange="previewImage(event, 'addPreview')">
                        </div>
                        <div class="mb-4">
                            <div id="addImagePreview" class="mt-3 text-center" style="display: none;">
                                <img id="addPreview" src="#" alt="Image Preview" class="img-fluid">
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Add Guest
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Guest Modal -->
    <div class="modal fade" id="editGuestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-person-gear me-2"></i>Edit Guest
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editGuestForm" enctype="multipart/form-data">
                        <input type="hidden" id="editGuestID" name="gId">
                        <div class="mb-4">
                            <label for="editName" class="form-label">
                                <i class="bi bi-person me-2"></i>Name
                            </label>
                            <input type="text" class="form-control" id="editName" name="gName" required
                                placeholder="Enter guest's full name">
                        </div>
                        <div class="mb-4">
                            <label for="editEmail" class="form-label">
                                <i class="bi bi-envelope me-2"></i>Email
                            </label>
                            <input type="email" class="form-control" id="editEmail" name="gMail" required
                                placeholder="Enter guest's email address">
                        </div>
                        <div class="mb-4">
                            <label for="editPhone" class="form-label">
                                <i class="bi bi-telephone me-2"></i>Phone
                            </label>
                            <input type="tel" class="form-control" id="editPhone" name="gPhone"
                                placeholder="Enter guest's phone number">
                        </div>
                        <div class="mb-4">
                            <label for="editDateOfBirth" class="form-label">
                                <i class="bi bi-calendar-event me-2"></i>Date of Birth
                            </label>
                            <input type="date" class="form-control" id="editDateOfBirth" name="gDob">
                        </div>
                        <div class="mb-4">
                            <label for="editImage" class="form-label">
                                <i class="bi bi-image me-2"></i>Profile Image
                            </label>
                            <input type="file" class="form-control" id="editImage" name="gImage" accept="image/*"
                                onchange="previewImage(event, 'editPreview')">
                        </div>
                        <div class="mb-4">
                            <div id="editImagePreview" class="mt-3 text-center" style="display: none;">
                                <img id="editPreview" src="#" alt="Image Preview" class="img-fluid">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Update Guest
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function previewImage(event, previewId) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById(previewId);
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);

        // Show the correct preview div based on which modal is being used
        if (previewId === 'addPreview') {
            document.getElementById('addImagePreview').style.display = 'block';
        } else if (previewId === 'editPreview') {
            document.getElementById('editImagePreview').style.display = 'block';
        }
    }

    $(document).ready(function() {
        // Add Guest
        $('#addGuestForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            // Check if all required fields are filled
            if (!$('#addName').val() || !$('#addEmail').val() || !$('#addImage').val()) {
                alert('Failed to add guest: Name and Email are required.');
                return;
            }

            $.ajax({
                url: 'add_guest.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        var res = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (res.success) {
                            alert('Guest added successfully');
                            location.reload();
                        } else {
                            alert('Failed to add guest: ' + res.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e, response);
                        alert('Unexpected error. Check server response.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error, xhr.responseText);
                    alert('Failed to add guest. Check the server logs.');
                }
            });

        });


        $('#editGuestForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            // The ID field name should match what update_guest.php expects
            formData.append('guestID', $('#editGuestID').val()); // Changed from 'gId' to 'guestID'

            // Check if required fields are filled
            if (!$('#editName').val() || !$('#editEmail').val()) {
                alert('Name and Email are required fields');
                return;
            }

            $.ajax({
                url: 'update_guest.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        var res = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (res.success) {
                            alert('Guest updated successfully');
                            location.reload();
                        } else {
                            alert('Failed to update guest: ' + res.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e, response);
                        alert('Unexpected error. Please check the server response.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    alert('Failed to update guest. Please try again.');
                }
            });
        });

        // Edit Guest
        $('.edit-guest').click(function() {
            var guestID = $(this).data('id');
            $.get('get_guest.php', {
                id: guestID
            }, function(data) {
                if (data.success !== false) {
                    $('#editName').val(data.gName);
                    $('#editEmail').val(data.gMail);
                    $('#editPhone').val(data.gPhone);
                    $('#editDateOfBirth').val(data.gDob);
                    $('#editGuestID').val(data.gId);

                    // Show current image in preview
                    if (data.gImage) {
                        $('#editPreview').attr('src', data.gImage);
                        $('#editImagePreview').show();
                    }

                    $('#editGuestModal').modal('show');
                } else {
                    alert(data.message);
                }
            }).fail(function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('Failed to fetch guest data. Please try again.');
            });
        });

        // Delete Guest
        $('.delete-guest').click(function() {
            var guestID = $(this).data('id');
            if (confirm(
                    'Are you sure you want to delete this guest? This action cannot be undone.'
                )) {
                $.post('delete_guest.php', {
                    id: guestID
                }, function(data) {
                    if (data.success) {
                        alert('Guest deleted successfully');
                        location.reload();
                    } else {
                        alert('Failed to delete guest: ' + data.message);
                    }
                }).fail(function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    alert('Failed to delete guest. Please try again.');
                });
            }
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>