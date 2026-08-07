<?php
/**
 * Database Configuration
 * Update these values for your local or hosted environment
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'blog_app');

// Application settings
define('APP_NAME', 'BlogApp');
define('APP_URL', 'http://localhost:8000'); // Change this when hosting

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection function
function getDBConnection() {
    static $conn = null;

    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $conn->set_charset("utf8mb4");
    }

    return $conn;
}

// Close database connection
function closeDBConnection() {
    global $conn;
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>