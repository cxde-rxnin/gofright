<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $conn = getDBConnection();
    
    // Check admin credentials
    $sql = "SELECT * FROM login WHERE username = ? AND role = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['role'] = 'admin';
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Admin not found!";
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - E-Cargo System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo" style="font-size: 48px; margin-bottom: 16px;">🔐</div>
                <h1>Admin Access</h1>
                <p>Login to E-Cargo admin panel</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter admin username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter admin password">
                </div>
                
                <button type="submit" class="btn btn-secondary" style="width: 100%;">Login to Admin Panel</button>
            </form>
            
            <div class="auth-footer">
                <div style="padding: 16px; background: var(--gray-100); border-radius: 8px; margin-top: 20px;">
                    <strong style="display: block; margin-bottom: 8px;">Default Credentials:</strong>
                    <div style="font-size: 0.875rem; color: var(--gray-600);">
                        <div>Username: <code style="background: var(--primary-white); padding: 2px 6px; border-radius: 4px; color: var(--accent-green);">admin</code></div>
                        <div style="margin-top: 4px;">Password: <code style="background: var(--primary-white); padding: 2px 6px; border-radius: 4px; color: var(--accent-green);">admin123</code></div>
                    </div>
                </div>
                <p style="margin-top: 16px;"><a href="../index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
