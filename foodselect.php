<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$mysqli = require __DIR__ . "/database.php";

// Fetch user information
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch booking information from session
if (!isset($_SESSION['booking_info'])) {
    die("No booking information found.");
}

$booking_info = $_SESSION['booking_info'];
$schedule_id = $booking_info['schedule_id'];
$selected_seats = $booking_info['selected_seats'];
$num_tickets = $booking_info['num_tickets'];

// Fetch schedule details (cinema, showtime)
$sql_schedule = "SELECT cinema, showtime, movie_id, ticket_type FROM schedules WHERE id = ?";
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

// Fetch movie details using movie_id from the schedule
$movie_id = $schedule['movie_id'];
$sql_movie = "SELECT title, image FROM movies WHERE id = ?";
$stmt_movie = $mysqli->prepare($sql_movie);
$stmt_movie->bind_param("i", $movie_id);
$stmt_movie->execute();
$result_movie = $stmt_movie->get_result();
$movie = $result_movie->fetch_assoc();

if (!$movie) {
    die("Invalid movie ID.");
}

// Fetch the seat price from the cinema table
$cinema_table = $schedule['cinema'];
$sql_price = "SELECT seat_price FROM $cinema_table LIMIT 1"; // Adjust as needed
$stmt_price = $mysqli->prepare($sql_price);
$stmt_price->execute();
$result_price = $stmt_price->get_result();
$price_data = $result_price->fetch_assoc();
$ticket_price = $price_data ? $price_data['seat_price'] : die("Price not available");

// Format the showtime to AM/PM format and extract the date
$showtime = date('g:i A', strtotime($schedule['showtime']));
$date = date('l, F j, Y', strtotime($schedule['showtime']));

// Calculate total price
$total_price = $ticket_price * $num_tickets; // Calculate total price based on number of tickets

// Fetch food items from the database
$sql_foods = "SELECT id, food_item, food_price FROM foods";
$stmt_foods = $mysqli->prepare($sql_foods);
$stmt_foods->execute();
$result_foods = $stmt_foods->get_result();
$foods = $result_foods->fetch_all(MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuroraBox - My Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/food.css">
    <script>
       function updateTotalPrice() {
    const foodPrices = <?php echo json_encode(array_column($foods, 'food_price', 'id')); ?>;
    let totalFoodPrice = 0;
    const selectedFoodsDiv = document.getElementById('selected-foods');
    selectedFoodsDiv.innerHTML = ''; // Clear previous selections

    for (const [id, price] of Object.entries(foodPrices)) {
        const quantityInput = document.getElementById(`${id}-quantity`);
        const quantity = parseInt(quantityInput.value) || 0; // Ensure quantity is numeric
        document.getElementById(`food-${id}-quantity`).value = quantity; // Set hidden input value
        
        console.log(`Food ID: ${id}, Price: ${price}, Quantity: ${quantity}`); // Debugging line

        if (quantity > 0) {
            totalFoodPrice += quantity * parseFloat(price); // Ensure price is a float
            const foodName = <?php echo json_encode(array_column($foods, 'food_item', 'id')); ?>[id];
            const foodItem = document.createElement('li');
            foodItem.innerText = `${foodName} x ${quantity} - ₱${(price * quantity).toFixed(2)}`;
            selectedFoodsDiv.appendChild(foodItem);
        }
    }

    const ticketPrice = parseFloat(<?php echo $ticket_price; ?>); // Ensure ticket price is a float
    const numTickets = parseInt(<?php echo $num_tickets; ?>); // Ensure numTickets is an integer
    const baseTotalPrice = ticketPrice * numTickets;

    const finalTotalPrice = baseTotalPrice + totalFoodPrice;
    console.log(`Base Total Price: ₱${baseTotalPrice.toFixed(2)}, Total Food Price: ₱${totalFoodPrice.toFixed(2)}, Final Total Price: ₱${finalTotalPrice.toFixed(2)}`); // Debugging line
    document.getElementById('total-price').innerText = `₱${finalTotalPrice.toFixed(2)}`;
}
    </script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary" id=home>
    <div class="container-fluid">
    <a class="navbar-brand" href="home.php">
    <img src="images/logo.png" alt="Logo" class="img-fluid">
        </a>

        <div class="d-flex align-items-center ms-auto">
            <div class="input-wrapper">
                <button class="icon"> 
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="25px" width="25px">
                        <path stroke-linejoin="round" stroke-linecap="round" stroke-width="1.5" stroke="#fff" d="M11.5 21C16.7467 21 21 16.7467 21 11.5C21 6.25329 16.7467 2 11.5 2C6.25329 2 2 6.25329 2 11.5C2 16.7467 6.25329 21 11.5 21Z"></path>
                        <path stroke-linejoin="round" stroke-linecap="round" stroke-width="1.5" stroke="#fff" d="M22 22L20 20"></path>
                    </svg>
                </button>
                <input placeholder="search.." class="input" name="text" type="text">
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="home.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="movies.php">Movies</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="events.php">Events and Promos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="aboutus.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="account.php">Account</a>
                </li>
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

    <hr>
    <div class="details">
    <div class="food-section">
    <h2><strong>ADD FOODS</strong></h2>
    <p>Note: The quantity of each food item is limited to 5 per order for online customers. This policy is to prevent stock shortages for those purchasing in-person.</p>
    <div class="food-items">
        <?php foreach ($foods as $food): ?>
            <div class="food-item">
                <img src="images/foods/<?php echo strtolower($food['food_item']); ?>.png" alt="<?php echo htmlspecialchars($food['food_item']); ?>" class="food-img">
                <p><?php echo htmlspecialchars($food['food_item']); ?><br>
                <strong>₱<?php echo number_format((float)$food['food_price'], 2); ?></strong></p>
                <label for="<?php echo $food['id']; ?>-quantity">Quantity:</label>
                <input type="number" id="<?php echo $food['id']; ?>-quantity" name="food[<?php echo $food['id']; ?>][quantity]" min="0" max="5" value="0" onchange="updateTotalPrice()" onkeyup="updateTotalPrice()">            </div>
        <?php endforeach; ?>
    </div>
</div>
        </div>
        <hr>
        <div class="order-summary">
            <h2><strong>ORDER SUMMARY</strong></h2>
            <div class="order-details">
                <p>CINEMA NO.: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($cinema_display_name);?></p>
                <p>DATE/SHOWTIME: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($date); ?> [ <?php echo htmlspecialchars($showtime); ?> ]</p>
                <br>
                <p>MOVIE TITLE: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($movie['title']); ?></p>
                <p>TICKET TYPE: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo isset($schedule['ticket_type']) ? strtoupper(htmlspecialchars(ucwords($schedule['ticket_type']))) : 'N/A'; ?></p>
                <p>SEAT(s):&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars(implode(', ', $selected_seats)); ?></p>
                <p>&nbsp;</p>
                <p>ADD ONS: </p>
                <div class= "add-ons">
                <ul id="selected-foods">
                    <li>No add-ons selected.</li>
                </ul>
                </div>
                <br><br>
                <h4 class="totalprice">TOTAL PRICE: <span id="total-price">₱<?php echo number_format($total_price, 2); ?></span></h4>
            </div>
            <hr>
        </div>

        <form action="process_food.php" method="POST">
    <input type="hidden" name="schedule_id" value="<?php echo $schedule_id; ?>">
    <?php foreach ($foods as $food): ?>
        <input type="hidden" name="food[<?php echo $food['id']; ?>][name]" value="<?php echo htmlspecialchars($food['food_item']); ?>">
        <input type="hidden" name="food[<?php echo $food['id']; ?>][quantity]" id="food-<?php echo $food['id']; ?>-quantity" value="0">
    <?php endforeach; ?>
    <button type="submit" class="btn btn-primary" id="confirm-button">NEXT TO PAYMENT</button>

</form>

<script>
    document.getElementById('foodForm').onsubmit = function() {
        document.getElementById('submitBtn').disabled = true;
    };
</script>
    </div>
</div>

     <!-- NO FOOTER PARA FOCUS SA PAYMENT -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>