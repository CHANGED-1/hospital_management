<?php
// api/prescriptions.php
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
            getPrescriptions($conn);
        } elseif ($action === 'get' && isset($_GET['id'])) {
            getPrescription($conn, $_GET['id']);
        } elseif ($action === 'patient' && isset($_GET['patient_id'])) {
            getPatientPrescriptions($conn, $_GET['patient_id']);
        } elseif ($action === 'pending') {
            getPendingPrescriptions($conn);
        }
        break;
        
    case 'POST':
        if ($action === 'create') {
            createPrescription($conn);
        }
        break;
        
    case 'PUT':
        if ($action === 'update') {
            parse_str(file_get_contents("php://input"), $_PUT);
            updatePrescription($conn, $_PUT);
        } elseif ($action === 'dispense') {
            parse_str(file_get_contents("php://input"), $_PUT);
            dispensePrescription($conn, $_PUT);
        }
        break;
        
    case 'DELETE':
        if ($action === 'delete' && isset($_GET['id'])) {
            deletePrescription($conn, $_GET['id']);
        }
        break;
}

// Get all prescriptions with details
function getPrescriptions($conn) {
    $query = "SELECT 
                pr.prescription_id,
                pr.prescription_date,
                pr.status,
                pr.notes,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.patient_id,
                CONCAT(u.full_name) as doctor_name,
                d.doctor_id,
                COUNT(pd.detail_id) as medicine_count
              FROM prescriptions pr
              INNER JOIN patients p ON pr.patient_id = p.patient_id
              INNER JOIN doctors d ON pr.doctor_id = d.doctor_id
              INNER JOIN users u ON d.user_id = u.user_id
              LEFT JOIN prescription_details pd ON pr.prescription_id = pd.prescription_id
              GROUP BY pr.prescription_id
              ORDER BY pr.prescription_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);
}

// Get single prescription with all details
function getPrescription($conn, $id) {
    // Get prescription header
    $query = "SELECT 
                pr.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.phone as patient_phone,
                p.gender,
                p.date_of_birth,
                TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as patient_age,
                CONCAT(u.full_name) as doctor_name,
                d.specialization
              FROM prescriptions pr
              INNER JOIN patients p ON pr.patient_id = p.patient_id
              INNER JOIN doctors d ON pr.doctor_id = d.doctor_id
              INNER JOIN users u ON d.user_id = u.user_id
              WHERE pr.prescription_id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $prescription = $stmt->fetch();
        
        // Get prescription details (medicines)
        $detailQuery = "SELECT * FROM prescription_details WHERE prescription_id = :id";
        $detailStmt = $conn->prepare($detailQuery);
        $detailStmt->bindParam(':id', $id);
        $detailStmt->execute();
        
        $prescription['medicines'] = $detailStmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $prescription
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Prescription not found'
        ]);
    }
}

// Get patient prescriptions
function getPatientPrescriptions($conn, $patientId) {
    $query = "SELECT 
                pr.prescription_id,
                pr.prescription_date,
                pr.status,
                CONCAT(u.full_name) as doctor_name,
                d.specialization,
                COUNT(pd.detail_id) as medicine_count
              FROM prescriptions pr
              INNER JOIN doctors d ON pr.doctor_id = d.doctor_id
              INNER JOIN users u ON d.user_id = u.user_id
              LEFT JOIN prescription_details pd ON pr.prescription_id = pd.prescription_id
              WHERE pr.patient_id = :patient_id
              GROUP BY pr.prescription_id
              ORDER BY pr.prescription_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':patient_id', $patientId);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);
}

// Get pending prescriptions
function getPendingPrescriptions($conn) {
    $query = "SELECT 
                pr.prescription_id,
                pr.prescription_date,
                pr.notes,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                CONCAT(u.full_name) as doctor_name,
                COUNT(pd.detail_id) as medicine_count
              FROM prescriptions pr
              INNER JOIN patients p ON pr.patient_id = p.patient_id
              INNER JOIN doctors d ON pr.doctor_id = d.doctor_id
              INNER JOIN users u ON d.user_id = u.user_id
              LEFT JOIN prescription_details pd ON pr.prescription_id = pd.prescription_id
              WHERE pr.status = 'pending'
              GROUP BY pr.prescription_id
              ORDER BY pr.prescription_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);
}

// Create prescription with medicines
function createPrescription($conn) {
    $patientId = $_POST['patient_id'];
    $doctorId = $_POST['doctor_id'];
    $appointmentId = isset($_POST['appointment_id']) ? $_POST['appointment_id'] : null;
    $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : null;
    $medicines = json_decode($_POST['medicines'], true);
    
    // Validate
    if (empty($patientId) || empty($doctorId) || empty($medicines)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill all required fields'
        ]);
        return;
    }
    
    try {
        $conn->beginTransaction();
        
        // Insert prescription
        $query = "INSERT INTO prescriptions 
                  (patient_id, doctor_id, appointment_id, notes, status) 
                  VALUES 
                  (:patient_id, :doctor_id, :appointment_id, :notes, 'pending')";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->bindParam(':doctor_id', $doctorId);
        $stmt->bindParam(':appointment_id', $appointmentId);
        $stmt->bindParam(':notes', $notes);
        $stmt->execute();
        
        $prescriptionId = $conn->lastInsertId();
        
        // Insert prescription details
        $detailQuery = "INSERT INTO prescription_details 
                        (prescription_id, medicine_name, dosage, frequency, duration, instructions) 
                        VALUES 
                        (:prescription_id, :medicine_name, :dosage, :frequency, :duration, :instructions)";
        
        $detailStmt = $conn->prepare($detailQuery);
        
        foreach ($medicines as $medicine) {
            $detailStmt->bindParam(':prescription_id', $prescriptionId);
            $detailStmt->bindParam(':medicine_name', $medicine['medicine_name']);
            $detailStmt->bindParam(':dosage', $medicine['dosage']);
            $detailStmt->bindParam(':frequency', $medicine['frequency']);
            $detailStmt->bindParam(':duration', $medicine['duration']);
            $detailStmt->bindParam(':instructions', $medicine['instructions']);
            $detailStmt->execute();
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Prescription created successfully',
            'prescription_id' => $prescriptionId
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create prescription: ' . $e->getMessage()
        ]);
    }
}

// Update prescription
function updatePrescription($conn, $data) {
    $prescriptionId = $data['prescription_id'];
    $notes = isset($data['notes']) ? sanitize($data['notes']) : null;
    $medicines = json_decode($data['medicines'], true);
    
    try {
        $conn->beginTransaction();
        
        // Update prescription notes
        $query = "UPDATE prescriptions SET notes = :notes WHERE prescription_id = :prescription_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':prescription_id', $prescriptionId);
        $stmt->bindParam(':notes', $notes);
        $stmt->execute();
        
        // Delete existing details
        $deleteQuery = "DELETE FROM prescription_details WHERE prescription_id = :prescription_id";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':prescription_id', $prescriptionId);
        $deleteStmt->execute();
        
        // Insert new details
        $detailQuery = "INSERT INTO prescription_details 
                        (prescription_id, medicine_name, dosage, frequency, duration, instructions) 
                        VALUES 
                        (:prescription_id, :medicine_name, :dosage, :frequency, :duration, :instructions)";
        
        $detailStmt = $conn->prepare($detailQuery);
        
        foreach ($medicines as $medicine) {
            $detailStmt->bindParam(':prescription_id', $prescriptionId);
            $detailStmt->bindParam(':medicine_name', $medicine['medicine_name']);
            $detailStmt->bindParam(':dosage', $medicine['dosage']);
            $detailStmt->bindParam(':frequency', $medicine['frequency']);
            $detailStmt->bindParam(':duration', $medicine['duration']);
            $detailStmt->bindParam(':instructions', $medicine['instructions']);
            $detailStmt->execute();
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Prescription updated successfully'
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update prescription: ' . $e->getMessage()
        ]);
    }
}

// Dispense prescription
function dispensePrescription($conn, $data) {
    $prescriptionId = $data['prescription_id'];
    
    $query = "UPDATE prescriptions SET status = 'dispensed' WHERE prescription_id = :prescription_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':prescription_id', $prescriptionId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Prescription marked as dispensed'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to dispense prescription'
        ]);
    }
}

// Delete prescription
function deletePrescription($conn, $id) {
    try {
        $conn->beginTransaction();
        
        // Delete prescription details first
        $detailQuery = "DELETE FROM prescription_details WHERE prescription_id = :id";
        $detailStmt = $conn->prepare($detailQuery);
        $detailStmt->bindParam(':id', $id);
        $detailStmt->execute();
        
        // Delete prescription
        $query = "DELETE FROM prescriptions WHERE prescription_id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Prescription deleted successfully'
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete prescription: ' . $e->getMessage()
        ]);
    }
}
?>