<?php
require_once '../config/database.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$conn = getDBConnection();

// Get customer bookings
$sql = "SELECT c.*, r.route, r.price, p.status as payment_status, p.payment_method 
        FROM cargo c 
        LEFT JOIN route r ON c.route_id = r.id 
        LEFT JOIN payment p ON c.id = p.cargo_id 
        WHERE c.customer_id = ? 
        ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$bookings = $stmt->get_result();

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'in_transit' THEN 1 ELSE 0 END) as in_transit,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered
    FROM cargo WHERE customer_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $customer_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - E-Cargo System</title>
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
                E-Cargo
            </h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php" class="active"><span class="icon">🏠</span> Dashboard</a></li>
                <li><a href="book_cargo.php"><span class="icon">📦</span> Book Cargo</a></li>
                <li><a href="my_bookings.php"><span class="icon">📋</span> My Bookings</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['customer_name'], 0, 1)); ?></div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['customer_name']); ?></div>
                    <div class="user-role">Customer</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-left">
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!</p>
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
                            <div class="card-title">Total Bookings</div>
                        </div>
                        <div class="card-icon green">📦</div>
                    </div>
                    <div class="card-value"><?php echo $stats['total_bookings']; ?></div>
                    <div class="card-footer">
                        <span class="text">All time bookings</span>
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
                            <div class="card-title">In Transit</div>
                        </div>
                        <div class="card-icon green">🚚</div>
                    </div>
                    <div class="card-value"><?php echo $stats['in_transit']; ?></div>
                    <div class="card-footer">
                        <span class="text">On the way</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Delivered</div>
                        </div>
                        <div class="card-icon green">✅</div>
                    </div>
                    <div class="card-value"><?php echo $stats['delivered']; ?></div>
                    <div class="card-footer">
                        <span class="trend up">↑ <?php echo $stats['delivered']; ?></span>
                        <span class="text">Successfully delivered</span>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings Table -->
            <div class="data-table">
                <div class="table-header">
                    <h3>Recent Bookings</h3>
                    <div class="table-actions">
                        <a href="book_cargo.php" class="btn btn-secondary btn-sm">+ New Booking</a>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Route</th>
                                <th>Cargo Type</th>
                                <th>Weight</th>
                                <th>Price</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($bookings->num_rows > 0): ?>
                                <?php while ($booking = $bookings->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($booking['route']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['type']); ?></td>
                                        <td><?php echo $booking['weight']; ?> kg</td>
                                        <td><strong>₦<?php echo number_format($booking['price'], 2); ?></strong></td>
                                        <td><?php echo date('d M Y', strtotime($booking['date'])); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $booking['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $booking['payment_status'] ?? 'pending'; ?>">
                                                <?php echo ucfirst($booking['payment_status'] ?? 'Pending'); ?>
                                            </span>
                                        </td>
                                        <td class="action-links">
                                            <a href="receipt.php?id=<?php echo $booking['id']; ?>">View Receipt</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 48px;">
                                        <p style="color: var(--gray-600); margin-bottom: 16px;">No bookings found</p>
                                        <a href="book_cargo.php" class="btn btn-secondary">Book Your First Cargo</a>
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
