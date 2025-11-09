<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $conn = getDBConnection();
    
    // Check if username or email already exists
    $check_sql = "SELECT * FROM customers WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = "Username or email already exists!";
    } else {
        // Insert customer
        $sql = "INSERT INTO customers (name, address, state, phone, email, gender, username, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $name, $address, $state, $phone, $email, $gender, $username, $password);
        
        if ($stmt->execute()) {
            // Also create login entry
            $login_sql = "INSERT INTO login (username, password, role) VALUES (?, ?, 'customer')";
            $login_stmt = $conn->prepare($login_sql);
            $login_stmt->bind_param("ss", $username, $password);
            $login_stmt->execute();
            
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Registration failed: " . $conn->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - E-Cargo System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card" style="max-width: 600px;">
            <div class="auth-header">
                <div class="logo" style="font-size: 48px; margin-bottom: 16px;">📦</div>
                <h1>Create Account</h1>
                <p>Join E-Cargo to start shipping</p>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <div style="margin-top: 16px;">
                        <a href="login.php" class="btn btn-secondary">Go to Login</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!isset($success)): ?>
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required placeholder="John Doe">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required placeholder="john@example.com">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required placeholder="+234 xxx xxx xxxx">
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="2" required placeholder="Street address"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" required placeholder="Lagos">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required placeholder="Choose username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Create password">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-secondary" style="width: 100%;">Create Account</button>
            </form>
            <?php endif; ?>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <p><a href="../index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
