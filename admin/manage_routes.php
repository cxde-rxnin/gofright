<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_sql = "DELETE FROM route WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);
    if ($delete_stmt->execute()) {
        $success = "Route deleted successfully!";
    }
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $route = $_POST['route'];
    $price = $_POST['price'];
    $type = $_POST['type'];
    $time = $_POST['time'];
    $capacity = $_POST['capacity'];
    $id = $_POST['id'] ?? null;
    
    if ($id) {
        // Update
        $sql = "UPDATE route SET route=?, price=?, type=?, time=?, capacity=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssdi", $route, $price, $type, $time, $capacity, $id);
    } else {
        // Insert
        $sql = "INSERT INTO route (route, price, type, time, capacity) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssd", $route, $price, $type, $time, $capacity);
    }
    
    if ($stmt->execute()) {
        $success = $id ? "Route updated successfully!" : "Route added successfully!";
    }
    $stmt->close();
}

// Get all routes
$routes_sql = "SELECT * FROM route ORDER BY route ASC";
$routes = $conn->query($routes_sql);

// Get route for editing
$edit_route = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_sql = "SELECT * FROM route WHERE id = ?";
    $edit_stmt = $conn->prepare($edit_sql);
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_route = $edit_stmt->get_result()->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Routes - E-Cargo System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Hamburger Menu -->
    <button class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>
                <div class="logo">📦</div>
                E-Cargo Admin
            </h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php"><span class="icon">🏠</span> Dashboard</a></li>
                <li><a href="manage_routes.php" class="active"><span class="icon">🛣️</span> Routes</a></li>
                <li><a href="manage_cargo.php"><span class="icon">📦</span> Cargo</a></li>
                <li><a href="manage_customers.php"><span class="icon">👥</span> Customers</a></li>
                <li><a href="view_payments.php"><span class="icon">💰</span> Payments</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar">A</div>
                <div class="user-details">
                    <div class="user-name">Administrator</div>
                    <div class="user-role">Admin</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-left">
            <h1>Manage Routes</h1>
            <p>Create and manage shipping routes</p>
        </div>
        <div class="topbar-right">
            <div class="topbar-actions">
                <button class="topbar-btn">🔔</button>
                <button class="topbar-btn">⚙️</button>
                <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="main-content">
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 400px 1fr; gap: 24px;">
                <!-- Add/Edit Form -->
                <div class="card">
                    <h3 style="margin-bottom: 24px;"><?php echo $edit_route ? 'Edit Route' : 'Add New Route'; ?></h3>
                    <form method="POST" action="">
                        <?php if ($edit_route): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_route['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="route">Route *</label>
                            <input type="text" id="route" name="route" required 
                                   value="<?php echo $edit_route['route'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="type">Type *</label>
                            <select id="type" name="type" required>
                                <option value="Air Freight" <?php echo ($edit_route['type'] ?? '') == 'Air Freight' ? 'selected' : ''; ?>>Air Freight</option>
                                <option value="Sea Freight" <?php echo ($edit_route['type'] ?? '') == 'Sea Freight' ? 'selected' : ''; ?>>Sea Freight</option>
                                <option value="Road Freight" <?php echo ($edit_route['type'] ?? '') == 'Road Freight' ? 'selected' : ''; ?>>Road Freight</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price (₦) *</label>
                            <input type="number" id="price" name="price" step="0.01" required 
                                   value="<?php echo $edit_route['price'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="time">Estimated Time *</label>
                            <input type="text" id="time" name="time" required 
                                   value="<?php echo $edit_route['time'] ?? ''; ?>" placeholder="e.g., 2 hours">
                        </div>
                        
                        <div class="form-group">
                            <label for="capacity">Max Capacity (kg) *</label>
                            <input type="number" id="capacity" name="capacity" step="0.01" required 
                                   value="<?php echo $edit_route['capacity'] ?? ''; ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-secondary" style="width: 100%;">
                            <?php echo $edit_route ? 'Update Route' : 'Add Route'; ?>
                        </button>
                        
                        <?php if ($edit_route): ?>
                            <a href="manage_routes.php" class="btn btn-outline" style="display: block; text-align: center; margin-top: 10px;">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Routes Table -->
                <div class="data-table">
                    <div class="table-header">
                        <h3>All Routes</h3>
                        <div class="table-actions">
                            <button class="btn btn-outline btn-sm">🔍 Search</button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Route</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                    <th>Time</th>
                                    <th>Capacity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($routes->num_rows > 0): ?>
                                    <?php while ($route = $routes->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?php echo $route['id']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($route['route']); ?></td>
                                            <td><?php echo htmlspecialchars($route['type']); ?></td>
                                            <td><strong>₦<?php echo number_format($route['price'], 2); ?></strong></td>
                                            <td><?php echo htmlspecialchars($route['time']); ?></td>
                                            <td><?php echo $route['capacity']; ?> kg</td>
                                            <td class="action-links">
                                                <a href="?edit=<?php echo $route['id']; ?>">Edit</a> |
                                                <a href="?delete=<?php echo $route['id']; ?>" 
                                                   onclick="return confirm('Are you sure?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 48px;">
                                            <p style="color: var(--gray-600);">No routes found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Mobile sidebar toggle
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });
        
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    </script>
</body>
</html>
