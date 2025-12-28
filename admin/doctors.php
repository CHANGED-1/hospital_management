<?php
/**
 * Doctor Management Page
 */

$pageTitle = "Doctor Management";
require_once 'includes/header.php';
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Fetch all doctors
try {
    $query = "SELECT 
                d.doctor_id,
                d.specialization,
                d.qualification,
                d.experience_years,
                d.consultation_fee,
                d.available_days,
                u.user_id,
                u.full_name,
                u.email,
                u.phone,
                u.status
              FROM doctors d
              INNER JOIN users u ON d.user_id = u.user_id
              ORDER BY d.doctor_id DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $doctors = $stmt->fetchAll();
} catch (PDOException $e) {
    logError("Doctors fetch error: " . $e->getMessage());
    $doctors = [];
}
?>

<div class="main-container">
    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-user-md"></i> Doctor Management</h1>
            <p>Manage doctors and their schedules</p>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2>All Doctors (<?php echo count($doctors); ?>)</h2>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add New Doctor
                </button>
            </div>

            <div class="search-bar">
                <input type="text" class="search-input" id="searchInput" 
                       placeholder="Search by name, specialization..." 
                       onkeyup="searchDoctors()">
                <select class="form-control" style="max-width: 250px;" onchange="filterDoctors()" id="specializationFilter">
                    <option value="">All Specializations</option>
                    <?php foreach (SPECIALIZATIONS as $spec): ?>
                        <option value="<?php echo $spec; ?>"><?php echo $spec; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <table id="doctorsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Doctor Name</th>
                        <th>Specialization</th>
                        <th>Qualification</th>
                        <th>Experience</th>
                        <th>Consultation Fee</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($doctors) > 0): ?>
                        <?php foreach ($doctors as $doctor): ?>
                        <tr>
                            <td>#D<?php echo str_pad($doctor['doctor_id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($doctor['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['qualification']); ?></td>
                            <td><?php echo $doctor['experience_years']; ?> years</td>
                            <td><?php echo formatCurrency($doctor['consultation_fee']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $doctor['status'] === 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($doctor['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <i class="fas fa-eye" onclick="viewDoctor(<?php echo $doctor['doctor_id']; ?>)" title="View"></i>
                                    <i class="fas fa-edit" onclick="editDoctor(<?php echo $doctor['doctor_id']; ?>)" title="Edit"></i>
                                    <i class="fas fa-calendar" onclick="setSchedule(<?php echo $doctor['doctor_id']; ?>)" title="Schedule"></i>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-user-md"></i>
                                <p>No doctors found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php require_once 'includes/footer.php'; ?>
    </main>
</div>

<!-- Add/Edit Doctor Modal -->
<div class="modal" id="doctorModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Doctor</h2>
            <span class="close-btn" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="doctorForm">
                <input type="hidden" id="doctorId" name="doctor_id">
                <input type="hidden" id="userId" name="user_id">
                
                <h3 style="margin-bottom: 15px; color: var(--primary-color);">Personal Information</h3>
                
                <div class="form-group">
                    <label for="fullName">Full Name <span class="required">*</span></label>
                    <input type="text" id="fullName" name="full_name" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" class="form-control">
                        <small style="color: var(--text-muted);">Leave blank to keep current password</small>
                    </div>
                </div>

                <h3 style="margin: 25px 0 15px; color: var(--primary-color);">Professional Information</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="specialization">Specialization <span class="required">*</span></label>
                        <select id="specialization" name="specialization" class="form-control" required>
                            <option value="">Select Specialization</option>
                            <?php foreach (SPECIALIZATIONS as $spec): ?>
                                <option value="<?php echo $spec; ?>"><?php echo $spec; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="experienceYears">Experience (Years) <span class="required">*</span></label>
                        <input type="number" id="experienceYears" name="experience_years" class="form-control" required min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="qualification">Qualification <span class="required">*</span></label>
                        <input type="text" id="qualification" name="qualification" class="form-control" required placeholder="e.g., MBBS, MD">
                    </div>
                    <div class="form-group">
                        <label for="consultationFee">Consultation Fee <span class="required">*</span></label>
                        <input type="number" id="consultationFee" name="consultation_fee" class="form-control" required min="0" step="0.01">
                    </div>
                </div>

                <div class="form-group">
                    <label>Available Days</label>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php foreach (DAYS_OF_WEEK as $day): ?>
                        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                            <input type="checkbox" name="available_days[]" value="<?php echo $day; ?>">
                            <span><?php echo $day; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="availableTimeStart">Available From</label>
                        <input type="time" id="availableTimeStart" name="available_time_start" class="form-control" value="09:00">
                    </div>
                    <div class="form-group">
                        <label for="availableTimeEnd">Available To</label>
                        <input type="time" id="availableTimeEnd" name="available_time_end" class="form-control" value="17:00">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveDoctor()">
                <i class="fas fa-save"></i> Save Doctor
            </button>
        </div>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Doctor';
        document.getElementById('doctorForm').reset();
        document.getElementById('doctorId').value = '';
        document.getElementById('password').required = true;
        document.getElementById('doctorModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('doctorModal').classList.remove('active');
    }

    async function saveDoctor() {
        const form = document.getElementById('doctorForm');
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        const doctorId = document.getElementById('doctorId').value;
        const action = doctorId ? 'update' : 'create';

        showLoading();

        try {
            const response = await fetch(`../api/doctors.php?action=${action}`, {
                method: 'POST',
                body: formData
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

    async function editDoctor(id) {
        showLoading();

        try {
            const response = await fetch(`../api/doctors.php?action=get&id=${id}`);
            const data = await response.json();
            hideLoading();

            if (data.success) {
                const doctor = data.data;
                
                document.getElementById('modalTitle').textContent = 'Edit Doctor';
                document.getElementById('doctorId').value = doctor.doctor_id;
                document.getElementById('userId').value = doctor.user_id;
                document.getElementById('fullName').value = doctor.full_name;
                document.getElementById('email').value = doctor.email;
                document.getElementById('phone').value = doctor.phone;
                document.getElementById('username').value = doctor.username;
                document.getElementById('password').required = false;
                document.getElementById('specialization').value = doctor.specialization;
                document.getElementById('experienceYears').value = doctor.experience_years;
                document.getElementById('qualification').value = doctor.qualification;
                document.getElementById('consultationFee').value = doctor.consultation_fee;
                
                // Set available days
                const availableDays = doctor.available_days ? doctor.available_days.split(',') : [];
                document.querySelectorAll('input[name="available_days[]"]').forEach(checkbox => {
                    checkbox.checked = availableDays.includes(checkbox.value);
                });
                
                document.getElementById('availableTimeStart').value = doctor.available_time_start || '09:00';
                document.getElementById('availableTimeEnd').value = doctor.available_time_end || '17:00';
                
                document.getElementById('doctorModal').classList.add('active');
            } else {
                showToast(data.message, 'error');
            }
        } catch (error) {
            hideLoading();
            showToast('An error occurred. Please try again.', 'error');
        }
    }

    function viewDoctor(id) {
        window.location.href = `doctor_details.php?id=${id}`;
    }

    function setSchedule(id) {
        showToast('Schedule management coming soon!', 'info');
    }

    function searchDoctors() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const table = document.getElementById('doctorsTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let row of rows) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        }
    }

    function filterDoctors() {
        const specialization = document.getElementById('specializationFilter').value;
        const table = document.getElementById('doctorsTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let row of rows) {
            if (!specialization) {
                row.style.display = '';
            } else {
                const specCell = row.cells[2];
                row.style.display = specCell.textContent.includes(specialization) ? '' : 'none';
            }
        }
    }
</script>