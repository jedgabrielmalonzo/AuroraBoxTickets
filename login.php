<?php
$is_invalid = false;
if ($_SERVER["REQUEST_METHOD"]==="POST") {
    $mysqli = require __DIR__ . "/database.php";

    $sql = sprintf("SELECT * FROM user
                    WHERE email = '%s'",
                    $mysqli->real_escape_string($_POST["email"]));
    
    $result = $mysqli->query($sql);

    $user = $result->fetch_assoc();

    if ($user) {
        if (password_verify($_POST["password"], $user["password_hash"])) {
            session_start();

            session_regenerate_id();
            
            $_SESSION["user_id"] = $user["id"];

            header("Location: home.php");
            exit;
            
        }
    }
    $is_invalid = true;
}

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

    <style><?php include 'CSS/login.css';?></style>
    <style> <?php include 'CSS/navbar-footer.css'; ?> </style>

</head>
<body>
    <div class="log-bg-image">
        <!-- Navbar stays above the background image and overlay -->
        <nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
            <div class="container-fluid">
                <div class="row w-100 align-items-center justify-content-between"> 
                    <div class="col-4 text-center">
                        <div class="navbar-brand">
                            <a href="/aurorabox/index.php"><img src="/aurorabox/images/logo.png" alt="Logo" class="img-fluid"></a> 
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <div class="sign-up-form">
        <h1 class="log-in-text">WELCOME BACK!</h1>
        <div class="sign-up-card">
            <h3>Log In</h3>
            <form method="post">
                <div class="input-div">
                    <label for="Email">Email</label>
                    <input type="email" name="email" placeholder="Email"
                                value="<?= htmlspecialchars( $_POST["email"] ?? "")?>"> 
                </div>
                <div class="input-div">
                    <label for="Password">Password</label>
                    <input type="password" name="password" placeholder="Password">
                </div>
                
                    <?php if ($is_invalid): ?>
                        <em>Invalid Login</em>
                    <?php endif; ?>
                <button class="btn">Log In</button>
                <p>Don't have account? <a href="signup.php">Sign Up</a></p>
            </div>
    </div>

    
<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js" integrity="sha384-nmDcf8eY73M3PzF+4kA/Z0wHzCk1Y7+/2n/Gzdf4F/MZCBlzOU1TQ7qZftP5ODZ7" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-C8K1m1gq0D/s3h6KcT7wN1Z7tVxB5X8wU4Pp+75tx6VX76LuTft4U6tK0W7kTQ8" crossorigin="anonymous"></script>

<script src="/scripts/LandingPage.js"></script>
</body>
</html>