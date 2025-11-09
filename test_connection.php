<?php
/**
 * Database Connection Test
 * This file tests if the database connection is working properly
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Test - E-Cargo System</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid green; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid red; border-radius: 5px; margin: 10px 0; }
        .info { padding: 10px; background: #d1ecf1; border: 1px solid #0c5460; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #667eea; color: white; }
    </style>
</head>
<body>
    <h1>E-Cargo Database Connection Test</h1>";

try {
    $conn = getDBConnection();
    echo "<div class='success'>✓ Database connection successful!</div>";
    
    // Test tables
    echo "<h2>Database Tables Status</h2>";
    $tables = ['login', 'customers', 'route', 'cargo', 'payment'];
    
    echo "<table>";
    echo "<tr><th>Table Name</th><th>Status</th><th>Record Count</th></tr>";
    
    foreach ($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $count = $result->fetch_assoc()['count'];
            echo "<tr><td>$table</td><td style='color: green;'>✓ Exists</td><td>$count records</td></tr>";
        } else {
            echo "<tr><td>$table</td><td style='color: red;'>✗ Missing</td><td>-</td></tr>";
        }
    }
    
    echo "</table>";
    
    // Check admin account
    echo "<h2>Admin Account Status</h2>";
    $admin_check = $conn->query("SELECT * FROM login WHERE role = 'admin' LIMIT 1");
    if ($admin_check && $admin_check->num_rows > 0) {
        $admin = $admin_check->fetch_assoc();
        echo "<div class='success'>✓ Admin account exists</div>";
        echo "<div class='info'><strong>Username:</strong> " . htmlspecialchars($admin['username']) . "</div>";
    } else {
        echo "<div class='error'>✗ No admin account found</div>";
    }
    
    // Check routes
    echo "<h2>Available Routes</h2>";
    $routes = $conn->query("SELECT * FROM route");
    if ($routes && $routes->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Route</th><th>Type</th><th>Price</th><th>Capacity</th></tr>";
        while ($route = $routes->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($route['route']) . "</td>";
            echo "<td>" . htmlspecialchars($route['type']) . "</td>";
            echo "<td>₦" . number_format($route['price'], 2) . "</td>";
            echo "<td>" . $route['capacity'] . " kg</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>No routes found. Please import the database.</div>";
    }
    
    $conn->close();
    
    echo "<h2>Next Steps</h2>";
    echo "<div class='info'>";
    echo "<p><strong>Everything looks good! You can now:</strong></p>";
    echo "<ul>";
    echo "<li><a href='index.php'>Go to Home Page</a></li>";
    echo "<li><a href='admin/login.php'>Login as Admin</a> (Username: admin, Password: admin123)</li>";
    echo "<li><a href='pages/register.php'>Register as Customer</a></li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Database connection failed: " . $e->getMessage() . "</div>";
    echo "<div class='info'>";
    echo "<p><strong>Troubleshooting:</strong></p>";
    echo "<ol>";
    echo "<li>Make sure XAMPP MySQL is running</li>";
    echo "<li>Import the database: Open phpMyAdmin → Import → Select cargo_db.sql</li>";
    echo "<li>Check database credentials in config/database.php</li>";
    echo "</ol>";
    echo "</div>";
}

echo "</body></html>";
?>
