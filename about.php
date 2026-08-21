<?php
require_once 'config.php';
require_once 'auth.php';

$pageTitle = 'About';
$conn = getDBConnection();

$usersResult = $conn->query("SELECT COUNT(*) as cnt FROM user");
$totalUsers = $usersResult ? $usersResult->fetch_assoc()['cnt'] : 0;

$postsResult = $conn->query("SELECT COUNT(*) as cnt FROM blogPost");
$totalPosts = $postsResult ? $postsResult->fetch_assoc()['cnt'] : 0;

$topics = getTopics();

require_once 'includes/header.php';
?>

<div class="page-fade" style="max-width: 1000px; margin: 0 auto;">

    <div class="about-hero animate-fade-in">
        <span class="hero-badge">✿ nice to meet you</span>
        <h1 class="script-font">about <?php echo escape(APP_NAME); ?></h1>
        <p>
            <?php echo escape(APP_NAME); ?> is a cozy little corner of the internet for real thoughts,
            honest notes, and the small things that quietly matter. It's a space to write without
            performing — messy first drafts, half-finished ideas, and the stuff you think about at
            2am — built for anyone who wants a gentle place to think out loud and read what others
            are figuring out too.
        </p>
    </div>

    <div class="about-stats animate-fade-in">
        <div class="stat-item">
            <span class="stat-number" data-count="<?php echo $totalPosts; ?>">0</span>
            <span class="stat-label">Articles shared</span>
        </div>
        <div class="stat-item">
            <span class="stat-number" data-count="<?php echo $totalUsers; ?>">0</span>
            <span class="stat-label">Writers here</span>
        </div>
        <div class="stat-item">
            <span class="stat-number" data-count="<?php echo count($topics); ?>">0</span>
            <span class="stat-label">Topics covered</span>
        </div>
    </div>

    <div class="about-story animate-fade-in">
        <h2 class="script-font">why this exists</h2>
        <p>
            Most places online reward polish over honesty. <?php echo escape(APP_NAME); ?> started as
            the opposite of that — a quiet notebook that happened to go online. No algorithms chasing
            engagement, no pressure to perform. Just people writing about lifestyle, productivity,
            mental health, relationships, self growth, dreams, and whatever else is on their mind that
            day, and readers who show up because they want to, not because they were pushed here.
        </p>
        <p>
            Whether you're here to read, to bookmark a few favourites for later, or to finally start
            writing the thing you've been putting off — you're welcome. Grab a coffee, get comfortable,
            and stay a while.
        </p>
    </div>

    <div class="about-grid animate-fade-in">
        <div class="about-card">
            <span class="about-card-icon">✎</span>
            <h3>Write freely</h3>
            <p>A distraction-free editor with Markdown, image uploads, and live preview — built for getting thoughts down, not fighting a toolbar.</p>
        </div>
        <div class="about-card">
            <span class="about-card-icon">♡</span>
            <h3>Read gently</h3>
            <p>Browse by topic, save the ones that resonate to your bookmarks, and come back to them whenever you need to.</p>
        </div>
        <div class="about-card">
            <span class="about-card-icon">🌱</span>
            <h3>Grow slowly</h3>
            <p>No follower counts to chase. Just a small, sincere community of people writing and reading in good faith.</p>
        </div>
    </div>

    <section class="newsletter-section animate-fade-in" style="background: var(--accent-light); padding: 3rem; border-radius: var(--radius-lg); text-align: center; margin-top: 1rem;">
        <div>
            <h3 class="newsletter-title script-font" style="color: var(--primary-dark); margin-bottom: 0.5rem;">let's be internet friends ♡</h3>
            <p style="color:var(--text-light);font-size:0.9rem; margin-bottom: 1.5rem;">get little notes from me in your inbox, or start writing your own</p>
        </div>
        <div class="hero-buttons" style="justify-content: center;">
            <?php if (isLoggedIn()): ?>
                <a href="create.php" class="btn btn-primary">Start writing</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary">Join <?php echo escape(APP_NAME); ?></a>
            <?php endif; ?>
            <a href="index.php?view=all" class="btn btn-outline">Browse articles</a>
        </div>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>
