<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$mysqli = require __DIR__ . "/database.php";
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "aurorabox"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the search query from the GET request
$searchQuery = isset($_GET['query']) ? $_GET['query'] : '';
$searchQuery = $conn->real_escape_string($searchQuery);

// SQL query to search movies by title
$sql = "SELECT * FROM movies WHERE title LIKE '%$searchQuery%'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Box - Movies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="CSS/movies.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary" id=home>
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


<div class="spacing"></div>

<div class="movies-text">
  <h1>MOVIES</h1>
</div>
<!-- Cards Section -->
<div class="movie-card-container">
    <div class="container my-5">
        <div class="row">
            <?php
            if ($result === false) {
                echo "SQL Error: " . $conn->error; // Debugging output
            } elseif ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4 d-flex justify-content-center">';
                    echo '<div class="card movie-card">';
                    echo '<img src="' . htmlspecialchars($row['image']) . '" class="card-img-top" alt="' . htmlspecialchars($row['title']) . '" onclick="showMovieModal(\'' . htmlspecialchars($row['title']) . '\', \'' . htmlspecialchars($row['description']) . '\', \'' . htmlspecialchars($row['trailer']) . '\', \'' . htmlspecialchars($row['runtime']) . '\', \'' . htmlspecialchars($row['release_year']) . '\', \'' . htmlspecialchars($row['image']) . '\', \'' . htmlspecialchars($row['rating']) . '\', ' . htmlspecialchars($row['id']) . ')">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . htmlspecialchars($row['title']) . '</h5>';
                    echo '</div></div></div>';
                }
            } else {
                echo "No movies found.";
            }
            $conn->close();
            ?>
        </div>
    </div>
</div>


    <div class="modal fade" id="ViewMovie" tabindex="-1" aria-labelledby="ViewMovieLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content custom-modal">
            <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="modal-body">
                <div class="trailer-container">
                    <div class="iframe-container">
                        <iframe id="movie-trailer" src="" title="Trailer" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
                <div class="movie-details">
                    <div class="row">
                        <div class="col-md-4">
                            <img id="movie-poster" src="" alt="Movie Poster" class="movie-poster img-fluid">
                        </div>
                        <div class="col-md-8">
                            <h4 id="movie-title">Movie Title</h4>
                            <p>
                                <span id="movie-year">2023</span>  ·  
                                <span id="movie-rating">This is the Rating for the Movie</span>  ·  
                                <span id="movie-runtime">120 min</span>
                            </p>
                            <p id="movie-description">This is a brief description of the movie.</p>
                            <a href="#" id="buy-ticket-button" class="btn-buy-ticket btn btn-primary">Buy Ticket</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    // Function to show the movie details in the modal and load the trailer
    function showMovieModal(title, description, trailer, runtime, year, image, rating, movieId) {
    // Convert the trailer URL to the embed format
    const videoId = new URL(trailer).searchParams.get("v");
    const embedUrl = `https://www.youtube.com/embed/${videoId}`;

    document.getElementById("movie-title").textContent = title;
    document.getElementById("movie-description").textContent = description;
    document.getElementById("movie-trailer").src = embedUrl;
    document.getElementById("movie-runtime").textContent = runtime;
    document.getElementById("movie-year").textContent = year;
    document.getElementById("movie-poster").src = image;
    document.getElementById("movie-rating").textContent = rating;

    // Update the 'Buy Ticket' button link dynamically
    const buyTicketButton = document.getElementById("buy-ticket-button");
    buyTicketButton.href = `schedule.php?movie_id=${movieId}`;  // Set the movie_id in the URL

    var myModal = new bootstrap.Modal(document.getElementById("ViewMovie"), {});
    myModal.show();
    }

    // Stop the trailer video when the modal is hidden
    const modal = document.getElementById('ViewMovie');
    const trailer = document.getElementById('movie-trailer');

    modal.addEventListener('hide.bs.modal', () => {
        trailer.src = ''; // Stop the video by clearing the src
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

     <!-- Footer -->

     <footer class="footer-section">
        <div class="container">
          <div class="footer-cta pt-5 pb-5">
            <div class="row">
              <div class="col-xl-4 col-md-4 mb-30">
                <div class="single-cta">
                  <i class="fas fa-map-marker-alt"></i>
                  <div class="cta-text">
                    <h4>Find us</h4>
                    <span>1010 Avenue, sw 54321, chandigarh</span>
                  </div>
                </div>
              </div>
              <div class="col-xl-4 col-md-4 mb-30">
                <div class="single-cta">
                  <i class="fas fa-phone"></i>
                  <div class="cta-text">
                    <h4>Call us</h4>
                    <span>9876543210 0</span>
                  </div>
                </div>
              </div>
              <div class="col-xl-4 col-md-4 mb-30">
                <div class="single-cta">
                  <i class="far fa-envelope-open"></i>
                  <div class="cta-text">
                    <h4>Mail us</h4>
                    <span>mail@info.com</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="footer-content pt-5 pb-5">
            <div class="row">
              <div class="col-xl-4 col-lg-4 mb-50">
                <div class="footer-widget">
                  <div class="footer-logo">
                    <a href="index.html"><img src="images/logo.png" class="img-fluid" alt="logo"></a>
                  </div>
                  <div class="footer-text">
                    <p>One-Stop movie ticketing site, bringing the magic of Aurora Cinemas straight to you!</p>
                  </div>
                  <div class="footer-social-icon">
                    <span>Follow us</span>
                    <a href="#"><i class="fab fa-facebook-f facebook-bg"></i></a>
                    <a href="#"><i class="fab fa-twitter twitter-bg"></i></a>
                    <a href="#"><i class="fab fa-google-plus-g google-bg"></i></a>
                  </div>
                </div>
              </div>
              <div class="col-xl-4 col-lg-4 col-md-6 mb-30">
                <div class="footer-widget">
                  <div class="footer-widget-heading">
                    <h3>Useful Links</h3>
                  </div>
                  <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="movies.php">Movies</a></li>
                    <li><a href="events.php">Events and Promos</a></li>
                    <li><a href="aboutus.php">About Us</a></li>
                  </ul>
                </div>
              </div>
              <div class="col-xl-4 col-lg-4 col-md-6 mb-50">
                <div class="footer-widget">
                  <div class="footer-widget-heading">
                    <h3>Subscribe</h3>
                  </div>
                  <div class="footer-text mb-25">
                    <p>Don’t miss to subscribe to our new feeds, kindly fill the form below.</p>
                  </div>
                  <div class="subscribe-form">
                    <form action="#">
                      <input type="text" placeholder="Email Address">
                      <button><i class="fab fa-telegram-plane"></i></button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="copyright-area">
          <div class="container">
            <div class="row">
              <div class="col-xl-6 col-lg-6 text-center"> <!-- Removed text-lg-left here -->
                <div class="copyright-text">
                  <p>Copyright &copy; 2025, All Right Reserved</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
      </footer>
</body>
</html>