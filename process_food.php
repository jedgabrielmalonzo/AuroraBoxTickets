<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$mysqli = require __DIR__ . "/database.php";

// Check if booking info exists in the session
if (!isset($_SESSION['booking_info'])) {
    header("Location: error.php?message=" . urlencode("No booking information found."));
    exit;
}

// Initialize food selection array
$food_selection = [];
$total_food_price = 0;

// Process food items from the POST request
if (isset($_POST['food']) && is_array($_POST['food'])) {
    foreach ($_POST['food'] as $food_id => $data) {
        if (isset($data['quantity']) && is_numeric($data['quantity'])) {
            $quantity = (int)$data['quantity'];
            if ($quantity > 0 && $quantity <= 5) { // Limit max quantity to 5
                // Fetch food details from the database
                $sql_food = "SELECT food_item, food_price FROM foods WHERE id = ?";
                $stmt = $mysqli->prepare($sql_food);
                $stmt->bind_param("i", $food_id);
                
                if ($stmt->execute()) {
                    $result_food = $stmt->get_result()->fetch_assoc();
                    
                    if ($result_food) {
                        $food_item = $result_food['food_item'];
                        $food_price = $result_food['food_price'];
                        $total = $quantity * $food_price;

                        // Store food selection with item name
                        $food_selection[] = [
                            'item_name' => $food_item,
                            'quantity' => $quantity,
                            'price' => $food_price,
                            'total' => $total
                        ];
                        $total_food_price += $total;
                    } else {
                        // Handle case where food item is not found
                        header("Location: error.php?message=" . urlencode("Food item not found."));
                        exit;
                    }
                } else {
                    // Handle database execution error
                    header("Location: error.php?message=" . urlencode("Database query failed."));
                    exit;
                }
            }
        }
    }
}

// Store food selection and total price in session
$_SESSION['food_selection'] = $food_selection;
$_SESSION['total_food_price'] = $total_food_price;

// Redirect to payment page
header("Location: payment.php");
exit;
?>