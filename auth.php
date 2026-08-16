<?php
/**
 * Authentication and Authorization Helper Functions
 */

/**
 * Check if user is currently logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged-in user's ID
 * @return int|null
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Get current logged-in user's username
 * @return string|null
 */
function getCurrentUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

/**
 * Get current logged-in user's role
 * @return string|null
 */
function getCurrentUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

/**
 * Redirect to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }
}

/**
 * Check if the current user owns the given blog post
 * @param int $postUserId - The user_id from the blog post
 * @return bool
 */
function isPostOwner($postUserId) {
    return isLoggedIn() && getCurrentUserId() === (int)$postUserId;
}

/**
 * Redirect with error message
 * @param string $message
 * @param string $redirectUrl
 */
function redirectWithError($message, $redirectUrl = 'index.php') {
    $_SESSION['error_message'] = $message;
    header('Location: ' . $redirectUrl);
    exit();
}

/**
 * Redirect with success message
 * @param string $message
 * @param string $redirectUrl
 */
function redirectWithSuccess($message, $redirectUrl = 'index.php') {
    $_SESSION['success_message'] = $message;
    header('Location: ' . $redirectUrl);
    exit();
}

/**
 * Get and clear flash messages (error/success)
 * @param string $type - 'error' or 'success'
 * @return string|null
 */
function getFlashMessage($type) {
    $key = $type . '_message';
    if (isset($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return null;
}

/**
 * Sanitize output for HTML display (prevent XSS)
 * @param string $text
 * @return string
 */
function escape($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Simple Markdown to HTML converter (basic implementation)
 * For production, consider using a library like Parsedown
 * @param string $text
 * @return string
 */
function markdownToHtml($text) {
    // Escape HTML first
    $html = escape($text);

    // Headers
    $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);

    // Bold
    $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);

    // Italic
    $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html);

    // Inline code
    $html = preg_replace('/`(.+?)`/', '<code>$1</code>', $html);

    // Links
    $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>', $html);

    // Unordered lists
    $html = preg_replace('/^\- (.+)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*<\/li>\n?)+/s', '<ul>$0</ul>', $html);

    // Ordered lists
    $html = preg_replace('/^\d+\. (.+)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*<\/li>\n?)+/s', '<ol>$0</ol>', $html);

    // Line breaks (double newline = paragraph)
    $html = preg_replace('/\n\n+/', '</p><p>', $html);
    $html = '<p>' . $html . '</p>';

    // Clean up empty paragraphs
    $html = preg_replace('/<p>\s*<\/p>/', '', $html);
    $html = preg_replace('/<p>(<h[1-6]>)/', '$1', $html);
    $html = preg_replace('/(<\/h[1-6]>)<\/p>/', '$1', $html);
    $html = preg_replace('/<p>(<ul>)/', '$1', $html);
    $html = preg_replace('/(<\/ul>)<\/p>/', '$1', $html);
    $html = preg_replace('/<p>(<ol>)/', '$1', $html);
    $html = preg_replace('/(<\/ol>)<\/p>/', '$1', $html);

    return $html;
}

/**
 * Generate excerpt from content
 * @param string $content
 * @param int $length
 * @return string
 */
function generateExcerpt($content, $length = 150) {
    $text = strip_tags($content);
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}
?>