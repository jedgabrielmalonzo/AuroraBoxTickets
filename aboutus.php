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
    <title>AuroraBox - My Account</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Stylesheets -->
    <link rel="stylesheet" href="CSS/navbar.css">
    <link rel="stylesheet" href="CSS/aboutpage.css">
    <!-- FontAwesome for Icons -->
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

<!-- Carousel Section -->
<div id="aboutCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">

        <!-- Slide 1: Main Slide with Logo and Description -->
        <div class="carousel-item active">
            <section class="home section" id="home">
                <div class="home__container container grid text-center" style="background-image: url('images/about-bg.jpg'); background-size: cover;">
                    <div class="home__background-text">
                        <h1 class="home__background-title">ABOUT</h1>
                    </div>
                    <div class="home__data mt-5">
                        <div class="home__logo-container mb-3">
                            <img src="images/logo.png" alt="Logo" class="home__img">
                        </div>
                        <p class="home__description">
                            At Aurorabox, we understand that every moviegoer is unique, with different preferences and expectations. That's why we have developed a ticketing platform that caters to your individual tastes and desires. Whether you're a fan of action-packed blockbusters, heartwarming romantic comedies, spine-chilling horror films, or thought-provoking dramas, we have it all, carefully curated to provide an unforgettable cinematic experience.
                        </p>
                    </div>
                </div>
            </section>
        </div>
        
<!-- Slide 2: Google Maps Location (Embedded Iframe) -->
<div class="carousel-item">
    <section class="find-us">
        <div class="find-us__background"></div> <!-- Background fade effect -->
        <div class="find-us__container">
            <div class="find-us__text">
                <h1 class="find-us__title">Find Us Here</h1>
                <p class="find-us__description">
                    We are located in the heart of Quezon City, Philippines. Visit us for an unforgettable experience and explore our offerings. Check out our location on the map below!
                </p>
            </div>
            <div class="find-us__map">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3860.5351543442066!2d121.05884476129992!3d14.625536385804821!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b796aecb8763%3A0xaa026ea7350f82e7!2sTechnological%20Institute%20of%20the%20Philippines%20-%20Quezon%20City!5e0!3m2!1sen!2sph!4v1731422017628!5m2!1sen!2sph" 
                    width="100%" height="500" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>
</div>

<!-- Slide 3: Text Left, Image Right -->
<!-- <div class="carousel-item">
    <section class="home section" id="home">
        <div class="home__container container grid text-center">
            <div class="home__background-text">
                <h1 class="home__background-title">OUR TEAM</h1>
            </div>
            <div class="row">
                <div class="col-md-6 d-flex align-items-center">S
                    <div class="home__data">
                        <h1 class="home__title">Aurorabox</h1>
                        <h3 class="home__subtitle">meet our team</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div> -->

    </div>

    <!-- Carousel Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#aboutCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#aboutCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/main.js"></script>
</body>
</html>