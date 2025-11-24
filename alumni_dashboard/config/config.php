<?php
// config.php - Database configuration for AlumniPanaon Hub

// Database credentials
define('DB_HOST', 'localhost');       // Database host
define('DB_USER', 'root');            // Database username
define('DB_PASS', '');                // Database password
define('DB_NAME', 'alumni_system');   // Database name

// Create a database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set charset to avoid encoding issues
$conn->set_charset("utf8mb4");
?>
