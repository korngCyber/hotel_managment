<?php
require_once 'core_config/db.php';
$conn = db_connect();

// Fetch all staff from the database
$query = "SELECT * FROM Staff";
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
    <title>Manage Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-3">Manage Staff</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addStaffModal">Add New
            Staff</button>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Salary</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['StaffID']) ?></td>
                        <td><?= htmlspecialchars($row['Name']) ?></td>
                        <td><?= htmlspecialchars($row['Position']) ?></td>
                        <td><?= htmlspecialchars($row['Salary']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-staff"
                                data-id="<?= $row['StaffID'] ?>">Edit</button>
                            <button class="btn btn-sm btn-outline-danger delete-staff"
                                data-id="<?= $row['StaffID'] ?>">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for Adding Staff -->
    <div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addStaffForm">
                        <div class="mb-3">
                            <label for="addStaffName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="addStaffName" required>
                        </div>
                        <div class="mb-3">
                            <label for="addStaffPosition" class="form-label">Position</label>
                            <!-- Changed ID and label -->
                            <input type="text" class="form-control" id="addStaffPosition" required> <!-- Changed ID -->
                        </div>
                        <div class="mb-3">
                            <label for="addStaffSalary" class="form-label">Salary</label>
                            <input type="number" class="form-control" id="addStaffSalary" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Staff</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Editing Staff -->
    <div class="modal fade" id="editStaffModal" tabindex="-1" aria-labelledby="editStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editStaffForm">
                        <input type="hidden" id="editStaffID">
                        <div class="mb-3">
                            <label for="editStaffName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="editStaffName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editStaffPosition" class="form-label">Position</label>
                            <input type="text" class="form-control" id="editStaffPosition" required> 
                        </div>
                        <div class="mb-3">
                            <label for="editStaffSalary" class="form-label">Salary</label>
                            <input type="number" class="form-control" id="editStaffSalary" required>
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

            // Add Staff
            $('#addStaffForm').submit(function (e) {
                e.preventDefault();
                var name = $('#addStaffName').val();
                var position = $('#addStaffPosition').val(); // Changed from role to position
                var salary = $('#addStaffSalary').val();

                console.log('Adding staff:', { name, position, salary }); // Updated log

                $.ajax({
                    url: 'add_staff.php',
                    type: 'POST',
                    data: { name: name, position: position, salary: salary }, // Changed key from role to position
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            alert('Success: Staff added successfully');
                            location.reload();
                        } else {
                            alert('Failed: ' + (response.message || 'An unknown error occurred.'));
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log('AJAX Error:', xhr.responseText);
                        alert('Failed: Unable to add staff. Error: ' + error);
                    }
                });
            });

            // Edit Staff (fetch)
            $('.edit-staff').click(function () {
                var staffID = $(this).data('id');
                $.ajax({
                    url: 'get_staff.php',
                    type: 'GET',
                    data: { id: staffID },
                    dataType: 'json',
                    success: function (data) {
                        $('#editStaffID').val(data.StaffID);
                        $('#editStaffName').val(data.Name);
                        $('#editStaffPosition').val(data.Position); // Changed from Role to Position
                        $('#editStaffSalary').val(data.Salary);
                        $('#editStaffModal').modal('show');
                    },
                    error: function () {
                        alert('Failed: Unable to fetch staff details.');
                    }
                });
            });

            // Update Staff
            $('#editStaffForm').submit(function (e) {
                e.preventDefault();
                var staffID = $('#editStaffID').val();
                var name = $('#editStaffName').val();
                var position = $('#editStaffPosition').val(); // Changed from role to position
                var salary = $('#editStaffSalary').val();

                console.log('Updating staff:', { id: staffID, name, position, salary }); // Updated log

                $.ajax({
                    url: 'update_staff.php',
                    type: 'POST',
                    data: { id: staffID, name: name, position: position, salary: salary }, // Changed key from role to position
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            alert('Success: Staff updated successfully');
                            location.reload();
                        } else {
                            alert('Failed: ' + (response.message || 'An unknown error occurred.'));
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log('AJAX Error:', xhr.responseText);
                        alert('Failed: Unable to update staff. Error: ' + error);
                    }
                });
            });


            // Delete Staff
            $('.delete-staff').click(function () {
                var staffID = $(this).data('id');
                if (confirm('Are you sure you want to delete this staff?')) {
                    $.ajax({
                        url: 'delete_staff.php',
                        type: 'POST',
                        data: { id: staffID },
                        success: function (response) {
                            if (response.success) {
                                alert('Success: Staff deleted successfully');
                                localStorage.setItem('scrollPosition', window.scrollY); // Save scroll position
                                location.reload();
                            } else {
                                alert('Failed: ' + (response.message || 'An unknown error occurred.'));
                            }
                        },
                        error: function () {
                            alert('Failed: Unable to delete staff. Please try again.');
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