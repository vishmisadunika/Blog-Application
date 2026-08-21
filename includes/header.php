<?php
/**
 * Common Header Include
 * Warm aesthetic blog design
 */
require_once __DIR__ . '/../auth.php';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' - ' . APP_NAME : APP_NAME . ' — ' . APP_TAGLINE; ?></title>
    <meta name="description" content="<?php echo APP_NAME; ?> — <?php echo APP_TAGLINE; ?>.">
    <meta name="theme-color" content="#8b6f5c">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600;700&family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>✿</text></svg>">
    <script>
        // Apply the saved theme before first paint to avoid a light/dark flash
        (function () {
            var saved = localStorage.getItem('inkbloom-theme');
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            var theme = saved || (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body>
    <!-- Reading Progress Bar -->
    <div id="reading-progress"></div>

    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand script-font"><?php echo APP_NAME; ?><?php echo isLoggedIn() ? '' : ' ♡'; ?></a>

            <div class="nav-links" id="nav-links">
                <a href="index.php" class="nav-link<?php echo $currentPage === 'index.php' && empty($_GET) ? ' active' : ''; ?>">HOME</a>
                <a href="topics.php" class="nav-link<?php echo $currentPage === 'topics.php' ? ' active' : ''; ?>">TOPICS</a>
                <a href="index.php?view=all" class="nav-link">EXPLORE</a>
                <a href="about.php" class="nav-link<?php echo $currentPage === 'about.php' ? ' active' : ''; ?>">ABOUT</a>
            </div>

            <div class="nav-right">
                <div class="nav-search">
                    <input type="text" placeholder="Search articles..." id="search-input" value="<?php echo isset($searchQuery) ? escape($searchQuery) : ''; ?>">
                </div>

                <div class="nav-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="create.php" class="btn btn-accent btn-sm">✎ Write</a>
                        <div class="nav-user-menu">
                            <span class="nav-user">Hi, <?php echo escape(getCurrentUsername()); ?></span>
                            <a href="bookmarks.php" class="nav-link" title="My Bookmarks">🔖</a>
                            <a href="logout.php" class="nav-link">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="nav-link">Login</a>
                        <a href="register.php" class="btn btn-primary btn-sm">Sign Up</a>
                    <?php endif; ?>
                </div>

                <button type="button" class="theme-toggle" id="theme-toggle" title="Toggle dark mode" aria-label="Toggle dark mode">
                    <span class="theme-toggle-knob">
                        <svg class="theme-toggle-icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path></svg>
                        <svg class="theme-toggle-icon-moon" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    </span>
                </button>

                <div class="nav-social">
                    <span class="nav-social-label">LET'S CONNECT</span>
                    <a href="#" title="Twitter / X" class="social-icon-link icon-twitter"><svg viewBox="0 0 24 24"><path d="M18.9 2H22l-7.6 8.7L23 22h-6.9l-5.4-6.6L4.5 22H1.4l8.1-9.3L1 2h7.1l4.9 6.1L18.9 2zm-1.2 18h1.9L7.4 4H5.4l12.3 16z"/></svg></a>
                    <a href="#" title="Instagram" class="social-icon-link icon-instagram"><svg viewBox="0 0 24 24"><path d="M12 2c2.7 0 3.06.01 4.12.06 1.06.05 1.79.22 2.43.47.66.26 1.22.6 1.77 1.16.5.5.9 1.1 1.16 1.77.25.64.42 1.37.47 2.43.05 1.06.06 1.42.06 4.12s-.01 3.06-.06 4.12c-.05 1.06-.22 1.79-.47 2.43a4.9 4.9 0 0 1-1.16 1.77 4.9 4.9 0 0 1-1.77 1.16c-.64.25-1.37.42-2.43.47-1.06.05-1.42.06-4.12.06s-3.06-.01-4.12-.06c-1.06-.05-1.79-.22-2.43-.47a4.9 4.9 0 0 1-1.77-1.16 4.9 4.9 0 0 1-1.16-1.77c-.25-.64-.42-1.37-.47-2.43C2.01 15.06 2 14.7 2 12s.01-3.06.06-4.12c.05-1.06.22-1.79.47-2.43.26-.66.6-1.22 1.16-1.77A4.9 4.9 0 0 1 5.45 2.53c.64-.25 1.37-.42 2.43-.47C8.94 2.01 9.3 2 12 2zm0 1.8c-2.65 0-2.98.01-4.02.06-.87.04-1.34.18-1.65.3-.42.16-.71.35-1.02.66-.31.31-.5.6-.66 1.02-.12.31-.26.78-.3 1.65-.05 1.04-.06 1.37-.06 4.02s.01 2.98.06 4.02c.04.87.18 1.34.3 1.65.16.42.35.71.66 1.02.31.31.6.5 1.02.66.31.12.78.26 1.65.3 1.04.05 1.37.06 4.02.06s2.98-.01 4.02-.06c.87-.04 1.34-.18 1.65-.3.42-.16.71-.35 1.02-.66.31-.31.5-.6.66-1.02.12-.31.26-.78.3-1.65.05-1.04.06-1.37.06-4.02s-.01-2.98-.06-4.02c-.04-.87-.18-1.34-.3-1.65a2.7 2.7 0 0 0-.66-1.02 2.7 2.7 0 0 0-1.02-.66c-.31-.12-.78-.26-1.65-.3-1.04-.05-1.37-.06-4.02-.06zm0 4.6a5.6 5.6 0 1 1 0 11.2 5.6 5.6 0 0 1 0-11.2zm0 1.8a3.8 3.8 0 1 0 0 7.6 3.8 3.8 0 0 0 0-7.6zm5.8-2a1.3 1.3 0 1 1 0 2.6 1.3 1.3 0 0 1 0-2.6z"/></svg></a>
                    <a href="#" title="Pinterest" class="social-icon-link icon-pinterest"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 4.24 2.66 7.86 6.4 9.28-.09-.79-.17-2 .04-2.86.19-.78 1.23-4.97 1.23-4.97s-.31-.63-.31-1.55c0-1.46.85-2.55 1.9-2.55.9 0 1.33.67 1.33 1.48 0 .9-.57 2.25-.87 3.5-.25 1.05.52 1.9 1.55 1.9 1.86 0 3.29-1.96 3.29-4.79 0-2.5-1.8-4.25-4.36-4.25-2.97 0-4.71 2.23-4.71 4.53 0 .9.34 1.86.78 2.38.09.1.1.2.07.3l-.28 1.15c-.05.19-.16.23-.38.14-1.4-.65-2.28-2.69-2.28-4.33 0-3.53 2.56-6.77 7.4-6.77 3.88 0 6.9 2.77 6.9 6.47 0 3.86-2.43 6.97-5.81 6.97-1.13 0-2.2-.59-2.56-1.28l-.7 2.65c-.25.97-.94 2.19-1.4 2.93 1.06.33 2.18.5 3.34.5 5.52 0 10-4.48 10-10S17.52 2 12 2z"/></svg></a>
                </div>
            </div>

            <button class="nav-toggle" aria-label="Toggle navigation" id="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <main class="main-content">
        <?php
        $errorMsg = getFlashMessage('error');
        $successMsg = getFlashMessage('success');
        if ($errorMsg):
        ?>
            <div class="alert alert-error">
                <?php echo escape($errorMsg); ?>
            </div>
        <?php endif; ?>
        <?php if ($successMsg): ?>
            <div class="alert alert-success">
                <?php echo escape($successMsg); ?>
            </div>
        <?php endif; ?>
