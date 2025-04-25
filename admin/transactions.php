<?php
session_start();

if (!isset($_SESSION["admin_first_name"])) {
    header("Location: index.php");  // Redirect to login page if not logged in
    exit;
}

// Access admin's name
$first_name = $_SESSION["admin_first_name"];
$last_name = $_SESSION["admin_last_name"];

// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "aurorabox"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search input
$search = isset($_GET['search']) ? $_GET['search'] : '';

// SQL query to fetch transaction history with search functionality
$sql = "
SELECT 
    p.transaction_id,
    p.user_id,
    GROUP_CONCAT(DISTINCT CONCAT(u.firstname, ' ', u.lastname) ORDER BY u.firstname, u.lastname SEPARATOR ', ') AS user_name,
    p.amount AS payment_amount,
    p.payment_status,
    GROUP_CONCAT(DISTINCT sp.seat_number ORDER BY sp.seat_number SEPARATOR ', ') AS seat_numbers,
    s.cinema,
    s.showtime,
    p.created_at AS payment_date,
    GROUP_CONCAT(DISTINCT CONCAT(fp.food_item, ' (x', fp.quantity, ')') ORDER BY fp.food_item SEPARATOR ', ') AS food_items 
FROM 
    payments p
LEFT JOIN 
    seat_purchases sp ON p.id = sp.payment_id
LEFT JOIN 
    schedules s ON sp.schedule_id = s.id
LEFT JOIN 
    food_purchases fp ON p.transaction_id = fp.transaction_id 
LEFT JOIN 
    user u ON p.user_id = u.id  -- Join with user table
WHERE 
    (u.firstname LIKE ? OR u.lastname LIKE ? OR p.transaction_id LIKE ?)  -- Search condition
GROUP BY 
    p.transaction_id, p.user_id, s.cinema, s.showtime, p.created_at
ORDER BY 
    p.created_at DESC;  -- Sort by payment date, most recent first
";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Prepare search placeholders
$searchTerm = "%$search%";  // Wildcards for LIKE
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);  // Bind the parameters
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="CSS/sidebar.css" rel="stylesheet">
    <link href="CSS/dashboard.css" rel="stylesheet">
    <link href="CSS/moviemanage.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <img src="/aurorabox/images/logoadminwhite.png" class="logoadmin">
        <a href="dashboard.php">Dashboard</a>
        <p class="sidebar-p">INTERFACE</p>
        <a href="moviemanagement.php">Movie Management</a>
        <a href="cinemamanagement.php">Cinema Management</a>
        <p class="sidebar-p">REPORTS</p>
        <a href="transactions.php" class="active">Transaction History</a>
        <a href="viewusers.php">View All Users</a>
        <a href="logoutadmin.php" class="sidebar-logout">Log Out</a>
    </div>
    <!-- Main Content -->
    <div class="main-content">
        <div class="admin-name">
            <a>Hello, <?php echo htmlspecialchars($first_name . " " . $last_name); ?>!</a>
        </div>
        <br>
        
        <h2 class="text-center">Transaction History</h2>
        <br>
        <!-- Search Form -->
        <form method="GET" action="" class="mb-4">
            <input type="text" name="search" placeholder="Enter Name or Transaction ID" required class="form-control" style="display:inline-block; width:auto;">
            <input type="submit" value="Search" class="btn btn-primary">
        </form>

        <!-- Display the results -->
        <?php
        if ($result->num_rows > 0) {
            echo "<table class='table table-striped'>
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>User ID</th>
                            <th>User Name</th>
                            <th>Payment Amount</th>
                            <th>Payment Status</th>
                            <th>Seat Numbers</th>
                            <th>Cinema</th>
                            <th>Showtime</th>
                            <th>Food Items</th>
                            <th>Payment Date</th>
                        </tr>
                    </thead>
                    <tbody>";
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row["transaction_id"]) . "</td>
                        <td>" . htmlspecialchars($row["user_id"]) . "</td>
                        <td>" . htmlspecialchars($row["user_name"] ?? 'N/A') . "</td>
                        <td>" . number_format($row["payment_amount"], 2) . "</td>
                        <td>" . htmlspecialchars($row["payment_status"]) . "</td>
                        <td>" . ($row["seat_numbers"] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($row["cinema"]) . "</td>
                        <td>" . htmlspecialchars(date('g:i A, l, F j, Y', strtotime($row["showtime"]))) . "</td>
                        <td>" . htmlspecialchars($row["food_items"] ?? 'No food purchased') . "</td>
                        <td>" . htmlspecialchars(date('g:i A, l, F j, Y', strtotime($row["payment_date"]))) . "</td>
                    </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<div class='alert alert-warning'>No transactions found.</div>";
        }

        $stmt->close();
        $conn->close();
        ?>
    </div>    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>