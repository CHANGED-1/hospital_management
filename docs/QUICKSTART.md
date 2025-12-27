# ⚡ Hospital Management System - Quick Start Guide

Get up and running in 5 minutes!

---

## 🚀 Express Installation (5 Minutes)

### Prerequisites
- XAMPP installed and running
- Basic command line knowledge

### Step 1: Get the Files (30 seconds)
```bash
cd C:\xampp\htdocs
git clone https://github.com/yourusername/hospital-management-system.git hospital_management
cd hospital_management
```

### Step 2: Import Database (2 minutes)
```bash
# Open browser
http://localhost/phpmyadmin

# In phpMyAdmin:
1. Click "New" → Create database "hospital_management"
2. Click "Import" → Choose "database/schema.sql"
3. Click "Go"
4. (Optional) Import "database/seed_data.sql"
```

### Step 3: Configure (1 minute)
```bash
# Edit config/database.php - Change these lines if needed:
private $host = "localhost";
private $db_name = "hospital_management";
private $username = "root";
private $password = ""; # Your MySQL password

# Edit config/config.php - Change this line:
define('BASE_URL', 'http://localhost/hospital_management/');
```

### Step 4: Test (1 minute)
```bash
# Open browser
http://localhost/hospital_management/

# Login with:
Username: admin
Password: password

# Success! 🎉
```

---

## 📦 What You Get Out of the Box

### Immediate Features
✅ User authentication (4 roles)  
✅ Patient management  
✅ Doctor profiles  
✅ Appointment scheduling  
✅ Prescription management  
✅ Billing system  
✅ Medicine inventory  
✅ Dashboard analytics  

### Default Accounts
| Role | Username | Password |
|------|----------|----------|
| Admin | admin | password |
| Doctor | doctor1 | password |
| Reception | reception1 | password |
| Pharmacy | pharma1 | password |

---

## 🎯 Quick Tasks

### Add Your First Patient (1 minute)
```
1. Login as Admin
2. Click "Patients" in sidebar
3. Click "Add New Patient"
4. Fill required fields: Name, DOB, Gender, Phone
5. Click "Save Patient"
```

### Schedule First Appointment (1 minute)
```
1. Click "Appointments" in sidebar
2. Click "New Appointment"
3. Select patient and doctor
4. Choose date and time
5. Add reason for visit
6. Click "Schedule"
```

### Create First Prescription (1 minute)
```
1. Login as Doctor
2. Open patient appointment
3. Click "Create Prescription"
4. Add medicine, dosage, frequency
5. Click "Save Prescription"
```

### Generate First Bill (1 minute)
```
1. Click "Billing" in sidebar
2. Click "Create Bill"
3. Select patient
4. Add consultation fee, medicine charges
5. Record payment
6. Click "Generate Bill"
```

---

## 🔧 Common Customizations

### Change Hospital Name
```php
// config/config.php
define('SITE_NAME', 'Your Hospital Name');
```

### Change Logo
```bash
# Replace this file:
assets/images/logo.png
```

### Change Theme Colors
```css
/* assets/css/main.css */
:root {
    --primary-color: #667eea;  /* Change to your color */
    --secondary-color: #764ba2;
}
```

### Add New User
```
1. Login as Admin
2. Go to "Users" menu
3. Click "Add New User"
4. Fill details and select role
5. Save
```

---

## 📱 Quick Reference

### Admin Quick Access
- Dashboard: `/admin/dashboard.php`
- Patients: `/admin/patients.php`
- Appointments: `/admin/appointments.php`
- Reports: `/admin/reports.php`

### API Quick Reference
```bash
# Get all patients
GET /api/patients.php?action=list

# Create appointment
POST /api/appointments.php?action=create

# Get today's appointments
GET /api/appointments.php?action=today

# Create bill
POST /api/billing.php?action=create
```

### Database Quick Commands
```sql
-- View all patients
SELECT * FROM patients;

-- Today's appointments
SELECT * FROM appointments WHERE appointment_date = CURDATE();

-- Unpaid bills
SELECT * FROM billing WHERE payment_status != 'paid';

-- Medicine stock
SELECT * FROM medicines WHERE stock_quantity < reorder_level;
```

---

## ⚠️ Quick Troubleshooting

### Can't Login?
```
1. Check database connection in config/database.php
2. Verify admin user exists:
   SELECT * FROM users WHERE username = 'admin';
3. Clear browser cache and cookies
```

### Page Not Found?
```
1. Check .htaccess file exists
2. Verify Apache mod_rewrite is enabled
3. Check BASE_URL in config/config.php
```

### Database Error?
```
1. Verify MySQL is running in XAMPP
2. Check database name: hospital_management
3. Verify credentials in config/database.php
4. Check if all tables imported correctly
```

### Blank Page?
```php
// Add to top of index.php temporarily
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Then check what error shows
```

---

## 📚 Next Steps

### Learn More
1. **User Manual** → Full feature documentation
2. **API Docs** → REST API reference
3. **Video Tutorial** → Step-by-step walkthrough
4. **FAQ** → Common questions answered

### Customize
1. Add your logo
2. Change colors
3. Add more users
4. Customize forms
5. Add custom reports

### Deploy
1. **Local Network** → Access from other devices
2. **Cloud Hosting** → Make it public
3. **Security** → Enable HTTPS
4. **Backup** → Schedule automatic backups

---

## 🎓 Quick Training

### For Receptionists (5 minutes)
```
1. Register new patients
2. Schedule appointments
3. Generate bills
4. Process payments
```

### For Doctors (5 minutes)
```
1. View appointments
2. Access patient history
3. Create prescriptions
4. Add medical notes
```

### For Pharmacists (5 minutes)
```
1. View pending prescriptions
2. Dispense medicines
3. Update inventory
4. Check stock levels
```

---

## 💡 Pro Tips

### Keyboard Shortcuts
- `Ctrl + K` → Quick search
- `Ctrl + N` → New patient
- `Ctrl + A` → New appointment
- `Esc` → Close modal

### Bulk Operations
```
1. Select multiple records
2. Use bulk actions menu
3. Apply to all selected
```

### Quick Reports
```
1. Use date range filters
2. Export to Excel/PDF
3. Schedule email reports
```

### Mobile Access
```
# Access from phone on same network
http://YOUR_COMPUTER_IP/hospital_management/

# Example:
http://192.168.1.100/hospital_management/
```

---

## 📞 Quick Help

**Need Help?**
- 📖 Documentation: `/docs/`
- 🐛 Issues: GitHub Issues
- 💬 Chat: Discord Server
- 📧 Email: support@example.com

**Video Tutorials**
- Installation: [YouTube Link]
- Patient Management: [YouTube Link]
- Appointments: [YouTube Link]
- Billing: [YouTube Link]

---

## ✅ Quick Checklist

After setup, verify:
- [ ] Login works
- [ ] Dashboard loads
- [ ] Can add patient
- [ ] Can schedule appointment
- [ ] Can create prescription
- [ ] Can generate bill
- [ ] All menus accessible
- [ ] No console errors

---

## 🎉 You're Ready!

Everything working? Great! You're now ready to:
- Start managing patients
- Schedule appointments
- Generate prescriptions
- Handle billing
- Run reports

**Have fun and happy managing! 🏥**

---

**Setup Time:** 5 minutes  
**Skill Level:** Beginner  
**Support:** Available 24/7

---

## 🔗 Quick Links

- [Full Installation Guide](INSTALLATION.md)
- [User Manual](docs/USER_MANUAL.md)
- [API Documentation](docs/API_DOCUMENTATION.md)
- [Video Tutorials](https://youtube.com/@youraccount)
- [GitHub Repository](https://github.com/yourusername/hospital-management-system)

---

*Last updated: December 27, 2025*