<?php

if (empty($_POST["firstname"])){
    die("This field is Required");
}

if (empty($_POST["lastname"])){
    die("This field is Required");
}

if ( ! filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    die ("Valid Email is Required");
}

if (strlen($_POST["password"]) < 8){
    die("Password must be at least 8 characters");
}

if  ( ! preg_match("/[a-z]/i", $_POST["password"])){
    die("Password must contain at least one letter");
}

if  ( ! preg_match("/[0-9]/", $_POST["password"])){
    die("Password must contain at least one number");
}

if ($_POST["password"] !== $_POST["password_confirmation"]) {
    die("Passwords must match");
}

$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

$mysqli = require __DIR__ . "/database.php";

$sql = "INSERT INTO user (firstname, lastname, email, password_hash)
        VALUES (?, ?, ?, ?)";

$stmt = $mysqli->stmt_init();

if ( ! $stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
}

$stmt->bind_param("ssss",
                    $_POST["firstname"],
                    $_POST["lastname"],        
                    $_POST["email"],
                    $password_hash);

if ($stmt->execute()) {

    header("Location: signup-success.php");
    exit;

} else {
    if ($mysqli->errno === 1062) {
        die("email already taken!");
    } else {
        die($mysqli->error . "" . $mysqli->errno); 
    }
   

}

                    

