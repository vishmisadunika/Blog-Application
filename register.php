<?php
/**
 * Registration Page
 */
require_once 'config.php';
require_once 'auth.php';

$pageTitle = 'Register';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
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

    // Check for existing username/email
    if (empty($errors)) {
        $conn = getDBConnection();

        $stmt = $conn->prepare("SELECT id FROM user WHERE username = ? OR email = ?");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Check which one exists
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

    // Create user if no errors
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

            // Auto-login after registration
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            redirectWithSuccess('Account created successfully! Welcome to ' . APP_NAME . '.', 'index.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
            $stmt->close();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Create Account</h1>
        <p class="auth-subtitle">Join our community and start writing</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo escape($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="auth-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo escape($_POST['username'] ?? ''); ?>"
                    required
                    autocomplete="username"
                    minlength="3"
                    pattern="[a-zA-Z0-9_]+"
                >
                <small class="form-hint">Letters, numbers, and underscores only</small>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo escape($_POST['email'] ?? ''); ?>"
                    required
                    autocomplete="email"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    minlength="6"
                >
                <small class="form-hint">At least 6 characters</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    required
                    autocomplete="new-password"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>

        <p class="auth-footer">
            Already have an account?
            <a href="login.php">Sign in</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>