<?php
require_once 'config.php';
require_once 'auth.php';

$pageTitle = 'Terms';

require_once 'includes/header.php';
?>

<div class="legal-page page-fade" style="max-width: 800px; margin: 0 auto;">

    <div class="page-header animate-fade-in">
        <h1 class="page-title script-font">terms of use ✿</h1>
        <p class="legal-updated">Last updated: <?php echo date('F Y'); ?></p>
    </div>

    <div class="legal-content animate-fade-in">
        <p>
            Welcome to <?php echo escape(APP_NAME); ?>. By creating an account or reading and writing
            here, you're agreeing to the following, kept intentionally short.
        </p>

        <h2>Your account</h2>
        <p>
            You're responsible for keeping your login details safe, and for anything published under
            your account. Please use a real, working email so you can recover your account if needed.
        </p>

        <h2>What you post</h2>
        <p>
            You keep ownership of everything you write and any images you upload — publishing here
            just gives <?php echo escape(APP_NAME); ?> permission to display it on the site. Please
            don't post anything illegal, hateful, or that you don't have the rights to share. We may
            remove content or accounts that break this.
        </p>

        <h2>Community</h2>
        <p>
            This is a small, sincere space. Be kind in comments and interactions, and respect other
            writers' work. Likes and bookmarks are meant to help you find your way back to posts you
            care about, not to be gamed.
        </p>

        <h2>No guarantees</h2>
        <p>
            <?php echo escape(APP_NAME); ?> is provided as-is, run on a best-effort basis. We'll do
            our best to keep things running smoothly, but can't promise the site will always be
            available or error-free.
        </p>

        <h2>Changes</h2>
        <p>
            These terms may be updated occasionally as the site grows. Continuing to use
            <?php echo escape(APP_NAME); ?> after a change means you accept the updated terms.
        </p>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
