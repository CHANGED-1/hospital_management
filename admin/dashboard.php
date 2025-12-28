<?php
/**
 * Admin Dashboard
 * Main dashboard with statistics and overview
 */

$pageTitle = "Dashboard";
require_once 'includes/header.php';

// Get database connection
require_once __DIR__ . '/../config/database.php';
$database = new Database();
$conn = $database->getConnection();

// Fetch statistics
try {
    // Total Patients
    $stmt = $conn->query("SELECT COUNT(*) as count FROM patients WHERE status = 'active'");
    $totalPatients = $stmt->fetch()['count'];

    // Today's Appointments
    $stmt = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = CURDATE()");
    $todayAppointments = $stmt->fetch()['count'];

    // Active Doctors
    $stmt = $conn->query("SELECT COUNT(*) as count FROM doctors d INNER JOIN users u ON d.user_id = u.user_id WHERE u.status = 'active'");
    $activeDoctors = $stmt->fetch()['count'];

    // Today's Revenue
    $stmt = $conn->query("SELECT COALESCE(SUM(paid_amount), 0) as total FROM billing WHERE DATE(bill_date) = CURDATE()");
    $todayRevenue = $stmt->fetch()['total'];

    // Pending Prescriptions
    $stmt = $conn->query("SELECT COUNT(*) as count FROM prescriptions WHERE status = 'pending'");
    $pendingPrescriptions = $stmt->fetch()['count'];

    // Low Stock Medicines
    $stmt = $conn->query("SELECT COUNT(*) as count FROM medicines WHERE stock_quantity <= reorder_level");
    $lowStockMedicines = $stmt->fetch()['count'];

    // Recent Appointments
    $stmt = $conn->query("
        SELECT 
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
        WHERE a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 10
    ");
    $recentAppointments = $stmt->fetchAll();

} catch (PDOException $e) {
    logError("Dashboard query error: " . $e->getMessage());
    $totalPatients = $todayAppointments = $activeDoctors = $todayRevenue = 0;
    $pendingPrescriptions = $lowStockMedicines = 0;
    $recentAppointments = [];
}
?>

<style>
    .main-content {
        flex: 1;
        padding: 30px;
        overflow-y: auto;
    }

    .page-header {
        margin-bottom: 30px;
    }

    .page-header h1 {
        font-size: 28px;
        color: var(--dark-color);
        margin-bottom: 5px;
    }

    .page-header p {
        color: var(--text-muted);
        font-size: 14px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: transform 0.3s, box-shadow 0.3s;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    .stat-info h3 {
        font-size: 32px;
        color: var(--dark-color);
        margin-bottom: 5px;
        font-weight: 700;
    }

    .stat-info p {
        color: var(--text-muted);
        font-size: 14px;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-icon.green {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-icon.yellow {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .stat-icon.purple {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: var(--primary-color);
    }

    .stat-icon.red {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    }

    .content-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .content-card {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--light-color);
    }

    .card-header h2 {
        font-size: 20px;
        color: var(--dark-color);
        font-weight: 600;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table thead {
        background: var(--light-color);
    }

    table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: var(--text-muted);
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    table td {
        padding: 15px;
        border-bottom: 1px solid var(--light-color);
        color: var(--text-color);
        font-size: 14px;
    }

    table tbody tr:hover {
        background: var(--light-color);
    }

    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.3;
    }

    @media (max-width: 768px) {
        .main-content {
            padding: 20px 15px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        table {
            font-size: 12px;
        }

        table th, table td {
            padding: 10px;
        }
    }
</style>

<div class="main-container">
    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-th-large"></i> Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($currentUserName); ?>! Here's what's happening today.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card" onclick="location.href='patients.php'">
                <div class="stat-info">
                    <h3><?php echo number_format($totalPatients); ?></h3>
                    <p>Total Patients</p>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-user-injured"></i>
                </div>
            </div>

            <div class="stat-card" onclick="location.href='appointments.php'">
                <div class="stat-info">
                    <h3><?php echo number_format($todayAppointments); ?></h3>
                    <p>Today's Appointments</p>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>

            <div class="stat-card" onclick="location.href='doctors.php'">
                <div class="stat-info">
                    <h3><?php echo number_format($activeDoctors); ?></h3>
                    <p>Active Doctors</p>
                </div>
                <div class="stat-icon orange">
                    <i class="fas fa-user-md"></i>
                </div>
            </div>

            <div class="stat-card" onclick="location.href='billing.php'">
                <div class="stat-info">
                    <h3><?php echo formatCurrency($todayRevenue); ?></h3>
                    <p>Today's Revenue</p>
                </div>
                <div class="stat-icon yellow">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>

            <div class="stat-card" onclick="location.href='prescriptions.php'">
                <div class="stat-info">
                    <h3><?php echo number_format($pendingPrescriptions); ?></h3>
                    <p>Pending Prescriptions</p>
                </div>
                <div class="stat-icon purple">
                    <i class="fas fa-prescription"></i>
                </div>
            </div>

            <div class="stat-card" onclick="location.href='medicines.php'">
                <div class="stat-info">
                    <h3><?php echo number_format($lowStockMedicines); ?></h3>
                    <p>Low Stock Alerts</p>
                </div>
                <div class="stat-icon red">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <!-- Recent Appointments -->
        <div class="content-row">
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fas fa-calendar-alt"></i> Upcoming Appointments</h2>
                    <a href="appointments.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Appointment
                    </a>
                </div>

                <?php if (count($recentAppointments) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Specialization</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentAppointments as $appointment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['specialization']); ?></td>
                            <td>
                                <?php echo formatDate($appointment['appointment_date']); ?><br>
                                <small><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></small>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo getStatusBadgeClass($appointment['status']); ?>">
                                    <?php echo getStatusText($appointment['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="appointment_details.php?id=<?php echo $appointment['appointment_id']; ?>" 
                                   style="color: var(--primary-color); text-decoration: none;" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No upcoming appointments</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php require_once 'includes/footer.php'; ?>
    </main>
</div>