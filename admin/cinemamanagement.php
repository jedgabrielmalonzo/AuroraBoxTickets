<?php
session_start();

if (!isset($_SESSION["admin_first_name"])) {
    header("Location: index.php");  // Redirect to login page if not logged in
    exit;
}

// Access admin's name
$first_name = $_SESSION["admin_first_name"];
$last_name = $_SESSION["admin_last_name"];

$mysqli = require __DIR__ . "/../database.php";

// Handle form submission to update schedules
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log all POST data for debugging
    error_log("POST Data: " . print_r($_POST, true));

    $schedule_id = $_POST['schedule_id'];
    $cinema = $_POST['cinema']; 
    $movie_id = $_POST['movie_id'];
    $showtime = $_POST['showtime']; 
    $ticket_type = strtolower($_POST['ticket_type']); 

    error_log("Updating schedule ID: $schedule_id, Movie ID: $movie_id, Showtime: $showtime, Ticket Type: $ticket_type");

    // Set seat price based on ticket type
    $seat_price = ($ticket_type === 'vip') ? 500.00 : 350.00;

    // Update the schedule in the schedules table
    $sql = "UPDATE schedules SET movie_id = ?, showtime = ?, ticket_type = ? WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement for updating schedules: " . htmlspecialchars($mysqli->error));
    }

    $stmt->bind_param("sssi", $movie_id, $showtime, $ticket_type, $schedule_id);
    $stmt->execute();

    if ($stmt->error) {
        die("Execute error: " . htmlspecialchars($stmt->error));
    } else {
        error_log("Successfully updated schedule ID: $schedule_id");
    }

    preg_match('/(cinema\d+)_(date\d+)_?(time\d+)?/', $cinema, $matches);
    if (count($matches) < 3) {
        die("Invalid cinema format: " . htmlspecialchars($cinema));
    }

    $combined_column = $matches[2] . '_' . $matches[3];

    $new_date_time = date('Y-m-d H:i:s', strtotime($showtime));
    $sql_update_movies = "UPDATE movies SET $combined_column = ? WHERE id = ?";
    $stmt_update_movies = $mysqli->prepare($sql_update_movies);
    if ($stmt_update_movies === false) {
        die("Error preparing statement for updating movies: " . htmlspecialchars($mysqli->error));
    }

    $stmt_update_movies->bind_param("si", $new_date_time, $movie_id);
    $stmt_update_movies->execute();

    if ($stmt_update_movies->error) {
        die("Execute error: " . htmlspecialchars($stmt_update_movies->error));
    } else {
        error_log("Successfully updated movie ID: $movie_id with new date and time in column $combined_column.");
    }

    $sql_reset_status = "UPDATE `$cinema` SET status = 'available'";
    $stmt_reset_status = $mysqli->prepare($sql_reset_status);
    if ($stmt_reset_status === false) {
        die("Error preparing statement for resetting status: " . htmlspecialchars($mysqli->error));
    }

    $stmt_reset_status->execute();

    if ($stmt_reset_status->error) {
        die("Execute error: " . htmlspecialchars($stmt_reset_status->error));
    }

    $sql_seat_price_update = "UPDATE `$cinema` SET seat_price = ?";
    $stmt_seat_price = $mysqli->prepare($sql_seat_price_update);
    if ($stmt_seat_price === false) {
        die("Error preparing statement for updating seat price: " . htmlspecialchars($mysqli->error));
    }

    $stmt_seat_price->bind_param("d", $seat_price);
    $stmt_seat_price->execute();

    if ($stmt_seat_price->error) {
        die("Execute error: " . htmlspecialchars($stmt_seat_price->error));
    }

    header("Location: cinemamanagement.php");
    exit();
}

$sql_schedules = "SELECT * FROM schedules";
$result_schedules = $mysqli->query($sql_schedules);
if ($result_schedules === false) {
    die("Query error: " . htmlspecialchars($mysqli->error));
}

$sql_movies = "SELECT id, title FROM movies";
$result_movies = $mysqli->query($sql_movies);
if ($result_movies === false) {
    die("Query error: " . htmlspecialchars($mysqli->error));
}

$movies = [];
while ($row = $result_movies->fetch_assoc()) {
    $movies[$row['id']] = $row['title'];
}

$cinema_schedules = [];
while ($schedule = $result_schedules->fetch_assoc()) {
    preg_match('/(cinema\d+)_(date\d+)_?(time\d+)?/', $schedule['cinema'], $matches);
    
    if (count($matches) >= 4) {
        $cinema_key = $matches[1];
        $date_key = $matches[2];
        $time_key = $matches[3];
        
        $cinema_schedules[$cinema_key][$date_key][$time_key][] = $schedule;
    } else {
        error_log("Unexpected cinema format: " . htmlspecialchars($schedule['cinema']));
    }
}

foreach ($cinema_schedules as $cinema => &$dates) {
    ksort($dates);
    foreach ($dates as $date => &$times) {
        ksort($times);
    }
}
unset($dates);

// Get the current cinema to display
$current_cinema = isset($_GET['cinema']) ? htmlspecialchars($_GET['cinema']) : 'cinema1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="CSS/sidebar.css" rel="stylesheet">
    <link href="CSS/cinemamanage.css" rel="stylesheet">
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
        <img src="/aurorabox/images/logoadminwhite.png" class="logoadmin" style="max-width: 100%;">
        
        <a href="dashboard.php" class="text-white">Dashboard</a>
        
        <p class="sidebar-p">INTERFACE</p>
        <a href="moviemanagement.php" class="text-white">Movie Management</a>
        <a href="cinemamanagement.php" class="text-white">Cinema Management</a>
        <p class="sidebar-p">REPORTS</p>
        <a href="transactions.php" class="text-white">Transaction History</a>
        <a href="viewusers.php" class="text-white">View All Users</a>

        <a href="logoutadmin.php" class="sidebar-logout text-white">Log Out</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="admin-name">
            <a>Hello, <?php echo htmlspecialchars($first_name . " " . $last_name); ?>!</a>
        </div>
        <div class="cinema-manage-container">
            <div class="container my-5">
                <h2>Edit Cinema Schedules</h2>

                <!-- Cinema Buttons -->
                <div class="mb-3">
                    <button class="btn btn-info cinema-button" onclick="toggleCinema('cinema1')">Cinema 1</button>
                    <button class="btn btn-info cinema-button" onclick="toggleCinema('cinema2')">Cinema 2</button>
                    <button class="btn btn-info cinema-button" onclick="toggleCinema('cinema3')">Cinema 3</button>
                    <button class="btn btn-info cinema-button" onclick="toggleCinema('cinema4')">Cinema 4</button>
                </div>

                <div id="cinema1" class="cinema-schedule" style="<?php echo ($current_cinema === 'cinema1') ? 'display: block;' : 'display: none;'; ?>">
                    <?php foreach ($cinema_schedules['cinema1'] as $date => $times): ?>
                        <div class="mb-4 border rounded p-3">
                            <h4><?php echo htmlspecialchars('Cinema 1 ' . $date); ?></h4>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Available Seats</th>
                                        <th>Movie</th>
                                        <th>Showtime</th>
                                        <th>Ticket Type</th>
                                        <th>Seat Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($times as $time => $schedules): ?>
                                        <?php foreach ($schedules as $schedule): ?>
                                            <tr>
                                                <form action="cinemamanagement.php" method="POST" onsubmit="return confirmUpdate();">
                                                    <td>
                                                        <span><?php echo htmlspecialchars($time); ?></span>
                                                        <input type="hidden" name="cinema" value="<?php echo htmlspecialchars($schedule['cinema']); ?>">
                                                    </td>
                                                    <td>
                                                        <span><?php echo htmlspecialchars($schedule['available_seats']); ?></span>
                                                    </td>
                                                    <td>
                                                        <select name="movie_id" required>
                                                            <option value="<?php echo htmlspecialchars($schedule['movie_id']); ?>">
                                                                <?php echo htmlspecialchars($movies[$schedule['movie_id']]); ?>
                                                            </option>
                                                            <?php foreach ($movies as $id => $title): ?>
                                                                <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($title); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="datetime-local" name="showtime" 
                                                            value="<?php echo date('Y-m-d\TH:i', strtotime($schedule['showtime'])); ?>" 
                                                            required>
                                                    </td>
                                                    <td>
                                                        <select name="ticket_type" onchange="updateSeatPrice(this)" required>
                                                            <option value="vip" <?php echo strtolower($schedule['ticket_type']) === 'vip' ? 'selected' : ''; ?>>vip</option>
                                                            <option value="regular" <?php echo strtolower($schedule['ticket_type']) === 'regular' ? 'selected' : ''; ?>>regular</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <span class="seat-price">
                                                            <?php
                                                            $price_table = htmlspecialchars($schedule['cinema']);
                                                            $sql_price_display = "SELECT seat_price FROM `$price_table` LIMIT 1";
                                                            $stmt_price_display = $mysqli->prepare($sql_price_display);
                                                            
                                                            if ($stmt_price_display === false) {
                                                                die("Error preparing statement for table '$price_table': " . htmlspecialchars($mysqli->error));
                                                            }

                                                            $stmt_price_display->execute();
                                                            $result_price_display = $stmt_price_display->get_result();
                                                            $price_row = $result_price_display->fetch_assoc();

                                                            if ($price_row) {
                                                                $display_price = number_format((float)$price_row['seat_price'], 2, '.', '');
                                                            } else {
                                                                die("No prices found for table '$price_table'.");
                                                            }
                                                            ?>
                                                            <span><?php echo htmlspecialchars($display_price); ?></span>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($schedule['id']); ?>">
                                                        <button type="submit" class="btn btn-primary">Update</button>
                                                    </td>
                                                </form>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="cinema2" class="cinema-schedule" style="<?php echo ($current_cinema === 'cinema2') ? 'display: block;' : 'display: none;'; ?>">
                    <?php foreach ($cinema_schedules['cinema2'] as $date => $times): ?>
                        <div class="mb-4 border rounded p-3">
                            <h4><?php echo htmlspecialchars('Cinema 2 ' . $date); ?></h4>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Available Seats</th>
                                        <th>Movie</th>
                                        <th>Showtime</th>
                                        <th>Ticket Type</th>
                                        <th>Seat Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($times as $time => $schedules): ?>
                                        <?php foreach ($schedules as $schedule): ?>
                                            <tr>
                                                <form action="cinemamanagement.php" method="POST" onsubmit="return confirmUpdate();">
                                                    <td>
                                                        <span><?php echo htmlspecialchars($time); ?></span>
                                                        <input type="hidden" name="cinema" value="<?php echo htmlspecialchars($schedule['cinema']); ?>">
                                                    </td>
                                                    <td>
                                                        <span><?php echo htmlspecialchars($schedule['available_seats']); ?></span>
                                                    </td>
                                                    <td>
                                                        <select name="movie_id" required>
                                                            <option value="<?php echo htmlspecialchars($schedule['movie_id']); ?>">
                                                                <?php echo htmlspecialchars($movies[$schedule['movie_id']]); ?>
                                                            </option>
                                                            <?php foreach ($movies as $id => $title): ?>
                                                                <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($title); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="datetime-local" name="showtime" 
                                                            value="<?php echo date('Y-m-d\TH:i', strtotime($schedule['showtime'])); ?>" 
                                                            required>
                                                    </td>
                                                    <td>
                                                        <select name="ticket_type" onchange="updateSeatPrice(this)" required>
                                                            <option value="vip" <?php echo strtolower($schedule['ticket_type']) === 'vip' ? 'selected' : ''; ?>>vip</option>
                                                            <option value="regular" <?php echo strtolower($schedule['ticket_type']) === 'regular' ? 'selected' : ''; ?>>regular</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <span class="seat-price">
                                                            <?php
                                                            $price_table = htmlspecialchars($schedule['cinema']);
                                                            $sql_price_display = "SELECT seat_price FROM `$price_table` LIMIT 1";
                                                            $stmt_price_display = $mysqli->prepare($sql_price_display);
                                                            
                                                            if ($stmt_price_display === false) {
                                                                die("Error preparing statement for table '$price_table': " . htmlspecialchars($mysqli->error));
                                                            }

                                                            $stmt_price_display->execute();
                                                            $result_price_display = $stmt_price_display->get_result();
                                                            $price_row = $result_price_display->fetch_assoc();

                                                            if ($price_row) {
                                                                $display_price = number_format((float)$price_row['seat_price'], 2, '.', '');
                                                            } else {
                                                                die("No prices found for table '$price_table'.");
                                                            }
                                                            ?>
                                                            <span><?php echo htmlspecialchars($display_price); ?></span>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($schedule['id']); ?>">
                                                        <button type="submit" class="btn btn-primary">Update</button>
                                                    </td>
                                                </form>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="cinema3" class="cinema-schedule" style="<?php echo ($current_cinema === 'cinema3') ? 'display: block;' : 'display: none;'; ?>">
                    <?php foreach ($cinema_schedules['cinema3'] as $date => $times): ?>
                        <div class="mb-4 border rounded p-3">
                            <h4><?php echo htmlspecialchars('Cinema 3 ' . $date); ?></h4>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Available Seats</th>
                                        <th>Movie</th>
                                        <th>Showtime</th>
                                        <th>Ticket Type</th>
                                        <th>Seat Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($times as $time => $schedules): ?>
                                        <?php foreach ($schedules as $schedule): ?>
                                            <tr>
                                                <form action="cinemamanagement.php" method="POST" onsubmit="return confirmUpdate();">
                                                    <td>
                                                        <span><?php echo htmlspecialchars($time); ?></span>
                                                        <input type="hidden" name="cinema" value="<?php echo htmlspecialchars($schedule['cinema']); ?>">
                                                    </td>
                                                    <td>
                                                        <span><?php echo htmlspecialchars($schedule['available_seats']); ?></span>
                                                    </td>
                                                    <td>
                                                        <select name="movie_id" required>
                                                            <option value="<?php echo htmlspecialchars($schedule['movie_id']); ?>">
                                                                <?php echo htmlspecialchars($movies[$schedule['movie_id']]); ?>
                                                            </option>
                                                            <?php foreach ($movies as $id => $title): ?>
                                                                <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($title); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="datetime-local" name="showtime" 
                                                            value="<?php echo date('Y-m-d\TH:i', strtotime($schedule['showtime'])); ?>" 
                                                            required>
                                                    </td>
                                                    <td>
                                                        <select name="ticket_type" onchange="updateSeatPrice(this)" required>
                                                            <option value="vip" <?php echo strtolower($schedule['ticket_type']) === 'vip' ? 'selected' : ''; ?>>vip</option>
                                                            <option value="regular" <?php echo strtolower($schedule['ticket_type']) === 'regular' ? 'selected' : ''; ?>>regular</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <span class="seat-price">
                                                            <?php
                                                            $price_table = htmlspecialchars($schedule['cinema']);
                                                            $sql_price_display = "SELECT seat_price FROM `$price_table` LIMIT 1";
                                                            $stmt_price_display = $mysqli->prepare($sql_price_display);
                                                            
                                                            if ($stmt_price_display === false) {
                                                                die("Error preparing statement for table '$price_table': " . htmlspecialchars($mysqli->error));
                                                            }

                                                            $stmt_price_display->execute();
                                                            $result_price_display = $stmt_price_display->get_result();
                                                            $price_row = $result_price_display->fetch_assoc();

                                                            if ($price_row) {
                                                                $display_price = number_format((float)$price_row['seat_price'], 2, '.', '');
                                                            } else {
                                                                die("No prices found for table '$price_table'.");
                                                            }
                                                            ?>
                                                            <span><?php echo htmlspecialchars($display_price); ?></span>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($schedule['id']); ?>">
                                                        <button type="submit" class="btn btn-primary">Update</button>
                                                    </td>
                                                </form>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="cinema4" class="cinema-schedule" style="<?php echo ($current_cinema === 'cinema4') ? 'display: block;' : 'display: none;'; ?>">
                    <?php foreach ($cinema_schedules['cinema4'] as $date => $times): ?>
                        <div class="mb-4 border rounded p-3">
                            <h4><?php echo htmlspecialchars('Cinema 4 ' . $date); ?></h4>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Available Seats</th>
                                        <th>Movie</th>
                                        <th>Showtime</th>
                                        <th>Ticket Type</th>
                                        <th>Seat Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($times as $time => $schedules): ?>
                                        <?php foreach ($schedules as $schedule): ?>
                                            <tr>
                                                <form action="cinemamanagement.php" method="POST" onsubmit="return confirmUpdate();">
                                                    <td>
                                                        <span><?php echo htmlspecialchars($time); ?></span>
                                                        <input type="hidden" name="cinema" value="<?php echo htmlspecialchars($schedule['cinema']); ?>">
                                                    </td>
                                                    <td>
                                                        <span><?php echo htmlspecialchars($schedule['available_seats']); ?></span>
                                                    </td>
                                                    <td>
                                                        <select name="movie_id" required>
                                                            <option value="<?php echo htmlspecialchars($schedule['movie_id']); ?>">
                                                                <?php echo htmlspecialchars($movies[$schedule['movie_id']]); ?>
                                                            </option>
                                                            <?php foreach ($movies as $id => $title): ?>
                                                                <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($title); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="datetime-local" name="showtime" 
                                                            value="<?php echo date('Y-m-d\TH:i', strtotime($schedule['showtime'])); ?>" 
                                                            required>
                                                    </td>
                                                    <td>
                                                        <select name="ticket_type" onchange="updateSeatPrice(this)" required>
                                                            <option value="vip" <?php echo strtolower($schedule['ticket_type']) === 'vip' ? 'selected' : ''; ?>>vip</option>
                                                            <option value="regular" <?php echo strtolower($schedule['ticket_type']) === 'regular' ? 'selected' : ''; ?>>regular</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <span class="seat-price">
                                                            <?php
                                                            $price_table = htmlspecialchars($schedule['cinema']);
                                                            $sql_price_display = "SELECT seat_price FROM `$price_table` LIMIT 1";
                                                            $stmt_price_display = $mysqli->prepare($sql_price_display);
                                                            
                                                            if ($stmt_price_display === false) {
                                                                die("Error preparing statement for table '$price_table': " . htmlspecialchars($mysqli->error));
                                                            }

                                                            $stmt_price_display->execute();
                                                            $result_price_display = $stmt_price_display->get_result();
                                                            $price_row = $result_price_display->fetch_assoc();

                                                            if ($price_row) {
                                                                $display_price = number_format((float)$price_row['seat_price'], 2, '.', '');
                                                            } else {
                                                                die("No prices found for table '$price_table'.");
                                                            }
                                                            ?>
                                                            <span><?php echo htmlspecialchars($display_price); ?></span>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($schedule['id']); ?>">
                                                        <button type="submit" class="btn btn-primary">Update</button>
                                                    </td>
                                                </form>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set the current cinema in local storage
        window.onload = function() {
            const currentCinema = localStorage.getItem('currentCinema') || 'cinema1';
            toggleCinema(currentCinema);
        };

        function toggleCinema(cinemaId) {
            const cinemas = document.querySelectorAll('.cinema-schedule');
            cinemas.forEach(cinema => {
                cinema.style.display = 'none';
            });
            const selectedCinema = document.getElementById(cinemaId);
            if (selectedCinema) {
                selectedCinema.style.display = 'block';
                localStorage.setItem('currentCinema', cinemaId);
            }
        }

        function confirmUpdate() {
            return confirm("Are you sure you want to update this schedule?");
        }

        function updateSeatPrice(select) {
            const price = select.value === 'vip' ? 500.00 : 350.00;
            select.closest('tr').querySelector('.seat-price span').textContent = price.toFixed(2);
        }
    </script>
</body>
</html>