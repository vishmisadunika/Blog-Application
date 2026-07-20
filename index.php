<?php
/**
 * Home Page - Blog List
 * Displays all blog posts with preview
 */
require_once 'config.php';
require_once 'auth.php';

$pageTitle = 'Home';

// Fetch all blog posts with author info
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT bp.id, bp.title, bp.content, bp.created_at, bp.updated_at,
           u.id as user_id, u.username
    FROM blogPost bp
    JOIN user u ON bp.user_id = u.id
    ORDER BY bp.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1>Latest Blogs</h1>
    <p class="page-subtitle">Discover stories and ideas from our community</p>
</div>

<?php if (empty($posts)): ?>
    <div class="empty-state">
        <h2>No blogs yet</h2>
        <p>Be the first to share your story!</p>
        <?php if (isLoggedIn()): ?>
            <a href="create.php" class="btn btn-primary">Write Your First Post</a>
        <?php else: ?>
            <a href="register.php" class="btn btn-primary">Join Now</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="blog-grid">
        <?php foreach ($posts as $post): ?>
            <article class="blog-card">
                <div class="blog-card-header">
                    <h2 class="blog-card-title">
                        <a href="view.php?id=<?php echo $post['id']; ?>">
                            <?php echo escape($post['title']); ?>
                        </a>
                    </h2>
                </div>

                <div class="blog-card-meta">
                    <span class="author">by <?php echo escape($post['username']); ?></span>
                    <span class="date"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                </div>

                <div class="blog-card-excerpt">
                    <?php
                    // Generate plain text excerpt from markdown content
                    $plainText = strip_tags(markdownToHtml($post['content']));
                    echo escape(generateExcerpt($plainText, 180));
                    ?>
                </div>

                <div class="blog-card-footer">
                    <a href="view.php?id=<?php echo $post['id']; ?>" class="btn btn-outline btn-sm">Read More</a>

                    <?php if (isLoggedIn() && isPostOwner($post['user_id'])): ?>
                        <div class="blog-card-actions">
                            <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm">Edit</a>
                            <a href="delete.php?id=<?php echo $post['id']; ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this post?');">
                                Delete
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>