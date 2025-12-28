<?php
/**
 * Hospital Management System
 * Application Constants
 * 
 * This file contains all constant values used throughout the application
 */

// ==================== USER ROLES ====================

define('ROLE_ADMIN', 'admin');
define('ROLE_DOCTOR', 'doctor');
define('ROLE_RECEPTIONIST', 'receptionist');
define('ROLE_PHARMACIST', 'pharmacist');

// ==================== USER STATUS ====================

define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');

// ==================== APPOINTMENT STATUS ====================

define('APPOINTMENT_SCHEDULED', 'scheduled');
define('APPOINTMENT_COMPLETED', 'completed');
define('APPOINTMENT_CANCELLED', 'cancelled');
define('APPOINTMENT_NO_SHOW', 'no-show');

// ==================== PRESCRIPTION STATUS ====================

define('PRESCRIPTION_PENDING', 'pending');
define('PRESCRIPTION_DISPENSED', 'dispensed');

// ==================== PAYMENT STATUS ====================

define('PAYMENT_UNPAID', 'unpaid');
define('PAYMENT_PARTIAL', 'partial');
define('PAYMENT_PAID', 'paid');

// ==================== PAYMENT METHODS ====================

define('PAYMENT_CASH', 'cash');
define('PAYMENT_CARD', 'card');
define('PAYMENT_INSURANCE', 'insurance');
define('PAYMENT_ONLINE', 'online');
define('PAYMENT_MOBILE_MONEY', 'mobile_money');

// ==================== GENDER OPTIONS ====================

define('GENDER_MALE', 'Male');
define('GENDER_FEMALE', 'Female');
define('GENDER_OTHER', 'Other');

// ==================== BLOOD GROUPS ====================

const BLOOD_GROUPS = [
    'A+',
    'A-',
    'B+',
    'B-',
    'AB+',
    'AB-',
    'O+',
    'O-'
];

// ==================== DAYS OF WEEK ====================

const DAYS_OF_WEEK = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday'
];

// ==================== DOCTOR SPECIALIZATIONS ====================

const SPECIALIZATIONS = [
    'General Physician',
    'Pediatrician',
    'Cardiologist',
    'Dermatologist',
    'Neurologist',
    'Orthopedic',
    'Gynecologist',
    'Dentist',
    'Psychiatrist',
    'ENT Specialist',
    'Ophthalmologist',
    'Urologist',
    'Gastroenterologist',
    'Endocrinologist',
    'Radiologist',
    'Anesthesiologist',
    'Surgeon',
    'Oncologist',
    'Nephrologist',
    'Pulmonologist'
];

// ==================== MEDICINE CATEGORIES ====================

const MEDICINE_CATEGORIES = [
    'Analgesics',
    'Antibiotics',
    'Antivirals',
    'Antifungals',
    'Antihistamines',
    'Antacids',
    'Antipyretics',
    'Anti-inflammatory',
    'Cardiovascular',
    'Dermatological',
    'Gastrointestinal',
    'Hormones',
    'Supplements',
    'Vaccines',
    'Others'
];

// ==================== DOSAGE FREQUENCIES ====================

const DOSAGE_FREQUENCIES = [
    'Once daily',
    'Twice daily',
    'Three times daily',
    'Four times daily',
    'Every 4 hours',
    'Every 6 hours',
    'Every 8 hours',
    'Every 12 hours',
    'Before meals',
    'After meals',
    'At bedtime',
    'As needed',
    'Weekly',
    'Monthly'
];

// ==================== DOSAGE DURATIONS ====================

const DOSAGE_DURATIONS = [
    '3 days',
    '5 days',
    '7 days',
    '10 days',
    '14 days',
    '1 month',
    '2 months',
    '3 months',
    'Ongoing',
    'As directed'
];

// ==================== BILL CHARGE TYPES ====================

define('CHARGE_CONSULTATION', 'consultation_fee');
define('CHARGE_MEDICINE', 'medicine_charges');
define('CHARGE_LAB', 'lab_charges');
define('CHARGE_OTHER', 'other_charges');

// ==================== REPORT TYPES ====================

define('REPORT_PATIENT', 'patient_report');
define('REPORT_APPOINTMENT', 'appointment_report');
define('REPORT_REVENUE', 'revenue_report');
define('REPORT_PRESCRIPTION', 'prescription_report');
define('REPORT_INVENTORY', 'inventory_report');

// ==================== NOTIFICATION TYPES ====================

define('NOTIFICATION_SUCCESS', 'success');
define('NOTIFICATION_ERROR', 'error');
define('NOTIFICATION_WARNING', 'warning');
define('NOTIFICATION_INFO', 'info');

// ==================== FILE TYPES ====================

define('FILE_TYPE_IMAGE', 'image');
define('FILE_TYPE_DOCUMENT', 'document');
define('FILE_TYPE_PDF', 'pdf');

// ==================== PERMISSIONS ====================

const PERMISSIONS = [
    'manage_users' => 'Manage Users',
    'manage_patients' => 'Manage Patients',
    'manage_doctors' => 'Manage Doctors',
    'manage_appointments' => 'Manage Appointments',
    'manage_prescriptions' => 'Manage Prescriptions',
    'manage_billing' => 'Manage Billing',
    'manage_medicines' => 'Manage Medicines',
    'view_reports' => 'View Reports',
    'manage_settings' => 'Manage Settings'
];

// ==================== ROLE PERMISSIONS MAPPING ====================

const ROLE_PERMISSIONS = [
    ROLE_ADMIN => [
        'manage_users',
        'manage_patients',
        'manage_doctors',
        'manage_appointments',
        'manage_prescriptions',
        'manage_billing',
        'manage_medicines',
        'view_reports',
        'manage_settings'
    ],
    ROLE_DOCTOR => [
        'manage_patients',
        'manage_appointments',
        'manage_prescriptions',
        'view_reports'
    ],
    ROLE_RECEPTIONIST => [
        'manage_patients',
        'manage_appointments',
        'manage_billing'
    ],
    ROLE_PHARMACIST => [
        'manage_prescriptions',
        'manage_medicines',
        'view_reports'
    ]
];

// ==================== HELPER FUNCTIONS FOR CONSTANTS ====================

/**
 * Check if user has permission
 * @param string $permission
 * @return bool
 */
function hasPermission($permission) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    $role = $_SESSION['role'];
    
    if (!isset(ROLE_PERMISSIONS[$role])) {
        return false;
    }
    
    return in_array($permission, ROLE_PERMISSIONS[$role]);
}

/**
 * Get status badge class
 * @param string $status
 * @return string
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case STATUS_ACTIVE:
        case APPOINTMENT_COMPLETED:
        case PRESCRIPTION_DISPENSED:
        case PAYMENT_PAID:
            return 'badge-success';
        
        case APPOINTMENT_SCHEDULED:
        case PRESCRIPTION_PENDING:
            return 'badge-info';
        
        case STATUS_INACTIVE:
        case APPOINTMENT_CANCELLED:
        case APPOINTMENT_NO_SHOW:
            return 'badge-danger';
        
        case PAYMENT_PARTIAL:
            return 'badge-warning';
        
        default:
            return 'badge-secondary';
    }
}

/**
 * Get readable status text
 * @param string $status
 * @return string
 */
function getStatusText($status) {
    return ucwords(str_replace(['_', '-'], ' ', $status));
}

/**
 * Get payment method icon
 * @param string $method
 * @return string
 */
function getPaymentMethodIcon($method) {
    switch ($method) {
        case PAYMENT_CASH:
            return 'fa-money-bill-wave';
        case PAYMENT_CARD:
            return 'fa-credit-card';
        case PAYMENT_INSURANCE:
            return 'fa-shield-alt';
        case PAYMENT_ONLINE:
            return 'fa-globe';
        case PAYMENT_MOBILE_MONEY:
            return 'fa-mobile-alt';
        default:
            return 'fa-wallet';
    }
}
?>