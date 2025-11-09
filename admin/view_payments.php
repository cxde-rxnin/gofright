<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

// Get all payments
$payments_sql = "SELECT p.*, c.type as cargo_type, c.weight, 
                        cu.name as customer_name, cu.phone, cu.email,
                        r.route
                 FROM payment p
                 LEFT JOIN cargo c ON p.cargo_id = c.id
                 LEFT JOIN customers cu ON c.customer_id = cu.id
                 LEFT JOIN route r ON c.route_id = r.id
                 ORDER BY p.created_at DESC";
$payments = $conn->query($payments_sql);

// Get payment statistics
$stats_sql = "SELECT 
                COUNT(*) as total_payments,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_amount,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                SUM(amount) as total_amount
              FROM payment";
$stats = $conn->query($stats_sql)->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payments - E-Cargo System</title>
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
                <li><a href="manage_customers.php"><span class="icon">👥</span> Customers</a></li>
                <li><a href="view_payments.php" class="active"><span class="icon">💰</span> Payments</a></li>
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
            <h1>Payment Records</h1>
            <p>View all payment transactions</p>
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
            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Total Payments</div>
                        </div>
                        <div class="card-icon green">💰</div>
                    </div>
                    <div class="card-value"><?php echo $stats['total_payments']; ?></div>
                    <div class="card-footer">
                        <span class="text">All transactions</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Completed</div>
                        </div>
                        <div class="card-icon green">✅</div>
                    </div>
                    <div class="card-value">₦<?php echo number_format($stats['completed_amount'], 0); ?></div>
                    <div class="card-footer">
                        <span class="text">Completed amount</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Pending</div>
                        </div>
                        <div class="card-icon black">⏳</div>
                    </div>
                    <div class="card-value">₦<?php echo number_format($stats['pending_amount'], 0); ?></div>
                    <div class="card-footer">
                        <span class="text">Pending amount</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Total Revenue</div>
                        </div>
                        <div class="card-icon green">💵</div>
                    </div>
                    <div class="card-value">₦<?php echo number_format($stats['total_amount'], 0); ?></div>
                    <div class="card-footer">
                        <span class="text">Total amount</span>
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="data-table">
                <div class="table-header">
                    <h3>All Payments</h3>
                    <div class="table-actions">
                        <button class="btn btn-outline btn-sm">🔍 Filter</button>
                        <button class="btn btn-outline btn-sm">⬇️ Export</button>
                    </div>
                </div>
                <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Cargo ID</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Route</th>
                            <th>Cargo Type</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($payments->num_rows > 0): ?>
                            <?php while ($payment = $payments->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $payment['id']; ?></strong></td>
                                    <td><strong>#<?php echo $payment['cargo_id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($payment['customer_name']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($payment['phone']); ?><br>
                                        <small style="color: var(--gray-500);"><?php echo htmlspecialchars($payment['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($payment['route']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['cargo_type']); ?></td>
                                    <td><strong>₦<?php echo number_format($payment['amount'], 2); ?></strong></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $payment['status']; ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($payment['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 48px;">
                                    <p style="color: var(--gray-600);">No payments found</p>
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
