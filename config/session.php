<?php
// config/session.php
if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = __DIR__ . '/../sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0755, true);
    }
    session_save_path($sessionPath);
    session_start();
}

// Include database connection (uses portfolio_db)
require_once __DIR__ . '/../database.php';

function isLoggedIn()
{
    return isset($_SESSION['user']);
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

function authenticateUser($username, $password)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            $_SESSION['username'] = $user['username'];
            return $user;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}
?>