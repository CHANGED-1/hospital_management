<?php
/**
 * Patient Management Page
 * Complete CRUD operations for patients
 */

$pageTitle = "Patient Management";
require_once 'includes/header.php';
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Fetch all patients
try {
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
                status,
                registered_date
              FROM patients 
              ORDER BY patient_id DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $patients = $stmt->fetchAll();
} catch (PDOException $e) {
    logError("Patients fetch error: " . $e->getMessage());
    $patients = [];
}
?>

<style>
    .search-bar {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .search-input {
        flex: 1;
        min-width: 250px;
        padding: 12px 15px;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .action-btns {
        display: flex;
        gap: 10px;
    }

    .action-btns i {
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s;
    }

    .action-btns .fa-eye {
        color: var(--info-color);
    }

    .action-btns .fa-edit {
        color: var(--warning-color);
    }

    .action-btns .fa-trash {
        color: var(--danger-color);
    }

    .action-btns i:hover {
        transform: scale(1.2);
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        overflow-y: auto;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 10px;
        width: 90%;
        max-width: 700px;
        max-height: 90vh;
        overflow-y: auto;
        margin: 20px;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        padding: 20px 25px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h2 {
        font-size: 20px;
        color: var(--dark-color);
    }

    .close-btn {
        font-size: 24px;
        cursor: pointer;
        color: var(--text-muted);
        transition: color 0.3s;
    }

    .close-btn:hover {
        color: var(--danger-color);
    }

    .modal-body {
        padding: 25px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--dark-color);
        font-weight: 500;
        font-size: 14px;
    }

    .form-group label .required {
        color: var(--danger-color);
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    select.form-control {
        cursor: pointer;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .modal-footer {
        padding: 20px 25px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-secondary {
        background: var(--text-muted);
        color: white;
    }

    .btn-secondary:hover {
        background: var(--dark-color);
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="main-container">
    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-user-injured"></i> Patient Management</h1>
            <p>Manage patient records and information</p>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2>All Patients (<?php echo count($patients); ?>)</h2>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add New Patient
                </button>
            </div>

            <div class="search-bar">
                <input type="text" class="search-input" id="searchInput" 
                       placeholder="Search by name, phone, email..." 
                       onkeyup="searchPatients()">
                <select class="form-control" style="max-width: 200px;" onchange="filterPatients()" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <table id="patientsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Blood Group</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($patients) > 0): ?>
                        <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td>#P<?php echo str_pad($patient['patient_id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                            <td><?php echo $patient['age']; ?> years</td>
                            <td><?php echo htmlspecialchars($patient['gender']); ?></td>
                            <td><?php echo htmlspecialchars($patient['blood_group'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $patient['status'] === 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($patient['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <i class="fas fa-eye" onclick="viewPatient(<?php echo $patient['patient_id']; ?>)" title="View"></i>
                                    <i class="fas fa-edit" onclick="editPatient(<?php echo $patient['patient_id']; ?>)" title="Edit"></i>
                                    <i class="fas fa-trash" onclick="deletePatient(<?php echo $patient['patient_id']; ?>)" title="Delete"></i>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-user-injured"></i>
                                <p>No patients found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php require_once 'includes/footer.php'; ?>
    </main>
</div>

<!-- Add/Edit Patient Modal -->
<div class="modal" id="patientModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Patient</h2>
            <span class="close-btn" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="patientForm">
                <input type="hidden" id="patientId" name="patient_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name <span class="required">*</span></label>
                        <input type="text" id="firstName" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name <span class="required">*</span></label>
                        <input type="text" id="lastName" name="last_name" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dateOfBirth">Date of Birth <span class="required">*</span></label>
                        <input type="date" id="dateOfBirth" name="date_of_birth" class="form-control" required max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender <span class="required">*</span></label>
                        <select id="gender" name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="bloodGroup">Blood Group</label>
                        <select id="bloodGroup" name="blood_group" class="form-control">
                            <option value="">Select Blood Group</option>
                            <?php foreach (BLOOD_GROUPS as $group): ?>
                                <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control" required placeholder="+256 700 000000">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="patient@example.com">
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3" placeholder="Enter full address"></textarea>
                </div>

                <div class="form-group">
                    <label for="emergencyContact">Emergency Contact</label>
                    <input type="tel" id="emergencyContact" name="emergency_contact" class="form-control" placeholder="+256 700 000000">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="savePatient()">
                <i class="fas fa-save"></i> Save Patient
            </button>
        </div>
    </div>
</div>

<script>
    // Open add modal
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Patient';
        document.getElementById('patientForm').reset();
        document.getElementById('patientId').value = '';
        document.getElementById('patientModal').classList.add('active');
    }

    // Close modal
    function closeModal() {
        document.getElementById('patientModal').classList.remove('active');
    }

    // Save patient
    async function savePatient() {
        const form = document.getElementById('patientForm');
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        const patientId = document.getElementById('patientId').value;
        const action = patientId ? 'update' : 'create';

        showLoading();

        try {
            const response = await fetch(`../api/patients.php?action=${action}`, {
                method: patientId ? 'PUT' : 'POST',
                body: patientId ? new URLSearchParams(formData) : formData
            });

            const data = await response.json();

            hideLoading();

            if (data.success) {
                showToast(data.message, 'success');
                closeModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message, 'error');
            }
        } catch (error) {
            hideLoading();
            showToast('An error occurred. Please try again.', 'error');
            console.error('Error:', error);
        }
    }

    // View patient
    async function viewPatient(id) {
        window.location.href = `patient_details.php?id=${id}`;
    }

    // Edit patient
    async function editPatient(id) {
        showLoading();

        try {
            const response = await fetch(`../api/patients.php?action=get&id=${id}`);
            const data = await response.json();

            hideLoading();

            if (data.success) {
                const patient = data.data;
                
                document.getElementById('modalTitle').textContent = 'Edit Patient';
                document.getElementById('patientId').value = patient.patient_id;
                document.getElementById('firstName').value = patient.first_name;
                document.getElementById('lastName').value = patient.last_name;
                document.getElementById('dateOfBirth').value = patient.date_of_birth;
                document.getElementById('gender').value = patient.gender;
                document.getElementById('bloodGroup').value = patient.blood_group || '';
                document.getElementById('phone').value = patient.phone;
                document.getElementById('email').value = patient.email || '';
                document.getElementById('address').value = patient.address || '';
                document.getElementById('emergencyContact').value = patient.emergency_contact || '';
                
                document.getElementById('patientModal').classList.add('active');
            } else {
                showToast(data.message, 'error');
            }
        } catch (error) {
            hideLoading();
            showToast('An error occurred. Please try again.', 'error');
            console.error('Error:', error);
        }
    }

    // Delete patient
    async function deletePatient(id) {
        if (!confirmDelete('Are you sure you want to delete this patient? This action cannot be undone.')) {
            return;
        }

        showLoading();

        try {
            const response = await fetch(`../api/patients.php?action=delete&id=${id}`, {
                method: 'DELETE'
            });

            const data = await response.json();

            hideLoading();

            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message, 'error');
            }
        } catch (error) {
            hideLoading();
            showToast('An error occurred. Please try again.', 'error');
            console.error('Error:', error);
        }
    }

    // Search patients
    function searchPatients() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const table = document.getElementById('patientsTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let row of rows) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        }
    }

    // Filter patients
    function filterPatients() {
        const status = document.getElementById('statusFilter').value;
        const table = document.getElementById('patientsTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let row of rows) {
            if (!status) {
                row.style.display = '';
            } else {
                const statusCell = row.cells[6];
                const rowStatus = statusCell.textContent.toLowerCase();
                row.style.display = rowStatus.includes(status) ? '' : 'none';
            }
        }
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>