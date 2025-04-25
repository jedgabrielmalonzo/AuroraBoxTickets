<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysqli = require __DIR__ . "/database.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Check if booking information is set in session
if (!isset($_SESSION['booking_info'])) {
    header("Location: error.php?error=BookingInfoNotFound");
    exit;
}

$booking_info = $_SESSION['booking_info'];
$schedule_id = $booking_info['schedule_id'];
$selected_seats = $booking_info['selected_seats'] ?? [];
$num_tickets = count($selected_seats);

// Fetch user info
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: error.php?error=UserNotFound");
    exit;
}

// Fetch schedule details
$sql_schedule = "SELECT cinema, showtime, movie_id, ticket_type FROM schedules WHERE id = ?";
$stmt_schedule = $mysqli->prepare($sql_schedule);
$stmt_schedule->bind_param("i", $schedule_id);
$stmt_schedule->execute();
$result_schedule = $stmt_schedule->get_result();
$schedule = $result_schedule->fetch_assoc();
$stmt_schedule->close();

if (!$schedule) {
    die("Invalid schedule ID.");
}

// Determine the cinema display name
$cinema_display_name = 'Cinema ' . substr($schedule['cinema'], -1);

// Format date and showtime
$showtime = date('g:i A', strtotime($schedule['showtime']));
$date = date('l, F j, Y', strtotime($schedule['showtime']));

// Fetch the movie title and image
$movie_id = $schedule['movie_id'];
$sql_movie = "SELECT title, image FROM movies WHERE id = ?";
$stmt_movie = $mysqli->prepare($sql_movie);
$stmt_movie->bind_param("i", $movie_id);
$stmt_movie->execute();
$result_movie = $stmt_movie->get_result();
$movie = $result_movie->fetch_assoc();
$stmt_movie->close();

if (!$movie) {
    die("Invalid movie ID.");
}

$title = htmlspecialchars($movie['title']);
$image = htmlspecialchars($movie['image']);

// Fetch seat price
$cinema_table = $schedule['cinema'];
$sql_price = "SELECT seat_price FROM $cinema_table LIMIT 1";
$stmt_price = $mysqli->prepare($sql_price);
$stmt_price->execute();
$result_price = $stmt_price->get_result();
$price_data = $result_price->fetch_assoc();
$stmt_price->close();

$ticket_price = $price_data['seat_price'] ?? null;

if ($ticket_price === null) {
    header("Location: error.php?error=PriceNotAvailable");
    exit;
}

// Calculate total price
$total_price = $ticket_price * $num_tickets;

// Fetch food selection
$food_selection = $_SESSION['food_selection'] ?? [];
$total_food_price = array_sum(array_column($food_selection, 'total'));

// Calculate final price
$final_total_price = $total_price + $total_food_price;

// Payment processing
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

    if ($payment_amount <= 0) {
        $error_message = "Invalid payment amount.";
    } elseif ($payment_amount < $final_total_price) {
        $error_message = "Insufficient payment amount.";
    } else {
        $_SESSION['payment_processed'] = true;

        // Generate a unique transaction ID
        $transaction_id = '';
        do {
            $timestamp = time(); // Current Unix timestamp
            $random_string = bin2hex(random_bytes(3)); // 6 hex characters
            $transaction_id = $timestamp . $random_string;
        
            // Check for uniqueness
            $sql_check = "SELECT COUNT(*) FROM payments WHERE transaction_id = ?";
            $stmt_check = $mysqli->prepare($sql_check);
            $stmt_check->bind_param("s", $transaction_id);
            $stmt_check->execute();
            $stmt_check->bind_result($count);
            $stmt_check->fetch();
            $stmt_check->close();
        } while ($count > 0); // Ensure ID is unique

        $change = $payment_amount - $final_total_price;

        // Insert payment into database
        $sql_payment = "INSERT INTO payments (transaction_id, user_id, amount, user_entered_amount, change_due, schedule_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_payment = $mysqli->prepare($sql_payment);
        $stmt_payment->bind_param("siidid", $transaction_id, $_SESSION["user_id"], $final_total_price, $payment_amount, $change, $schedule_id);

        if ($stmt_payment->execute()) {
            $payment_id = $stmt_payment->insert_id;
            $stmt_payment->close();

            // Update available seats in schedules table
            $new_seats = $schedule['available_seats'] - $num_tickets;
            $sql_update = "UPDATE schedules SET available_seats = ? WHERE id = ?";
            $stmt_update = $mysqli->prepare($sql_update);
            $stmt_update->bind_param("ii", $new_seats, $schedule_id);
            $stmt_update->execute();
            $stmt_update->close();

            // Update seat statuses and insert into purchases
            foreach ($selected_seats as $seat_id) {
                $sql_seat_update = "UPDATE $cinema_table SET status = 'unavailable' WHERE seat_id = ?";
                $stmt_seat_update = $mysqli->prepare($sql_seat_update);
                $stmt_seat_update->bind_param("i", $seat_id);
                $stmt_seat_update->execute();
                $stmt_seat_update->close();

                // Insert into seat_purchases table
                $sql_seat_purchase = "INSERT INTO seat_purchases (payment_id, seat_number, schedule_id) VALUES (?, ?, ?)";
                $stmt_seat_purchase = $mysqli->prepare($sql_seat_purchase);
                $stmt_seat_purchase->bind_param("iii", $payment_id, $seat_id, $schedule_id);
                $stmt_seat_purchase->execute();
                $stmt_seat_purchase->close();
            }

            // Insert food purchases
            foreach ($food_selection as $food_item) {
                if (!isset($food_item['item_name'])) {
                    header("Location: error.php?error=FoodItemMissing");
                    exit;
                }

                $food_item_name = strtoupper($food_item['item_name']);
                $sql_food_id = "SELECT id, food_price FROM foods WHERE UPPER(food_item) = ?";
                $stmt_food_id = $mysqli->prepare($sql_food_id);
                $stmt_food_id->bind_param("s", $food_item_name);
                $stmt_food_id->execute();
                $result_food_id = $stmt_food_id->get_result();
                $food_id_row = $result_food_id->fetch_assoc();
                $food_id = $food_id_row['id'] ?? null;
                $food_price = $food_id_row['food_price'] ?? null; 
                $stmt_food_id->close();

                if ($food_id !== null) {
                    $sql_food = "INSERT INTO food_purchases (user_id, food_id, food_item, price, quantity, transaction_id) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_food = $mysqli->prepare($sql_food);
                    $stmt_food->bind_param("iisdis", $_SESSION["user_id"], $food_id, $food_item_name, $food_price, $food_item['quantity'], $transaction_id);
                    if (!$stmt_food->execute()) {
                        die("Food purchase insertion failed: " . $stmt_food->error);
                    }
                    $stmt_food->close();
                }
            }

            // Clear session data
            unset($_SESSION['booking_info']);
            unset($_SESSION['food_selection']);

            // Redirect to payment success page
            header("Location: payment_success.php?transaction_id=" . urlencode($transaction_id));
            exit;
        } else {
            die("Payment insertion failed: " . $stmt_payment->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuroraBox - Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/payment.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="home.php">
            <img src="images/logo.png" alt="Logo" class="img-fluid">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <form method="GET" action="movies.php" class="d-flex align-items-center ms-auto">
                <div class="input-wrapper me-2">
                    <button class="icon" type="submit" aria-label="Search">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="25px" width="25px">
                            <path stroke-linejoin="round" stroke-linecap="round" stroke-width="1.5" stroke="#fff" d="M11.5 21C16.7467 21 21 16.7467 21 11.5C21 6.25329 16.7467 2 11.5 2C6.25329 2 2 6.25329 2 11.5C2 16.7467 6.25329 21 11.5 21Z"></path>
                            <path stroke-linejoin="round" stroke-linecap="round" stroke-width="1.5" stroke="#fff" d="M22 22L20 20"></path>
                        </svg>
                    </button>
                    <input placeholder="Search by title..." class="input" name="query" type="text" aria-label="Search input">
                </div>
            </form>
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="movies.php">Movies</a></li>
                <li class="nav-item"><a class="nav-link" href="events.php">Events and Promos</a></li>
                <li class="nav-item"><a class="nav-link" href="aboutus.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="account.php">Account</a></li>
            </ul>
            <ul class="navbar-nav">
                <a class="nav-link">Welcome, <?php echo htmlspecialchars($user['firstname']); ?>!</a>
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
        </div>
    </div>
    <div class="order-summary">
        <h2>ORDER SUMMARY</h2>
        <hr>
        <p>Cinema: <strong><?php echo htmlspecialchars($cinema_display_name); ?></strong></p>
        <p>Date/Showtime: <strong><?php echo htmlspecialchars($date) . ' [' . htmlspecialchars($showtime) . ']'; ?></strong></p>
        <p>Movie Title: <strong><?php echo htmlspecialchars($movie['title']); ?></strong></p>
        <p>Seats: <strong><?php echo htmlspecialchars(implode(', ', $selected_seats)); ?></strong></p>
        <p>Food Selected:</p>
        <ul>
            <?php if (!empty($food_selection)): ?>
                <?php foreach ($food_selection as $food): ?>
                    <li><?php echo htmlspecialchars($food['item_name']) . " x " . (int)$food['quantity'] . " - ₱" . number_format($food['total'], 2); ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No food selected.</li>
            <?php endif; ?>
        </ul>
        <br>
        <p><strong>Total Price: ₱<?php echo number_format($final_total_price, 2); ?></strong></p>
        <hr>
        
        <form action="" method="POST">
            <label for="amount"><strong>Enter Payment Amount:</strong></label>
            <input type="number" id="amount" name="amount" required>
            <?php if (!empty($error_message)): ?>
                <div class="text-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <button type="submit" class="btn-primary">Confirm Payment</button>
        </form>
    </div>
</div>

</body>
</html>