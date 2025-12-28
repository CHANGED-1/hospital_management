<?php
// config/database.php
// Database configuration file

class Database {
    private $host = "localhost";
    private $db_name = "hospital_management";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}

// // config/config.php
// // General configuration

// // Start session if not already started
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// // Define constants
// define('SITE_NAME', 'Hospital Management System');
// define('BASE_URL', 'http://localhost/hospital_management/');

// // Timezone
// date_default_timezone_set('Africa/Kampala');

// // Error reporting (disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// // Helper function to check if user is logged in
// function isLoggedIn() {
//     return isset($_SESSION['user_id']);
// }

// // Helper function to check user role
// function hasRole($role) {
//     return isset($_SESSION['role']) && $_SESSION['role'] === $role;
// }

// // Helper function to redirect
// function redirect($page) {
//     header("Location: " . BASE_URL . $page);
//     exit();
// }

// // Helper function to sanitize input
// function sanitize($data) {
//     $data = trim($data);
//     $data = stripslashes($data);
//     $data = htmlspecialchars($data);
//     return $data;
// }

// // Helper function to format date
// function formatDate($date) {
//     return date('M d, Y', strtotime($date));
// }

// // Helper function to format datetime
// function formatDateTime($datetime) {
//     return date('M d, Y h:i A', strtotime($datetime));
// }

// // Helper function to format currency
// function formatCurrency($amount) {
//     return 'UGX ' . number_format($amount, 2);
// }
// ?>