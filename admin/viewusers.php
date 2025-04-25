<?php

// Include the database connection
$mysqli = require __DIR__ . "/../database.php";

// Start the session at the top of your page
session_start();

if (!isset($_SESSION["admin_first_name"])) {
    header("Location: index.php");  // Redirect to login page if not logged in
    exit;
}

// Access admin's name
$first_name = $_SESSION["admin_first_name"];
$last_name = $_SESSION["admin_last_name"];

// Pagination configuration
$users_per_page = 10; // Set how many users you want to display per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $users_per_page;

// Initialize variables
$search_by_id = isset($_GET['search_id']) ? trim($_GET['search_id']) : '';
$search_by_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$result = null; // Initialize result variable

if ($search_by_id) {
    // Search specifically by ID
    $stmt = $mysqli->prepare("SELECT id, firstname, lastname, email, password_hash FROM user WHERE id = ?");
    $stmt->bind_param('i', $search_by_id);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($search_by_name) {
    // Search by name or email
    $search_query = " WHERE firstname LIKE ? OR lastname LIKE ? OR email LIKE ?";
    $stmt = $mysqli->prepare("SELECT id, firstname, lastname, email, password_hash FROM user" . $search_query . " LIMIT ? OFFSET ?");
    $like_search = "%" . $search_by_name . "%";
    $stmt->bind_param('ssssi', $like_search, $like_search, $like_search, $users_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Original query without search
    $sql = "SELECT id, firstname, lastname, email, password_hash FROM user LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $users_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Count total users for pagination
$total_users_query = "SELECT COUNT(*) as total FROM user" . ($search_by_name ? $search_query : "");
$total_users_stmt = $mysqli->prepare($total_users_query);
if ($search_by_name) {
    $total_users_stmt->bind_param('sss', $like_search, $like_search, $like_search);
}
$total_users_stmt->execute();
$total_users = $total_users_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_users / $users_per_page);

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
    <link href="CSS/viewusers.css" rel="stylesheet">
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
        <a href="transactions.php">Transaction History</a>
        <a href="viewusers.php">View All Users</a>
        <a href="logoutadmin.php" class="sidebar-logout">Log Out</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="admin-name">
            <a>Hello, <?php echo htmlspecialchars($first_name . " " . $last_name); ?>!</a>
        </div>
        <div id="alertContainer"></div>
        <h2 class="text-center">Users Manager</h2>
        
        <!-- Search Bar -->
        <div class="mb-3 d-flex justify-content-center">
            <!-- Search by ID -->
            <form method="GET" action="" class="d-flex me-2">
                <input type="text" id="searchInput" name="search_id" class="form-control me-2" placeholder="Search by ID" value="<?php echo htmlspecialchars($search_by_id); ?>" style="width: 500px;">
                <button type="submit" class="btn btn-primary">Search ID</button>
            </form>

            <!-- Search by Name or Email -->
            <form method="GET" action="" class="d-flex">
                <input type="text" id="searchInputName" name="search_name" class="form-control me-2" placeholder="Search by Name or Email" value="<?php echo htmlspecialchars($search_by_name); ?>" style="width: 500px;">
                <button type="submit" class="btn btn-primary">Search Name/Email</button>
            </form>
        </div>

        <!-- Back Button -->
        <?php if ($search_by_id || $search_by_name): ?>
            <div class="mb-3 d-flex justify-content-center">
                <a href="viewusers.php" class="btn btn-secondary">Back to All Users</a>
            </div>
        <?php endif; ?>

        <table class="table table-bordered" id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Password Hash</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr data-id='" . $row['id'] . "'>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td class='firstname'>" . htmlspecialchars($row['firstname']) . "</td>";
                        echo "<td class='lastname'>" . htmlspecialchars($row['lastname']) . "</td>";
                        echo "<td class='email'>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['password_hash']) . "</td>";
                        echo "<td>";
                        echo "<button class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#editUserModal' 
                            data-id='" . $row['id'] . "' 
                            data-firstname='" . htmlspecialchars($row['firstname']) . "' 
                            data-lastname='" . htmlspecialchars($row['lastname']) . "' 
                            data-email='" . htmlspecialchars($row['email']) . "'>Edit</button> ";
                        echo "<button class='btn btn-danger btn-sm deleteUserBtn' data-id='" . $row['id'] . "'>Delete</button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    // Display a single row indicating no users were found
                    echo "<tr><td colspan='6' class='text-center'>No users found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <?php if (empty($search_by_name) && empty($search_by_id)): ?>
            <div class="d-flex justify-content-center">
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo $current_page - 1; ?>" class="btn btn-secondary me-2">Previous</a>
                <?php endif; ?>

                <?php
                // Determine the start and end page numbers
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);

                // Show the first page
                if ($start_page > 1) {
                    echo '<a href="?page=1" class="btn btn-light">1</a>';
                    if ($start_page > 2) {
                        echo '<span class="btn btn-light disabled">...</span>';
                    }
                }

                // Show the current range of page buttons
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search_name=<?php echo urlencode($search_by_name); ?>" class="btn <?php echo ($i === $current_page) ? 'btn-primary' : 'btn-light'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <!-- Show the last page -->
                <?php if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span class="btn btn-light disabled">...</span>';
                    }
                    echo '<a href="?page=' . $total_pages . '" class="btn btn-light">' . $total_pages . '</a>';
                } ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo $current_page + 1; ?>" class="btn btn-secondary ms-2">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal for Editing User -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" method="POST">
                        <input type="hidden" id="userId" name="id">
                        <div class="mb-3">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Modal Event to populate fields
        var editUserModal = document.getElementById('editUserModal');
        editUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var firstname = button.getAttribute('data-firstname');
            var lastname = button.getAttribute('data-lastname');
            var email = button.getAttribute('data-email');

            // Populate modal form fields
            var modalBody = editUserModal.querySelector('.modal-body');
            modalBody.querySelector('#userId').value = id;
            modalBody.querySelector('#firstname').value = firstname;
            modalBody.querySelector('#lastname').value = lastname;
            modalBody.querySelector('#email').value = email;
        });

        // Handle form submission with AJAX
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            var formData = new FormData(this);
            
            fetch('databaseactions/edituser.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())  // Parse the JSON response
            .then(data => {
                var alertContainer = document.getElementById('alertContainer');
                alertContainer.innerHTML = '';

                if (data.success) {
                    var successAlert = document.createElement('div');
                    successAlert.classList.add('alert', 'alert-success');
                    successAlert.textContent = data.message; // Display success message
                    alertContainer.appendChild(successAlert);

                    // Update the row in the table with new data
                    var row = document.querySelector("tr[data-id='" + data.user.id + "']");
                    row.querySelector(".firstname").textContent = data.user.firstname;
                    row.querySelector(".lastname").textContent = data.user.lastname;
                    row.querySelector(".email").textContent = data.user.email;
                } else {
                    var errorAlert = document.createElement('div');
                    errorAlert.classList.add('alert', 'alert-danger');
                    errorAlert.textContent = data.message; // Display error message
                    alertContainer.appendChild(errorAlert);
                }
            })
            .catch(error => {
                alert('Error updating user');
                console.error(error);
            });
        });

        // Handle user deletion via AJAX using event delegation
        document.querySelector('.main-content').addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('deleteUserBtn')) {
                var userId = e.target.getAttribute('data-id');
                
                var confirmation = confirm("Are you sure you want to delete this user? This action cannot be undone.");
                if (confirmation) {
                    var formData = new FormData();
                    formData.append('id', userId);

                    fetch('databaseactions/deleteuser.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        var alertContainer = document.getElementById('alertContainer');
                        alertContainer.innerHTML = '';

                        if (data.success) {
                            var successAlert = document.createElement('div');
                            successAlert.classList.add('alert', 'alert-success');
                            successAlert.textContent = data.message;
                            alertContainer.appendChild(successAlert);

                            var row = document.querySelector("tr[data-id='" + userId + "']");
                            if (row) {
                                row.remove(); // Remove the row from the table
                            }
                        } else {
                            var errorAlert = document.createElement('div');
                            errorAlert.classList.add('alert', 'alert-danger');
                            errorAlert.textContent = data.message;
                            alertContainer.appendChild(errorAlert);
                        }
                    })
                    .catch(error => {
                        alert('Error deleting user');
                        console.error(error);
                    });
                }
            }
        });
    </script>
</body>
</html>