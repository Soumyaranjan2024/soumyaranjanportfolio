<?php
// Remove the duplicate session_start() and fix file includes
if (session_status() === PHP_SESSION_NONE) {
    // Set a local session path if the default one is invalid or non-writable
    $sessionPath = __DIR__ . '/sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0755, true);
    }
    session_save_path($sessionPath);
    session_start();
}

require_once 'database.php'; // Uses your existing database.php with portfolio_db

// Cookie settings
define('REMEMBER_ME_COOKIE_NAME', 'remember_token');
define('REMEMBER_ME_DURATION', 30 * 24 * 60 * 60); // 30 days

// Function to generate a secure random token
function generateSecureToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

// Function to set remember me cookie
function setRememberMeCookie($user_id)
{
    global $pdo;

    $token = generateSecureToken();
    $expires = time() + REMEMBER_ME_DURATION;

    // Store token in database
    try {
        // First, remove any existing tokens for this user
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Insert new token
        $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, hash('sha256', $token), date('Y-m-d H:i:s', $expires)]);

        // Set cookie
        setcookie(REMEMBER_ME_COOKIE_NAME, $token, $expires, '/', '', false, true);

        return true;
    } catch (PDOException $e) {
        error_log("Remember me token error: " . $e->getMessage());
        return false;
    }
}

// Function to check remember me cookie
function checkRememberMeCookie()
{
    global $pdo;

    if (!isset($_COOKIE[REMEMBER_ME_COOKIE_NAME])) {
        return false;
    }

    $token = $_COOKIE[REMEMBER_ME_COOKIE_NAME];
    $hashed_token = hash('sha256', $token);

    try {
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("
            SELECT rt.user_id, u.* 
            FROM remember_tokens rt 
            JOIN users u ON rt.user_id = u.id 
            WHERE rt.token = ? AND rt.expires_at > ?
        ");
        $stmt->execute([$hashed_token, $now]);

        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Log the user in (matching your existing session structure)
            $_SESSION['user'] = $user;
            $_SESSION['username'] = $user['username'];

            // Refresh the token
            setRememberMeCookie($user['id']);

            return true;
        } else {
            // Invalid or expired token, remove cookie
            clearRememberMeCookie();
            return false;
        }
    } catch (PDOException $e) {
        error_log("Remember me check error: " . $e->getMessage());
        return false;
    }
}

// Function to clear remember me cookie
function clearRememberMeCookie()
{
    global $pdo;

    if (isset($_COOKIE[REMEMBER_ME_COOKIE_NAME])) {
        $token = $_COOKIE[REMEMBER_ME_COOKIE_NAME];
        $hashed_token = hash('sha256', $token);

        // Remove from database
        try {
            $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
            $stmt->execute([$hashed_token]);
        } catch (PDOException $e) {
            error_log("Clear remember me error: " . $e->getMessage());
        }

        // Clear cookie
        setcookie(REMEMBER_ME_COOKIE_NAME, '', time() - 3600, '/', '', false, true);
    }
}

// Enhanced login function
function loginUser($username, $password, $remember_me = false)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $user['password'])) {
                // Set session variables (matching your existing structure)
                $_SESSION['user'] = $user;
                $_SESSION['username'] = $user['username'];

                // Set remember me cookie if requested
                if ($remember_me) {
                    setRememberMeCookie($user['id']);
                }

                // Update last login time
                $now = date('Y-m-d H:i:s');
                $stmt = $pdo->prepare("UPDATE users SET last_login = ? WHERE id = ?");
                $stmt->execute([$now, $user['id']]);

                return $user; // Return user data for redirect logic
            }
        }
        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

// Your existing auth functions (keeping them for compatibility)
function isAdminLoggedIn()
{
    return isset($_SESSION['user']) && $_SESSION['user']['is_admin'] == 1;
}

function isUserLoggedIn()
{
    // First check session, then check remember me cookie
    if (isset($_SESSION['user'])) {
        return true;
    }

    // Check remember me cookie
    return checkRememberMeCookie();
}

function isLoggedIn()
{
    return isUserLoggedIn();
}

function isAdmin()
{
    return isset($_SESSION['user']) && $_SESSION['user']['is_admin'] == 1;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    if (!isAdminLoggedIn()) {
        header('Location: index.php?error=Access denied');
        exit;
    }
}

// Enhanced logout function
function logout()
{
    // Clear remember me cookie
    clearRememberMeCookie();

    // Destroy session
    session_destroy();

    // Redirect to login
    header('Location: login.php');
    exit;
}

// Handle login form submission (enhanced with remember me)
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] == '1';

    if (empty($username) || empty($password)) {
        header('Location: login.php?error=Please enter both username and password');
        exit;
    } else {
        $user = loginUser($username, $password, $remember_me);
        if ($user) {
            // Redirect based on role (matching your existing logic)
            if ($user['is_admin'] == 1) {
                header('Location: admin.php');
            } else {
                header('Location: user_dashboard.php');
            }
            exit;
        } else {
            header('Location: login.php?error=Invalid credentials');
            exit;
        }
    }
}

// Handle logout (enhanced to clear remember me)
if (isset($_GET['logout'])) {
    logout();
}

// Auto-check remember me cookie on page load
if (!isset($_SESSION['user'])) {
    checkRememberMeCookie();
}
?>