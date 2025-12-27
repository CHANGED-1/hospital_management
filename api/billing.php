<?php
// api/billing.php
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
            getBills($conn);
        } elseif ($action === 'get' && isset($_GET['id'])) {
            getBill($conn, $_GET['id']);
        } elseif ($action === 'patient' && isset($_GET['patient_id'])) {
            getPatientBills($conn, $_GET['patient_id']);
        } elseif ($action === 'unpaid') {
            getUnpaidBills($conn);
        } elseif ($action === 'revenue') {
            getRevenue($conn, $_GET['from'], $_GET['to']);
        }
        break;
        
    case 'POST':
        if ($action === 'create') {
            createBill($conn);
        } elseif ($action === 'payment') {
            recordPayment($conn);
        }
        break;
        
    case 'PUT':
        if ($action === 'update') {
            parse_str(file_get_contents("php://input"), $_PUT);
            updateBill($conn, $_PUT);
        }
        break;
        
    case 'DELETE':
        if ($action === 'delete' && isset($_GET['id'])) {
            deleteBill($conn, $_GET['id']);
        }
        break;
}

// Get all bills
function getBills($conn) {
    $query = "SELECT 
                b.bill_id,
                b.bill_date,
                b.consultation_fee,
                b.medicine_charges,
                b.lab_charges,
                b.other_charges,
                b.total_amount,
                b.paid_amount,
                b.payment_status,
                b.payment_method,
                (b.total_amount - b.paid_amount) as balance,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.patient_id,
                p.phone as patient_phone
              FROM billing b
              INNER JOIN patients p ON b.patient_id = p.patient_id
              ORDER BY b.bill_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);
}

// Get single bill
function getBill($conn, $id) {
    $query = "SELECT 
                b.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.phone as patient_phone,
                p.email as patient_email,
                p.address as patient_address,
                (b.total_amount - b.paid_amount) as balance
              FROM billing b
              INNER JOIN patients p ON b.patient_id = p.patient_id
              WHERE b.bill_id = :id";
    
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
            'message' => 'Bill not found'
        ]);
    }
}

// Get patient bills
function getPatientBills($conn, $patientId) {
    $query = "SELECT 
                b.*,
                (b.total_amount - b.paid_amount) as balance
              FROM billing b
              WHERE b.patient_id = :patient_id
              ORDER BY b.bill_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':patient_id', $patientId);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);
}

// Get unpaid bills
function getUnpaidBills($conn) {
    $query = "SELECT 
                b.bill_id,
                b.bill_date,
                b.total_amount,
                b.paid_amount,
                (b.total_amount - b.paid_amount) as balance,
                b.payment_status,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.patient_id,
                p.phone as patient_phone
              FROM billing b
              INNER JOIN patients p ON b.patient_id = p.patient_id
              WHERE b.payment_status IN ('unpaid', 'partial')
              ORDER BY b.bill_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);
}

// Get revenue for a date range
function getRevenue($conn, $from, $to) {
    $query = "SELECT 
                DATE(bill_date) as date,
                COUNT(*) as bill_count,
                SUM(total_amount) as total_revenue,
                SUM(paid_amount) as collected_revenue,
                SUM(consultation_fee) as consultation_revenue,
                SUM(medicine_charges) as medicine_revenue,
                SUM(lab_charges) as lab_revenue,
                SUM(other_charges) as other_revenue
              FROM billing 
              WHERE DATE(bill_date) BETWEEN :from AND :to
              GROUP BY DATE(bill_date)
              ORDER BY DATE(bill_date) DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':from', $from);
    $stmt->bindParam(':to', $to);
    $stmt->execute();
    
    $daily = $stmt->fetchAll();
    
    // Get summary
    $summaryQuery = "SELECT 
                       COUNT(*) as total_bills,
                       SUM(total_amount) as total_revenue,
                       SUM(paid_amount) as collected_revenue,
                       SUM(total_amount - paid_amount) as pending_revenue
                     FROM billing 
                     WHERE DATE(bill_date) BETWEEN :from AND :to";
    
    $summaryStmt = $conn->prepare($summaryQuery);
    $summaryStmt->bindParam(':from', $from);
    $summaryStmt->bindParam(':to', $to);
    $summaryStmt->execute();
    
    $summary = $summaryStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'daily' => $daily,
        'summary' => $summary
    ]);
}

// Create bill
function createBill($conn) {
    $patientId = $_POST['patient_id'];
    $appointmentId = isset($_POST['appointment_id']) ? $_POST['appointment_id'] : null;
    $consultationFee = isset($_POST['consultation_fee']) ? $_POST['consultation_fee'] : 0;
    $medicineCharges = isset($_POST['medicine_charges']) ? $_POST['medicine_charges'] : 0;
    $labCharges = isset($_POST['lab_charges']) ? $_POST['lab_charges'] : 0;
    $otherCharges = isset($_POST['other_charges']) ? $_POST['other_charges'] : 0;
    $paidAmount = isset($_POST['paid_amount']) ? $_POST['paid_amount'] : 0;
    $paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cash';
    
    // Calculate total
    $totalAmount = $consultationFee + $medicineCharges + $labCharges + $otherCharges;
    
    // Determine payment status
    $paymentStatus = 'unpaid';
    if ($paidAmount >= $totalAmount) {
        $paymentStatus = 'paid';
    } elseif ($paidAmount > 0) {
        $paymentStatus = 'partial';
    }
    
    $query = "INSERT INTO billing 
              (patient_id, appointment_id, consultation_fee, medicine_charges, lab_charges, 
               other_charges, total_amount, paid_amount, payment_status, payment_method) 
              VALUES 
              (:patient_id, :appointment_id, :consultation_fee, :medicine_charges, :lab_charges,
               :other_charges, :total_amount, :paid_amount, :payment_status, :payment_method)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':patient_id', $patientId);
    $stmt->bindParam(':appointment_id', $appointmentId);
    $stmt->bindParam(':consultation_fee', $consultationFee);
    $stmt->bindParam(':medicine_charges', $medicineCharges);
    $stmt->bindParam(':lab_charges', $labCharges);
    $stmt->bindParam(':other_charges', $otherCharges);
    $stmt->bindParam(':total_amount', $totalAmount);
    $stmt->bindParam(':paid_amount', $paidAmount);
    $stmt->bindParam(':payment_status', $paymentStatus);
    $stmt->bindParam(':payment_method', $paymentMethod);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Bill created successfully',
            'bill_id' => $conn->lastInsertId()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create bill'
        ]);
    }
}

// Record payment
function recordPayment($conn) {
    $billId = $_POST['bill_id'];
    $amount = $_POST['amount'];
    $paymentMethod = $_POST['payment_method'];
    
    // Get current bill details
    $query = "SELECT total_amount, paid_amount FROM billing WHERE bill_id = :bill_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':bill_id', $billId);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Bill not found'
        ]);
        return;
    }
    
    $bill = $stmt->fetch();
    $newPaidAmount = $bill['paid_amount'] + $amount;
    
    // Determine new payment status
    $paymentStatus = 'partial';
    if ($newPaidAmount >= $bill['total_amount']) {
        $paymentStatus = 'paid';
        $newPaidAmount = $bill['total_amount']; // Cap at total amount
    }
    
    // Update bill
    $updateQuery = "UPDATE billing 
                    SET paid_amount = :paid_amount,
                        payment_status = :payment_status,
                        payment_method = :payment_method
                    WHERE bill_id = :bill_id";
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindParam(':bill_id', $billId);
    $updateStmt->bindParam(':paid_amount', $newPaidAmount);
    $updateStmt->bindParam(':payment_status', $paymentStatus);
    $updateStmt->bindParam(':payment_method', $paymentMethod);
    
    if ($updateStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'new_balance' => $bill['total_amount'] - $newPaidAmount
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to record payment'
        ]);
    }
}

// Update bill
function updateBill($conn, $data) {
    $billId = $data['bill_id'];
    $consultationFee = $data['consultation_fee'];
    $medicineCharges = $data['medicine_charges'];
    $labCharges = $data['lab_charges'];
    $otherCharges = $data['other_charges'];
    
    // Calculate new total
    $totalAmount = $consultationFee + $medicineCharges + $labCharges + $otherCharges;
    
    // Get current paid amount
    $query = "SELECT paid_amount FROM billing WHERE bill_id = :bill_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':bill_id', $billId);
    $stmt->execute();
    $bill = $stmt->fetch();
    
    // Determine payment status
    $paymentStatus = 'unpaid';
    if ($bill['paid_amount'] >= $totalAmount) {
        $paymentStatus = 'paid';
    } elseif ($bill['paid_amount'] > 0) {
        $paymentStatus = 'partial';
    }
    
    $updateQuery = "UPDATE billing 
                    SET consultation_fee = :consultation_fee,
                        medicine_charges = :medicine_charges,
                        lab_charges = :lab_charges,
                        other_charges = :other_charges,
                        total_amount = :total_amount,
                        payment_status = :payment_status
                    WHERE bill_id = :bill_id";
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindParam(':bill_id', $billId);
    $updateStmt->bindParam(':consultation_fee', $consultationFee);
    $updateStmt->bindParam(':medicine_charges', $medicineCharges);
    $updateStmt->bindParam(':lab_charges', $labCharges);
    $updateStmt->bindParam(':other_charges', $otherCharges);
    $updateStmt->bindParam(':total_amount', $totalAmount);
    $updateStmt->bindParam(':payment_status', $paymentStatus);
    
    if ($updateStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Bill updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update bill'
        ]);
    }
}

// Delete bill
function deleteBill($conn, $id) {
    $query = "DELETE FROM billing WHERE bill_id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Bill deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete bill'
        ]);
    }
}
?>