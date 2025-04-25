<?php
$is_invalid = false;

// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Include the database connection file
    $mysqli = require __DIR__ . "/../database.php";

    // Query the adminuser table for the email entered
    $sql = sprintf("SELECT * FROM adminuser WHERE email = '%s'", 
                    $mysqli->real_escape_string($_POST["email"]));

    // Execute the query
    $result = $mysqli->query($sql);

    // Fetch the result (the admin record)
    $admin = $result->fetch_assoc();

    // If the admin exists and the password matches
    if ($admin) {
        // Verify the password using password_verify
        if (password_verify($_POST["password"], $admin["password_hash"])) {
            session_start();
            session_regenerate_id();

            // Store the admin ID, first name, and last name in the session
            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_first_name"] = $admin["first_name"];
            $_SESSION["admin_last_name"] = $admin["last_name"];

            // Redirect to the admin dashboard
            header("Location: dashboard.php");
            exit;
        }
    }

    // If login fails
    $is_invalid = true;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>AuB Admin</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="CSS/login.css" rel="stylesheet">
</head>

<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-6 col-sm-4">
                <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="logo-container">
                    <img src="/aurorabox/images/logoadmin.png" class="logo-admin">
                    </div>    
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <form method="post">
                                        
                                        <div class="input-div">
                                            <label for="Email">Email</label>
                                            <input type="email" name="email" placeholder="Email" 
                                                value="<?= htmlspecialchars($_POST["email"] ?? "")?>">
                                        </div>
                                        
                                        <div class="input-div">
                                            <label for="Password">Password</label>
                                            <input type="password" name="password" placeholder="Password">
                                        </div>

                                        <?php if ($is_invalid): ?>
                                            <em>Invalid Login</em>
                                        <?php endif; ?>
                                        
                                        <button class="btn">Log In</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>

</html>
