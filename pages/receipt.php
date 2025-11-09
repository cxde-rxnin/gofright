<?php
require_once '../config/database.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$cargo_id = $_GET['id'] ?? 0;

$conn = getDBConnection();

// Get booking details
$sql = "SELECT c.*, r.route, r.price, r.type as route_type, r.time, 
               p.payment_method, p.status as payment_status, p.amount,
               cu.name, cu.address, cu.phone, cu.email
        FROM cargo c
        LEFT JOIN route r ON c.route_id = r.id
        LEFT JOIN payment p ON c.id = p.cargo_id
        LEFT JOIN customers cu ON c.customer_id = cu.id
        WHERE c.id = ? AND c.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cargo_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$booking = $result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt - E-Cargo System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @media print {
            .sidebar, .topbar, .no-print, .btn, .hamburger, .sidebar-overlay { display: none !important; }
            .main-wrapper { margin-left: 0 !important; padding-top: 0 !important; }
            .receipt { box-shadow: none !important; }
        }
    </style>
</head>
<body>
    <!-- Hamburger Menu -->
    <button class="hamburger no-print" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay no-print" id="sidebar-overlay"></div>
    
    <!-- Sidebar -->
    <aside class="sidebar no-print">
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
                <li><a href="my_bookings.php"><span class="icon">📋</span> My Bookings</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar"><?php echo strtoupper(substr($booking['name'], 0, 1)); ?></div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($booking['name']); ?></div>
                    <div class="user-role">Customer</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Topbar -->
    <div class="topbar no-print">
        <div class="topbar-left">
            <h1>Receipt #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></h1>
            <p>Booking receipt and details</p>
        </div>
        <div class="topbar-right">
            <div class="topbar-actions">
                <button onclick="window.print()" class="btn btn-sm btn-secondary">🖨️ Print</button>
                <a href="my_bookings.php" class="btn btn-sm btn-outline">Back to Bookings</a>
                <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="main-content">
            <div class="receipt">
            <div class="receipt-header">
                <h1>E-CARGO MANAGEMENT SYSTEM</h1>
                <h2>BOOKING RECEIPT</h2>
                <p>Receipt No: #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                <p>Date: <?php echo date('d M Y, h:i A', strtotime($booking['created_at'])); ?></p>
            </div>

            <div class="receipt-details">
                <h3>Customer Information</h3>
                <table>
                    <tr>
                        <td>Name:</td>
                        <td><?php echo htmlspecialchars($booking['name']); ?></td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td><?php echo htmlspecialchars($booking['email']); ?></td>
                    </tr>
                    <tr>
                        <td>Phone:</td>
                        <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                    </tr>
                    <tr>
                        <td>Address:</td>
                        <td><?php echo htmlspecialchars($booking['address']); ?></td>
                    </tr>
                </table>

                <h3>Shipment Details</h3>
                <table>
                    <tr>
                        <td>Route:</td>
                        <td><?php echo htmlspecialchars($booking['route']); ?></td>
                    </tr>
                    <tr>
                        <td>Transport Type:</td>
                        <td><?php echo htmlspecialchars($booking['route_type']); ?></td>
                    </tr>
                    <tr>
                        <td>Estimated Time:</td>
                        <td><?php echo htmlspecialchars($booking['time']); ?></td>
                    </tr>
                    <tr>
                        <td>Cargo Type:</td>
                        <td><?php echo htmlspecialchars($booking['type']); ?></td>
                    </tr>
                    <tr>
                        <td>Weight:</td>
                        <td><?php echo $booking['weight']; ?> kg</td>
                    </tr>
                    <tr>
                        <td>Destination:</td>
                        <td><?php echo htmlspecialchars($booking['destination']); ?></td>
                    </tr>
                    <tr>
                        <td>Booking Date:</td>
                        <td><?php echo date('d M Y', strtotime($booking['date'])); ?></td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td><strong><?php echo ucfirst($booking['status']); ?></strong></td>
                    </tr>
                </table>

                <h3>Payment Information</h3>
                <table>
                    <tr>
                        <td>Amount:</td>
                        <td><strong>₦<?php echo number_format($booking['amount'], 2); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Payment Method:</td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $booking['payment_method'])); ?></td>
                    </tr>
                    <tr>
                        <td>Payment Status:</td>
                        <td><strong><?php echo ucfirst($booking['payment_status']); ?></strong></td>
                    </tr>
                </table>
            </div>

            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 0.9em; color: #666;">
                <p>Thank you for using E-Cargo Management System</p>
                <p>For inquiries, please contact: support@ecargo.com | +234-xxx-xxx-xxxx</p>
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
