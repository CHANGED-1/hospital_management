<?php
/**
 * Sidebar Navigation for Admin Module
 */
?>
<style>
    .main-container {
        display: flex;
        min-height: calc(100vh - 70px);
    }

    .sidebar {
        width: 260px;
        background: white;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        position: sticky;
        top: 70px;
        height: calc(100vh - 70px);
        overflow-y: auto;
    }

    .sidebar-menu {
        list-style: none;
        padding: 20px 0;
    }

    .sidebar-menu li {
        margin: 5px 0;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px 25px;
        color: #666;
        text-decoration: none;
        transition: all 0.3s;
        font-size: 15px;
    }

    .sidebar-menu a:hover {
        background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding-left: 30px;
    }

    .sidebar-menu a.active {
        background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        border-right: 4px solid var(--secondary-color);
    }

    .sidebar-menu a i {
        font-size: 18px;
        width: 20px;
        text-align: center;
    }

    .sidebar-menu a .badge {
        margin-left: auto;
        background: var(--danger-color);
        color: white;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
    }

    .sidebar-section {
        padding: 15px 25px 10px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .sidebar-toggle {
        display: none;
        position: fixed;
        bottom: 20px;
        left: 20px;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 1000;
    }

    @media (max-width: 768px) {
        .sidebar {
            position: fixed;
            left: -260px;
            top: 70px;
            transition: left 0.3s;
            z-index: 999;
        }

        .sidebar.show {
            left: 0;
        }

        .sidebar-toggle {
            display: block;
        }

        .main-container {
            display: block;
        }
    }
</style>

<aside class="sidebar" id="sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-section">Patient Management</div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo BASE_URL; ?>admin/patients.php" class="<?php echo $currentPage === 'patients' ? 'active' : ''; ?>">
                <i class="fas fa-user-injured"></i>
                <span>All Patients</span>
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/medical_records.php" class="<?php echo $currentPage === 'medical_records' ? 'active' : ''; ?>">
                <i class="fas fa-file-medical"></i>
                <span>Medical Records</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-section">Appointments</div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo BASE_URL; ?>admin/appointments.php" class="<?php echo $currentPage === 'appointments' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>All Appointments</span>
                <span class="badge">5</span>
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/appointment_schedule.php" class="<?php echo $currentPage === 'appointment_schedule' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Schedule</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-section">Medical Services</div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo BASE_URL; ?>admin/doctors.php" class="<?php echo $currentPage === 'doctors' ? 'active' : ''; ?>">
                <i class="fas fa-user-md"></i>
                <span>Doctors</span>
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/prescriptions.php" class="<?php echo $currentPage === 'prescriptions' ? 'active' : ''; ?>">
                <i class="fas fa-prescription"></i>
                <span>Prescriptions</span>
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/medicines.php" class="<?php echo $currentPage === 'medicines' ? 'active' : ''; ?>">
                <i class="fas fa-pills"></i>
                <span>Medicines</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-section">Financial</div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo BASE_URL; ?>admin/billing.php" class="<?php echo $currentPage === 'billing' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Billing</span>
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/payments.php" class="<?php echo $currentPage === 'payments' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Payments</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-section">System</div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo BASE_URL; ?>admin/users.php" class="<?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/reports.php" class="<?php echo $currentPage === 'reports' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/settings.php" class="<?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>
</aside>

<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.sidebar-toggle');
        
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
</script>