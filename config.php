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
define('APP_NAME', 'Inkbloom');
define('APP_TAGLINE', 'thoughts, notes & things that matter');
define('APP_URL', 'http://localhost:8000'); // Change this when hosting
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

// -----------------------------------------------------------------------
// Social Login (OAuth 2.0) settings
// -----------------------------------------------------------------------
// To enable real "Continue with Google / GitHub" sign-in, create OAuth
// credentials with each provider and paste them below. Until you do,
// the buttons stay visible and interactive but will show a friendly
// message explaining that an admin needs to finish setup — they will
// never silently fail.
//
// Google:  https://console.cloud.google.com/apis/credentials
//          Authorized redirect URI -> APP_URL . '/oauth.php?provider=google'
// GitHub:  https://github.com/settings/developers
//          Authorization callback URL -> APP_URL . '/oauth.php?provider=github'
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');
define('GITHUB_CLIENT_ID', '');
define('GITHUB_CLIENT_SECRET', '');

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