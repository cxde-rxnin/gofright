<?php
require_once '../config/database.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$conn = getDBConnection();

// Get all customer bookings
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - E-Cargo System</title>
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
                <li><a href="dashboard.php"><span class="icon">🏠</span> Dashboard</a></li>
                <li><a href="book_cargo.php"><span class="icon">📦</span> Book Cargo</a></li>
                <li><a href="my_bookings.php" class="active"><span class="icon">📋</span> My Bookings</a></li>
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
            <h1>My Bookings</h1>
            <p>View and manage all your cargo bookings</p>
        </div>
        <div class="topbar-right">
            <div class="topbar-actions">
                <a href="book_cargo.php" class="btn btn-sm btn-secondary">+ New Booking</a>
                <button class="topbar-btn">🔔</button>
                <button class="topbar-btn">⚙️</button>
                <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="main-content">
            <div class="data-table">
                <div class="table-header">
                    <h3>All Bookings</h3>
                    <div class="table-actions">
                        <button class="btn btn-outline btn-sm">🔍 Filter</button>
                        <button class="btn btn-outline btn-sm">⬇️ Export</button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Route</th>
                            <th>Cargo Type</th>
                            <th>Weight (kg)</th>
                            <th>Destination</th>
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
                                    <td><?php echo htmlspecialchars(substr($booking['destination'], 0, 30)) . '...'; ?></td>
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
                                <td colspan="10" style="text-align: center; padding: 48px;">
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
