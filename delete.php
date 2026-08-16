<?php
/**
 * Delete Blog Post Handler
 * POST-only with ownership verification
 */
require_once 'config.php';
require_once 'auth.php';

// Require authentication
requireLogin();

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($postId <= 0) {
    redirectWithError('Invalid post ID');
}

// Verify ownership before delete
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT user_id FROM blogPost WHERE id = ?");
$stmt->bind_param('i', $postId);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    redirectWithError('Post not found');
}

if (!isPostOwner($post['user_id'])) {
    redirectWithError('You do not have permission to delete this post');
}

// Delete the post
$stmt = $conn->prepare("DELETE FROM blogPost WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $postId, $post['user_id']);

if ($stmt->execute()) {
    $stmt->close();
    redirectWithSuccess('Post deleted successfully');
} else {
    $stmt->close();
    redirectWithError('Failed to delete post');
}
?>