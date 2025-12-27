<?php
// auth/login.php
require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter both username and password'
        ]);
        exit();
    }
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT user_id, username, password, full_name, email, role, status 
              FROM users WHERE username = :username LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();
        
        if ($user['status'] === 'inactive') {
            echo json_encode([
                'success' => false,
                'message' => 'Your account has been deactivated'
            ]);
            exit();
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            $redirect = '';
            switch ($user['role']) {
                case 'admin':
                    $redirect = 'admin/dashboard.php';
                    break;
                case 'doctor':
                    $redirect = 'doctor/dashboard.php';
                    break;
                case 'receptionist':
                    $redirect = 'receptionist/dashboard.php';
                    break;
                case 'pharmacist':
                    $redirect = 'pharmacist/dashboard.php';
                    break;
                default:
                    $redirect = 'index.php';
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => $redirect
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid username or password'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>

<?php
// auth/logout.php
require_once '../config/config.php';

session_destroy();
redirect('login.php');
?>

<?php
// auth/check_auth.php
// Include this file at the top of protected pages

require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}
?>

<?php
// auth/check_role.php
// Include this file to check specific roles

require_once '../config/config.php';

function requireRole($allowedRoles) {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        redirect('unauthorized.php');
    }
}
?>