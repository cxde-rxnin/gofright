<?php
/**
 * Database Setup Script
 * Run this file ONCE after importing cargo_db.sql to create the admin account
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup - E-Cargo System</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 15px; background: #d4edda; border: 1px solid green; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 15px; background: #f8d7da; border: 1px solid red; border-radius: 5px; margin: 10px 0; }
        .info { padding: 15px; background: #d1ecf1; border: 1px solid #0c5460; border-radius: 5px; margin: 10px 0; }
        .warning { padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; margin: 10px 0; }
        h1 { color: #667eea; }
        .btn { padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
    </style>
</head>
<body>
    <h1>E-Cargo System Setup</h1>";

try {
    $conn = getDBConnection();
    
    // Check if admin already exists
    $check_sql = "SELECT * FROM login WHERE username = 'admin' AND role = 'admin'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows > 0) {
        echo "<div class='info'>Admin account already exists.</div>";
        
        // Update password to make sure it's correct
        $password = 'admin123';
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        $update_sql = "UPDATE login SET password = ? WHERE username = 'admin' AND role = 'admin'";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("s", $hashed);
        
        if ($stmt->execute()) {
            echo "<div class='success'>✓ Admin password has been reset to: <strong>admin123</strong></div>";
        }
        $stmt->close();
    } else {
        // Create admin account
        $username = 'admin';
        $password = 'admin123';
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin';
        
        $insert_sql = "INSERT INTO login (username, password, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sss", $username, $hashed, $role);
        
        if ($stmt->execute()) {
            echo "<div class='success'>✓ Admin account created successfully!</div>";
        } else {
            echo "<div class='error'>✗ Failed to create admin account: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
    
    echo "<div class='info'>";
    echo "<h3>Admin Login Credentials:</h3>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "</div>";
    
    echo "<div class='warning'>";
    echo "<strong>Important:</strong> After successful login, you can delete this setup.php file for security.";
    echo "</div>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<a href='admin/login.php' class='btn'>Go to Admin Login</a>";
    echo "<a href='test_connection.php' class='btn'>Test Database</a>";
    echo "<a href='index.php' class='btn'>Go to Homepage</a>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
    echo "<div class='info'>";
    echo "<p><strong>Make sure you have:</strong></p>";
    echo "<ol>";
    echo "<li>Started XAMPP MySQL service</li>";
    echo "<li>Imported cargo_db.sql in phpMyAdmin</li>";
    echo "</ol>";
    echo "</div>";
}

echo "</body></html>";
?>
