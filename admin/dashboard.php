<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total customers
$customers_sql = "SELECT COUNT(*) as total FROM customers";
$stats['customers'] = $conn->query($customers_sql)->fetch_assoc()['total'];

// Total cargo
$cargo_sql = "SELECT COUNT(*) as total FROM cargo";
$stats['total_cargo'] = $conn->query($cargo_sql)->fetch_assoc()['total'];

// Pending shipments
$pending_sql = "SELECT COUNT(*) as total FROM cargo WHERE status = 'pending'";
$stats['pending'] = $conn->query($pending_sql)->fetch_assoc()['total'];

// Completed shipments
$completed_sql = "SELECT COUNT(*) as total FROM cargo WHERE status = 'delivered'";
$stats['completed'] = $conn->query($completed_sql)->fetch_assoc()['total'];

// Recent bookings
$recent_sql = "SELECT c.*, r.route, cu.name as customer_name, cu.phone 
               FROM cargo c 
               LEFT JOIN route r ON c.route_id = r.id 
               LEFT JOIN customers cu ON c.customer_id = cu.id 
               ORDER BY c.created_at DESC LIMIT 10";
$recent_bookings = $conn->query($recent_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - E-Cargo System</title>
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
                <li><a href="dashboard.php" class="active"><span class="icon">🏠</span> Dashboard</a></li>
                <li><a href="manage_routes.php"><span class="icon">🛣️</span> Routes</a></li>
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
            <h1>Admin Dashboard</h1>
            <p>Welcome back, Administrator!</p>
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
                            <div class="card-title">Total Customers</div>
                        </div>
                        <div class="card-icon green">👥</div>
                    </div>
                    <div class="card-value"><?php echo $stats['customers']; ?></div>
                    <div class="card-footer">
                        <span class="text">Registered users</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Total Shipments</div>
                        </div>
                        <div class="card-icon black">📦</div>
                    </div>
                    <div class="card-value"><?php echo $stats['total_cargo']; ?></div>
                    <div class="card-footer">
                        <span class="text">All time shipments</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Pending</div>
                        </div>
                        <div class="card-icon black">⏳</div>
                    </div>
                    <div class="card-value"><?php echo $stats['pending']; ?></div>
                    <div class="card-footer">
                        <span class="text">Awaiting pickup</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Completed</div>
                        </div>
                        <div class="card-icon green">✅</div>
                    </div>
                    <div class="card-value"><?php echo $stats['completed']; ?></div>
                    <div class="card-footer">
                        <span class="trend up">↑ <?php echo $stats['completed']; ?></span>
                        <span class="text">Successfully delivered</span>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings Table -->
            <div class="data-table">
                <div class="table-header">
                    <h3>Recent Bookings</h3>
                    <div class="table-actions">
                        <button class="btn btn-outline btn-sm">🔍 Filter</button>
                        <button class="btn btn-outline btn-sm">⬇️ Export</button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Route</th>
                            <th>Cargo Type</th>
                            <th>Weight (kg)</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_bookings->num_rows > 0): ?>
                            <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['route']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['type']); ?></td>
                                    <td><?php echo $booking['weight']; ?> kg</td>
                                    <td><?php echo date('d M Y', strtotime($booking['date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 48px;">
                                    <p style="color: var(--gray-600);">No bookings found</p>
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
