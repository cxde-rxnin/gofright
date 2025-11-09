# 🚀 Deployment Guide for pxxl.app

## Issue: 404 Error on Live Deployment

### Common Causes & Solutions:

---

## 1. ✅ Fixed: .htaccess Path Issue

**Problem**: `.htaccess` had wrong error document path `/cargo/index.php`
**Solution**: Updated to `/index.php` (root path)

---

## 2. Check Deployment Root Directory

On pxxl.app, ensure your files are deployed to the **document root** (usually `public_html`, `www`, or `htdocs`).

### Correct Structure:
```
public_html/  (or www/)
├── .htaccess
├── index.php
├── admin/
├── pages/
├── assets/
├── config/
├── database/
└── ...other files
```

### ❌ WRONG Structure:
```
public_html/
└── cargo/  (subfolder)
    ├── index.php
    └── ...
```

---

## 3. Database Configuration

Update `config/database.php` with your pxxl.app database credentials:

```php
<?php
session_start();

function getDBConnection() {
    // UPDATE THESE FOR pxxl.app
    $host = "your-pxxl-db-host";        // Usually 'localhost' or provided by pxxl
    $username = "your-pxxl-db-user";    // Your database username
    $password = "your-pxxl-db-pass";    // Your database password
    $database = "your-pxxl-db-name";    // Your database name
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}
?>
```

---

## 4. Import Database

### Option A: Using phpMyAdmin (Recommended)
1. Log into your pxxl.app control panel
2. Open phpMyAdmin
3. Create a new database
4. Select the database
5. Click "Import" tab
6. Upload `database/cargo_db.sql`
7. Click "Go"

### Option B: Using MySQL CLI
```bash
mysql -u your-username -p your-database < database/cargo_db.sql
```

---

## 5. File Permissions

Ensure correct permissions on Linux server:

```bash
# Set folder permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make config writable if needed
chmod 755 config/
```

---

## 6. Verify PHP Version

E-Cargo requires **PHP 7.0 or higher**

Check PHP version on pxxl.app:
1. Create a file `phpinfo.php` in root:
```php
<?php phpinfo(); ?>
```
2. Visit: `https://your-domain.pxxl.app/phpinfo.php`
3. Check PHP version
4. **Delete this file after checking** (security)

---

## 7. Setup Admin Account

After successful deployment:

1. Visit: `https://your-domain.pxxl.app/setup.php`
2. This will create the admin account
3. **Delete setup.php after running** (security)

Default credentials:
- Username: `admin`
- Password: `admin123`

---

## 8. Test Your Deployment

### Test URLs:
```
https://your-domain.pxxl.app/              → Landing page
https://your-domain.pxxl.app/admin/login.php  → Admin login
https://your-domain.pxxl.app/pages/login.php  → Customer login
https://your-domain.pxxl.app/test_connection.php → Database test
```

---

## 9. Troubleshooting Steps

### If you still get 404:

#### A. Check Apache Modules
Ensure these modules are enabled:
- `mod_rewrite`
- `mod_headers`

#### B. Check .htaccess is Active
Create test file `.htaccess`:
```apache
# Test file
Options -Indexes
```
Try accessing a folder. If you can browse folders, `.htaccess` is not active.

#### C. Check AllowOverride
Your Apache config needs:
```apache
<Directory /var/www/html>
    AllowOverride All
</Directory>
```

#### D. Check Error Logs
On pxxl.app:
- Access your control panel
- Look for "Error Logs" or "Log Files"
- Check Apache error log for details

---

## 10. Security Checklist (Post-Deployment)

After deployment, secure your app:

### ✅ Immediate Actions:
1. ❌ Delete `setup.php`
2. ❌ Delete `test_connection.php`
3. ❌ Delete `phpinfo.php` (if created)
4. ✅ Change admin password
5. ✅ Update database credentials in `config/database.php`
6. ✅ Set proper file permissions
7. ✅ Enable HTTPS (SSL certificate)

### Secure config/database.php:
```bash
chmod 600 config/database.php
```

---

## 11. Quick Fix Commands

If you have SSH access to pxxl.app:

```bash
# Navigate to your app directory
cd /path/to/your/app

# Fix permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# Check if index.php exists
ls -la index.php

# Test Apache configuration
apache2ctl -t

# Restart Apache (if you have permission)
sudo systemctl restart apache2
```

---

## 12. Contact pxxl.app Support

If the issue persists, contact pxxl.app support with:

1. **Error Description**: "Getting 404 error on homepage"
2. **Files**: Confirm all files are in document root
3. **Questions to Ask**:
   - Is `mod_rewrite` enabled?
   - Is `.htaccess` being read?
   - What's the correct document root path?
   - Are there any error logs I can check?

---

## 13. Alternative: Check Deployment Method

### If using Git deployment:
```bash
git add .
git commit -m "Fix deployment configuration"
git push origin main
```

### If using FTP:
1. Ensure all files uploaded to **root directory**
2. Check `.htaccess` was uploaded (hidden files!)
3. Upload in **Binary mode** for PHP files

---

## 14. Expected File Structure on pxxl.app

```
/ (root)
├── .htaccess                 ← MUST be in root
├── .git/                     ← If using Git
├── index.php                 ← Entry point
├── README.md
├── admin/
│   ├── dashboard.php
│   ├── login.php
│   └── ...
├── pages/
│   ├── dashboard.php
│   ├── login.php
│   └── ...
├── assets/
│   ├── css/
│   │   └── style.css
│   └── ...
├── config/
│   └── database.php         ← Update with pxxl credentials
├── database/
│   └── cargo_db.sql         ← Import to pxxl database
└── ...
```

---

## 15. Quick Diagnosis

Run through this checklist:

```
□ Files uploaded to correct root directory?
□ index.php exists in root?
□ .htaccess uploaded (including the dot)?
□ Database imported and configured?
□ PHP version is 7.0+?
□ File permissions correct?
□ Apache mod_rewrite enabled?
□ Checked error logs?
```

---

## 16. Most Likely Solutions

### Solution 1: Upload to Correct Directory
Move all files from `cargo/` subfolder to root directory.

### Solution 2: Database Not Configured
Update `config/database.php` with pxxl.app credentials.

### Solution 3: Database Not Imported
Import `database/cargo_db.sql` via phpMyAdmin.

### Solution 4: .htaccess Not Working
Contact pxxl support to enable `mod_rewrite`.

---

## Need More Help?

### Debug Steps:
1. Create `test.php` in root:
```php
<?php
echo "PHP is working!";
echo "<br>Document Root: " . $_SERVER['DOCUMENT_ROOT'];
echo "<br>Script Path: " . __FILE__;
?>
```

2. Visit: `https://your-domain.pxxl.app/test.php`
3. If this works but `index.php` doesn't, there's an issue in `index.php`
4. If this doesn't work, PHP is not configured properly

---

## Summary

The main fix I applied:
✅ Updated `.htaccess` to use correct root path (`/index.php` instead of `/cargo/index.php`)

Next steps:
1. Re-upload `.htaccess` to pxxl.app
2. Ensure files are in root directory
3. Configure database connection
4. Import database
5. Test deployment

**If you're still getting 404 after these steps, please share:**
- The exact URL showing 404
- Your pxxl.app control panel screenshots
- Any error messages from logs
- Your current file structure on the server
