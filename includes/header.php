<?php
/**
 * Common Header Include
 * Include this at the top of every page
 */
require_once __DIR__ . '/../auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' - ' . APP_NAME : APP_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📝</text></svg>">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand"><?php echo APP_NAME; ?></a>

            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>

                <?php if (isLoggedIn()): ?>
                    <a href="create.php" class="nav-link nav-cta">+ New Post</a>
                    <span class="nav-user">Hi, <?php echo escape(getCurrentUsername()); ?></span>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link nav-cta">Sign Up</a>
                <?php endif; ?>
            </div>

            <button class="nav-toggle" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <main class="main-content">
        <?php
        // Display flash messages
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
