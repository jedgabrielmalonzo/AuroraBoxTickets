<?php
session_start();

// Check if required POST data is set
if (!isset($_POST['schedule_id'], $_POST['selected_seats'])) {
    header("Location: error.php?message=" . urlencode("Missing information."));
    exit;
}

$schedule_id = $_POST['schedule_id'];
$selected_seats = explode(',', $_POST['selected_seats']);


$mysqli = require __DIR__ . "/database.php";

// Fetch the schedule data
$sql = "SELECT * FROM schedules WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();
$schedule = $result->fetch_assoc();

if (!$schedule) {
    header("Location: error.php?message=" . urlencode("Invalid schedule ID."));
    exit;
}

// Store booking information in session for later use in food selection
$_SESSION['booking_info'] = [
    'schedule_id' => $schedule_id,
    'selected_seats' => $selected_seats,
    'num_tickets' => count($selected_seats),
];

// Redirect to food selection page
header("Location: foodselect.php");
exit;
?>