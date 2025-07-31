<?php
<<<<<<< HEAD
// conn_db.php - Database connection file for InfinityFree hosting

// Database configuration for InfinityFree (from your control panel)
$servername = "sql108.infinityfree.com";
$username = "if0_39401290";
$password = "oR1NlfxVH0x";  // Your actual database password from the screenshot
$dbname = "if0_39401290_db";
$port = 3306; // MySQL port from your screenshot

// Create connection using both variable names for compatibility
$conn = mysqli_connect($servername, $username, $password, $dbname, $port);
$mysqli = $conn; // For compatibility with payment.php

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8 (recommended for better character support)
mysqli_set_charset($conn, "utf8");

// Optional: You can uncomment the line below for debugging
// echo "Connected successfully to database: " . $dbname;
?>
=======
    $mysqli= new mysqli("localhost","root","","foodcave");
    if($mysqli->connect_errno){
        header("location:db_error.php");
        exit(1);
    }

    define('SITE_ROOT',realpath(dirname(__FILE__)));
    date_default_timezone_set('Asia/Kolkata');
?>    
>>>>>>> 5027eac0c6b4220983dc702d727e608a440f1685
