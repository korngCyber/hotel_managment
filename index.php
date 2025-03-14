<?php
session_start();

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
        display: flex;
        margin: 0;
        padding: 0;
    }

    .sidebar {
        width: 280px;
        background: linear-gradient(135deg, #1e3c72, #2a5298, #3498db);
        color: white;
        height: 100vh;
        padding-top: 20px;
        box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
    }

    .sidebar h2 {
        padding: 20px;
        font-size: 1.6rem;
        font-weight: 600;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        margin-bottom: 30px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    .sidebar a {
        display: block;
        color: #ffffff;
        padding: 15px 25px;
        text-decoration: none;
        transition: all 0.3s;
        border-left: 4px solid transparent;
        font-weight: 500;
    }

    .sidebar a:hover,
    .sidebar a.active {
        background: rgba(255, 255, 255, 0.15);
        border-left: 4px solid #3498db;
    }

    .sidebar a i {
        margin-right: 15px;
        width: 20px;
    }

    .content {
        flex-grow: 1;
        padding: 40px;
        background: #ffffff;
        box-shadow: -2px 0 15px rgba(0, 0, 0, 0.05);
    }

    .loader {
        display: none;
        text-align: center;
        font-size: 24px;
        color: #1e3c72;
        margin-top: 50px;
    }

    .loader i {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .tab-content {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .content h1 {
        font-size: 2.2rem;
        font-weight: 600;
        color: #1e3c72;
        margin-bottom: 30px;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }

    .logout-btn {
        position: absolute;
        bottom: 20px;
        left: 20px;
        width: calc(280px - 40px);
        padding: 12px;
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .logout-btn:hover {
        background-color: #c82333;
        color: white;
    }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2 class="text-center"><i class="fas fa-hotel"></i> Hotel Management</h2>
        <a href="booking.php" class="tab-link" data-target="booking"><i class="fas fa-calendar-check"></i> Booking</a>
        <a href="booking.php" class="tab-link" data-target="booking"><i class="fas fa-calendar-check"></i> Booking</a>
        <a href="room.php" class="tab-link" data-target="rooms"><i class="fas fa-bed"></i> Rooms</a>
        <a href="hotel.php" class="tab-link" data-target="hotels"><i class="fas fa-building"></i> Hotels</a>
        <a href="staff.php" class="tab-link" data-target="staffs"><i class="fas fa-users"></i> Staff</a>
        <a href="guest.php" class="tab-link" data-target="guests"><i class="fas fa-user-friends"></i> Guests</a>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>


    <!-- Content Area -->
    <div class="content">
        <div class="loader">Loading...</div>
        <div id="booking" class="tab-content"></div>
        <div id="booking" class="tab-content"></div>
        <div id="rooms" class="tab-content" style="display: none;"></div>
        <div id="hotels" class="tab-content" style="display: none;"></div>
        <div id="staffs" class="tab-content" style="display: none;"></div>
        <div id="guests" class="tab-content" style="display: none;"></div>
    </div>

    <script>
    $(document).ready(function() {
        // Load the active tab from localStorage or default to the first tab
        let activeTab = localStorage.getItem('activeTab') || $(".tab-link").first().attr("href");
        $(".tab-link").removeClass("active");
        $(`.tab-link[href='${activeTab}']`).addClass("active");
        loadContent(activeTab, "#" + $(`.tab-link[href='${activeTab}']`).data("target"));

        $(".tab-link").click(function(e) {
            e.preventDefault(); // Prevent default link behavior

            var target = $(this).data("target");
            var url = $(this).attr("href");

            // Remove active class from all and add to clicked tab
            $(".tab-link").removeClass("active");
            $(this).addClass("active");

            // Save the active tab to localStorage
            localStorage.setItem('activeTab', url);

            // Load content dynamically
            loadContent(url, "#" + target);
        });

        function loadContent(url, targetTab) {
            $(".loader").show();
            $(".tab-content").hide();
            console.log("Loading content from:", url); // Debugging

            $.ajax({
                url: url,
                type: "GET",
                dataType: "html",
                success: function(response) {
                    $(".loader").hide();
                    console.log("Response received:", response); // Debugging
                    $(targetTab).html(response).show();
                },
                error: function(xhr, status, error) {
                    $(".loader").hide();
                    console.error("AJAX Error:", status, error);
                    console.error("Response Text:", xhr.responseText);
                    $(targetTab)
                        .html(
                            "<p style='color:red;'>Error loading content. Check Console (F12).</p>"
                        )
                        .show();
                },
            });
        }
    });
    </script>
</body>

</html>