<?php
require_once 'config.php';
require_once 'auth.php';

$pageTitle = 'Home';

$conn = getDBConnection();

// Handle topic filter
$topicFilter = isset($_GET['topic']) ? trim($_GET['topic']) : '';
$viewFilter = isset($_GET['view']) ? trim($_GET['view']) : '';

// Build query based on filters
$query = "
    SELECT bp.id, bp.title, bp.content, bp.cover_image, bp.excerpt, bp.topic_id,
           bp.views, bp.reading_time, bp.created_at, bp.updated_at,
           u.id as user_id, u.username, u.avatar,
           t.name as topic_name, t.slug as topic_slug, t.icon as topic_icon,
           (SELECT COUNT(*) FROM post_like WHERE post_id = bp.id) as like_count
    FROM blogPost bp
    JOIN user u ON bp.user_id = u.id
    LEFT JOIN topic t ON bp.topic_id = t.id
";

if ($topicFilter) {
    $query .= " WHERE t.slug = ?";
    $stmt = $conn->prepare($query . " ORDER BY bp.created_at DESC");
    $stmt->bind_param('s', $topicFilter);
} elseif ($viewFilter === 'popular') {
    $query .= " ORDER BY like_count DESC, bp.views DESC";
    $stmt = $conn->prepare($query);
} else {
    $query .= " ORDER BY bp.created_at DESC";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch topics for topic bar
$topics = getTopics();

// Stats
$totalPosts = count($posts);
// Get total users
$usersResult = $conn->query("SELECT COUNT(*) as cnt FROM user");
$totalUsers = $usersResult ? $usersResult->fetch_assoc()['cnt'] : 0;
$totalTopics = count($topics);
// Simple readers estimate
$viewsResult = $conn->query("SELECT SUM(views) as total FROM blogPost");
$totalViews = $viewsResult ? ($viewsResult->fetch_assoc()['total'] ?? 0) : 0;

require_once 'includes/header.php';
?>

<?php if (!$topicFilter && !$viewFilter): ?>
<section class="hero animate-fade-in">
    <div class="hero-content">
        <span class="hero-badge">✿ welcome to my little corner</span>
        <h1 class="hero-title">thoughts, notes & things that matter.</h1>
        <p class="hero-desc">Real life. Messy mind. Big dreams.<br>Here I write the things I feel, think and figure out along the way.</p>
        <div class="hero-buttons">
            <a href="index.php?view=all" class="btn btn-primary">Read articles</a>
            <?php if (isLoggedIn()): ?>
                <a href="create.php" class="btn btn-outline">Start writing</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-outline">Start writing</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero-visual">
        <div class="hero-deco hero-deco-1" aria-hidden="true">
            <svg viewBox="0 0 40 40"><path fill="var(--accent)" d="M20 2c2 6 10 8 10 8s-8 2-10 8c-2-6-10-8-10-8s8-2 10-8z"/></svg>
        </div>
        <div class="hero-deco hero-deco-2" aria-hidden="true">
            <svg viewBox="0 0 40 40"><path fill="var(--accent-pink)" d="M20 34S4 24.5 4 13.8C4 8 8.4 4 13.6 4c2.7 0 5.2 1.3 6.4 3.4C21.2 5.3 23.7 4 26.4 4 31.6 4 36 8 36 13.8 36 24.5 20 34 20 34z"/></svg>
        </div>
        <div class="hero-deco hero-deco-3" aria-hidden="true">
            <svg viewBox="0 0 40 40"><circle cx="20" cy="20" r="6" fill="var(--primary-light)"/></svg>
        </div>
        <div class="hero-deco hero-deco-4" aria-hidden="true">
            <svg viewBox="0 0 40 40"><path fill="var(--accent)" d="M6 30c8-14 20-14 28-24-2 12-2 24-14 28-6 2-11-1-14-4z"/></svg>
        </div>

        <svg class="hero-illustration" viewBox="0 0 420 400" xmlns="http://www.w3.org/2000/svg">
            <circle cx="210" cy="190" r="180" fill="var(--accent-light)"/>
            <ellipse cx="210" cy="352" rx="150" ry="14" fill="var(--border)" opacity="0.5"/>

            <!-- desk -->
            <rect x="40" y="266" width="340" height="20" rx="6" fill="var(--accent-rose)"/>
            <rect x="60" y="286" width="14" height="60" fill="var(--border)"/>
            <rect x="346" y="286" width="14" height="60" fill="var(--border)"/>

            <!-- laptop -->
            <g>
                <rect x="150" y="188" width="120" height="80" rx="6" fill="var(--bg-card)" stroke="var(--border)" stroke-width="3"/>
                <rect x="160" y="198" width="100" height="52" rx="2" fill="var(--accent-light)"/>
                <rect x="168" y="208" width="55" height="6" rx="3" fill="var(--primary-light)"/>
                <rect x="168" y="220" width="70" height="6" rx="3" fill="var(--border)"/>
                <rect x="168" y="232" width="40" height="6" rx="3" fill="var(--border)"/>
                <path d="M138 266 L282 266 L270 288 L150 288 Z" fill="var(--primary)"/>
            </g>

            <!-- mug -->
            <g>
                <path d="M292 232c0 16 -12 26 -26 26s-26 -10 -26 -26 v-4h52z" fill="var(--bg-card)" stroke="var(--primary)" stroke-width="3"/>
                <path d="M292 232c8 0 14 5 14 12s-6 12 -14 12" fill="none" stroke="var(--primary)" stroke-width="3"/>
                <path d="M245 214c2 -6 -3 -8 -1 -14" stroke="var(--text-light)" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                <path d="M258 214c2 -6 -3 -8 -1 -14" stroke="var(--text-light)" stroke-width="2.5" fill="none" stroke-linecap="round"/>
            </g>

            <!-- notebook -->
            <g>
                <rect x="96" y="240" width="56" height="42" rx="4" fill="var(--bg-card)" stroke="var(--border)" stroke-width="2.5" transform="rotate(-8 124 261)"/>
                <path d="M108 253c3-4 9-4 9 0 0-4 6-4 9 0 0 5-9 11-9 11s-9-6-9-11z" fill="var(--accent-pink)" transform="rotate(-8 124 261)"/>
            </g>

            <!-- plant -->
            <g>
                <path d="M330 268c-4 -30 4 -46 4 -46s10 16 4 46z" fill="var(--primary-light)"/>
                <path d="M334 260c10 -24 26 -30 26 -30s-2 20 -26 30z" fill="var(--primary)"/>
                <path d="M334 260c-14 -18 -32 -18 -32 -18s6 18 32 18z" fill="var(--accent)"/>
                <rect x="316" y="266" width="36" height="30" rx="6" fill="var(--accent-rose)"/>
            </g>

            <!-- fairy sparkles -->
            <path d="M96 120c1.6 5 6 6.5 6 6.5s-4.4 1.5-6 6.5c-1.6-5-6-6.5-6-6.5s4.4-1.5 6-6.5z" fill="var(--accent)"/>
            <path d="M330 100c1.3 4 5 5 5 5s-3.7 1-5 5c-1.3-4-5-5-5-5s3.7-1 5-5z" fill="var(--accent-pink)"/>
            <circle cx="60" cy="200" r="4" fill="var(--primary-light)"/>
            <circle cx="360" cy="180" r="5" fill="var(--accent)"/>
        </svg>

        <div class="hero-polaroid">
            <svg viewBox="0 0 100 90" style="width:100%;display:block;border-radius:2px;background:var(--accent-light)">
                <rect width="100" height="90" fill="var(--accent-light)"/>
                <path d="M20 60c8-24 20-30 30-30s22 6 30 30" fill="none" stroke="var(--primary-light)" stroke-width="3" stroke-linecap="round"/>
                <circle cx="50" cy="34" r="10" fill="var(--accent-pink)"/>
            </svg>
            <span class="script-font">life lately ✿</span>
        </div>
    </div>

    <div class="hero-stats">
        <div class="stat-item animate-fade-in">
            <span class="stat-number" data-count="<?php echo $totalPosts; ?>">0</span>
            <span class="stat-label">Articles published</span>
        </div>
        <div class="stat-item animate-fade-in">
            <span class="stat-number" data-count="<?php echo $totalUsers * 12; ?>">0</span>
            <span class="stat-label">Thoughts shared</span>
        </div>
        <div class="stat-item animate-fade-in">
            <span class="stat-number" data-count="<?php echo $totalTopics; ?>">0</span>
            <span class="stat-label">Topics covered</span>
        </div>
        <div class="stat-item animate-fade-in">
            <span class="stat-number" data-count="<?php echo max($totalViews, 100); ?>">0</span>
            <span class="stat-label">Readers here</span>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="topics-bar animate-fade-in">
    <h2 class="topics-bar-title script-font">what's on my mind ♡</h2>
    <div class="topics-scroll">
        <a href="index.php" class="topic-badge <?php echo !$topicFilter ? 'active' : ''; ?>">
            <span class="topic-badge-icon">📝</span>
            <span class="topic-badge-name">all</span>
        </a>
        <?php foreach ($topics as $topic): ?>
            <a href="index.php?topic=<?php echo escape($topic['slug']); ?>" class="topic-badge <?php echo $topicFilter === $topic['slug'] ? 'active' : ''; ?>">
                <span class="topic-badge-icon"><?php echo $topic['icon']; ?></span>
                <span class="topic-badge-name"><?php echo escape($topic['name']); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php if ($topicFilter || $viewFilter): ?>
<div class="page-header animate-fade-in" style="text-align: center; margin: 2rem 0;">
    <h1 class="script-font" style="font-size: 2.5rem; color: var(--primary);">
        <?php echo $topicFilter ? 'Topic: ' . escape($topicFilter) : 'All Articles'; ?>
    </h1>
</div>
<?php endif; ?>

<?php if (empty($posts)): ?>
    <div class="empty-state animate-fade-in" style="text-align: center; padding: 4rem 1rem;">
        <h2 class="script-font" style="color: var(--primary); font-size: 2rem;">No posts yet ♡</h2>
        <p style="color: var(--text-light); margin-bottom: 1.5rem;">Be the first to share your story!</p>
        <a href="create.php" class="btn btn-primary">Start writing</a>
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
                        <?php if (isLoggedIn() && isPostOwner($post['user_id'])): ?>
                            <div class="blog-card-actions">
                                <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-ghost btn-sm">Edit</a>
                                <a href="delete.php?id=<?php echo $post['id']; ?>" class="btn btn-ghost btn-sm" style="color:var(--danger)" onclick="return confirm('Delete this post?');">Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!$topicFilter && !$viewFilter): ?>
<section class="newsletter-section animate-fade-in" style="background: var(--accent-light); padding: 3rem; border-radius: var(--radius-lg); text-align: center; margin-top: 4rem;">
    <div>
        <h3 class="newsletter-title script-font" style="font-size: 2rem; color: var(--primary-dark); margin-bottom: 0.5rem;">let's be internet friends ♡</h3>
        <p style="color:var(--text-light);font-size:0.9rem; margin-bottom: 1.5rem;">get little notes from me in your inbox</p>
    </div>
    <form class="newsletter-form" id="newsletter-form" style="display: flex; gap: 0.5rem; justify-content: center;">
        <input type="email" name="email" placeholder="youremail@email.com" required style="padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--border); width: 100%; max-width: 300px;">
        <button type="submit" class="btn btn-primary">Subscribe ♡</button>
    </form>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>