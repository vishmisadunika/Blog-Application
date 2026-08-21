<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to bookmark posts.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit();
}

$postId = (int)$_POST['post_id'];
$userId = getCurrentUserId();
$conn = getDBConnection();

// Check if post exists
$stmt = $conn->prepare("SELECT id FROM blogPost WHERE id = ?");
$stmt->bind_param("i", $postId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Post not found.']);
    $stmt->close();
    exit();
}
$stmt->close();

if (isBookmarkedByUser($postId, $userId)) {
    // Unbookmark
    $stmt = $conn->prepare("DELETE FROM bookmark WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $postId, $userId);
    $stmt->execute();
    $stmt->close();
    $bookmarked = false;
} else {
    // Bookmark
    $stmt = $conn->prepare("INSERT INTO bookmark (post_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $postId, $userId);
    $stmt->execute();
    $stmt->close();
    $bookmarked = true;
}

echo json_encode(['success' => true, 'bookmarked' => $bookmarked]);
?>
