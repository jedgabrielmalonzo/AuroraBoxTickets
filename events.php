<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

$mysqli = require __DIR__ . "/database.php";
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]); // Bind user ID
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuroraBox - Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/events.css">
    <link rel="stylesheet" href="CSS/navbar.css">
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
    
    <!-- Promo Section -->
    <div class="promo" style="background-image: url('images/events/cinemabg.png');">
        <header>
            <img src="images/logo.png" alt="Logo" class="promo-logo">
        </header>
    
        <!-- Main Banner Section -->
        <section class="banner">
            <h1>Book a Cinema</h1>
            <p class="promo-text">For booking, visit www.AuroraBox.com - Promos and events</p>
        </section>
        <div class="button-container">

        </div>
    </div>

    <!-- Promo Boxes -->
    <div class="promo-cards">
        <div class="promo-card" data-bs-toggle="modal" data-bs-target="#christmasModal">
            <div class="promo-box" style="background-image: url('images/events/grinch.jpg');">
                <h3>Christmas Promo</h3>
                <p>Exclusive gifts and meals for the family this Christmas!</p>
            </div>
        </div>
        <div class="promo-card" data-bs-toggle="modal" data-bs-target="#sundayModal">
            <div class="promo-box" style="background-image: url('images/events/FAM1.jpg');">
                <h3>Sunday Funday</h3>
                <p>Enjoy a special family bundle at a discount!</p>
            </div>
        </div>
        <div class="promo-card" data-bs-toggle="modal" data-bs-target="#birthdayModal">
            <div class="promo-box" style="background-image: url('images/events/bday.jpg');">
                <h3>Happy Birthday</h3>
                <p>Celebrate with us! Free food and tickets.</p>
            </div>
        </div>
    </div>

    <!-- Christmas Promo Modal -->
<div class="modal fade" id="christmasModal" tabindex="-1" aria-labelledby="christmasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="christmasModalLabel">Christmas Promo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4>Exclusive Christmas Special</h4>
                <p>This holiday season, treat your family to the best movie experience with exclusive Christmas gifts, meals, and discounted movie tickets. Perfect for the whole family!</p>
                
               

                <div class="row mt-3">
                    <div class="col-md-12">
                        <h5>Christmas Bundle Includes:</h5>
                        <ul>
                            <li>Get a free Christmas meal with every movie ticket purchased for the entire</li>
                            <li>enjoy a gift bag with exclusive Christmas items.</li>
                            <li>Have a chance to be one of the lucky winner of the AuroraBox upcoming Lucky draw.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sunday Promo Modal -->
<div class="modal fade" id="sundayModal" tabindex="-1" aria-labelledby="sundayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sundayModalLabel">Sunday Funday</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4>Sunday Funday Special</h4>
                <p>Every Sunday, we offer a special family bundle including 4 movie tickets, popcorn, and a drink for only $400! Bring your family and make it a fun day out.</p>
                
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h5>Sunday Funday Includes:</h5>
                        <ul>
                            <li>4 Movie Tickets</li>
                            <li>Popcorn and Drinks</li>
                            <li>Special Sunday Gift</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Birthday Promo Modal -->
<div class="modal fade" id="birthdayModal" tabindex="-1" aria-labelledby="birthdayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="birthdayModalLabel">Happy Birthday Promo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4>Celebrate Your Birthday with Us!</h4>
                <p>Join us for a special birthday celebration! Enjoy a complimentary birthday cake and a special gift for the birthday guest when you book a party of 10 or more.</p>
                
              

                <div class="row mt-3">
                    <div class="col-md-12">
                        <h5>Birthday Package Includes:</h5>
                        <ul>
                            <li>Complimentary Birthday Cake</li>
                            <li>Special Gift for the Birthday Guest</li>
                            <li>Discounted Movie Tickets for Guests</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Link to external JavaScript file -->
    <script src="/scripts/homepage.js"></script>
</body>
</html>
