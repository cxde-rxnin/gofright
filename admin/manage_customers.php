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
    $delete_sql = "DELETE FROM customers WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);
    if ($delete_stmt->execute()) {
        $success = "Customer deleted successfully!";
    }
}

// Get all customers
$customers_sql = "SELECT c.*, 
                         COUNT(ca.id) as total_bookings,
                         SUM(CASE WHEN ca.status = 'delivered' THEN 1 ELSE 0 END) as completed_bookings
                  FROM customers c
                  LEFT JOIN cargo ca ON c.id = ca.customer_id
                  GROUP BY c.id
                  ORDER BY c.created_at DESC";
$customers = $conn->query($customers_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - E-Cargo System</title>
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
                <li><a href="manage_cargo.php"><span class="icon">📦</span> Cargo</a></li>
                <li><a href="manage_customers.php" class="active"><span class="icon">👥</span> Customers</a></li>
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
            <h1>Manage Customers</h1>
            <p>View and manage customer accounts</p>
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

            <div class="data-table">
                <div class="table-header">
                    <h3>All Customers</h3>
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Address</th>
                            <th>State</th>
                            <th>Total Bookings</th>
                            <th>Completed</th>
                            <th>Registered</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($customers->num_rows > 0): ?>
                            <?php while ($customer = $customers->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo $customer['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['gender']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($customer['address'], 0, 30)); ?>...</td>
                                    <td><?php echo htmlspecialchars($customer['state']); ?></td>
                                    <td><strong><?php echo $customer['total_bookings']; ?></strong></td>
                                    <td><strong><?php echo $customer['completed_bookings']; ?></strong></td>
                                    <td><?php echo date('d M Y', strtotime($customer['created_at'])); ?></td>
                                    <td class="action-links">
                                        <a href="?delete=<?php echo $customer['id']; ?>" 
                                           onclick="return confirm('Delete this customer and all their bookings?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" style="text-align: center; padding: 48px;">
                                    <p style="color: var(--gray-600);">No customers found</p>
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
