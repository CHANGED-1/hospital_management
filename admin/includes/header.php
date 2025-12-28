<?php
/**
 * Common Header for Admin Module
 * Include this at the top of every admin page
 */

// Check authentication
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';

// Ensure user has admin role
if (!hasRole('admin')) {
    header('Location: ' . BASE_URL . $_SESSION['role'] . '/dashboard.php');
    exit();
}

// Get current user info
$currentUserId = getCurrentUserId();
$currentUserName = getCurrentUserName();
$currentUserRole = getCurrentUserRole();

// Get user initials for avatar
$nameParts = explode(' ', $currentUserName);
$initials = '';
if (count($nameParts) >= 2) {
    $initials = strtoupper($nameParts[0][0] . $nameParts[1][0]);
} else {
    $initials = strtoupper(substr($currentUserName, 0, 2));
}

// Get current page name for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Hospital Management System - Admin Panel">
    <meta name="author" content="Hospital Management System">
    
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin - <?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/images/favicon.png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/main.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --light-color: #f3f4f6;
            --border-color: #e5e7eb;
            --text-color: #374151;
            --text-muted: #6b7280;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: var(--text-color);
        }

        /* Navbar Styles */
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            font-weight: 600;
            color: var(--primary-color);
            text-decoration: none;
        }

        .navbar-brand i {
            font-size: 28px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar-search {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--light-color);
            padding: 8px 15px;
            border-radius: 25px;
        }

        .navbar-search input {
            border: none;
            background: none;
            outline: none;
            width: 200px;
        }

        .navbar-search i {
            color: var(--text-muted);
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
            font-size: 20px;
            color: var(--text-muted);
            transition: color 0.3s;
        }

        .notification-icon:hover {
            color: var(--primary-color);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 16px;
            text-align: center;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 25px;
            transition: background 0.3s;
            position: relative;
        }

        .user-menu:hover {
            background: var(--light-color);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
        }

        .user-role {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: capitalize;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            min-width: 200px;
            display: none;
            z-index: 1000;
        }

        .user-dropdown.show {
            display: block;
        }

        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: var(--text-color);
            text-decoration: none;
            transition: background 0.3s;
        }

        .user-dropdown a:hover {
            background: var(--light-color);
        }

        .user-dropdown a:first-child {
            border-radius: 8px 8px 0 0;
        }

        .user-dropdown a:last-child {
            border-radius: 0 0 8px 8px;
            color: var(--danger-color);
        }

        .user-dropdown hr {
            margin: 0;
            border: none;
            border-top: 1px solid var(--border-color);
        }

        /* Flash Message */
        .flash-message {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid var(--success-color);
        }

        .alert-error, .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid var(--danger-color);
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid var(--warning-color);
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid var(--info-color);
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 10px 15px;
            }

            .navbar-search {
                display: none;
            }

            .user-info {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Flash Messages -->
    <?php
    $flashMessage = getFlashMessage();
    if ($flashMessage):
    ?>
    <div class="flash-message alert alert-<?php echo $flashMessage['type']; ?>">
        <i class="fas fa-<?php echo $flashMessage['type'] === 'success' ? 'check-circle' : ($flashMessage['type'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
        <span><?php echo htmlspecialchars($flashMessage['message']); ?></span>
    </div>
    <script>
        setTimeout(() => {
            document.querySelector('.flash-message')?.remove();
        }, 5000);
    </script>
    <?php endif; ?>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="navbar-brand">
            <i class="fas fa-hospital"></i>
            <span><?php echo SITE_SHORT_NAME; ?></span>
        </a>
        
        <div class="navbar-right">
            <div class="navbar-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search..." id="globalSearch">
            </div>
            
            <div class="notification-icon" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">3</span>
            </div>
            
            <div class="user-menu" onclick="toggleUserDropdown()">
                <div class="user-avatar"><?php echo $initials; ?></div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($currentUserName); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars($currentUserRole); ?></div>
                </div>
                <i class="fas fa-chevron-down"></i>
                
                <div class="user-dropdown" id="userDropdown">
                    <a href="<?php echo BASE_URL; ?>admin/profile.php">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/settings.php">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <hr>
                    <a href="<?php echo BASE_URL; ?>auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        function toggleUserDropdown() {
            document.getElementById('userDropdown').classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            
            if (!userMenu.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
    </script>