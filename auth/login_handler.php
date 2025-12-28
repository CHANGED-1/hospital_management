<?php
/**
 * Hospital Management System
 * Login Handler
 * 
 * Processes login requests and authenticates users
 */

session_start();

require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

// Get and sanitize input
$username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$remember = isset($_POST['remember']) && $_POST['remember'] === '1';

// Validate input
if (empty($username) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter both username and password'
    ]);
    exit();
}

try {
    // Connect to database
    $database = new Database();
    $conn = $database->getConnection();
    
    // Prepare query
    $query = "SELECT 
                user_id, 
                username, 
                password, 
                full_name, 
                email, 
                role, 
                status 
              FROM users 
              WHERE username = :username 
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    // Check if user exists
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if account is active
        if ($user['status'] === 'inactive') {
            echo json_encode([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact the administrator.'
            ]);
            exit();
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct - create session
            session_regenerate_id(true); // Prevent session fixation
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['LAST_ACTIVITY'] = time();
            
            // Set remember me cookie if requested
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
                
                // Store token in database (you should create a remember_tokens table)
                // For now, we'll skip this for simplicity
            }
            
            // Log login activity
            logActivity($conn, $user['user_id'], 'login', 'User logged in successfully');
            
            // Determine redirect URL based on role
            $redirect = '';
            switch ($user['role']) {
                case 'admin':
                    $redirect = '../admin/dashboard.php';
                    break;
                case 'doctor':
                    $redirect = '../doctor/dashboard.php';
                    break;
                case 'receptionist':
                    $redirect = '../receptionist/dashboard.php';
                    break;
                case 'pharmacist':
                    $redirect = '../pharmacist/dashboard.php';
                    break;
                default:
                    $redirect = '../index.php';
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => $redirect,
                'user' => [
                    'name' => $user['full_name'],
                    'role' => $user['role']
                ]
            ]);
            
        } else {
            // Invalid password
            // Log failed attempt
            logActivity($conn, null, 'login_failed', "Failed login attempt for username: {$username}");
            
            echo json_encode([
                'success' => false,
                'message' => 'Invalid username or password'
            ]);
        }
    } else {
        // User not found
        // Log failed attempt
        logActivity($conn, null, 'login_failed', "Failed login attempt for username: {$username}");
        
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
    }
    
} catch (PDOException $e) {
    // Database error
    logError('Login error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'A database error occurred. Please try again later.'
    ]);
    
    if (APP_DEBUG) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} catch (Exception $e) {
    // General error
    logError('Login error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again.'
    ]);
}

/**
 * Log user activity
 * @param PDO $conn
 * @param int|null $userId
 * @param string $action
 * @param string $description
 */
function logActivity($conn, $userId, $action, $description) {
    try {
        $query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) 
                  VALUES (:user_id, :action, :description, :ip_address, :user_agent, NOW())";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':description', $description);
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $stmt->bindParam(':ip_address', $ipAddress);
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $stmt->bindParam(':user_agent', $userAgent);
        
        $stmt->execute();
    } catch (PDOException $e) {
        // Silently fail - don't break login process
        logError('Activity log error: ' . $e->getMessage());
    }
}
?>