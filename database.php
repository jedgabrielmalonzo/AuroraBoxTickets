<?php

$host = "localhost";
$dbname = "aurorabox";
$username = "root";
$password = "";

$mysqli = new mysqli (hostname: $host, 
                    username: $username, 
                    password: $password, 
                    database: $dbname);

                    mysqli_report(0);

if ($mysqli->connect_errno) {
    die("Connection error: ". $mysqli->connect_error);
}

return $mysqli;