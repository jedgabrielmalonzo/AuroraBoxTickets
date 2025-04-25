<?php
// book_ticket.php
if (!isset($_GET['schedule_id'])) {
    die("Schedule ID is required.");
}
$schedule_id = $_GET['schedule_id'];

$mysqli = require __DIR__ . "/database.php";

// Fetch the schedule data
$sql = "SELECT *, ticket_type FROM schedules WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();
$schedule = $result->fetch_assoc();

if (!$schedule) {
    die("Invalid schedule ID.");
}

// Fetch the seats data based on the cinema from the schedule
$cinema_table = $schedule['cinema'];
$sql_seats = "SELECT seat_id, status FROM $cinema_table";
$stmt_seats = $mysqli->prepare($sql_seats);
$stmt_seats->execute();
$result_seats = $stmt_seats->get_result();
$seats = $result_seats->fetch_all(MYSQLI_ASSOC);

// Fetch the seat price from the cinema table
$sql_price = "SELECT seat_price FROM $cinema_table LIMIT 1"; // Adjust as needed
$stmt_price = $mysqli->prepare($sql_price);

if (!$stmt_price) {
    die("Prepare failed: " . $mysqli->error);
}

$stmt_price->execute();
$result_price = $stmt_price->get_result();

if (!$result_price) {
    die("Query failed: " . $mysqli->error);
}

$price_data = $result_price->fetch_assoc();

if ($price_data) {
    $ticket_price = $price_data['seat_price'];
} else {
    $ticket_price = "Price not available"; // Fallback if no price is found
}

// Format the showtime to AM/PM format and extract the date
$showtime = date('g:i A', strtotime($schedule['showtime']));
$date = date('l, F j, Y', strtotime($schedule['showtime']));

// Determine the cinema display name
$cinema_display_name = '';
if (strpos($schedule['cinema'], 'cinema1_') === 0) {
    $cinema_display_name = 'Cinema 1';
} elseif (strpos($schedule['cinema'], 'cinema2_') === 0) {
    $cinema_display_name = 'Cinema 2';
} elseif (strpos($schedule['cinema'], 'cinema3_') === 0) {
    $cinema_display_name = 'Cinema 3';
} elseif (strpos($schedule['cinema'], 'cinema4_') === 0) {
    $cinema_display_name = 'Cinema 4';
}
?>

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

$mysqli = require __DIR__ . "/database.php";
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]); // Bind user ID
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch the movie title and image
$movie_id = $schedule['movie_id']; // Assuming 'movie_id' is the column in schedules
$sql_movie = "SELECT title, image FROM movies WHERE id = ?";
$stmt_movie = $mysqli->prepare($sql_movie);
$stmt_movie->bind_param("i", $movie_id);
$stmt_movie->execute();
$result_movie = $stmt_movie->get_result();

if (!$result_movie) {
    die("Query failed: " . $mysqli->error); // Check for query errors
}

$movie = $result_movie->fetch_assoc();

if (!$movie) {
    die("Invalid movie ID."); // This will trigger if no movie is found
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuroraBox - Book Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/navbar.css">
    <link rel="stylesheet" href="CSS/seats.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
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
                <a class= "nav-link">Welcome, <?php echo htmlspecialchars($user['firstname']); ?>!</a>    
                <li class="nav-item">
                    <a class="Btn" href="logout.php">
                        <div class="sign">
                            <svg viewBox="0 0 512 512">
                                <path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"/>
                            </svg>
                        </div>
                        <div class="text">Logout</div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="main-content">
    <div class="movie-poster">
        <img src="<?php echo htmlspecialchars($movie['image']); ?>" class="img-poster" alt="<?php echo htmlspecialchars($movie['title']); ?>">
        <div class="title-text">
            <h2 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h2>
            <div class="movie-information">
                <p>DATE: <?php echo htmlspecialchars($date); ?></p>
                <p>SHOWTIME: <?php echo htmlspecialchars($showtime); ?></p>
                <p>CINEMA: <?php echo htmlspecialchars($cinema_display_name); ?></p>
                <p>TYPE: <?php echo isset($schedule['ticket_type']) ? strtoupper(htmlspecialchars(ucwords($schedule['ticket_type']))) : 'N/A'; ?></p>
                <p>PRICE: ₱<?php echo htmlspecialchars($ticket_price); ?></p>
            </div>
            <div class="select-seats">
                <div class="select-seats-text">
                    <h2>SELECT YOUR SEATS</h2>
                    <div class="box-indicators">
                        <div class="box-availability"></div>
                        <p class="box-text">AVAILABLE</p>
                        <div class="box-availability1"></div>
                        <p class="box-text">UNAVAILABLE</p>
                        <div class="box-availability2"></div>
                        <p class="box-text">SELECTED</p>
                    </div>
                </div>
                <p>SEATS AVAILABLE: <span id="available-seats"><?php echo $schedule['available_seats']; ?></span></p>
                <div class="screen"><p>SCREEN</p></div>
              
                <div class="seat-selection">
                    <?php foreach ($seats as $seat): ?>
                        <div class="seat <?php echo $seat['status']; ?>" data-seat-id="<?php echo htmlspecialchars($seat['seat_id']); ?>">
                            <?php echo htmlspecialchars($seat['seat_id']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <hr>
                <div class="ticket-selected-text">
                    <p id="tickets-selected-text">Tickets Selected: 0 []</p>
                    <p>Total Price: ₱<span id="total-price">0.00</span></p>
                </div>

                <form action="process_booking.php" method="POST" id="booking-form">
                    <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($schedule['id']); ?>">
                    <input type="hidden" name="selected_seats" id="selected-seats" value="">
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary" id="confirm-button">NEXT TO ORDER SUMMARY</button>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
</div>

<script>
    const seats = document.querySelectorAll('.seat');
    const selectedSeats = [];
    const availableSeatsCount = document.getElementById('available-seats');
    const ticketsSelectedText = document.getElementById('tickets-selected-text'); 
    const totalPriceElement = document.getElementById('total-price');
    
    // Get the ticket price from PHP and convert it to a number
    const ticketPrice = <?php echo json_encode($ticket_price); ?>; // Assuming ticket_price is numeric

    seats.forEach(seat => {
        seat.addEventListener('click', () => {
            if (seat.classList.contains('available')) {
                seat.classList.toggle('selected');
                const seatId = seat.getAttribute('data-seat-id');

                // Update selectedSeats array
                if (selectedSeats.includes(seatId)) {
                    selectedSeats.splice(selectedSeats.indexOf(seatId), 1); // Remove seat if already selected
                } else {
                    if (selectedSeats.length < 5) { // Limit to 5 seats
                        selectedSeats.push(seatId);
                    } else {
                        alert("Maximum tickets reached.");
                        seat.classList.remove('selected');
                    }
                }

                // Update the hidden input for selected seats
                document.getElementById('selected-seats').value = selectedSeats.join(',');

                // Update counts displayed on the page
                ticketsSelectedText.textContent = `Tickets Selected: ${selectedSeats.length} [${selectedSeats.join(', ')}]`;
                availableSeatsCount.textContent = parseInt(availableSeatsCount.textContent) - (seat.classList.contains('selected') ? 1 : -1);

                // Calculate total price
                const totalPrice = selectedSeats.length * parseFloat(ticketPrice);
                totalPriceElement.textContent = totalPrice.toFixed(2); // Show total price with 2 decimal places
            }
        });
    });

    // Validate selected seats on form submission
    document.getElementById('booking-form').addEventListener('submit', (event) => {
        if (selectedSeats.length === 0) {
            event.preventDefault();
            alert('Please select at least one seat.');
        }
    });
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>