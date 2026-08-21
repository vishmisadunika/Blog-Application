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
 * Simple Markdown to HTML converter
 * @param string $text
 * @return string
 */
function markdownToHtml($text) {
    // Escape HTML first
    $html = escape($text);

    // Images: ![alt](url) -> <img src="url" alt="alt" class="content-image">
    $html = preg_replace('/\!\[([^\]]+)\]\(([^)]+)\)/', '<img src="$2" alt="$1" class="content-image">', $html);

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

    // Links (ignore image links which were processed first)
    $html = preg_replace('/(?<!\!)\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>', $html);

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
    
    // Clean up img inside p if needed
    $html = preg_replace('/<p>(<img[^>]+>)<\/p>/', '$1', $html);

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

/**
 * Calculate reading time in minutes (avg 200 words/min)
 * @param string $content
 * @return int
 */
function getReadingTime($content) {
    $wordCount = str_word_count(strip_tags($content));
    $minutes = ceil($wordCount / 200);
    return $minutes < 1 ? 1 : $minutes;
}

/**
 * Get number of likes for a post
 * @param int $postId
 * @return int
 */
function getLikeCount($postId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM post_like WHERE post_id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int)$row['count'];
}

/**
 * Check if a user has liked a post
 * @param int $postId
 * @param int $userId
 * @return bool
 */
function isLikedByUser($postId, $userId) {
    if (!$userId) return false;
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id FROM post_like WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $postId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $hasLiked = $result->num_rows > 0;
    $stmt->close();
    return $hasLiked;
}

/**
 * Check if a user has bookmarked a post
 * @param int $postId
 * @param int $userId
 * @return bool
 */
function isBookmarkedByUser($postId, $userId) {
    if (!$userId) return false;
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id FROM bookmark WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $postId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $hasBookmarked = $result->num_rows > 0;
    $stmt->close();
    return $hasBookmarked;
}

/**
 * Get all topics
 * @return array
 */
function getTopics() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM topic ORDER BY name ASC");
    $topics = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $topics[] = $row;
        }
    }
    return $topics;
}

/**
 * Get single topic by ID
 * @param int $id
 * @return array|null
 */
function getTopicById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM topic WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $topic = $result->fetch_assoc();
    $stmt->close();
    return $topic ? $topic : null;
}

/**
 * Get user avatar or generate default
 * @param string $username
 * @return string
 */
function getUserAvatar($username) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT avatar FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row && !empty($row['avatar'])) {
        return escape($row['avatar']);
    }
    
    // Generate default using UI Avatars
    $name = urlencode($username);
    return "https://ui-avatars.com/api/?name={$name}&background=f0e6d8&color=8b6f5c&rounded=true&bold=true";
}

/**
 * Check whether OAuth credentials have been configured for a provider
 * @param string $provider 'google' or 'github'
 * @return bool
 */
function isOAuthConfigured($provider) {
    if ($provider === 'google') {
        return GOOGLE_CLIENT_ID !== '' && GOOGLE_CLIENT_SECRET !== '';
    }
    if ($provider === 'github') {
        return GITHUB_CLIENT_ID !== '' && GITHUB_CLIENT_SECRET !== '';
    }
    return false;
}

/**
 * Increment view count for a post
 * @param int $postId
 */
function incrementViews($postId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE blogPost SET views = views + 1 WHERE id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $stmt->close();
}
?>