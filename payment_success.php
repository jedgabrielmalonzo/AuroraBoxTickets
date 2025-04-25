<?php
session_start();
$mysqli = require __DIR__ . "/database.php"; // Ensure this connects to your database

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Get the transaction ID from the URL
$transaction_id = $_GET['transaction_id'] ?? '';

// Check if transaction_id is empty
if (empty($transaction_id)) {
    die("Transaction ID is missing.");
}

// Fetch payment details including change_due, amount, and user_amount_paid
$sql = "SELECT *, created_at FROM payments WHERE transaction_id = ?";
$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
    die("MySQL prepare error: " . $mysqli->error);
}

$stmt->bind_param("s", $transaction_id);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    die("Payment not found for Transaction ID: " . htmlspecialchars($transaction_id));
}

// Format the created_at timestamp
$date_of_purchase = date("m/d/y H:i", strtotime($payment['created_at'])); // Format: MM/DD/YY HH:MM

$schedule_id = $payment['schedule_id'];

$sql_schedule = "
    SELECT s.cinema, s.showtime, s.movie_id, s.ticket_type, tp.price AS ticket_price 
    FROM schedules s 
    JOIN ticket_prices tp ON s.ticket_type = tp.ticket_type 
    WHERE s.id = ?";
$stmt_schedule = $mysqli->prepare($sql_schedule);
$stmt_schedule->bind_param("i", $schedule_id);
$stmt_schedule->execute();
$result_schedule = $stmt_schedule->get_result();
$schedule = $result_schedule->fetch_assoc();

if (!$schedule) {
    die("Invalid schedule ID.");
}

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

// Format showtime to include date and time
$showtime_timestamp = strtotime($schedule['showtime']);
$formatted_showtime = date("F j, Y g:i A", $showtime_timestamp); // Format: MONTH, DATE, YEAR TIME

$movie_id = $schedule['movie_id'];
$sql_movie = "SELECT title FROM movies WHERE id = ?";
$stmt_movie = $mysqli->prepare($sql_movie);
$stmt_movie->bind_param("i", $movie_id);
$stmt_movie->execute();
$result_movie = $stmt_movie->get_result();
$movie = $result_movie->fetch_assoc();
$stmt_movie->close();

$movie_title = $movie ? htmlspecialchars($movie['title']) : 'Unknown Movie';

// Fetch user information
$user_id = $payment['user_id'];
$user = null;

if ($user_id) {
    $sql_user = "SELECT firstname, lastname FROM user WHERE id = ?";
    $stmt_user = $mysqli->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows > 0) {
        $user = $result_user->fetch_assoc();
    }
}

$customer_name = ($user) ? htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) : 'Guest';

// Fetch food purchases linked to the transaction
$sql_food = "SELECT food_item, price, quantity FROM food_purchases WHERE transaction_id = ?";
$stmt_food = $mysqli->prepare($sql_food);
$stmt_food->bind_param("s", $transaction_id);
$stmt_food->execute();
$result_food = $stmt_food->get_result();
$food_purchases = $result_food->fetch_all(MYSQLI_ASSOC);

// Fetch seat numbers linked to the transaction
$sql_seat = "SELECT seat_number FROM seat_purchases WHERE payment_id = ?";
$stmt_seat = $mysqli->prepare($sql_seat);
$stmt_seat->bind_param("i", $payment['id']);
$stmt_seat->execute();
$result_seat = $stmt_seat->get_result();
$seat_numbers = $result_seat->fetch_all(MYSQLI_ASSOC);
$stmt_seat->close();

// HTML Output
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/payment-success.css" rel="stylesheet">
</head>
<body>
    <div class="main-content">
        <div class="ticket-header">
            <div class="confirmation-header d-flex align-items-center">
                <img src="images/check.png" class="img-poster" alt="Movie Title"> 
                <h1 class="m-0">You have successfully purchased your tickets.</h1>
            </div>
            <hr>
        </div>
        <div class="ticket-details">
            <div class="order-sum-text">
                <p class="success-text"><strong>Order Summary</strong></p>
                <div class="ref-no">
                    <p class="text-muted">Transaction ID: <?php echo htmlspecialchars($payment['transaction_id']); ?></p>
                    <p class="text-muted">Date of Purchase: <?php echo $date_of_purchase; ?></p>
                </div>
            </div>
            <hr>
            <p><span>CUSTOMER NAME:</span> <?php echo $customer_name; ?></p>
            <br>
            <p><span>MOVIE TITLE:</span> <?php echo $movie_title; ?></p>
            <p><span>CINEMA:</span> <?php echo htmlspecialchars($cinema_display_name); ?></p>
            <p><span>SHOWTIME:</span> <?php echo htmlspecialchars($formatted_showtime); ?></p>
            <p><span>TICKET TYPE:</span> <?php echo strtoupper(htmlspecialchars($schedule['ticket_type'])); ?></p>
            <div class="summary-box">
                <p>SEATS NO.:</p>
                <ul class="seat-list">
                    <?php if (!empty($seat_numbers)): ?>
                        <?php foreach ($seat_numbers as $seat): ?>
                        <strong><?php echo htmlspecialchars($seat['seat_number']); ?></strong>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No seats selected for this transaction.</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="summary-box">
                <p>FOOD SELECTED:</p>
                <ul class="food-list">
                    <?php if (!empty($food_purchases)): ?>
                        <?php foreach ($food_purchases as $food): ?>
                            <li>
                                <?php echo htmlspecialchars($food['food_item']); ?> 
                                <span>₱<?php echo number_format($food['price'], 2); ?> x <?php echo (int)$food['quantity']; ?> &nbsp;&ndash;&nbsp; ₱<?php echo number_format($food['price'] * $food['quantity'], 2); ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No food items selected for this transaction.</li>
                    <?php endif; ?>
                </ul>
            </div>
            <br>
            <p><span>Amount Due:</span> ₱<?php echo number_format($payment['amount'], 2); ?></p>
            <p><span>Amount Entered:</span> ₱<?php echo number_format($payment['user_entered_amount'], 2); ?></p>
            <p><span>Change Due:</span> ₱<?php echo number_format($payment['change_due'], 2); ?></p>
            <hr>
            <div class="footer-text">
                <p>NOTES:</p>
                <ul>
                    <li>Present your voucher at Aurora Box Cinemas on the day of your booking</li>
                    <li>Only the name transacted online can redeem the ticket; if not, please present an ID and an authorized letter from the name of the buyer.</li>
                </ul>
                
                <div class="button-type">
                <a class="download-button" href="generate_pdf.php?transaction_id=<?php echo urlencode($payment['transaction_id']); ?>" 
                   class="btn btn-success" 
                   style="margin-bottom: 10px;">Download PDF Ticket</a>
                <form action="home.php" method="get">
                    <button type="submit" class="btn btn-primary" id="confirm-button">GO TO HOME</button>
                </form>
            </div>
        </div>
        </div>
    </div>
</body>
</html>