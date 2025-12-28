<?php
/**
 * Hospital Management System
 * Main Entry Point
 * 
 * This file redirects users to appropriate pages based on their authentication status
 */

session_start();

// Check if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    // Redirect to appropriate dashboard based on role
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'doctor':
            header('Location: doctor/dashboard.php');
            break;
        case 'receptionist':
            header('Location: receptionist/dashboard.php');
            break;
        case 'pharmacist':
            header('Location: pharmacist/dashboard.php');
            break;
        default:
            header('Location: auth/login.php');
    }
} else {
    // Not logged in, redirect to login page
    header('Location: auth/login.php');
}

exit();
?>