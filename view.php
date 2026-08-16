<?php
/**
 * Single Blog Post View
 */
require_once 'config.php';
require_once 'auth.php';

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($postId <= 0) {
    header('Location: index.php');
    exit();
}

// Fetch the post with author info
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT bp.id, bp.title, bp.content, bp.created_at, bp.updated_at,
           u.id as user_id, u.username
    FROM blogPost bp
    JOIN user u ON bp.user_id = u.id
    WHERE bp.id = ?
");
$stmt->bind_param('i', $postId);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    $pageTitle = 'Post Not Found';
    require_once 'includes/header.php';
    ?>
    <div class="empty-state">
        <h2>Post Not Found</h2>
        <p>The blog post you're looking for doesn't exist or has been removed.</p>
        <a href="index.php" class="btn btn-primary">Back to Home</a>
    </div>
    <?php
    require_once 'includes/footer.php';
    exit();
}

$pageTitle = $post['title'];
$isOwner = isLoggedIn() && isPostOwner($post['user_id']);

// Convert markdown to HTML
$renderedContent = markdownToHtml($post['content']);

require_once 'includes/header.php';
?>

<article class="blog-single">
    <header class="blog-single-header">
        <h1><?php echo escape($post['title']); ?></h1>

        <div class="blog-single-meta">
            <div class="meta-item">
                <span class="meta-label">Author</span>
                <span class="meta-value"><?php echo escape($post['username']); ?></span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Published</span>
                <span class="meta-value"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
            </div>
            <?php if ($post['updated_at'] !== $post['created_at']): ?>
                <div class="meta-item">
                    <span class="meta-label">Updated</span>
                    <span class="meta-value"><?php echo date('F j, Y', strtotime($post['updated_at'])); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($isOwner): ?>
            <div class="blog-single-actions">
                <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-outline">Edit</a>
                <a href="delete.php?id=<?php echo $post['id']; ?>"
                   class="btn btn-danger"
                   onclick="return confirm('Are you sure you want to delete this post?');">
                    Delete
                </a>
            </div>
        <?php endif; ?>
    </header>

    <div class="blog-single-content">
        <?php echo $renderedContent; ?>
    </div>

    <footer class="blog-single-footer">
        <a href="index.php" class="btn btn-outline">&larr; Back to All Posts</a>

        <?php if (isLoggedIn() && !$isOwner): ?>
            <p class="footer-note">You can only edit or delete posts you created.</p>
        <?php endif; ?>
    </footer>
</article>

<?php require_once 'includes/footer.php'; ?>