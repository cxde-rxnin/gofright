<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

// Handle status update
if (isset($_POST['update_status'])) {
    $cargo_id = $_POST['cargo_id'];
    $new_status = $_POST['status'];
    
    $update_sql = "UPDATE cargo SET status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $cargo_id);
    
    if ($update_stmt->execute()) {
        $success = "Status updated successfully!";
    }
    $update_stmt->close();
}

// Filter
$filter = $_GET['filter'] ?? 'all';
$where_clause = "";
if ($filter != 'all') {
    $where_clause = "WHERE c.status = '" . $conn->real_escape_string($filter) . "'";
}

// Get all cargo
$cargo_sql = "SELECT c.*, r.route, r.price, cu.name as customer_name, cu.phone, cu.email,
                     p.payment_method, p.status as payment_status
              FROM cargo c
              LEFT JOIN route r ON c.route_id = r.id
              LEFT JOIN customers cu ON c.customer_id = cu.id
              LEFT JOIN payment p ON c.id = p.cargo_id
              $where_clause
              ORDER BY c.created_at DESC";
$cargo_list = $conn->query($cargo_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cargo - E-Cargo System</title>
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
                <li><a href="manage_routes.php"><span class="icon">🛣️</span> Routes</a></li>
                <li><a href="manage_cargo.php" class="active"><span class="icon">📦</span> Cargo</a></li>
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
            <h1>Manage Cargo</h1>
            <p>View and update cargo shipments</p>
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

            <div style="margin-bottom: 24px; display: flex; gap: 12px;">
                <a href="?filter=all" class="btn <?php echo ($filter == 'all') ? 'btn-secondary' : 'btn-outline'; ?> btn-sm">All</a>
                <a href="?filter=pending" class="btn <?php echo ($filter == 'pending') ? 'btn-secondary' : 'btn-outline'; ?> btn-sm">Pending</a>
                <a href="?filter=in_transit" class="btn <?php echo ($filter == 'in_transit') ? 'btn-secondary' : 'btn-outline'; ?> btn-sm">In Transit</a>
                <a href="?filter=delivered" class="btn <?php echo ($filter == 'delivered') ? 'btn-secondary' : 'btn-outline'; ?> btn-sm">Delivered</a>
                <a href="?filter=cancelled" class="btn <?php echo ($filter == 'cancelled') ? 'btn-secondary' : 'btn-outline'; ?> btn-sm">Cancelled</a>
            </div>

            <div class="data-table">
                <div class="table-header">
                    <h3>Cargo Shipments</h3>
                    <div class="table-actions">
                        <button class="btn btn-outline btn-sm">🔍 Search</button>
                        <button class="btn btn-outline btn-sm">⬇️ Export</button>
                    </div>
                </div>
                <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Route</th>
                            <th>Cargo Type</th>
                            <th>Weight</th>
                            <th>Destination</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($cargo_list->num_rows > 0): ?>
                            <?php while ($cargo = $cargo_list->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $cargo['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($cargo['customer_name']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($cargo['phone']); ?><br>
                                        <small style="color: var(--gray-500);"><?php echo htmlspecialchars($cargo['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($cargo['route']); ?></td>
                                    <td><?php echo htmlspecialchars($cargo['type']); ?></td>
                                    <td><?php echo $cargo['weight']; ?> kg</td>
                                    <td><?php echo htmlspecialchars(substr($cargo['destination'], 0, 30)) . '...'; ?></td>
                                    <td><?php echo date('d M Y', strtotime($cargo['date'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="cargo_id" value="<?php echo $cargo['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" style="padding: 6px 8px; border: 1px solid var(--gray-300); border-radius: 6px; font-size: 0.875rem;">
                                                <option value="pending" <?php echo $cargo['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in_transit" <?php echo $cargo['status'] == 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                                                <option value="delivered" <?php echo $cargo['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $cargo['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $cargo['payment_status'] ?? 'pending'; ?>">
                                            <?php echo ucfirst($cargo['payment_status'] ?? 'Pending'); ?>
                                        </span>
                                    </td>
                                    <td class="action-links">
                                        <a href="../pages/receipt.php?id=<?php echo $cargo['id']; ?>" target="_blank">View</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" style="text-align: center; padding: 48px;">
                                    <p style="color: var(--gray-600);">No cargo found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
