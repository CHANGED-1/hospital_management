
<?php
/**
 * ========================================
 * FILE: auth/check_role.php
 * ========================================
 * 
 * Functions to check user roles and permissions
 */

/**
 * Require user to have specific role
 * @param string|array $allowedRoles Single role or array of roles
 * @param string $redirectTo Where to redirect if access denied
 */
function requireRole($allowedRoles, $redirectTo = null) {
    // Ensure we have authentication
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit();
    }
    
    // Convert single role to array
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    
    // Check if user has required role
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        // Access denied
        if ($redirectTo === null) {
            // Redirect to appropriate dashboard
            $redirectTo = BASE_URL . $_SESSION['role'] . '/dashboard.php';
        }
        
        $_SESSION['flash_message'] = 'You do not have permission to access that page.';
        $_SESSION['flash_type'] = 'error';
        
        header('Location: ' . $redirectTo);
        exit();
    }
}

/**
 * Check if current user has specific role
 * @param string|array $roles
 * @return bool
 */
function checkRole($roles) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['role'], $roles);
}

/**
 * Check if user has permission for specific action
 * @param string $permission
 * @return bool
 */
function checkPermission($permission) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    require_once __DIR__ . '/../config/constants.php';
    
    $role = $_SESSION['role'];
    
    if (!isset(ROLE_PERMISSIONS[$role])) {
        return false;
    }
    
    return in_array($permission, ROLE_PERMISSIONS[$role]);
}

/**
 * Require specific permission
 * @param string $permission
 * @param string $redirectTo
 */
function requirePermission($permission, $redirectTo = null) {
    if (!checkPermission($permission)) {
        if ($redirectTo === null) {
            $redirectTo = BASE_URL . $_SESSION['role'] . '/dashboard.php';
        }
        
        $_SESSION['flash_message'] = 'You do not have permission to perform that action.';
        $_SESSION['flash_type'] = 'error';
        
        header('Location: ' . $redirectTo);
        exit();
    }
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return checkRole('admin');
}

/**
 * Check if user is doctor
 * @return bool
 */
function isDoctor() {
    return checkRole('doctor');
}

/**
 * Check if user is receptionist
 * @return bool
 */
function isReceptionist() {
    return checkRole('receptionist');
}

/**
 * Check if user is pharmacist
 * @return bool
 */
function isPharmacist() {
    return checkRole('pharmacist');
}

?>
