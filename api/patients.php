<?php
// api/patients.php
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
            getPatients($conn);
        } elseif ($action === 'get' && isset($_GET['id'])) {
            getPatient($conn, $_GET['id']);
        } elseif ($action === 'search' && isset($_GET['term'])) {
            searchPatients($conn, $_GET['term']);
        }
        break;
        
    case 'POST':
        if ($action === 'create') {
            createPatient($conn);
        }
        break;
        
    case 'PUT':
        if ($action === 'update') {
            parse_str(file_get_contents("php://input"), $_PUT);
            updatePatient($conn, $_PUT);
        }
        break;
        
    case 'DELETE':
        if ($action === 'delete' && isset($_GET['id'])) {
            deletePatient($conn, $_GET['id']);
        }
        break;
}

// Get all patients
function getPatients($conn) {
    $query = "SELECT 
                patient_id,
                CONCAT(first_name, ' ', last_name) as full_name,
                first_name,
                last_name,
                date_of_birth,
                TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age,
                gender,
                blood_group,
                phone,
                email,
                address,
                emergency_contact,
                status,
                registered_date
              FROM patients 
              ORDER BY patient_id DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $patients = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $patients
    ]);
}

// Get single patient
function getPatient($conn, $id) {
    $query = "SELECT * FROM patients WHERE patient_id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $patient = $stmt->fetch();
        echo json_encode([
            'success' => true,
            'data' => $patient
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Patient not found'
        ]);
    }
}

// Search patients
function searchPatients($conn, $searchTerm) {
    $searchTerm = "%{$searchTerm}%";
    
    $query = "SELECT 
                patient_id,
                CONCAT(first_name, ' ', last_name) as full_name,
                date_of_birth,
                TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age,
                gender,
                blood_group,
                phone,
                email,
                status
              FROM patients 
              WHERE first_name LIKE :term 
                 OR last_name LIKE :term 
                 OR phone LIKE :term 
                 OR email LIKE :term
              ORDER BY patient_id DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':term', $searchTerm);
    $stmt->execute();
    
    $patients = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $patients
    ]);
}

// Create new patient
function createPatient($conn) {
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name']);
    $dateOfBirth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $bloodGroup = isset($_POST['blood_group']) ? $_POST['blood_group'] : null;
    $phone = sanitize($_POST['phone']);
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : null;
    $address = isset($_POST['address']) ? sanitize($_POST['address']) : null;
    $emergencyContact = isset($_POST['emergency_contact']) ? sanitize($_POST['emergency_contact']) : null;
    
    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($dateOfBirth) || empty($gender) || empty($phone)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill all required fields'
        ]);
        return;
    }
    
    // Check if phone already exists
    $checkQuery = "SELECT patient_id FROM patients WHERE phone = :phone";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':phone', $phone);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Phone number already registered'
        ]);
        return;
    }
    
    $query = "INSERT INTO patients 
              (first_name, last_name, date_of_birth, gender, blood_group, phone, email, address, emergency_contact) 
              VALUES 
              (:first_name, :last_name, :date_of_birth, :gender, :blood_group, :phone, :email, :address, :emergency_contact)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':first_name', $firstName);
    $stmt->bindParam(':last_name', $lastName);
    $stmt->bindParam(':date_of_birth', $dateOfBirth);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':blood_group', $bloodGroup);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':emergency_contact', $emergencyContact);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Patient registered successfully',
            'patient_id' => $conn->lastInsertId()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to register patient'
        ]);
    }
}

// Update patient
function updatePatient($conn, $data) {
    $patientId = $data['patient_id'];
    $firstName = sanitize($data['first_name']);
    $lastName = sanitize($data['last_name']);
    $dateOfBirth = $data['date_of_birth'];
    $gender = $data['gender'];
    $bloodGroup = isset($data['blood_group']) ? $data['blood_group'] : null;
    $phone = sanitize($data['phone']);
    $email = isset($data['email']) ? sanitize($data['email']) : null;
    $address = isset($data['address']) ? sanitize($data['address']) : null;
    $emergencyContact = isset($data['emergency_contact']) ? sanitize($data['emergency_contact']) : null;
    $status = isset($data['status']) ? $data['status'] : 'active';
    
    $query = "UPDATE patients 
              SET first_name = :first_name,
                  last_name = :last_name,
                  date_of_birth = :date_of_birth,
                  gender = :gender,
                  blood_group = :blood_group,
                  phone = :phone,
                  email = :email,
                  address = :address,
                  emergency_contact = :emergency_contact,
                  status = :status
              WHERE patient_id = :patient_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':patient_id', $patientId);
    $stmt->bindParam(':first_name', $firstName);
    $stmt->bindParam(':last_name', $lastName);
    $stmt->bindParam(':date_of_birth', $dateOfBirth);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':blood_group', $bloodGroup);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':emergency_contact', $emergencyContact);
    $stmt->bindParam(':status', $status);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Patient updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update patient'
        ]);
    }
}

// Delete patient
function deletePatient($conn, $id) {
    // Soft delete - change status to inactive
    $query = "UPDATE patients SET status = 'inactive' WHERE patient_id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Patient deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete patient'
        ]);
    }
}
?>