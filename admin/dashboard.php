<?php
session_start();

if (!isset($_SESSION["admin_first_name"])) {
    header("Location: index.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "aurorabox"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variable
$message = "";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Delete movie
    if (isset($_POST['delete_movie'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Movie deleted successfully!";
        } else {
            $message = "Error deleting movie: " . $stmt->error;
        }
        $stmt->close();
    }

    // Update movie
    if (isset($_POST['update_movie'])) {
        $id = intval($_POST['id']);
        $title = $conn->real_escape_string($_POST['title']);
        $image = $conn->real_escape_string($_POST['image']);
        $trailer = $conn->real_escape_string($_POST['trailer']);
        $runtime = $conn->real_escape_string($_POST['runtime']);
        $description = $conn->real_escape_string($_POST['description']);
        $release_year = intval($_POST['release_year']);
        $rating = $conn->real_escape_string($_POST['rating']);

        if (empty($title) || empty($image) || empty($trailer) || empty($description) || empty($release_year) || empty($rating)) {
            $message = "Error: All fields are required.";
        } else {
            $stmt = $conn->prepare("UPDATE movies SET title=?, image=?, trailer=?, runtime=?, description=?, release_year=?, rating=? WHERE id=?");
            $stmt->bind_param("ssssiisi", $title, $image, $trailer, $runtime, $description, $release_year, $rating, $id);
            
            if ($stmt->execute()) {
                $message = "Movie updated successfully!";
            } else {
                $message = "Error updating movie: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch total sales from the transactions table
$salesQuery = "SELECT SUM(amount) AS total_sales FROM payments";
$salesResult = $conn->query($salesQuery);
$totalSales = $salesResult->fetch_assoc()['total_sales'] ?? 0; 

// Fetch user count from the users table
$usersQuery = "SELECT COUNT(DISTINCT id) AS user_count FROM user"; 
$usersResult = $conn->query($usersQuery);
$totalUsers = $usersResult->fetch_assoc()['user_count'] ?? 0; 

// Fetch all movies
$sql = "SELECT * FROM movies";
$result = $conn->query($sql);
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
    <!-- Toggle Button for Sidebar -->
    <button class="hamburger" id="sidebarToggle">
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
    </button>
    
    <div class="sidebar" id="sidebar">
        <img src="/aurorabox/images/logoadminwhite.png" class="logoadmin">
        <a href="dashboard.php">Dashboard</a>
        <p class="sidebar-p">INTERFACE</p>
        <a href="moviemanagement.php">Movie Management</a>
        <a href="cinemamanagement.php">Cinema Management</a>
        <p class="sidebar-p">REPORTS</p>
        <a href="transactions.php">Transaction History</a>
        <a href="viewusers.php">View All Users</a>
        <a href="logoutadmin.php" class="sidebar-logout">Log Out</a>
    </div>
    
    <div class="main-content">
        <div class="admin-name">
            <a>Hello, <?php echo htmlspecialchars($_SESSION["admin_first_name"] . " " . $_SESSION["admin_last_name"]); ?>!</a>
        </div>
        <br>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="dashboard-cards row">
            <div class="card col-12 col-md-4">
                <h2>Movies Showing</h2>
                <p><?php echo $result->num_rows; ?></p>
            </div>
            <div class="card col-12 col-md-4">
                <h2>Total Sales</h2>
                <p>₱<?php echo number_format($totalSales, 2); ?></p>
            </div>
            <div class="card col-12 col-md-4">
                <h2>No. of Users</h2>
                <p><?php echo $totalUsers; ?></p>
            </div>
        </div>

        <h3 class="mt-5">Manage Movies</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Image</th>
                    <th>Trailer</th>
                    <th>Runtime</th>
                    <th>Release Year</th>
                    <th>Rating</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td><img src='" . htmlspecialchars($row['image']) . "' alt='" . htmlspecialchars($row['title']) . "' class='img-fluid' style='max-width: 100px;'></td>";
                        echo "<td><a href='" . htmlspecialchars($row['trailer']) . "' target='_blank'>Watch</a></td>";
                        echo "<td>" . htmlspecialchars($row['runtime']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['release_year']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['rating']) . "</td>";
                        echo "<td>
                                <form method='POST' style='display:inline-block'>
                                    <input type='hidden' name='id' value='" . $row['id'] . "'>
                                    <button type='submit' name='delete_movie' class='btn btn-danger btn-sm'>Delete</button>
                                </form>
                                <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#updateModal' data-id='" . $row['id'] . "' 
                                data-title='" . htmlspecialchars($row['title']) . "' 
                                data-image='" . htmlspecialchars($row['image']) . "' 
                                data-trailer='" . htmlspecialchars($row['trailer']) . "' 
                                data-runtime='" . htmlspecialchars($row['runtime']) . "' 
                                data-description='" . htmlspecialchars($row['description']) . "' 
                                data-release_year='" . $row['release_year'] . "' 
                                data-rating='" . htmlspecialchars($row['rating']) . "'>Edit</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No movies found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Edit Movie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <input type="hidden" name="id" id="update-id">
                        <div class="mb-3">
                            <label for="update-title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="update-title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="update-image" class="form-label">Image URL</label>
                            <input type="text" class="form-control" id="update-image" name="image" required>
                        </div>
                        <div class="mb-3">
                            <label for="update-trailer" class="form-label">Trailer URL</label>
                            <input type="text" class="form-control" id="update-trailer" name="trailer" required>
                        </div>
                        <div class="mb-3">
                            <label for="update-runtime" class="form-label">Runtime</label>
                            <input type="text" class="form-control" id="update-runtime" name="runtime" required>
                        </div>
                        <div class="mb-3">
                            <label for="update-description" class="form-label">Description</label>
                            <textarea class="form-control" id="update-description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="update-release_year" class="form-label">Release Year</label>
                            <input type="number" class="form-control" id="update-release_year" name="release_year" required>
                        </div>
                        <div class="mb-3">
                            <label for="update-rating" class="form-label">Rating</label>
                            <input type="text" class="form-control" id="update-rating" name="rating" required>
                        </div>
                        <button type="submit" name="update_movie" class="btn btn-primary">Update Movie</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const updateModal = document.getElementById('updateModal');
        updateModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('update-id').value = button.getAttribute('data-id');
            document.getElementById('update-title').value = button.getAttribute('data-title');
            document.getElementById('update-image').value = button.getAttribute('data-image');
            document.getElementById('update-trailer').value = button.getAttribute('data-trailer');
            document.getElementById('update-runtime').value = button.getAttribute('data-runtime');
            document.getElementById('update-description').value = button.getAttribute('data-description');
            document.getElementById('update-release_year').value = button.getAttribute('data-release_year');
            document.getElementById('update-rating').value = button.getAttribute('data-rating');
        });

        // Toggle sidebar
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            document.querySelector('.main-content').classList.toggle('expanded');
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>