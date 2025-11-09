<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $conn = getDBConnection();
    
    // Check credentials
    $sql = "SELECT c.*, l.role FROM customers c 
            INNER JOIN login l ON c.username = l.username 
            WHERE c.username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_name'] = $user['name'];
            $_SESSION['customer_username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
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
    <title>Customer Login - E-Cargo System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo" style="font-size: 48px; margin-bottom: 16px;">📦</div>
                <h1>Welcome Back</h1>
                <p>Login to your E-Cargo account</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-secondary" style="width: 100%;">Login</button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
                <p><a href="../index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
