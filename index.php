<?php
// Main entry point - redirects to login or dashboard
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ' . $_SESSION['role'] . '/dashboard.php');
} else {
    header('Location: auth/login.php');
}
exit();
?>