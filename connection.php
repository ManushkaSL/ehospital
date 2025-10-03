<?php
// Database configuration for eHospital System
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'SQL_Database_edoc');  // Using 'edoc' to match your SQL file

// Create connection
$database = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($database->connect_error) {
    die("Connection failed: " . $database->connect_error);
}

// Set charset to utf8mb4 for better compatibility
$database->set_charset("utf8mb4");
?>