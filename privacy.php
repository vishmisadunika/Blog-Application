<?php
require_once 'config.php';
require_once 'auth.php';

$pageTitle = 'Privacy Policy';

require_once 'includes/header.php';
?>

<div class="legal-page page-fade" style="max-width: 800px; margin: 0 auto;">

    <div class="page-header animate-fade-in">
        <h1 class="page-title script-font">privacy policy ✿</h1>
        <p class="legal-updated">Last updated: <?php echo date('F Y'); ?></p>
    </div>

    <div class="legal-content animate-fade-in">
        <p>
            This page explains, in plain language, what information <?php echo escape(APP_NAME); ?>
            collects and how it's used. We keep things simple: this is a small personal blog, not a
            data-driven platform.
        </p>

        <h2>What we collect</h2>
        <p>
            When you create an account, we store your username, email address, and a securely hashed
            password. When you write a post, we store the content, any images you upload, and basic
            metadata like publish date and reading time. If you subscribe to the newsletter, we store
            your email address for that purpose only.
        </p>

        <h2>How we use it</h2>
        <p>
            Your information is used to run your account (logging in, showing your posts, likes, and
            bookmarks) and, if you've subscribed, to send occasional updates from
            <?php echo escape(APP_NAME); ?>. We don't sell or share your personal data with third
            parties for advertising.
        </p>

        <h2>Cookies</h2>
        <p>
            We use a small number of cookies to keep you logged in and to remember your light/dark
            theme preference. See our <a href="cookie-policy.php">Cookie Policy</a> for details.
        </p>

        <h2>Your choices</h2>
        <p>
            You can edit or delete your posts at any time from your account, and you can unsubscribe
            from the newsletter using the link in any email we send. If you'd like your account or
            data removed entirely, reach out and we'll take care of it.
        </p>

        <h2>Questions</h2>
        <p>
            If anything here is unclear, feel free to get in touch through any of the
            <a href="about.php">about page</a> or social links in the footer.
        </p>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
