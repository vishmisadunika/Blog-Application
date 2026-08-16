<?php
/**
 * Logout Handler
 * Destroys session and redirects to home
 */
require_once 'config.php';
require_once 'auth.php';

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to home
header('Location: index.php');
exit();
?>