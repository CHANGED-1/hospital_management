
<?php
/**
 * ========================================
 * FILE: auth/check_auth.php
 * ========================================
 * 
 * Include this file at the top of pages that require authentication
 * Usage: require_once '../auth/check_auth.php';
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config if not already included
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Not logged in - redirect to login page
    $redirect = BASE_URL . 'auth/login.php';
    header('Location: ' . $redirect);
    exit();
}

// Check session timeout
if (isset($_SESSION['LAST_ACTIVITY'])) {
    $inactiveTime = time() - $_SESSION['LAST_ACTIVITY'];
    
    if ($inactiveTime > SESSION_TIMEOUT) {
        // Session expired
        session_unset();
        session_destroy();
        
        $redirect = BASE_URL . 'auth/login.php?timeout=1';
        header('Location: ' . $redirect);
        exit();
    }
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();

?>
