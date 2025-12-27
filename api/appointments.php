<?php
// api/appointments.php
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
            getAppointments($conn);
        } elseif ($action === 'get' && isset($_GET['id'])) {
            getAppointment($conn, $_GET['id']);
        } elseif ($action === 'today') {
            getTodayAppointments($conn);
        } elseif ($action === 'patient' && isset($_GET['patient_id'])) {
            getPatientAppointments($conn, $_GET['patient_id']);
        } elseif ($action === 'doctor' && isset($_GET['doctor_id'])) {
            getDoctorAppointments($conn, $_GET['doctor_id']);
        } elseif ($action === 'check_availability') {
            checkAvailability($conn, $_GET['doctor_id'], $_GET['date'], $_GET['time']);
        }
        break;
        
    case 'POST':
        if ($action === 'create') {
            createAppointment($conn);
        }
        break;
        
    case 'PUT':
        if ($action === 'update') {
            parse_str(file_get_contents("php://input"), $_PUT);
            updateAppointment($conn, $_PUT);
        } elseif ($action === 'status') {
            parse_str(file_get_contents("php://input"), $_PUT);
            updateAppointmentStatus($conn, $_PUT);
        }
        break;
        
    case 'DELETE':
        if ($action === 'delete' && isset($_GET['id'])) {
            deleteAppointment($conn, $_GET['id']);
        }
        break;
}

// Get all appointments
function getAppointments($conn) {
    $query = "SELECT 
                a.appointment_id,
                a.appointment_date,
                a.appointment_time,
                a.reason,
                a.status,
                a.notes,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.patient_id,
                p.phone as patient_phone,
                CONCAT(u.full_name) as doctor_name,
                d.doctor_id,
                d.specialization
              FROM appointments a
              INNER JOIN patients p ON a.patient_id = p.patient_id
              INNER JOIN doctors d ON a.doctor_id = d.doctor_id
              INNER JOIN users u ON d.user_id = u.user_id
              ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $appointments = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $appointments
    ]);
}

// Get single appointment
function getAppointment($conn, $id) {
    $query = "SELECT 
                a.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.phone as patient_phone,
                p.date_of_birth,
                p.gender,
                CONCAT(u.full_name) as doctor_name,
                d.specialization,
                d.consultation_fee
              FROM appointments a
              INNER JOIN patients p ON a.patient_id = p.patient_id
              INNER JOIN doctors d ON a.doctor_id = d.doctor_id
              INNER JOIN users u ON d.user_id = u.user_id
              WHERE a.appointment_id = :id";
    
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
            'message' => 'Appointment not found'
        ]);
    }
}

// Get today's appointments
function getTodayAppointments($conn) {
    $today = date('Y-m-d');
    
    $query = "SELECT 
                a.appointment_id,
                a.appointment_date,
                a.appointment_time,
                a.status,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                CONCAT(u.full_name) as doctor_name,
                d.specialization
              FROM appointments a
              INNER JOIN patients p ON a.patient_id = p.patient_id
              INNER JOIN doctors d ON a.doctor_id = d.doctor_id
              INNER JOIN users u ON d.user_id = u.user_id
              WHERE a.appointment_date = :today
              ORDER BY a.appointment_time ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':today', $today);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);
}

// Get patient appointments
function getPatientAppointments($conn, $patientId) {
    $query = "SELECT 
                a.*,
                CONCAT(u.full_name) as doctor_name,
                d.specialization
              FROM appointments a
              INNER JOIN doctors d ON a.doctor_id = d.doctor_id
              INNER JOIN users u ON d.user_id = u.user_id
              WHERE a.patient_id = :patient_id
              ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':patient_id', $patientId);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);
}

// Get doctor appointments
function getDoctorAppointments($conn, $doctorId) {
    $query = "SELECT 
                a.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.phone as patient_phone
              FROM appointments a
              INNER JOIN patients p ON a.patient_id = p.patient_id
              WHERE a.doctor_id = :doctor_id
              ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':doctor_id', $doctorId);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);
}

// Check doctor availability
function checkAvailability($conn, $doctorId, $date, $time) {
    $query = "SELECT COUNT(*) as count 
              FROM appointments 
              WHERE doctor_id = :doctor_id 
                AND appointment_date = :date 
                AND appointment_time = :time 
                AND status != 'cancelled'";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':doctor_id', $doctorId);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':time', $time);
    $stmt->execute();
    
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'available' => $result['count'] == 0
    ]);
}

// Create appointment
function createAppointment($conn) {
    $patientId = $_POST['patient_id'];
    $doctorId = $_POST['doctor_id'];
    $appointmentDate = $_POST['appointment_date'];
    $appointmentTime = $_POST['appointment_time'];
    $reason = isset($_POST['reason']) ? sanitize($_POST['reason']) : null;
    
    // Validate required fields
    if (empty($patientId) || empty($doctorId) || empty($appointmentDate) || empty($appointmentTime)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill all required fields'
        ]);
        return;
    }
    
    // Check if slot is available
    $checkQuery = "SELECT COUNT(*) as count 
                   FROM appointments 
                   WHERE doctor_id = :doctor_id 
                     AND appointment_date = :date 
                     AND appointment_time = :time 
                     AND status != 'cancelled'";
    
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':doctor_id', $doctorId);
    $checkStmt->bindParam(':date', $appointmentDate);
    $checkStmt->bindParam(':time', $appointmentTime);
    $checkStmt->execute();
    
    $result = $checkStmt->fetch();
    if ($result['count'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'This time slot is not available'
        ]);
        return;
    }
    
    $query = "INSERT INTO appointments 
              (patient_id, doctor_id, appointment_date, appointment_time, reason, status) 
              VALUES 
              (:patient_id, :doctor_id, :appointment_date, :appointment_time, :reason, 'scheduled')";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':patient_id', $patientId);
    $stmt->bindParam(':doctor_id', $doctorId);
    $stmt->bindParam(':appointment_date', $appointmentDate);
    $stmt->bindParam(':appointment_time', $appointmentTime);
    $stmt->bindParam(':reason', $reason);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Appointment created successfully',
            'appointment_id' => $conn->lastInsertId()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create appointment'
        ]);
    }
}

// Update appointment
function updateAppointment($conn, $data) {
    $appointmentId = $data['appointment_id'];
    $patientId = $data['patient_id'];
    $doctorId = $data['doctor_id'];
    $appointmentDate = $data['appointment_date'];
    $appointmentTime = $data['appointment_time'];
    $reason = isset($data['reason']) ? sanitize($data['reason']) : null;
    $notes = isset($data['notes']) ? sanitize($data['notes']) : null;
    
    $query = "UPDATE appointments 
              SET patient_id = :patient_id,
                  doctor_id = :doctor_id,
                  appointment_date = :appointment_date,
                  appointment_time = :appointment_time,
                  reason = :reason,
                  notes = :notes
              WHERE appointment_id = :appointment_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':appointment_id', $appointmentId);
    $stmt->bindParam(':patient_id', $patientId);
    $stmt->bindParam(':doctor_id', $doctorId);
    $stmt->bindParam(':appointment_date', $appointmentDate);
    $stmt->bindParam(':appointment_time', $appointmentTime);
    $stmt->bindParam(':reason', $reason);
    $stmt->bindParam(':notes', $notes);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Appointment updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update appointment'
        ]);
    }
}

// Update appointment status
function updateAppointmentStatus($conn, $data) {
    $appointmentId = $data['appointment_id'];
    $status = $data['status'];
    
    $query = "UPDATE appointments SET status = :status WHERE appointment_id = :appointment_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':appointment_id', $appointmentId);
    $stmt->bindParam(':status', $status);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Appointment status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update appointment status'
        ]);
    }
}

// Delete appointment
function deleteAppointment($conn, $id) {
    $query = "UPDATE appointments SET status = 'cancelled' WHERE appointment_id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Appointment cancelled successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to cancel appointment'
        ]);
    }
}
?>