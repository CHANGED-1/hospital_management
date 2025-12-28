<?php
/**
 * Doctors API
 * Handles all doctor-related operations
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../auth/check_auth.php';

header('Content-Type: application/json');

$database = new Database();
$conn = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($method) {
    case 'GET':
        if ($action === 'list') {
            getDoctors($conn);
        } elseif ($action === 'get' && isset($_GET['id'])) {
            getDoctor($conn, $_GET['id']);
        } elseif ($action === 'search' && isset($_GET['term'])) {
            searchDoctors($conn, $_GET['term']);
        } elseif ($action === 'by_specialization' && isset($_GET['specialization'])) {
            getDoctorsBySpecialization($conn, $_GET['specialization']);
        }
        break;
        
    case 'POST':
        if ($action === 'create') {
            createDoctor($conn);
        } elseif ($action === 'update') {
            updateDoctor($conn);
        }
        break;
        
    case 'DELETE':
        if ($action === 'delete' && isset($_GET['id'])) {
            deleteDoctor($conn, $_GET['id']);
        }
        break;
}

// Get all doctors
function getDoctors($conn) {
    $query = "SELECT 
                d.*,
                u.full_name,
                u.email,
                u.phone,
                u.status,
                u.username
              FROM doctors d
              INNER JOIN users u ON d.user_id = u.user_id
              ORDER BY d.doctor_id DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);
}

// Get single doctor
function getDoctor($conn, $id) {
    $query = "SELECT 
                d.*,
                u.full_name,
                u.email,
                u.phone,
                u.status,
                u.username
              FROM doctors d
              INNER JOIN users u ON d.user_id = u.user_id
              WHERE d.doctor_id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'data' => $stmt->fetch()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Doctor not found'
        ]);
    }
}

// Search doctors
function searchDoctors($conn, $searchTerm) {
    $searchTerm = "%{$searchTerm}%";
    
    $query = "SELECT 
                d.*,
                u.full_name,
                u.email,
                u.phone,
                u.status
              FROM doctors d
              INNER JOIN users u ON d.user_id = u.user_id
              WHERE u.full_name LIKE :term 
                 OR d.specialization LIKE :term 
                 OR d.qualification LIKE :term
              ORDER BY u.full_name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':term', $searchTerm);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);
}

// Get doctors by specialization
function getDoctorsBySpecialization($conn, $specialization) {
    $query = "SELECT 
                d.*,
                u.full_name,
                u.email,
                u.phone,
                u.status
              FROM doctors d
              INNER JOIN users u ON d.user_id = u.user_id
              WHERE d.specialization = :specialization
                AND u.status = 'active'
              ORDER BY u.full_name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':specialization', $specialization);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);
}

// Create doctor
function createDoctor($conn) {
    $fullName = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $specialization = sanitize($_POST['specialization']);
    $qualification = sanitize($_POST['qualification']);
    $experienceYears = (int)$_POST['experience_years'];
    $consultationFee = (float)$_POST['consultation_fee'];
    $availableDays = isset($_POST['available_days']) ? implode(',', $_POST['available_days']) : '';
    $availableTimeStart = $_POST['available_time_start'] ?? '09:00';
    $availableTimeEnd = $_POST['available_time_end'] ?? '17:00';
    
    // Validate required fields
    if (empty($fullName) || empty($email) || empty($username) || empty($password) || empty($specialization)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill all required fields'
        ]);
        return;
    }
    
    // Check if username exists
    $checkQuery = "SELECT user_id FROM users WHERE username = :username";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username already exists'
        ]);
        return;
    }
    
    try {
        $conn->beginTransaction();
        
        // Create user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $userQuery = "INSERT INTO users (username, password, full_name, email, phone, role, status) 
                      VALUES (:username, :password, :full_name, :email, :phone, 'doctor', 'active')";
        
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bindParam(':username', $username);
        $userStmt->bindParam(':password', $hashedPassword);
        $userStmt->bindParam(':full_name', $fullName);
        $userStmt->bindParam(':email', $email);
        $userStmt->bindParam(':phone', $phone);
        $userStmt->execute();
        
        $userId = $conn->lastInsertId();
        
        // Create doctor
        $doctorQuery = "INSERT INTO doctors 
                        (user_id, specialization, qualification, experience_years, consultation_fee, 
                         available_days, available_time_start, available_time_end) 
                        VALUES 
                        (:user_id, :specialization, :qualification, :experience_years, :consultation_fee,
                         :available_days, :available_time_start, :available_time_end)";
        
        $doctorStmt = $conn->prepare($doctorQuery);
        $doctorStmt->bindParam(':user_id', $userId);
        $doctorStmt->bindParam(':specialization', $specialization);
        $doctorStmt->bindParam(':qualification', $qualification);
        $doctorStmt->bindParam(':experience_years', $experienceYears);
        $doctorStmt->bindParam(':consultation_fee', $consultationFee);
        $doctorStmt->bindParam(':available_days', $availableDays);
        $doctorStmt->bindParam(':available_time_start', $availableTimeStart);
        $doctorStmt->bindParam(':available_time_end', $availableTimeEnd);
        $doctorStmt->execute();
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Doctor added successfully',
            'doctor_id' => $conn->lastInsertId()
        ]);
    } catch (PDOException $e) {
        $conn->rollBack();
        logError('Create doctor error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add doctor: ' . $e->getMessage()
        ]);
    }
}

// Update doctor
function updateDoctor($conn) {
    $doctorId = (int)$_POST['doctor_id'];
    $userId = (int)$_POST['user_id'];
    $fullName = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $specialization = sanitize($_POST['specialization']);
    $qualification = sanitize($_POST['qualification']);
    $experienceYears = (int)$_POST['experience_years'];
    $consultationFee = (float)$_POST['consultation_fee'];
    $availableDays = isset($_POST['available_days']) ? implode(',', $_POST['available_days']) : '';
    $availableTimeStart = $_POST['available_time_start'] ?? '09:00';
    $availableTimeEnd = $_POST['available_time_end'] ?? '17:00';
    
    try {
        $conn->beginTransaction();
        
        // Update user
        $userQuery = "UPDATE users 
                      SET full_name = :full_name,
                          email = :email,
                          phone = :phone";
        
        // Update password if provided
        if (!empty($_POST['password'])) {
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $userQuery .= ", password = :password";
        }
        
        $userQuery .= " WHERE user_id = :user_id";
        
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bindParam(':user_id', $userId);
        $userStmt->bindParam(':full_name', $fullName);
        $userStmt->bindParam(':email', $email);
        $userStmt->bindParam(':phone', $phone);
        
        if (!empty($_POST['password'])) {
            $userStmt->bindParam(':password', $hashedPassword);
        }
        
        $userStmt->execute();
        
        // Update doctor
        $doctorQuery = "UPDATE doctors 
                        SET specialization = :specialization,
                            qualification = :qualification,
                            experience_years = :experience_years,
                            consultation_fee = :consultation_fee,
                            available_days = :available_days,
                            available_time_start = :available_time_start,
                            available_time_end = :available_time_end
                        WHERE doctor_id = :doctor_id";
        
        $doctorStmt = $conn->prepare($doctorQuery);
        $doctorStmt->bindParam(':doctor_id', $doctorId);
        $doctorStmt->bindParam(':specialization', $specialization);
        $doctorStmt->bindParam(':qualification', $qualification);
        $doctorStmt->bindParam(':experience_years', $experienceYears);
        $doctorStmt->bindParam(':consultation_fee', $consultationFee);
        $doctorStmt->bindParam(':available_days', $availableDays);
        $doctorStmt->bindParam(':available_time_start', $availableTimeStart);
        $doctorStmt->bindParam(':available_time_end', $availableTimeEnd);
        $doctorStmt->execute();
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Doctor updated successfully'
        ]);
    } catch (PDOException $e) {
        $conn->rollBack();
        logError('Update doctor error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update doctor: ' . $e->getMessage()
        ]);
    }
}

// Delete doctor
function deleteDoctor($conn, $id) {
    try {
        // Get user_id
        $query = "SELECT user_id FROM doctors WHERE doctor_id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Doctor not found'
            ]);
            return;
        }
        
        $userId = $stmt->fetch()['user_id'];
        
        // Soft delete - deactivate user
        $updateQuery = "UPDATE users SET status = 'inactive' WHERE user_id = :user_id";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':user_id', $userId);
        $updateStmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Doctor deleted successfully'
        ]);
    } catch (PDOException $e) {
        logError('Delete doctor error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete doctor'
        ]);
    }
}
?>