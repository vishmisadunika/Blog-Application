<?php
require_once 'config.php';
require_once 'auth.php';

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($postId <= 0) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();
// Increment views
incrementViews($postId);

// Fetch post with author and topic info
$stmt = $conn->prepare("
    SELECT bp.id, bp.title, bp.content, bp.cover_image, bp.created_at, bp.updated_at, bp.views, bp.topic_id,
           u.id as user_id, u.username, u.avatar,
           t.name as topic_name, t.slug as topic_slug, t.icon as topic_icon
    FROM blogPost bp
    JOIN user u ON bp.user_id = u.id
    LEFT JOIN topic t ON bp.topic_id = t.id
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
    <div class="empty-state animate-fade-in" style="text-align: center; padding: 4rem 1rem;">
        <h2 class="script-font" style="color: var(--primary); font-size: 2rem;">Post Not Found ♡</h2>
        <p style="color: var(--text-light); margin-bottom: 1.5rem;">The blog post you're looking for doesn't exist or has been removed.</p>
        <a href="index.php" class="btn btn-primary">Back to Home</a>
    </div>
    <?php
    require_once 'includes/footer.php';
    exit();
}

$pageTitle = $post['title'];
$isOwner = isLoggedIn() && isPostOwner($post['user_id']);
$likeCount = getLikeCount($postId);
$isLiked = isLoggedIn() && isLikedByUser($postId, getCurrentUserId());
$isBookmarked = isLoggedIn() && isBookmarkedByUser($postId, getCurrentUserId());

$renderedContent = markdownToHtml($post['content']);

// Fetch related posts
$relatedPosts = [];
if (!empty($post['topic_id'])) {
    $stmt = $conn->prepare("
        SELECT bp.id, bp.title, bp.cover_image, bp.excerpt, bp.created_at, bp.content,
               u.username, u.avatar
        FROM blogPost bp
        JOIN user u ON bp.user_id = u.id
        WHERE bp.topic_id = ? AND bp.id != ?
        ORDER BY bp.created_at DESC LIMIT 3
    ");
    $stmt->bind_param('ii', $post['topic_id'], $postId);
    $stmt->execute();
    $relatedPosts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

require_once 'includes/header.php';
?>

<article class="blog-single animate-fade-in" style="max-width: 800px; margin: 0 auto; padding: 2rem 0;">
    <a href="index.php" class="btn btn-ghost btn-sm" style="margin-bottom:1rem; display: inline-block;">← back to all articles</a>
    
    <?php if (!empty($post['cover_image'])): ?>
        <img src="<?php echo escape($post['cover_image']); ?>" alt="Cover" class="blog-single-cover" style="width: 100%; border-radius: var(--radius-lg); margin-bottom: 2rem; max-height: 400px; object-fit: cover;">
    <?php endif; ?>
    
    <?php if (!empty($post['topic_name'])): ?>
        <a href="index.php?topic=<?php echo escape($post['topic_slug']); ?>" class="blog-single-topic" style="display: inline-flex; align-items: center; gap: 0.5rem; background: var(--accent-light); color: var(--primary-dark); padding: 0.5rem 1rem; border-radius: 20px; text-decoration: none; font-size: 0.9rem; font-weight: 500; margin-bottom: 1rem;">
            <span><?php echo $post['topic_icon']; ?></span>
            <span><?php echo escape($post['topic_name']); ?></span>
        </a>
    <?php endif; ?>
    
    <header class="blog-single-header" style="margin-bottom: 2rem;">
        <h1 style="font-family: 'Playfair Display', serif; font-size: 3rem; color: var(--text); line-height: 1.2; margin-bottom: 1.5rem;"><?php echo escape($post['title']); ?></h1>
    </header>
    
    <div class="blog-single-author" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 3rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border);">
        <img src="<?php echo getUserAvatar($post['username']); ?>" alt="<?php echo escape($post['username']); ?>" style="width: 48px; height: 48px; border-radius: 50%;">
        <div class="author-info" style="display: flex; flex-direction: column;">
            <span class="author-name" style="font-weight: bold; color: var(--text);"><?php echo escape($post['username']); ?></span>
            <span class="author-meta" style="color: var(--text-light); font-size: 0.9rem;">
                <?php echo date('M d, Y', strtotime($post['created_at'])); ?> · <?php echo getReadingTime($post['content']); ?> min read · <?php echo $post['views']; ?> views
            </span>
        </div>
        
        <?php if ($isOwner): ?>
            <div style="margin-left:auto; display:flex; gap:0.5rem;">
                <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                <a href="delete.php?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="blog-single-content" style="font-family: 'DM Sans', sans-serif; font-size: 1.1rem; line-height: 1.8; color: var(--text); margin-bottom: 4rem;">
        <?php echo $renderedContent; ?>
    </div>
    
    <div class="blog-actions-bar" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); margin-bottom: 3rem;">
        <div class="action-group" style="display: flex; align-items: center; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <button class="btn btn-icon like-btn <?php echo $isLiked ? 'liked' : ''; ?>" data-post-id="<?php echo $post['id']; ?>" id="like-btn" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: <?php echo $isLiked ? 'var(--danger)' : 'var(--text-light)'; ?>;">
                    <?php echo $isLiked ? '♥' : '♡'; ?>
                </button>
                <span class="like-count" id="like-count" style="font-weight: 500; color: var(--text);"><?php echo $likeCount; ?></span>
            </div>
            
            <button class="btn btn-icon bookmark-btn <?php echo $isBookmarked ? 'bookmarked' : ''; ?>" data-post-id="<?php echo $post['id']; ?>" id="bookmark-btn" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: <?php echo $isBookmarked ? 'var(--primary)' : 'var(--text-light)'; ?>;" title="Bookmark">
                <?php echo $isBookmarked ? '🔖' : '🏷'; ?>
            </button>
        </div>
        
        <div class="action-group" style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size:0.85rem; color:var(--text-light); margin-right: 0.5rem;">Share this article ♡</span>
            <button class="btn btn-icon share-btn" data-action="twitter" title="Share on Twitter" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 50%; width: 36px; height: 36px; cursor: pointer;">𝕏</button>
            <button class="btn btn-icon share-btn" data-action="facebook" title="Share on Facebook" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 50%; width: 36px; height: 36px; cursor: pointer;">f</button>
            <button class="btn btn-icon share-btn" data-action="copy" title="Copy link" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 50%; width: 36px; height: 36px; cursor: pointer;">🔗</button>
        </div>
    </div>
    
    <?php if (!empty($post['topic_name'])): ?>
    <div class="blog-tags" style="margin-bottom: 4rem;">
        <a href="index.php?topic=<?php echo escape($post['topic_slug']); ?>" class="tag" style="display: inline-block; background: var(--bg-card); border: 1px solid var(--border); color: var(--text-light); padding: 0.25rem 0.75rem; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">#<?php echo escape($post['topic_name']); ?></a>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($relatedPosts)): ?>
    <section class="related-posts animate-fade-in" style="background: var(--bg-card); padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow);">
        <h3 class="script-font" style="font-size: 2rem; color: var(--primary); margin-bottom: 1.5rem; text-align: center;">you might also like ♡</h3>
        <div class="blog-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem;">
            <?php foreach ($relatedPosts as $rPost): ?>
                <article class="blog-card" style="border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden;">
                    <?php if (!empty($rPost['cover_image'])): ?>
                        <a href="view.php?id=<?php echo $rPost['id']; ?>">
                            <img src="<?php echo escape($rPost['cover_image']); ?>" alt="Cover" style="width: 100%; height: 120px; object-fit: cover;">
                        </a>
                    <?php endif; ?>
                    <div style="padding: 1rem;">
                        <h4 style="font-family: 'Playfair Display', serif; font-size: 1.1rem; margin-bottom: 0.5rem; line-height: 1.3;">
                            <a href="view.php?id=<?php echo $rPost['id']; ?>" style="color: var(--text); text-decoration: none;"><?php echo escape($rPost['title']); ?></a>
                        </h4>
                        <div style="font-size: 0.8rem; color: var(--text-light); display: flex; align-items: center; gap: 0.5rem;">
                            <img src="<?php echo getUserAvatar($rPost['username']); ?>" style="width: 16px; height: 16px; border-radius: 50%;">
                            <span><?php echo escape($rPost['username']); ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</article>

<?php require_once 'includes/footer.php'; ?>