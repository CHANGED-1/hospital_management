# 🏥 Hospital Management System

<div align="center">

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?logo=mysql)
![License](https://img.shields.io/badge/license-Educational-green.svg)

**A comprehensive web-based Hospital Management System built with PHP, MySQL, HTML, CSS, JavaScript, and FontAwesome**

[Features](#-features) • [Installation](#-installation) • [Usage](#-usage) • [API Documentation](#-api-documentation) • [Screenshots](#-screenshots)

</div>

---

## 📋 Table of Contents

- [About](#-about)
- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [User Roles](#-user-roles)
- [API Documentation](#-api-documentation)
- [Database Schema](#-database-schema)
- [Screenshots](#-screenshots)
- [Testing](#-testing)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [License](#-license)
- [Contact](#-contact)

---

## 📖 About

The Hospital Management System (HMS) is a comprehensive web-based application designed to streamline hospital operations. It manages patient records, appointment scheduling, prescription management, billing, and more. Built as a final year IT project, it demonstrates full-stack development skills with PHP and MySQL.

### Project Goals

- Digitalize hospital operations
- Improve patient care efficiency
- Streamline administrative tasks
- Provide role-based access control
- Generate comprehensive reports

---

## ✨ Features

### 👥 Patient Management
- ✅ Patient registration with complete details
- ✅ Search and filter patients
- ✅ View patient medical history
- ✅ Update patient information
- ✅ Track patient appointments

### 📅 Appointment Scheduling
- ✅ Book appointments with doctors
- ✅ Check doctor availability
- ✅ View appointment calendar
- ✅ Reschedule appointments
- ✅ Cancel appointments
- ✅ Appointment status tracking

### 💊 Prescription Management
- ✅ Create digital prescriptions
- ✅ Multiple medicines per prescription
- ✅ Dosage and frequency tracking
- ✅ Prescription history
- ✅ Print prescriptions
- ✅ Dispense tracking

### 💰 Billing System
- ✅ Generate itemized bills
- ✅ Multiple charge categories
- ✅ Payment processing
- ✅ Partial payment support
- ✅ Payment history
- ✅ Revenue reports

### 👨‍⚕️ Doctor Management
- ✅ Doctor profiles
- ✅ Specialization tracking
- ✅ Availability scheduling
- ✅ Consultation fee management
- ✅ Performance analytics

### 💊 Medicine Inventory
- ✅ Medicine stock management
- ✅ Expiry date tracking
- ✅ Low stock alerts
- ✅ Purchase tracking
- ✅ Medicine search

### 📊 Reports & Analytics
- ✅ Patient statistics
- ✅ Appointment trends
- ✅ Revenue reports
- ✅ Doctor performance
- ✅ Inventory reports

### 🔐 Security Features
- ✅ Role-based access control
- ✅ Secure password hashing
- ✅ Session management
- ✅ SQL injection prevention
- ✅ XSS protection

---

## 🛠️ Technology Stack

### Frontend
- **HTML5** - Structure and semantics
- **CSS3** - Styling and layouts
- **JavaScript (ES6)** - Client-side logic
- **FontAwesome 6.4.0** - Icons
- **AJAX/Fetch API** - Asynchronous requests

### Backend
- **PHP 7.4+** - Server-side scripting
- **PDO** - Database abstraction
- **REST API** - API architecture

### Database
- **MySQL 5.7+** - Relational database

### Tools & Libraries
- **XAMPP/WAMP** - Development environment
- **Git** - Version control
- **VS Code** - Code editor

---

## 💻 System Requirements

### Minimum Requirements
- **OS**: Windows 7+, Linux, macOS
- **RAM**: 2GB
- **Disk Space**: 500MB
- **Browser**: Chrome 90+, Firefox 88+, Edge 90+

### Software Requirements
- **Web Server**: Apache 2.4+
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **XAMPP/WAMP/LAMP**: Latest version

---

## 🚀 Installation

### Step 1: Clone Repository

```bash
git clone https://github.com/CHANGED-1/hospital_management.git
cd hospital_management
```

Or download and extract the ZIP file.

### Step 2: Setup Web Server

1. Install XAMPP/WAMP
2. Copy project folder to `htdocs` (XAMPP) or `www` (WAMP)
3. Start Apache and MySQL services

### Step 3: Create Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create new database: `hospital_management`
3. Import SQL file: `database/schema.sql`
4. (Optional) Import sample data: `database/seed_data.sql`

### Step 4: Configure Application

Edit `config/database.php`:

```php
private $host = "localhost";
private $db_name = "hospital_management";
private $username = "root";
private $password = ""; // Your MySQL password
```

Edit `config/config.php`:

```php
define('BASE_URL', 'http://localhost/hospital_management/');
date_default_timezone_set('Africa/Kampala'); // Your timezone
```

### Step 5: Set Permissions

```bash
chmod 755 assets/uploads/
chmod 755 logs/
chmod 644 config/*.php
```

### Step 6: Access Application

Navigate to: `http://localhost/hospital_management/`

---

## ⚙️ Configuration

### Default Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | password |
| Doctor | doctor1 | password |
| Receptionist | reception1 | password |
| Pharmacist | pharma1 | password |

**⚠️ Change default passwords immediately after first login!**

### Environment Variables

Create `.env` file (optional):

```env
DB_HOST=localhost
DB_NAME=hospital_management
DB_USER=root
DB_PASS=
APP_ENV=development
APP_DEBUG=true
```

### Email Configuration

Edit `includes/email.php` for email notifications:

```php
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

---

## 📘 Usage

### Admin Module

#### Dashboard
- View key statistics
- Today's appointments
- Recent activities
- Quick actions

#### Patient Management
1. Navigate to **Patients** menu
2. Click **Add New Patient**
3. Fill patient details
4. Click **Save Patient**

#### Appointment Scheduling
1. Navigate to **Appointments**
2. Click **New Appointment**
3. Select patient and doctor
4. Choose date and time
5. Add reason for visit
6. Click **Schedule Appointment**

#### Billing
1. Navigate to **Billing**
2. Click **Create Bill**
3. Select patient
4. Add charges (consultation, medicines, lab)
5. Record payment
6. Generate receipt

### Doctor Module

#### View Appointments
1. Login as doctor
2. Dashboard shows today's appointments
3. Click appointment to view details
4. Update appointment status

#### Create Prescription
1. Open patient appointment
2. Click **Create Prescription**
3. Add medicines with dosage
4. Add instructions
5. Save prescription

### Receptionist Module

#### Check-in Patient
1. View today's appointments
2. Find patient
3. Mark as checked-in
4. Send to doctor

#### Register New Patient
1. Navigate to **Patients**
2. Click **Register New Patient**
3. Complete registration form
4. Assign patient ID

### Pharmacist Module

#### Dispense Medicine
1. View pending prescriptions
2. Select prescription
3. Check medicine availability
4. Mark as dispensed
5. Update inventory

---

## 👥 User Roles

### Admin
**Full System Access**
- Manage all users
- Configure system settings
- View all reports
- Access all modules

### Doctor
**Medical Operations**
- View assigned patients
- Manage appointments
- Create prescriptions
- Add medical records

### Receptionist
**Front Desk Operations**
- Register patients
- Schedule appointments
- Generate bills
- Process payments

### Pharmacist
**Pharmacy Operations**
- Manage medicine inventory
- Dispense prescriptions
- Track expiry dates
- Generate inventory reports

---

## 🔌 API Documentation

### Base URL
```
http://localhost/hospital_management/api/
```

### Authentication
All API endpoints require active session except login.

### Patients API

#### Get All Patients
```http
GET /api/patients.php?action=list
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "patient_id": 1,
      "full_name": "John Doe",
      "age": 35,
      "gender": "Male",
      "phone": "+256 700 123456"
    }
  ]
}
```

#### Get Single Patient
```http
GET /api/patients.php?action=get&id=1
```

#### Create Patient
```http
POST /api/patients.php?action=create
Content-Type: application/x-www-form-urlencoded

first_name=John&last_name=Doe&date_of_birth=1988-01-15&gender=Male&phone=0700123456
```

#### Update Patient
```http
PUT /api/patients.php?action=update
Content-Type: application/x-www-form-urlencoded

patient_id=1&first_name=John&last_name=Doe&...
```

#### Delete Patient
```http
DELETE /api/patients.php?action=delete&id=1
```

### Appointments API

#### Create Appointment
```http
POST /api/appointments.php?action=create

patient_id=1&doctor_id=1&appointment_date=2025-12-28&appointment_time=10:00
```

#### Check Availability
```http
GET /api/appointments.php?action=check_availability&doctor_id=1&date=2025-12-28&time=10:00
```

### Prescriptions API

#### Create Prescription
```http
POST /api/prescriptions.php?action=create

patient_id=1&doctor_id=1&medicines=[{"medicine_name":"Paracetamol","dosage":"500mg"}]
```

### Billing API

#### Create Bill
```http
POST /api/billing.php?action=create

patient_id=1&consultation_fee=50000&medicine_charges=20000
```

#### Record Payment
```http
POST /api/billing.php?action=payment

bill_id=1&amount=30000&payment_method=cash
```

### Error Responses

```json
{
  "success": false,
  "message": "Error description"
}
```

**For complete API documentation, see:** `docs/API_DOCUMENTATION.md`

---

## 🗄️ Database Schema

### Main Tables

#### users
- user_id (PK)
- username
- password (hashed)
- full_name
- email
- role (admin/doctor/receptionist/pharmacist)

#### patients
- patient_id (PK)
- first_name
- last_name
- date_of_birth
- gender
- blood_group
- phone
- email
- address

#### doctors
- doctor_id (PK)
- user_id (FK)
- specialization
- qualification
- consultation_fee
- available_days
- available_time_start
- available_time_end

#### appointments
- appointment_id (PK)
- patient_id (FK)
- doctor_id (FK)
- appointment_date
- appointment_time
- status
- reason

#### prescriptions
- prescription_id (PK)
- patient_id (FK)
- doctor_id (FK)
- prescription_date
- status

#### prescription_details
- detail_id (PK)
- prescription_id (FK)
- medicine_name
- dosage
- frequency
- duration

#### billing
- bill_id (PK)
- patient_id (FK)
- total_amount
- paid_amount
- payment_status
- payment_method

#### medicines
- medicine_id (PK)
- medicine_name
- unit_price
- stock_quantity
- expiry_date

**For complete schema, see:** `docs/DATABASE_SCHEMA.md`

---

## 📸 Screenshots

### Login Page
![Login](docs/screenshots/login.png)

### Admin Dashboard
![Dashboard](docs/screenshots/dashboard.png)

### Patient Management
![Patients](docs/screenshots/patients.png)

### Appointment Scheduling
![Appointments](docs/screenshots/appointments.png)

### Prescription Management
![Prescriptions](docs/screenshots/prescriptions.png)

---

## 🧪 Testing

### Manual Testing

1. **Authentication Testing**
   - Valid login
   - Invalid credentials
   - Session timeout
   - Role-based access

2. **CRUD Operations**
   - Create records
   - Read/View records
   - Update records
   - Delete records

3. **Business Logic**
   - Appointment conflicts
   - Payment calculations
   - Stock management
   - Date validations

### Test Accounts

| Username | Password | Role |
|----------|----------|------|
| admin | password | Admin |
| doctor1 | password | Doctor |
| reception1 | password | Receptionist |
| pharma1 | password | Pharmacist |

### Running Tests

```bash
# Open in browser
http://localhost/hospital_management/tests/
```

---

## 🐛 Troubleshooting

### Common Issues

#### Database Connection Failed
**Problem:** Cannot connect to database

**Solution:**
1. Verify MySQL is running
2. Check credentials in `config/database.php`
3. Ensure database exists
4. Check user permissions

#### 404 Errors
**Problem:** Pages not found

**Solution:**
1. Check `.htaccess` configuration
2. Verify `RewriteBase` path
3. Enable `mod_rewrite` in Apache

#### Session Issues
**Problem:** Login not persisting

**Solution:**
1. Check `session_start()` is called
2. Verify session directory permissions
3. Clear browser cookies
4. Check PHP session settings

#### Blank Pages
**Problem:** White screen, no errors

**Solution:**
1. Enable error reporting in `php.ini`
2. Check PHP error logs
3. Verify file permissions
4. Check for syntax errors

### Debug Mode

Enable debugging in `config/config.php`:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('APP_DEBUG', true);
```

### Getting Help

1. Check error logs in `/logs/`
2. Review browser console
3. Search Stack Overflow
4. Create GitHub issue

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

### Coding Standards

- Follow PSR-12 coding style
- Comment complex logic
- Write descriptive commit messages
- Update documentation

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

**Educational Use:** This project is created for educational purposes and final year IT project requirements.

---

## 👨‍💻 Author

**Guloba Moses*
- GitHub: [@CHANGED-1](https://github.com/CHANGED-1)
- Email: consult@guloba.com
<!-- - LinkedIn: [Your Name](https://linkedin.com/in/yourprofile) -->

---

## 🙏 Acknowledgments

- FontAwesome for icons
- XAMPP for development environment
- Stack Overflow community
- My project supervisor: [Supervisor Name]
- [University Name] - Department of IT

---

## 📅 Project Timeline

- **Planning**: Week 1-2
- **Database Design**: Week 3-4
- **Backend Development**: Week 5-8
- **Frontend Development**: Week 9-12
- **Testing**: Week 13-14
- **Documentation**: Week 15-16
- **Deployment**: Week 17

---

## 🔮 Future Enhancements

- [ ] Email notifications
- [ ] SMS integration
- [ ] PDF report generation
- [ ] Online payment gateway
- [ ] Mobile application
- [ ] Telemedicine support
- [ ] Lab test integration
- [ ] Advanced analytics
- [ ] Multi-language support
- [ ] Dark mode theme

---

## 📊 Project Statistics

- **Lines of Code**: 15,000+
- **Files**: 80+
- **Database Tables**: 12
- **API Endpoints**: 40+
- **Development Time**: 4 months

---

## 📞 Support

For support and questions:
- 📧 Email: consult@guloba.com
- 📱 WhatsApp: +256 783 968 324
- 🐛 Issues: [GitHub Issues](https://github.com/CHANGED-1/hospital_management/issues)

---

<div align="center">

**⭐ Star this repository if you find it helpful!**

Made with ❤️ by Guloba Moses

</div>