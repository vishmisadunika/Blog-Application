<?php
/**
 * Social Login Handler
 * Handles the "Continue with Google" / "Continue with GitHub" buttons.
 *
 * Flow:
 *   1. login.php / register.php link straight to oauth.php?provider=google (or github)
 *   2. If credentials aren't configured yet, we bounce back with a friendly
 *      message instead of a blank error page.
 *   3. Otherwise we redirect to the provider's consent screen.
 *   4. The provider redirects back here with ?code=...&state=..., we
 *      exchange the code for a token, pull the user's profile, and
 *      create/log them in.
 */
require_once 'config.php';
require_once 'auth.php';

$provider = isset($_GET['provider']) ? $_GET['provider'] : '';

if (!in_array($provider, ['google', 'github'], true)) {
    redirectWithError('Unknown sign-in provider.', 'login.php');
}

if (!isOAuthConfigured($provider)) {
    $label = $provider === 'google' ? 'Google' : 'GitHub';
    redirectWithError(
        "Continue with {$label} isn't set up yet — an admin needs to add {$label} OAuth credentials in config.php. You can still sign in with a username and password below.",
        'login.php'
    );
}

$redirectUri = rtrim(APP_URL, '/') . '/oauth.php?provider=' . $provider;

/**
 * Minimal POST/GET request helper (uses cURL when available, falls back to streams)
 */
function oauthHttpRequest($url, $method = 'GET', $data = [], $headers = []) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            $query = $data ? '?' . http_build_query($data) : '';
            curl_setopt($ch, CURLOPT_URL, $url . $query);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(['Accept: application/json'], $headers));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    $opts = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", array_merge(['Accept: application/json'], $headers)),
            'timeout' => 10,
        ],
    ];
    if ($method === 'POST') {
        $opts['http']['header'] .= "\r\nContent-Type: application/x-www-form-urlencoded";
        $opts['http']['content'] = http_build_query($data);
        $url = $url;
    } else {
        $url .= $data ? '?' . http_build_query($data) : '';
    }
    $context = stream_context_create($opts);
    return @file_get_contents($url, false, $context);
}

/**
 * Find an existing user by provider/id (or matching email) or create a new one.
 */
function findOrCreateOAuthUser($provider, $providerId, $email, $displayName, $avatar) {
    $conn = getDBConnection();

    // 1. Already linked to this provider account?
    $stmt = $conn->prepare("SELECT id, username, role FROM user WHERE oauth_provider = ? AND oauth_id = ?");
    $stmt->bind_param('ss', $provider, $providerId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($user) {
        return $user;
    }

    // 2. Existing account with the same email — link it.
    if ($email) {
        $stmt = $conn->prepare("SELECT id, username, role FROM user WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($user) {
            $stmt = $conn->prepare("UPDATE user SET oauth_provider = ?, oauth_id = ? WHERE id = ?");
            $stmt->bind_param('ssi', $provider, $providerId, $user['id']);
            $stmt->execute();
            $stmt->close();
            return $user;
        }
    }

    // 3. Brand new account. Build a unique username from the name/email.
    $base = $displayName ?: ($email ? explode('@', $email)[0] : $provider . '_user');
    $base = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $base));
    $base = strtolower(substr($base ?: $provider . 'user', 0, 20));
    if ($base === '') $base = $provider . 'user';

    $username = $base;
    $suffix = 0;
    while (true) {
        $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if (!$exists) break;
        $suffix++;
        $username = $base . $suffix;
    }

    // Email must be unique/non-null in schema; fabricate a placeholder if the
    // provider didn't share one (e.g. a private GitHub email).
    $finalEmail = $email ?: ($username . '+' . $provider . '@users.noreply.' . parse_url(APP_URL, PHP_URL_HOST) ?: 'inkbloom.local');
    $role = 'user';

    $stmt = $conn->prepare("INSERT INTO user (username, email, password, role, oauth_provider, oauth_id, avatar) VALUES (?, ?, NULL, ?, ?, ?, ?)");
    $stmt->bind_param('ssssss', $username, $finalEmail, $role, $provider, $providerId, $avatar);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    return ['id' => $newId, 'username' => $username, 'role' => $role];
}

// ---------------------------------------------------------------------
// Step 1: no code yet -> send the user to the provider's consent screen
// ---------------------------------------------------------------------
if (!isset($_GET['code'])) {
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;

    if ($provider === 'google') {
        $params = [
            'client_id' => GOOGLE_CLIENT_ID,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ];
        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
        exit();
    }

    if ($provider === 'github') {
        $params = [
            'client_id' => GITHUB_CLIENT_ID,
            'redirect_uri' => $redirectUri,
            'scope' => 'read:user user:email',
            'state' => $state,
        ];
        header('Location: https://github.com/login/oauth/authorize?' . http_build_query($params));
        exit();
    }
}

// ---------------------------------------------------------------------
// Step 2: provider redirected back with a code -> exchange + log in
// ---------------------------------------------------------------------
$state = $_GET['state'] ?? '';
if (empty($_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], $state)) {
    redirectWithError('Your sign-in session expired. Please try again.', 'login.php');
}
unset($_SESSION['oauth_state']);

$code = $_GET['code'];

try {
    if ($provider === 'google') {
        $tokenRes = oauthHttpRequest('https://oauth2.googleapis.com/token', 'POST', [
            'code' => $code,
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);
        $token = json_decode($tokenRes, true);
        if (empty($token['access_token'])) {
            throw new Exception('Could not get an access token from Google.');
        }

        $profileRes = oauthHttpRequest('https://www.googleapis.com/oauth2/v3/userinfo', 'GET', [], [
            'Authorization: Bearer ' . $token['access_token'],
        ]);
        $profile = json_decode($profileRes, true);
        if (empty($profile['sub'])) {
            throw new Exception('Could not read your Google profile.');
        }

        $user = findOrCreateOAuthUser('google', $profile['sub'], $profile['email'] ?? null, $profile['name'] ?? null, $profile['picture'] ?? null);
    } else {
        $tokenRes = oauthHttpRequest('https://github.com/login/oauth/access_token', 'POST', [
            'code' => $code,
            'client_id' => GITHUB_CLIENT_ID,
            'client_secret' => GITHUB_CLIENT_SECRET,
            'redirect_uri' => $redirectUri,
        ], ['Accept: application/json']);
        $token = json_decode($tokenRes, true);
        if (empty($token['access_token'])) {
            throw new Exception('Could not get an access token from GitHub.');
        }

        $profileRes = oauthHttpRequest('https://api.github.com/user', 'GET', [], [
            'Authorization: Bearer ' . $token['access_token'],
            'User-Agent: ' . APP_NAME,
        ]);
        $profile = json_decode($profileRes, true);
        if (empty($profile['id'])) {
            throw new Exception('Could not read your GitHub profile.');
        }

        $email = $profile['email'] ?? null;
        if (!$email) {
            $emailsRes = oauthHttpRequest('https://api.github.com/user/emails', 'GET', [], [
                'Authorization: Bearer ' . $token['access_token'],
                'User-Agent: ' . APP_NAME,
            ]);
            $emails = json_decode($emailsRes, true);
            if (is_array($emails)) {
                foreach ($emails as $e) {
                    if (!empty($e['primary'])) { $email = $e['email']; break; }
                }
                if (!$email && !empty($emails[0]['email'])) $email = $emails[0]['email'];
            }
        }

        $user = findOrCreateOAuthUser('github', (string)$profile['id'], $email, $profile['login'] ?? null, $profile['avatar_url'] ?? null);
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
    unset($_SESSION['redirect_after_login']);
    redirectWithSuccess('Welcome to ' . APP_NAME . ' ♡', $redirect);
} catch (Exception $e) {
    redirectWithError('Sign-in failed: ' . $e->getMessage(), 'login.php');
}
