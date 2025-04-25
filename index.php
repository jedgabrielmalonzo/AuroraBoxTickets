<?php


// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aurorabox";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT title, image FROM movies LIMIT 4"; 
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuroraBox</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" 
    integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style> <?php include 'CSS/landingpage.css'; ?> </style>
    <style> <?php include 'CSS/navbar-footer.css'; ?> </style>
</head>
<body>
    <div class="bg-image">
        <nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
            <div class="container-fluid">
                <div class="row w-100 align-items-center justify-content-between"> 
                    <div class="col-4 text-center">
                        <div class="navbar-brand">
                        <img src="<?php echo 'images/logo.png'; ?>" alt="Logo">
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <div class="landing-text">
            <h1>Your Ultimate Movie Experience Awaits!</h1>
            <p>Skip the lines and grab your seats in just a few clicks!</p>
            <form action="login.php">
                <button class="btn btn-pill" type="submit">BOOK YOUR TICKETS NOW!</button>
            </form>        
        </div>
    </div>

    <!-- Custom Shape Divider -->
    <div class="custom-shape-divider-top-1730016653">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
        </svg>
    </div>

    <div class="divider"></div>
    <div class="bg-image-home">
        <div class="container-fluid py-5">
            <div class="row justify-content-center">
                <div class="col-12 text-center mb-4">
                    <h1>NOW SHOWING</h1>
                </div>
            </div>
            <div class="row justify-content-center">
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '
                        <!-- Movie Card -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4 d-flex justify-content-center">
                            <div class="card">
                                <div class="card-img">
                                    <img src="' . htmlspecialchars($row['image']) . '" class="card-img-top img-fluid" alt="' . htmlspecialchars($row['title']) . '">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">' . htmlspecialchars($row['title']) . '</h5>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<p>No movies available.</p>';
                }
                ?>
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
                                <span>9876543210</span>
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
                                <li><a href="homepage.html">Home</a></li>
                                <li><a href="moviepage.html">Movies</a></li>
                                <li><a href="eventpage.html">Events and Promos</a></li>
                                <li><a href="aboutus.html">About Us</a></li>
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
                    <div class="col-xl-6 col-lg-6 text-center">
                        <div class="copyright-text">
                            <p>Copyright &copy; 2025, All Right Reserved</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js" integrity="sha384-J7XWcftl8W+XgdIl7gZBaXMokh/W35dV6X8qQ2+AO3Pz9D1t6/7d6a8Pqkz6QJ9O" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0xA4gQ6q8vAayXITdf9xEK2m72hZ5jLQ1Qmm9wQ0HggXBw6z" crossorigin="anonymous"></script>
</body>
</html>

<?php
$conn->close();
?>

<script>
    <?php include('Landingpage.js'); ?>
</script>
</body>
</html>