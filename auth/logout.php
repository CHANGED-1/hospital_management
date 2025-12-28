<?php
/**
 * ========================================
 * FILE: auth/logout.php
 * ========================================
 */

session_start();

require_once '../config/database.php';
require_once '../config/config.php';

// Log logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) 
                  VALUES (:user_id, 'logout', 'User logged out', :ip_address, NOW())";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $stmt->bindParam(':ip_address', $ipAddress);
        
        $stmt->execute();
    } catch (PDOException $e) {
        // Silently fail
        logError('Logout activity log error: ' . $e->getMessage());
    }
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: login.php?logout=1');
exit();

?>
