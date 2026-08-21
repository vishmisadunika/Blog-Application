<?php
require_once 'config.php';
require_once 'auth.php';

$conn = getDBConnection();

// Fetch topics with post counts
$query = "
    SELECT t.*, COUNT(b.id) as post_count 
    FROM topic t 
    LEFT JOIN blogPost b ON t.id = b.topic_id 
    GROUP BY t.id 
    ORDER BY t.name ASC
";
$result = $conn->query($query);
$topics = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }
}

// Fetch popular posts (most likes or views - we'll use likes + views formula or just simple views/likes if post_like is empty initially. Let's do a left join on post_like)
$popularQuery = "
    SELECT b.id, b.title, b.views, u.username,
           (SELECT COUNT(*) FROM post_like WHERE post_id = b.id) as like_count
    FROM blogPost b
    JOIN user u ON b.user_id = u.id
    ORDER BY (like_count * 2 + b.views) DESC
    LIMIT 5
";
$popularResult = $conn->query($popularQuery);
$popularPosts = [];
if ($popularResult) {
    while ($row = $popularResult->fetch_assoc()) {
        $popularPosts[] = $row;
    }
}

require_once 'includes/header.php';
?>

<div class="topics-page container">
    <div class="page-header animate-fade-in">
        <h1 class="page-title script-font">explore by Topics ✿</h1>
        <p>Find stories and notes on what matters most.</p>
    </div>
    
    <div class="topics-layout">
        <div class="topics-grid">
            <?php foreach ($topics as $topic): ?>
                <a href="index.php?topic=<?php echo urlencode($topic['slug']); ?>" class="topic-card animate-fade-in">
                    <div class="topic-card-icon"><?php echo escape($topic['icon']); ?></div>
                    <h3 class="topic-card-name"><?php echo escape($topic['name']); ?></h3>
                    <span class="topic-card-count"><?php echo $topic['post_count']; ?> article<?php echo $topic['post_count'] != 1 ? 's' : ''; ?></span>
                </a>
            <?php endforeach; ?>
            
            <?php if (empty($topics)): ?>
                <div class="no-results">No topics found.</div>
            <?php endif; ?>
        </div>
        
        <aside class="topics-sidebar">
            <div class="sidebar-card">
                <h3 class="script-font">community love ♡</h3>
                <p class="sidebar-desc">The most loved articles by readers like you.</p>
                <div class="popular-posts-list">
                    <?php foreach ($popularPosts as $post): ?>
                        <a href="view.php?id=<?php echo $post['id']; ?>" class="popular-post-item">
                            <h4><?php echo escape($post['title']); ?></h4>
                            <div class="meta">
                                <span>by <?php echo escape($post['username']); ?></span>
                                <span>·</span>
                                <span><?php echo $post['like_count']; ?> likes</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    
                    <?php if (empty($popularPosts)): ?>
                        <div class="no-results">Check back soon for popular posts!</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="sidebar-card newsletter-card">
                <h3 class="script-font">stay in the loop</h3>
                <p>Get new posts, notes, and little reminders in your inbox.</p>
                <form class="newsletter-form" id="newsletter-form-sidebar">
                    <input type="email" name="email" placeholder="your email" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </aside>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
