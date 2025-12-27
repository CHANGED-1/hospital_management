# 🚀 Hospital Management System - Installation Guide

Complete step-by-step guide to install and configure the Hospital Management System.

---

## 📋 Prerequisites Checklist

Before starting, ensure you have:

- [ ] Windows 7/10/11, Linux, or macOS
- [ ] Minimum 2GB RAM
- [ ] 500MB free disk space
- [ ] Internet connection (for initial setup)
- [ ] Administrator/sudo access

---

## 📥 Step 1: Install XAMPP

### Windows Installation

1. **Download XAMPP**
   - Visit: https://www.apachefriends.org/
   - Download: XAMPP for Windows (PHP 7.4 or higher)
   - File size: ~150MB

2. **Install XAMPP**
   ```
   - Run installer as Administrator
   - Choose installation directory: C:\xampp
   - Select components:
     ✓ Apache
     ✓ MySQL
     ✓ PHP
     ✓ phpMyAdmin
   - Click Install
   - Wait for installation (5-10 minutes)
   ```

3. **Start XAMPP**
   - Open XAMPP Control Panel
   - Click "Start" for Apache
   - Click "Start" for MySQL
   - Both should show green "Running" status

### Linux Installation

```bash
# Ubuntu/Debian
wget https://www.apachefriends.org/xampp-files/7.4.33/xampp-linux-x64-7.4.33-0-installer.run
chmod +x xampp-linux-x64-7.4.33-0-installer.run
sudo ./xampp-linux-x64-7.4.33-0-installer.run

# Start services
sudo /opt/lampp/lampp start
```

### macOS Installation

```bash
# Download from apachefriends.org
# Open .dmg file
# Drag XAMPP to Applications
# Open XAMPP and start Apache & MySQL
```

---

## 📁 Step 2: Download Project Files

### Option A: Using Git (Recommended)

```bash
# Open Command Prompt/Terminal
cd C:\xampp\htdocs  # Windows
cd /opt/lampp/htdocs  # Linux
cd /Applications/XAMPP/htdocs  # macOS

# Clone repository
git clone https://github.com/yourusername/hospital-management-system.git

# Rename folder if needed
mv hospital-management-system hospital_management
```

### Option B: Manual Download

1. Download ZIP from GitHub
2. Extract the ZIP file
3. Copy extracted folder to:
   - **Windows**: `C:\xampp\htdocs\`
   - **Linux**: `/opt/lampp/htdocs/`
   - **macOS**: `/Applications/XAMPP/htdocs/`
4. Rename folder to: `hospital_management`

### Verify Installation
```bash
cd C:\xampp\htdocs\hospital_management  # Windows
ls  # Should show: config, auth, api, admin, etc.
```

---

## 🗄️ Step 3: Create Database

### Using phpMyAdmin (Recommended)

1. **Open phpMyAdmin**
   - URL: `http://localhost/phpmyadmin`
   - Default username: `root`
   - Default password: (leave empty)

2. **Create Database**
   ```sql
   - Click "New" in left sidebar
   - Database name: hospital_management
   - Collation: utf8mb4_general_ci
   - Click "Create"
   ```

3. **Import Schema**
   ```
   - Select "hospital_management" database
   - Click "Import" tab
   - Click "Choose File"
   - Navigate to: hospital_management/database/schema.sql
   - Click "Go"
   - Wait for success message
   ```

4. **Import Sample Data (Optional)**
   ```
   - Click "Import" tab again
   - Choose: hospital_management/database/seed_data.sql
   - Click "Go"
   ```

### Using MySQL Command Line

```bash
# Open Command Prompt/Terminal
mysql -u root -p

# In MySQL prompt:
CREATE DATABASE hospital_management CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE hospital_management;
SOURCE C:/xampp/htdocs/hospital_management/database/schema.sql;
SOURCE C:/xampp/htdocs/hospital_management/database/seed_data.sql;
EXIT;
```

### Verify Database
```sql
USE hospital_management;
SHOW TABLES;
-- Should show: users, patients, doctors, appointments, etc.

SELECT * FROM users LIMIT 1;
-- Should show default admin user
```

---

## ⚙️ Step 4: Configure Application

### 4.1 Database Configuration

Edit `config/database.php`:

```php
<?php
class Database {
    private $host = "localhost";        // Database host
    private $db_name = "hospital_management";  // Database name
    private $username = "root";         // MySQL username
    private $password = "";             // MySQL password (leave empty for XAMPP default)
    public $conn;
    
    // ... rest of the code
}
?>
```

### 4.2 Application Configuration

Edit `config/config.php`:

```php
<?php
// Base URL - IMPORTANT: Update this!
define('BASE_URL', 'http://localhost/hospital_management/');

// Site Name
define('SITE_NAME', 'Hospital Management System');

// Timezone - Change to your timezone
date_default_timezone_set('Africa/Kampala');

// Email settings (for notifications)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');

// Development/Production mode
define('APP_ENV', 'development');  // Change to 'production' when live
define('APP_DEBUG', true);         // Set to false in production

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>
```

### 4.3 Set File Permissions (Linux/macOS)

```bash
cd /path/to/hospital_management

# Set directory permissions
chmod 755 assets/uploads/
chmod 755 logs/
chmod 755 exports/

# Set file permissions
chmod 644 config/*.php
chmod 644 *.php

# Make setup script executable
chmod +x setup.sh
```

### 4.4 Configure Apache (Optional)

Edit `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerAdmin admin@localhost
    DocumentRoot "C:/xampp/htdocs/hospital_management"
    ServerName hospital.local
    
    <Directory "C:/xampp/htdocs/hospital_management">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "logs/hospital-error.log"
    CustomLog "logs/hospital-access.log" common
</VirtualHost>
```

Edit `C:\Windows\System32\drivers\etc\hosts` (as Administrator):
```
127.0.0.1    hospital.local
```

Restart Apache.

---

## 🧪 Step 5: Test Installation

### 5.1 Test Database Connection

Create `test_connection.php` in root:

```php
<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    echo "✓ Database connection successful!<br>";
    
    // Test query
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✓ Users table accessible: " . $result['count'] . " users found<br>";
    
    echo "<br><strong>Installation successful!</strong><br>";
    echo "<a href='index.php'>Go to Login Page</a>";
} else {
    echo "✗ Database connection failed!<br>";
    echo "Please check your configuration.";
}
?>
```

Access: `http://localhost/hospital_management/test_connection.php`

### 5.2 Test Application

1. **Access Login Page**
   - URL: `http://localhost/hospital_management/`
   - Should redirect to: `http://localhost/hospital_management/auth/login.php`
   - Should see: Login form with hospital logo

2. **Test Login**
   ```
   Username: admin
   Password: password
   ```
   - Click "Login"
   - Should redirect to: Admin Dashboard
   - Should see: Statistics, sidebar, appointments table

3. **Test Navigation**
   - Click each sidebar menu item
   - Verify pages load without errors
   - Check browser console (F12) for JavaScript errors

---

## ✅ Step 6: Post-Installation

### 6.1 Security Measures

1. **Change Default Passwords**
   ```sql
   -- Access phpMyAdmin or MySQL
   UPDATE users SET password = '$2y$10$your_new_hashed_password' 
   WHERE username = 'admin';
   ```
   Or login and change through UI.

2. **Secure MySQL**
   ```bash
   # Open XAMPP Shell
   mysql -u root -p
   
   # In MySQL:
   ALTER USER 'root'@'localhost' IDENTIFIED BY 'your_strong_password';
   FLUSH PRIVILEGES;
   ```
   Update `config/database.php` with new password.

3. **Delete Test Files**
   ```bash
   rm test_connection.php
   rm database/seed_data.sql  # After importing
   ```

4. **Disable Error Display in Production**
   ```php
   // In config/config.php
   define('APP_ENV', 'production');
   define('APP_DEBUG', false);
   ```

### 6.2 Create Additional Users

1. Login as admin
2. Navigate to **Users** menu
3. Click **Add New User**
4. Create users for different roles:
   - Doctors
   - Receptionists
   - Pharmacists

### 6.3 Configure Backup

```bash
# Create backup script
cat > backup.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="exports/backups"
DB_NAME="hospital_management"

# Create backup
mysqldump -u root -p $DB_NAME > "$BACKUP_DIR/backup_$DATE.sql"

# Keep only last 7 days
find $BACKUP_DIR -name "backup_*.sql" -mtime +7 -delete

echo "Backup created: backup_$DATE.sql"
EOF

chmod +x backup.sh

# Run daily (add to crontab)
0 2 * * * /path/to/hospital_management/backup.sh
```

### 6.4 Setup Email Notifications (Optional)

1. **Get Gmail App Password**
   - Go to: https://myaccount.google.com/security
   - Enable 2-Step Verification
   - Generate App Password

2. **Update Configuration**
   ```php
   // In config/config.php
   define('SMTP_USERNAME', 'youremail@gmail.com');
   define('SMTP_PASSWORD', 'your_app_password');
   ```

3. **Test Email**
   ```php
   // Create test_email.php
   <?php
   require_once 'includes/email.php';
   sendEmail('test@example.com', 'Test', 'Email working!');
   ?>
   ```

---

## 🔍 Troubleshooting

### Issue: Apache Won't Start

**Symptoms:**
- Port 80 already in use
- Apache shows red in XAMPP

**Solutions:**
```
1. Close Skype (uses port 80)
2. Stop IIS service:
   - Win+R → services.msc
   - Find "World Wide Web Publishing Service"
   - Stop and disable
3. Change Apache port:
   - Edit httpd.conf
   - Change: Listen 80 → Listen 8080
   - Access: http://localhost:8080/
```

### Issue: MySQL Won't Start

**Symptoms:**
- Port 3306 already in use
- MySQL shows red in XAMPP

**Solutions:**
```
1. Stop other MySQL instances
2. Change MySQL port:
   - Edit my.ini
   - Change: port=3306 → port=3307
   - Update config/database.php
```

### Issue: Database Import Error

**Error:** "Unknown collation: 'utf8mb4_unicode_ci'"

**Solution:**
```sql
-- Change collation in schema.sql
-- Replace all: utf8mb4_unicode_ci
-- With: utf8mb4_general_ci
```

### Issue: Permission Denied (Linux)

**Solution:**
```bash
sudo chown -R $USER:$USER /opt/lampp/htdocs/hospital_management
sudo chmod -R 755 /opt/lampp/htdocs/hospital_management
```

### Issue: Blank White Page

**Solutions:**
1. Enable error display:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

2. Check PHP error log:
   - XAMPP: `C:\xampp\php\logs\php_error_log`
   - Linux: `/var/log/apache2/error.log`

3. Check file permissions

4. Verify PHP version:
   ```bash
   php -v  # Should be 7.4+
   ```

---

## 📞 Getting Help

If you encounter issues:

1. **Check Logs**
   - Apache error log
   - PHP error log
   - Application logs: `logs/error.log`

2. **Browser Console**
   - Press F12
   - Check Console and Network tabs

3. **Common Solutions**
   - Clear browser cache
   - Restart Apache & MySQL
   - Check file permissions
   - Verify configuration files

4. **Resources**
   - Project Wiki: [Link]
   - FAQ: [Link]
   - GitHub Issues: [Link]
   - Email: support@example.com

---

## ✅ Installation Verification Checklist

- [ ] XAMPP installed and running
- [ ] Project files in htdocs
- [ ] Database created and imported
- [ ] Configuration files updated
- [ ] Login page accessible
- [ ] Can login with default credentials
- [ ] Dashboard loads correctly
- [ ] All menu items accessible
- [ ] No console errors
- [ ] File permissions set (Linux/macOS)
- [ ] Default passwords changed
- [ ] Backup configured
- [ ] Additional users created

---

## 🎉 Success!

If all steps completed successfully, your Hospital Management System is ready to use!

**Next Steps:**
1. Read the [User Manual](docs/USER_MANUAL.md)
2. Explore the features
3. Customize for your needs
4. Start adding real data

**Happy Managing! 🏥**

---

**Installation Time:** 30-45 minutes  
**Difficulty:** Beginner to Intermediate  
**Last Updated:** December 27, 2025