<?php
require_once 'core_config/db.php';
$conn = db_connect();

if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

$query = "SELECT sId, sName, sPos, sCon, sAddr, sImage, sWork FROM tbStaffs";
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
    <title>Manage Staff</title>
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
        <h1 class="text-center">Manage Staff</h1>
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addStaffModal">
            <i class="bi bi-plus-circle me-2"></i>Add New Staff
        </button>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Image</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['sId']) ?></td>
                        <td><?= htmlspecialchars($row['sName']) ?></td>
                        <td><?= htmlspecialchars($row['sPos']) ?></td>
                        <td><?= htmlspecialchars($row['sCon']) ?></td>
                        <td><?= htmlspecialchars($row['sAddr']) ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($row['sImage'] ?: 'assets/img/default-user.png') ?>"
                                alt="Staff Image" width="50" height="50">
                        </td>
                        <td>
                            <span class="badge <?= $row['sWork'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $row['sWork'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-staff me-2" data-id="<?= $row['sId'] ?>">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-staff" data-id="<?= $row['sId'] ?>">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Staff Modal -->
    <div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-person-plus-fill me-2"></i>Add New Staff
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addStaffForm" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="addName" class="form-label">
                                <i class="bi bi-person me-2"></i>Name
                            </label>
                            <input type="text" class="form-control" id="addName" name="sName" required>
                        </div>
                        <div class="mb-4">
                            <label for="addPosition" class="form-label">
                                <i class="bi bi-briefcase me-2"></i>Position
                            </label>
                            <select class="form-control" id="addPosition" name="sPos" required>
                                <option value="Admin">Admin</option>
                                <option value="Manager">Manager</option>
                                <option value="Receptionist">Receptionist</option>
                                <option value="Fulltime Staff">Fulltime Staff</option>
                                <option value="Parttime Staff">Parttime Staff</option>
                                <option value="Cleaner">Cleaner</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="addContact" class="form-label">
                                <i class="bi bi-telephone me-2"></i>Contact
                            </label>
                            <input type="text" class="form-control" id="addContact" name="sCon">
                        </div>
                        <div class="mb-4">
                            <label for="addAddress" class="form-label">
                                <i class="bi bi-geo-alt me-2"></i>Address
                            </label>
                            <textarea class="form-control" id="addAddress" name="sAddr" rows="3"></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="addImage" class="form-label">
                                <i class="bi bi-image me-2"></i>Profile Image
                            </label>
                            <input type="file" class="form-control" id="addImage" name="sImage" accept="image/*"
                                onchange="previewImage(event, 'addPreview')">
                            <div id="addImagePreview" class="mt-3 text-center" style="display: none;">
                                <img id="addPreview" src="#" alt="Preview" class="img-fluid">
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="addActive" name="sWork" checked>
                                <label class="form-check-label" for="addActive">Active Staff</label>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Add Staff
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Staff Modal -->
    <div class="modal fade" id="editStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-person-gear me-2"></i>Edit Staff
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editStaffForm" enctype="multipart/form-data">
                        <input type="hidden" id="editStaffId" name="sId">
                        <div class="mb-4">
                            <label for="editName" class="form-label">
                                <i class="bi bi-person me-2"></i>Name
                            </label>
                            <input type="text" class="form-control" id="editName" name="sName" required>
                        </div>
                        <div class="mb-4">
                            <label for="editPosition" class="form-label">
                                <i class="bi bi-briefcase me-2"></i>Position
                            </label>
                            <select class="form-control" id="editPosition" name="sPos" required>
                                <option value="Admin">Admin</option>
                                <option value="Manager">Manager</option>
                                <option value="Receptionist">Receptionist</option>
                                <option value="Fulltime Staff">Fulltime Staff</option>
                                <option value="Parttime Staff">Parttime Staff</option>
                                <option value="Cleaner">Cleaner</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="editContact" class="form-label">
                                <i class="bi bi-telephone me-2"></i>Contact
                            </label>
                            <input type="text" class="form-control" id="editContact" name="sCon">
                        </div>
                        <div class="mb-4">
                            <label for="editAddress" class="form-label">
                                <i class="bi bi-geo-alt me-2"></i>Address
                            </label>
                            <textarea class="form-control" id="editAddress" name="sAddr" rows="3"></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="editImage" class="form-label">
                                <i class="bi bi-image me-2"></i>Profile Image
                            </label>
                            <input type="file" class="form-control" id="editImage" name="sImage" accept="image/*"
                                onchange="previewImage(event, 'editPreview')">
                            <div id="editImagePreview" class="mt-3 text-center" style="display: none;">
                                <img id="editPreview" src="#" alt="Preview" class="img-fluid">
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="editActive" name="sWork">
                                <label class="form-check-label" for="editActive">Active Staff</label>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Update Staff
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

        if (previewId === 'addPreview') {
            document.getElementById('addImagePreview').style.display = 'block';
        } else if (previewId === 'editPreview') {
            document.getElementById('editImagePreview').style.display = 'block';
        }
    }

    $(document).ready(function() {
        // Add Staff
        $('#addStaffForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('sWork', $('#addActive').is(':checked') ? '1' : '0');

            $.ajax({
                url: 'add_staff.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        var res = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (res.success) {
                            alert('Staff added successfully');
                            location.reload();
                        } else {
                            alert('Failed to add staff: ' + res.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e, response);
                        alert('Unexpected error. Check server response.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Failed to add staff. Check the server logs.');
                }
            });
        });

        // Edit Staff
        $('.edit-staff').click(function() {
            var staffId = $(this).data('id');
            $.get('get_staff.php', {
                id: staffId
            }, function(data) {
                if (data.success !== false) {
                    $('#editStaffId').val(data.sId);
                    $('#editName').val(data.sName);
                    $('#editPosition').val(data.sPos);
                    $('#editContact').val(data.sCon);
                    $('#editAddress').val(data.sAddr);
                    $('#editActive').prop('checked', data.sWork == 1);

                    if (data.sImage) {
                        $('#editPreview').attr('src', data.sImage);
                        $('#editImagePreview').show();
                    }

                    $('#editStaffModal').modal('show');
                } else {
                    alert(data.message);
                }
            }).fail(function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('Failed to fetch staff data. Please try again.');
            });
        });

        // Update Staff
        $('#editStaffForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('sWork', $('#editActive').is(':checked') ? '1' : '0');

            $.ajax({
                url: 'update_staff.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        var res = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (res.success) {
                            alert('Staff updated successfully');
                            location.reload();
                        } else {
                            alert('Failed to update staff: ' + res.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('Unexpected error. Please check the server response.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Failed to update staff. Please try again.');
                }
            });
        });

        // Delete Staff
        $('.delete-staff').click(function() {
            var staffId = $(this).data('id');
            if (confirm(
                    'Are you sure you want to delete this staff member? This action cannot be undone.'
                )) {
                $.post('delete_staff.php', {
                    id: staffId
                }, function(data) {
                    if (data.success) {
                        alert('Staff deleted successfully');
                        location.reload();
                    } else {
                        alert('Failed to delete staff: ' + data.message);
                    }
                }).fail(function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Failed to delete staff. Please try again.');
                });
            }
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
</script>