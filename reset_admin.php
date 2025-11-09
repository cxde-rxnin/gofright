<?php
/**
 * Admin Password Reset Tool
 * Use this file to reset the admin password if needed
 */

require_once 'config/database.php';

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $message = "Passwords do not match!";
        $messageType = "error";
    } else if (strlen($new_password) < 6) {
        $message = "Password must be at least 6 characters!";
        $messageType = "error";
    } else {
        $conn = getDBConnection();
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        
        $sql = "UPDATE login SET password = ? WHERE username = 'admin' AND role = 'admin'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $hashed);
        
        if ($stmt->execute()) {
            $message = "Admin password updated successfully! You can now login with the new password.";
            $messageType = "success";
        } else {
            $message = "Failed to update password: " . $conn->error;
            $messageType = "error";
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password - E-Cargo System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Reset Admin Password</h1>
        </header>

        <div class="form-container">
            <h2>Change Admin Password</h2>
            
            <div class="alert" style="background: #fff3cd; color: #856404; border: 1px solid #ffc107;">
                <strong>Warning:</strong> This tool allows you to reset the admin password. 
                Delete this file after use for security purposes.
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                    <small>Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="submit-btn">Reset Password</button>
            </form>
            
            <div class="link">
                <p><a href="admin/login.php">Go to Admin Login</a></p>
                <p><a href="index.php">Back to Home</a></p>
            </div>
            
            <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                <strong>Default Admin Credentials:</strong><br>
                Username: admin<br>
                Password: admin123
            </div>
        </div>
    </div>
</body>
</html>
