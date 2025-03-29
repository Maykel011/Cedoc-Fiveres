<?php
// login/logout.php
session_start();

// Verify CSRF token if you're using it
if (isset($_POST['csrf_token']) && (!isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
    header('HTTP/1.0 403 Forbidden');
    die('Invalid CSRF token');
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page
header("Location: login.php");
exit();
?>