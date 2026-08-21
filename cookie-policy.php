<?php
require_once 'config.php';
require_once 'auth.php';

$pageTitle = 'Cookie Policy';

require_once 'includes/header.php';
?>

<div class="legal-page page-fade" style="max-width: 800px; margin: 0 auto;">

    <div class="page-header animate-fade-in">
        <h1 class="page-title script-font">cookie policy ✿</h1>
        <p class="legal-updated">Last updated: <?php echo date('F Y'); ?></p>
    </div>

    <div class="legal-content animate-fade-in">
        <p>
            <?php echo escape(APP_NAME); ?> uses a small number of cookies and browser storage
            entries — nothing used for advertising or cross-site tracking.
        </p>

        <h2>Essential cookies</h2>
        <p>
            A session cookie keeps you logged in as you move between pages. Without it, you'd need to
            log in again on every page load. This cookie is required for the site to function and is
            removed automatically when your session ends.
        </p>

        <h2>Preference storage</h2>
        <p>
            Your light/dark theme choice is saved in your browser's local storage so the site
            remembers it on your next visit. This never leaves your device and isn't shared with us.
        </p>

        <h2>No third-party tracking</h2>
        <p>
            We don't use analytics or advertising cookies, and we don't share cookie data with third
            parties.
        </p>

        <h2>Managing cookies</h2>
        <p>
            You can clear cookies and local storage at any time through your browser settings. Doing
            so will simply log you out and reset your theme preference back to your system default.
        </p>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
