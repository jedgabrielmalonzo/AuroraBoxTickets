<?php
$servername = "localhost";
$username = "root"; // default XAMPP username
$password = ""; // default XAMPP password
$dbname = "aurorabox"; // updated to use the new database

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handling add, edit, and delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add Movie
    if (isset($_POST['add_movie'])) {
        $title = $_POST['title'];
        $image = $_POST['image'];
        $trailer = $_POST['trailer'];
        $runtime = $_POST['runtime'];
        $description = $_POST['description'];
        $release_year = $_POST['release_year'];
        $rating = $_POST['rating']; // Keep as VARCHAR

        $sql = "INSERT INTO movies (title, image, trailer, runtime, description, release_year, rating) 
                VALUES ('$title', '$image', '$trailer', '$runtime', '$description', '$release_year', '$rating')";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='alert alert-success'>New movie added successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $sql . "<br>" . $conn->error . "</div>";
        }
    }
    // Update Movie
    elseif (isset($_POST['update_movie'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $image = $_POST['image'];
        $trailer = $_POST['trailer'];
        $runtime = $_POST['runtime'];
        $description = $_POST['description'];
        $release_year = $_POST['release_year'];
        $rating = $_POST['rating']; // Keep as VARCHAR

        $sql = "UPDATE movies SET title='$title', image='$image', trailer='$trailer', runtime='$runtime', 
                description='$description', release_year='$release_year', rating='$rating' WHERE id='$id'";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='alert alert-success'>Movie updated successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $sql . "<br>" . $conn->error . "</div>";
        }
    }
    // Delete Movie
    elseif (isset($_POST['delete_movie'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM movies WHERE id='$id'";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='alert alert-success'>Movie deleted successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $sql . "<br>" . $conn->error . "</div>";
        }
    }
}

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
    <style>
        body {
            background-color: #f8f9fa;
            color: #9b29e8;
        }
        h2, h3 {
            color: #9b29e8;
        }
        .table img {
            max-height: 50px;
            border-radius: 5px;
        }
        .modal-content {
            border-radius: 10px;
        }
        .btn-primary, .btn-danger, .btn-warning {
            border-radius: 5px;
        }
        .btn-primary {
            background-color: #9b29e8;
        }

    </style>
</head>
<body>

    <div class="container mt-5">
        <h2 class="text-center">Movie Maintenance</h2>
 <!-- Form to Add New Movie -->
<h3 class="mt-4">Add Movie</h3>
<form method="POST" class="mb-4">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="image" class="form-label">Image URL</label>
            <input type="text" class="form-control" id="image" name="image" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="trailer" class="form-label">Trailer URL</label>
            <input type="text" class="form-control" id="trailer" name="trailer" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="runtime" class="form-label">Runtime</label>
            <input type="text" class="form-control" id="runtime" name="runtime" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="release_year" class="form-label">Release Year</label>
            <input type="number" class="form-control" id="release_year" name="release_year" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="rating" class="form-label">Rating</label>
            <input type="text" class="form-control" id="rating" name="rating" required>
        </div>
    </div>
    <button type="submit" name="add_movie" class="btn btn-primary">Add Movie</button>
</form>
        <!-- Display Existing Movies -->
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
                    <th>Rating</th> <!-- New column for Rating -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['title'] . "</td>";
                        echo "<td><img src='" . $row['image'] . "' alt='" . $row['title'] . "'></td>";
                        echo "<td><a href='" . $row['trailer'] . "' target='_blank'>Watch</a></td>";
                        echo "<td>" . $row['runtime'] . "</td>";
                        echo "<td>" . $row['release_year'] . "</td>";
                        echo "<td>" . $row['rating'] . "</td>"; // Display the rating
                        echo "<td>
                                <form method='POST' style='display:inline-block'>
                                    <input type='hidden' name='id' value='" . $row['id'] . "'>
                                    <button type='submit' name='delete_movie' class='btn btn-danger btn-sm'>Delete</button>
                                </form>
                                <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#updateModal' 
                                        data-id='" . $row['id'] . "' data-title='" . $row['title'] . "' 
                                        data-image='" . $row['image'] . "' data-trailer='" . $row['trailer'] . "' 
                                        data-runtime='" . $row['runtime'] . "' data-description='" . $row['description'] . "' 
                                        data-release_year='" . $row['release_year'] . "' data-rating='" . $row['rating'] . "'>Edit</button>
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

    <!-- Modal for Editing Movie -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Edit Movie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
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
                            <input type="text" class="form-control" id="update-rating" name="rating" required> <!-- Changed to text -->
                        </div>
                        <button type="submit" name="update_movie" class="btn btn-primary">Update Movie</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pre-fill the modal with movie details for editing
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
            document.getElementById('update-rating').value = button.getAttribute('data-rating'); // New line for rating
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>