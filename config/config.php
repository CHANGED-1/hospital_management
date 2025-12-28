<?php
/**
 * Hospital Management System
 * General Configuration File
 * 
 * This file contains all general application settings and helper functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================== APPLICATION SETTINGS ====================

// Site Information
define('SITE_NAME', 'Hospital Management System');
define('SITE_SHORT_NAME', 'HMS');
define('HOSPITAL_NAME', 'City General Hospital'); // Change to your hospital name

// Base URL - IMPORTANT: Update this to match your installation path
define('BASE_URL', 'http://localhost:8001');

// Application Version
define('APP_VERSION', '1.0.0');

// Timezone - Change to your timezone
date_default_timezone_set('Africa/Kampala');

// ==================== ENVIRONMENT SETTINGS ====================

// Environment: 'development' or 'production'
define('APP_ENV', 'development');

// Debug mode (set to false in production)
define('APP_DEBUG', true);

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// ==================== FILE UPLOAD SETTINGS ====================

// Upload paths
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('PATIENT_UPLOAD_PATH', UPLOAD_PATH . 'patients/');
define('REPORT_UPLOAD_PATH', UPLOAD_PATH . 'reports/');
define('PRESCRIPTION_UPLOAD_PATH', UPLOAD_PATH . 'prescriptions/');

// Maximum file sizes (in bytes)
define('MAX_FILE_SIZE', 5242880); // 5MB
define('MAX_IMAGE_SIZE', 2097152); // 2MB

// Allowed file types
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'txt']);

// ==================== PAGINATION SETTINGS ====================

define('RECORDS_PER_PAGE', 10);
define('MAX_PAGINATION_LINKS', 5);

// ==================== SESSION SETTINGS ====================

// Session timeout in seconds (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Session name
define('SESSION_NAME', 'HMS_SESSION');

// ==================== EMAIL SETTINGS ====================

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@hospital.com');
define('SMTP_FROM_NAME', SITE_NAME);

// ==================== DATE/TIME FORMATS ====================

define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'M d, Y');
define('DISPLAY_TIME_FORMAT', 'h:i A');
define('DISPLAY_DATETIME_FORMAT', 'M d, Y h:i A');

// ==================== CURRENCY SETTINGS ====================

define('CURRENCY_SYMBOL', 'UGX');
define('CURRENCY_POSITION', 'before'); // 'before' or 'after'

// ==================== APPOINTMENT SETTINGS ====================

define('APPOINTMENT_DURATION', 30); // Duration in minutes
define('APPOINTMENT_START_TIME', '08:00');
define('APPOINTMENT_END_TIME', '17:00');
define('APPOINTMENT_DAYS', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);

// ==================== HELPER FUNCTIONS ====================

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 * @param string $role
 * @return bool
 */
function hasRole($role) {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Check if user has any of the specified roles
 * @param array $roles
 * @return bool
 */
function hasAnyRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    return in_array($_SESSION['role'], $roles);
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * @return string|null
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Get current user full name
 * @return string|null
 */
function getCurrentUserName() {
    return $_SESSION['full_name'] ?? null;
}

/**
 * Redirect to specified page
 * @param string $page
 */
function redirect($page) {
    header('Location: ' . BASE_URL . $page);
    exit();
}

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Format date for display
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = DISPLAY_DATE_FORMAT) {
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 * @param string $datetime
 * @param string $format
 * @return string
 */
function formatDateTime($datetime, $format = DISPLAY_DATETIME_FORMAT) {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return '-';
    }
    return date($format, strtotime($datetime));
}

/**
 * Format currency
 * @param float $amount
 * @return string
 */
function formatCurrency($amount) {
    $formatted = number_format($amount, 2);
    if (CURRENCY_POSITION === 'before') {
        return CURRENCY_SYMBOL . ' ' . $formatted;
    } else {
        return $formatted . ' ' . CURRENCY_SYMBOL;
    }
}

/**
 * Calculate age from date of birth
 * @param string $dob
 * @return int
 */
function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
    return $age;
}

/**
 * Generate random string
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Validate email address
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number
 * @param string $phone
 * @return bool
 */
function isValidPhone($phone) {
    // Remove spaces, dashes, and parentheses
    $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
    // Check if it contains only digits and + sign
    return preg_match('/^[\+]?[0-9]{10,15}$/', $phone);
}

/**
 * Get time slots for appointments
 * @param string $startTime
 * @param string $endTime
 * @param int $duration
 * @return array
 */
function getTimeSlots($startTime = APPOINTMENT_START_TIME, $endTime = APPOINTMENT_END_TIME, $duration = APPOINTMENT_DURATION) {
    $slots = [];
    $start = new DateTime($startTime);
    $end = new DateTime($endTime);
    
    while ($start < $end) {
        $slots[] = $start->format('H:i');
        $start->add(new DateInterval('PT' . $duration . 'M'));
    }
    
    return $slots;
}

/**
 * Log error to file
 * @param string $message
 * @param string $file
 */
function logError($message, $file = 'error.log') {
    $logPath = __DIR__ . '/../logs/' . $file;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    file_put_contents($logPath, $logMessage, FILE_APPEND);
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isLoggedIn()) {
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
            session_unset();
            session_destroy();
            redirect('auth/login.php?timeout=1');
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    }
}

/**
 * Flash message functions
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'];
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        return ['type' => $type, 'message' => $message];
    }
    return null;
}

// Check session timeout on every page load
checkSessionTimeout();
?>