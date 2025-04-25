<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$mysqli = require __DIR__ . "/database.php";

// Fetch user data
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch movie data
$movie_id = $_GET['movie_id'] ?? null;
$movie = null;

if ($movie_id) {
    $sql = "SELECT * FROM movies WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $movie = $result->fetch_assoc();
    }
}

// Group showtimes by date
$showtimes_by_date = [];

if ($movie_id) {
    // Fetch showtimes from the schedules table
    $sql_showtimes = "SELECT showtime, available_seats, cinema, id, ticket_type FROM schedules WHERE movie_id = ?";
    $stmt_showtimes = $mysqli->prepare($sql_showtimes);
    $stmt_showtimes->bind_param("i", $movie_id);
    $stmt_showtimes->execute();
    $result_showtimes = $stmt_showtimes->get_result();

    while ($row = $result_showtimes->fetch_assoc()) {
        $date = date('Y-m-d', strtotime($row['showtime']));
        $showtime = $row['showtime'];
        $ticket_type = $row['ticket_type'];

        // Determine the display name for the cinema
        $cinema_display_name = '';
        if (strpos($row['cinema'], 'cinema1_') === 0) {
            $cinema_display_name = 'Cinema 1';
        } elseif (strpos($row['cinema'], 'cinema2_') === 0) {
            $cinema_display_name = 'Cinema 2';
        } elseif (strpos($row['cinema'], 'cinema3_') === 0) {
            $cinema_display_name = 'Cinema 3';
        } elseif (strpos($row['cinema'], 'cinema4_') === 0) {
            $cinema_display_name = 'Cinema 4';
        }

        // Fetch ticket price based on ticket_type
        $sql_price = "SELECT price FROM ticket_prices WHERE ticket_type = ?";
        $stmt_price = $mysqli->prepare($sql_price);
        $stmt_price->bind_param("s", $ticket_type);
        $stmt_price->execute();
        $result_price = $stmt_price->get_result();

        $ticket_price = null;
        if ($result_price->num_rows > 0) {
            $ticket_price = $result_price->fetch_assoc()['price'];
        }

        // Group showtimes by date including ticket price and cinema display name
        $showtimes_by_date[$date][] = [
            'showtime' => $showtime,
            'cinema' => $cinema_display_name,
            'available_seats' => $row['available_seats'],
            'schedule_id' => $row['id'],
            'ticket_type' => $ticket_type,
            'ticket_price' => $ticket_price
        ];
    }
}

// Sort the dates
ksort($showtimes_by_date);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuroraBox - Movie Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/navbar.css">
    <link rel="stylesheet" href="CSS/schedule.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary" id="home">
    <div class="container-fluid">
      <a class="navbar-brand" href="home.php">
    <img src="images/logo.png" alt="Logo" class="img-fluid">
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="movies.php">Movies</a></li>
                <li class="nav-item"><a class="nav-link" href="events.php">Events and Promos</a></li>
                <li class="nav-item"><a class="nav-link" href="aboutus.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="account.php">Account</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link">Welcome, <?php echo htmlspecialchars($user['firstname']); ?>!</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="main-content">
    <?php if ($movie): ?>
        <div class="movie-poster">
            <img src="<?php echo htmlspecialchars($movie['image']); ?>" class="img-poster" alt="<?php echo htmlspecialchars($movie['title']); ?>">
            <div class="title-text">
                <h4 class="cinema-text"><?php echo htmlspecialchars($showtimes_by_date[array_key_first($showtimes_by_date)][0]['cinema']); ?></h4> <!-- Fetch the first cinema -->
                <h2 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h2>
                <p class="runtime"><?php echo htmlspecialchars($movie['runtime']); ?></p> <!-- Display runtime -->
                <a href="#" id="openDialog" class="btn btn-link">VIP AND REGULAR (?)</a>
            </div>
        </div>

        <!-- Rest of your code remains the same -->
        <div id="seatInfoDialog" style="display:none;">
    <div class="dialog-content">
        <h2 class="dialog-title">Seat Information</h2>
        <div class="seat-type">
            <i class="fas fa-crown icon-vip"></i>
            <h4>VIP Seats</h4>
            <ul>
                <li>Premium comfort seating</li>
                <li>Dolby Atmos Sound System</li>
                <li>Complimentary food and drink, with the option to choose your meal at the cinema.</li>
            </ul>
        </div>
        <hr>
        <div class="seat-type">
            <i class="fas fa-chair icon-regular"></i>
            <h4>Regular Seats</h4>
            <ul>
                <li>Premium Cushioned Seats</li>
                <li>Standard Sound System</li>
                <li>No additional perks.</li>
            </ul>
            <p><strong>Note:</strong> VIP ticket holders can select their complimentary food and drink upon arrival at the cinema.</p>
        </div>
        <div class="dialog-buttons">
            <button id="closeDialog" class="btn">Close</button>
        </div>
    </div>
</div>
        <br>
        <h3 class="showtime-text">Showtimes</h3>
        <?php if (!empty($showtimes_by_date)): ?>
            <?php foreach ($showtimes_by_date as $date => $showtimes): ?>
                <div class="card">
                    <div class="card-header">
                        <h4><?php echo date('l, F j, Y', strtotime($date)); ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($showtimes as $showtime): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="list-group">
                                        <div class="list-group-item text-center">
                                            <div class="showtime-details">
                                                <span class="ticket-type"><?php echo strtoupper(htmlspecialchars($showtime['ticket_type'])); ?></span>
                                                <span class="showtime"><?php echo date('h:i A', strtotime($showtime['showtime'])); ?></span>
                                                <span class="ticket-price">₱<?php echo number_format($showtime['ticket_price'], 2); ?></span>
                                                <a href="book_seats.php?schedule_id=<?php echo $showtime['schedule_id']; ?>" class="btn btn-book">Book Ticket</a>
                                            </div>
                                            <p class="available-seats">Available Seats: <?php echo $showtime['available_seats']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                No showtimes available for this movie yet.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p>Movie details not available.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(document).ready(function () {
    $("#seatInfoDialog").dialog({
        autoOpen: false,
        modal: true,
        width: 'auto', // Allow auto width
        maxWidth: 600, // Maximum width for the dialog
        closeOnEscape: true,
        open: function(event, ui) {
            $(this).dialog("widget").find(".ui-dialog-titlebar").hide();
        }
    });

    $("#openDialog").click(function () {
        $("#seatInfoDialog").dialog("open");
    });

    $("#closeDialog").click(function () {
        $("#seatInfoDialog").dialog("close");
    });
});
</script>
</body>
</html>