<?php 

// Database connection
$servername = "sql12.freesqldatabase.com";
$username = "sql12771059";
$password = "RGEHRMkDNL";
$dbname = "sql12771059";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>