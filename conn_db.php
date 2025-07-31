<?php
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