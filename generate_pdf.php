<?php
session_start();

$tcpdf_path = __DIR__ . '/libraries/tcpdf/tcpdf.php';
if (!file_exists($tcpdf_path)) {
    die("TCPDF file not found at: " . $tcpdf_path);
}

// Include TCPDF library
require_once($tcpdf_path);
require __DIR__ . "/database.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

function generateTicketPDF($transaction_id) {
    global $mysqli;
    
    // Fetch payment details
    $sql = "SELECT *, created_at, amount AS total_amount, user_entered_amount AS cash, change_due AS `change` FROM payments WHERE transaction_id = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("Error preparing payment query: " . $mysqli->error);
    }
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    
    if (!$payment) {
        die("Payment not found for transaction ID: " . htmlspecialchars($transaction_id));
    }
    
    // Add default values in case fields are null
    $payment['total_amount'] = $payment['total_amount'] ?? '0.00';
    $payment['cash'] = $payment['cash'] ?? '0.00';
    $payment['change'] = $payment['change'] ?? '0.00';
    
    // Fetch schedule details
    $sql_schedule = "
        SELECT s.cinema, s.showtime, s.movie_id, s.ticket_type, tp.price AS ticket_price 
        FROM schedules s 
        JOIN ticket_prices tp ON s.ticket_type = tp.ticket_type 
        WHERE s.id = ?";
    $stmt_schedule = $mysqli->prepare($sql_schedule);
    $stmt_schedule->bind_param("i", $payment['schedule_id']);
    $stmt_schedule->execute();
    $schedule = $stmt_schedule->get_result()->fetch_assoc();
    
    // Fetch movie details
    $sql_movie = "SELECT title FROM movies WHERE id = ?";
    $stmt_movie = $mysqli->prepare($sql_movie);
    $stmt_movie->bind_param("i", $schedule['movie_id']);
    $stmt_movie->execute();
    $movie = $stmt_movie->get_result()->fetch_assoc();
    
    // Fetch user details
    $customer_name = 'Guest';
    if ($payment['user_id']) {
        $sql_user = "SELECT firstname, lastname FROM user WHERE id = ?";
        $stmt_user = $mysqli->prepare($sql_user);
        $stmt_user->bind_param("i", $payment['user_id']);
        $stmt_user->execute();
        $user = $stmt_user->get_result()->fetch_assoc();
        if ($user) {
            $customer_name = $user['firstname'] . ' ' . $user['lastname'];
        }
    }
    
    // Fetch seat numbers
    $sql_seat = "SELECT seat_number FROM seat_purchases WHERE payment_id = ?";
    $stmt_seat = $mysqli->prepare($sql_seat);
    $stmt_seat->bind_param("i", $payment['id']);
    $stmt_seat->execute();
    $seat_numbers = $stmt_seat->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Fetch food purchases
    $sql_food = "SELECT food_item, price, quantity FROM food_purchases WHERE transaction_id = ?";
    $stmt_food = $mysqli->prepare($sql_food);
    $stmt_food->bind_param("s", $transaction_id);
    $stmt_food->execute();
    $food_purchases = $stmt_food->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    $pdf->AddPage();
    
    $html = <<<EOD
<div style="padding: 20px; font-family: Helvetica;">
    <h1 style="color: #663399; font-size: 28px; text-align: center; margin-bottom: 10px;">AuroraBox</h1>
    <div style="border-top: 3px solid #663399; margin: 10px 0;"></div>
    
    <div style="margin: 20px 0;">
        <table width="100%">
            <tr>
                <td>
                    <h2 style="color: #663399; font-size: 18px;">ORDER DETAILS:</h2>
                </td>
                <td style="text-align: right;">
                    <p>TRANSACTION ID: {$transaction_id}</p>
                    <p>DATE OF PURCHASE: {$payment['created_at']}</p>
                </td>
            </tr>
        </table>
        
        <p style="margin: 15px 0;">CUSTOMER NAME: {$customer_name}</p>
        
        <table style="width: 100%; margin: 15px 0;">
            <tr>
                <td style="width: 120px;">MOVIE TITLE:</td>
                <td>{$movie['title']}</td>
            </tr>
            <tr>
                <td>TICKET TYPE:</td>
                <td>{$schedule['ticket_type']}</td>
            </tr>
            <tr>
                <td>CINEMA NO.:</td>
                <td>Cinema {$schedule['cinema']}</td>
            </tr>
            <tr>
                <td>SHOWTIME:</td>
                <td>{$schedule['showtime']}</td>
            </tr>
        </table>

        <h3 style="color: #663399; font-size: 16px;">ADD ONS:</h3>
        <ul>
EOD;

    // Add food purchases with 'P' instead of peso sign
    foreach ($food_purchases as $food) {
        $total_price = $food['price'] * $food['quantity'];
        $html .= "<li>{$food['food_item']} P{$food['price']} x{$food['quantity']} = P{$total_price}</li>";
    }

    $html .= <<<EOD
        </ul>

        <table style="width: 100%; margin: 15px 0;">
            <tr>
                <td style="width: 120px;">TOTAL AMOUNT:</td>
                <td>P{$payment['total_amount']}</td>
            </tr>
            <tr>
                <td>CASH:</td>
                <td>P{$payment['cash']}</td>
            </tr>
            <tr>
                <td>CHANGE:</td>
                <td>P{$payment['change']}</td>
            </tr>
        </table>
    </div>

    <div style="border-top: 1px solid #663399; margin: 20px 0;"></div>
    
    <div style="font-size: 12px; color: #666;">
        <h3>NOTES:</h3>
        <ul>
            <li>PLEASE DOWNLOAD YOUR VOUCHER ON "ACCOUNT" PAGE.</li>
            <li>PRESENT YOUR VOUCHER ON AURORA BOX CINEMAS ON THE DAY OF YOUR BOOKING.</li>
            <li>ONLY THE NAME TRANSACTED ONLINE CAN REDEEM THE TICKET; IF NOT, PLEASE PRESENT AN ID AND AUTHORIZED LETTER FROM THE NAME OF THE BUYER.</li>
        </ul>
    </div>
</div>
EOD;

    // Now log the HTML content after it's created
    error_log("HTML Content Length: " . strlen($html));
    
    try {
        // Write HTML to PDF
        $pdf->writeHTML($html, true, false, true, false, '');
    } catch (Exception $e) {
        error_log("Error writing HTML to PDF: " . $e->getMessage());
        die("Error generating PDF content: " . $e->getMessage());
    }
    
    // Output PDF with error checking
    try {
        $pdf->Output('movie_ticket_' . $transaction_id . '.pdf', 'I');
    } catch (Exception $e) {
        error_log("Error outputting PDF: " . $e->getMessage());
        die("Error generating final PDF: " . $e->getMessage());
    }
    exit();
}

// Check if transaction_id is provided
if (isset($_GET['transaction_id'])) {
    try {
        generateTicketPDF($_GET['transaction_id']);
    } catch (Exception $e) {
        die("Error generating PDF: " . $e->getMessage());
    }
} else {
    die("Transaction ID is required");
}