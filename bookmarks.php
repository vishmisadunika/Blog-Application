<?php
require_once 'config.php';
require_once 'auth.php';

requireLogin();

$pageTitle = 'My Bookmarks';
$userId = getCurrentUserId();

$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT bp.id, bp.title, bp.content, bp.cover_image, bp.excerpt, bp.topic_id,
           bp.views, bp.reading_time, bp.created_at,
           u.username, u.avatar,
           t.name as topic_name, t.slug as topic_slug, t.icon as topic_icon
    FROM bookmark bm
    JOIN blogPost bp ON bm.post_id = bp.id
    JOIN user u ON bp.user_id = u.id
    LEFT JOIN topic t ON bp.topic_id = t.id
    WHERE bm.user_id = ?
    ORDER BY bm.created_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once 'includes/header.php';
?>

<div class="page-header animate-fade-in" style="text-align: center; margin: 2rem 0;">
    <h1 class="script-font" style="font-size: 2.5rem; color: var(--primary);">my bookmarks 🔖</h1>
    <p style="color: var(--text-light);">Articles you've saved to read again.</p>
</div>

<?php if (empty($posts)): ?>
    <div class="empty-state animate-fade-in" style="text-align: center; padding: 4rem 1rem;">
        <h2 class="script-font" style="color: var(--primary); font-size: 2rem;">No bookmarks yet ♡</h2>
        <p style="color: var(--text-light); margin-bottom: 1.5rem;">Tap the 🏷 icon on any article to save it here.</p>
        <a href="index.php?view=all" class="btn btn-primary">Browse articles</a>
    </div>
<?php else: ?>
    <div class="blog-grid">
        <?php foreach ($posts as $post): ?>
            <article class="blog-card animate-fade-in">
                <?php if (!empty($post['cover_image'])): ?>
                    <a href="view.php?id=<?php echo $post['id']; ?>">
                        <img src="<?php echo escape($post['cover_image']); ?>" alt="<?php echo escape($post['title']); ?>" class="blog-card-image" loading="lazy">
                    </a>
                <?php endif; ?>
                <div class="blog-card-body">
                    <?php if (!empty($post['topic_name'])): ?>
                        <a href="index.php?topic=<?php echo escape($post['topic_slug']); ?>" class="blog-card-topic">
                            <?php echo $post['topic_icon'] . ' ' . escape($post['topic_name']); ?>
                        </a>
                    <?php endif; ?>
                    <h2 class="blog-card-title">
                        <a href="view.php?id=<?php echo $post['id']; ?>">
                            <?php echo escape($post['title']); ?>
                        </a>
                    </h2>
                    <p class="blog-card-excerpt">
                        <?php
                        $excerptText = !empty($post['excerpt']) ? $post['excerpt'] : generateExcerpt(strip_tags(markdownToHtml($post['content'])), 120);
                        echo escape($excerptText);
                        ?>
                    </p>
                    <div class="blog-card-meta">
                        <div class="blog-card-author">
                            <img src="<?php echo getUserAvatar($post['username']); ?>" alt="<?php echo escape($post['username']); ?>" style="width: 24px; height: 24px; border-radius: 50%;">
                            <span><?php echo escape($post['username']); ?></span>
                        </div>
                        <span>·</span>
                        <span><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                        <span>·</span>
                        <span><?php echo getReadingTime($post['content']); ?> min read</span>
                    </div>
                    <div class="blog-card-footer">
                        <a href="view.php?id=<?php echo $post['id']; ?>" class="btn btn-ghost btn-sm">Read more →</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
