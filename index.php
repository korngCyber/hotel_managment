<?php
// session_start();
// // Check if user is logged in
// if (!isset($_SESSION['username'])) {
//     header("Location: login.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background: #007BFF;
            color: white;
            height: 100vh;
            padding-top: 20px;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            transition: background 0.3s;
            cursor: pointer;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #0056b3;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            background: #ffffff;
        }

        .loader {
            display: none;
            text-align: center;
            font-size: 18px;
            color: #007BFF;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2 class="text-center">Hotel Management</h2>
        <a href="booking.php" class="tab-link" data-target="booking">Booking</a>
        <a href="room.php" class="tab-link" data-target="rooms">Rooms</a>
        <a href="hotel.php" class="tab-link" data-target="hotels">Hotels</a>
        <a href="staff.php" class="tab-link" data-target="staffs">Staffs</a>
        <a href="guest.php" class="tab-link" data-target="guests">Guests</a>
    </div>

    <!-- Content Area -->
    <div class="content">
        <div class="loader">Loading...</div>
        <div id="booking" class="tab-content"></div>
        <div id="rooms" class="tab-content" style="display: none;"></div>
        <div id="hotels" class="tab-content" style="display: none;"></div>
        <div id="staffs" class="tab-content" style="display: none;"></div>
        <div id="guests" class="tab-content" style="display: none;"></div>
    </div>

    <script>
        $(document).ready(function () {
            // Load the active tab from localStorage or default to the first tab
            let activeTab = localStorage.getItem('activeTab') || $(".tab-link").first().attr("href");
            $(".tab-link").removeClass("active");
            $(`.tab-link[href='${activeTab}']`).addClass("active");
            loadContent(activeTab, "#" + $(`.tab-link[href='${activeTab}']`).data("target"));

            $(".tab-link").click(function (e) {
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
                    success: function (response) {
                        $(".loader").hide();
                        console.log("Response received:", response); // Debugging
                        $(targetTab).html(response).show();
                    },
                    error: function (xhr, status, error) {
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