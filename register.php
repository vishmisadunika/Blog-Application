<?php
require_once 'config.php';
require_once 'auth.php';

$pageTitle = 'Register';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    if (empty($errors)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id FROM user WHERE username = ? OR email = ?");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $checkStmt = $conn->prepare("SELECT username, email FROM user WHERE username = ? OR email = ?");
            $checkStmt->bind_param('ss', $username, $email);
            $checkStmt->execute();
            $existing = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();

            if ($existing['username'] === $username) {
                $errors[] = 'Username is already taken';
            }
            if ($existing['email'] === $email) {
                $errors[] = 'Email is already registered';
            }
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user';

        $stmt = $conn->prepare("
            INSERT INTO user (username, email, password, role)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param('ssss', $username, $email, $hashedPassword, $role);

        if ($stmt->execute()) {
            $newUserId = $stmt->insert_id;
            $stmt->close();

            $_SESSION['user_id'] = $newUserId;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            redirectWithSuccess('Account created successfully! Welcome to ' . APP_NAME . ' ♡', 'index.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
            $stmt->close();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container animate-fade-in" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem;">
    <div class="auth-card" style="background: var(--bg-card); padding: 3rem 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); width: 100%; max-width: 450px; text-align: center;">
        <h1 class="script-font" style="font-size: 3rem; color: var(--primary); margin-bottom: 0.5rem;">create your account ♡</h1>
        <p class="auth-subtitle" style="color: var(--text-light); margin-bottom: 2rem;">Start writing your story today.</p>
        
        <div class="social-login">
            <a href="oauth.php?provider=google" class="social-btn">
                <svg class="social-btn-icon" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.9 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l6-6C34.5 5.5 29.5 3.5 24 3.5 12.7 3.5 3.5 12.7 3.5 24S12.7 44.5 24 44.5 44.5 35.3 44.5 24c0-1.2-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.6 15.9 18.9 13 24 13c3.1 0 5.8 1.1 8 3l6-6C34.5 5.5 29.5 3.5 24 3.5c-8 0-14.8 4.6-18 11.2z"/><path fill="#4CAF50" d="M24 44.5c5.4 0 10.3-1.9 14-5.1l-6.5-5.5c-2 1.5-4.6 2.4-7.5 2.4-5.3 0-9.7-3.1-11.3-7.6l-6.6 5.1C9.1 39.9 15.9 44.5 24 44.5z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.3-4.2 5.7l6.5 5.5C41.5 36.1 44.5 30.6 44.5 24c0-1.2-.1-2.4-.4-3.5z"/></svg>
                Continue with Google
            </a>
            <a href="oauth.php?provider=github" class="social-btn">
                <svg class="social-btn-icon" viewBox="0 0 24 24" aria-hidden="true" fill="#181717"><path d="M12 .5C5.65.5.5 5.65.5 12c0 5.08 3.29 9.39 7.86 10.91.57.1.78-.25.78-.55 0-.27-.01-1.16-.02-2.1-3.2.7-3.87-1.36-3.87-1.36-.53-1.34-1.29-1.7-1.29-1.7-1.05-.72.08-.71.08-.71 1.17.08 1.78 1.2 1.78 1.2 1.03 1.77 2.7 1.26 3.36.96.1-.75.4-1.26.73-1.55-2.55-.29-5.23-1.28-5.23-5.68 0-1.25.45-2.28 1.19-3.08-.12-.29-.52-1.46.11-3.05 0 0 .97-.31 3.18 1.18a11 11 0 0 1 5.79 0c2.2-1.49 3.17-1.18 3.17-1.18.64 1.59.24 2.76.12 3.05.74.8 1.18 1.83 1.18 3.08 0 4.41-2.69 5.38-5.25 5.67.41.36.78 1.08.78 2.17 0 1.57-.01 2.83-.01 3.22 0 .3.2.66.79.55A10.5 10.5 0 0 0 23.5 12c0-6.35-5.15-11.5-11.5-11.5z"/></svg>
                Continue with GitHub
            </a>
        </div>
        
        <div class="auth-divider" style="position: relative; text-align: center; margin: 2rem 0;">
            <span style="background: var(--bg-card); padding: 0 1rem; color: var(--text-light); font-size: 0.9rem; position: relative; z-index: 1;">or</span>
            <div style="position: absolute; top: 50%; left: 0; width: 100%; height: 1px; background: var(--border); z-index: 0;"></div>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" style="background: var(--accent-light); color: var(--danger); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: left;">
                <?php foreach ($errors as $error): ?>
                    <p style="margin: 0;"><?php echo escape($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="register.php" class="auth-form" style="text-align: left;">
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="username" style="display: block; margin-bottom: 0.5rem; color: var(--primary-dark); font-weight: 500;">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" value="<?php echo escape($_POST['username'] ?? ''); ?>" required minlength="3" pattern="[a-zA-Z0-9_]+" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-family: 'DM Sans', sans-serif;">
            </div>
            
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="email" style="display: block; margin-bottom: 0.5rem; color: var(--primary-dark); font-weight: 500;">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo escape($_POST['email'] ?? ''); ?>" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-family: 'DM Sans', sans-serif;">
            </div>
            
            <div class="form-group" style="margin-bottom: 1.25rem; position: relative;">
                <label for="password" style="display: block; margin-bottom: 0.5rem; color: var(--primary-dark); font-weight: 500;">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required minlength="6" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-family: 'DM Sans', sans-serif;">
                <div class="password-strength" style="height: 4px; background: var(--border); border-radius: 2px; margin-top: 0.5rem; overflow: hidden; display: none;">
                    <div class="strength-fill" style="height: 100%; width: 0%; transition: width 0.3s, background 0.3s;"></div>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="confirm_password" style="display: block; margin-bottom: 0.5rem; color: var(--primary-dark); font-weight: 500;">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required minlength="6" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-family: 'DM Sans', sans-serif;">
            </div>
            
            <button type="submit" class="btn btn-rose btn-block" style="width: 100%; padding: 0.85rem; background: var(--accent-pink); color: white; border: none; border-radius: 8px; font-weight: bold; font-size: 1.1rem; cursor: pointer; transition: background 0.2s;">Create account ♡</button>
        </form>
        
        <p class="auth-footer" style="margin-top: 2rem; color: var(--text-light); font-size: 0.95rem;">
            Already have an account? <a href="login.php" style="color: var(--primary); font-weight: bold; text-decoration: none;">Sign in</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>