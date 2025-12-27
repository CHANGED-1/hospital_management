<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - Hospital Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            font-weight: 600;
            color: #667eea;
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }

        .page-header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            font-size: 28px;
            color: #333;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .content-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .search-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: #f8f9fa;
        }

        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }

        table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
            font-size: 14px;
        }

        table tbody tr:hover {
            background: #f8f9fa;
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
            color: #667eea;
        }

        .action-btns .fa-edit {
            color: #11998e;
        }

        .action-btns .fa-trash {
            color: #f44336;
        }

        .action-btns i:hover {
            transform: scale(1.2);
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

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
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 20px;
            color: #333;
        }

        .close-btn {
            font-size: 24px;
            cursor: pointer;
            color: #999;
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
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        select.form-control {
            cursor: pointer;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <i class="fas fa-hospital"></i>
            <span>Hospital MS - Patients</span>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-user-injured"></i> Patient Management</h1>
            <button class="btn btn-primary" onclick="openModal()">
                <i class="fas fa-plus"></i> Add New Patient
            </button>
        </div>

        <div class="content-card">
            <div class="search-bar">
                <input type="text" class="search-input" id="searchInput" placeholder="Search patients by name, phone, or email...">
                <button class="btn btn-primary" onclick="searchPatients()">
                    <i class="fas fa-search"></i> Search
                </button>
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
                    <tr>
                        <td>#P001</td>
                        <td>John Doe</td>
                        <td>35</td>
                        <td>Male</td>
                        <td>O+</td>
                        <td>+256 700 123456</td>
                        <td><span class="badge badge-success">Active</span></td>
                        <td>
                            <div class="action-btns">
                                <i class="fas fa-eye" onclick="viewPatient(1)" title="View"></i>
                                <i class="fas fa-edit" onclick="editPatient(1)" title="Edit"></i>
                                <i class="fas fa-trash" onclick="deletePatient(1)" title="Delete"></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>#P002</td>
                        <td>Jane Smith</td>
                        <td>28</td>
                        <td>Female</td>
                        <td>A+</td>
                        <td>+256 700 234567</td>
                        <td><span class="badge badge-success">Active</span></td>
                        <td>
                            <div class="action-btns">
                                <i class="fas fa-eye" onclick="viewPatient(2)" title="View"></i>
                                <i class="fas fa-edit" onclick="editPatient(2)" title="Edit"></i>
                                <i class="fas fa-trash" onclick="deletePatient(2)" title="Delete"></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>#P003</td>
                        <td>Robert Wilson</td>
                        <td>42</td>
                        <td>Male</td>
                        <td>B+</td>
                        <td>+256 700 345678</td>
                        <td><span class="badge badge-success">Active</span></td>
                        <td>
                            <div class="action-btns">
                                <i class="fas fa-eye" onclick="viewPatient(3)" title="View"></i>
                                <i class="fas fa-edit" onclick="editPatient(3)" title="Edit"></i>
                                <i class="fas fa-trash" onclick="deletePatient(3)" title="Delete"></i>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
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
                    <input type="hidden" id="patientId">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name *</label>
                            <input type="text" id="firstName" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name *</label>
                            <input type="text" id="lastName" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dateOfBirth">Date of Birth *</label>
                            <input type="date" id="dateOfBirth" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select id="gender" class="form-control" required>
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
                            <select id="bloodGroup" class="form-control">
                                <option value="">Select Blood Group</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="emergencyContact">Emergency Contact</label>
                        <input type="tel" id="emergencyContact" class="form-control">
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
        function openModal() {
            document.getElementById('patientModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Add New Patient';
            document.getElementById('patientForm').reset();
        }

        function closeModal() {
            document.getElementById('patientModal').classList.remove('active');
        }

        function savePatient() {
            const formData = {
                firstName: document.getElementById('firstName').value,
                lastName: document.getElementById('lastName').value,
                dateOfBirth: document.getElementById('dateOfBirth').value,
                gender: document.getElementById('gender').value,
                bloodGroup: document.getElementById('bloodGroup').value,
                phone: document.getElementById('phone').value,
                email: document.getElementById('email').value,
                address: document.getElementById('address').value,
                emergencyContact: document.getElementById('emergencyContact').value
            };

            // Here you would send this data to your PHP backend
            console.log('Saving patient:', formData);
            
            // Simulating save
            alert('Patient saved successfully!');
            closeModal();
        }

        function viewPatient(id) {
            alert('View patient details for ID: ' + id);
        }

        function editPatient(id) {
            document.getElementById('modalTitle').textContent = 'Edit Patient';
            document.getElementById('patientModal').classList.add('active');
            // Load patient data here
        }

        function deletePatient(id) {
            if (confirm('Are you sure you want to delete this patient?')) {
                alert('Patient deleted: ' + id);
            }
        }

        function searchPatients() {
            const searchTerm = document.getElementById('searchInput').value;
            console.log('Searching for:', searchTerm);
        }
    </script>
</body>
</html>