<?php
require_once '../config/database.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$conn = getDBConnection();

// Get all routes
$routes_sql = "SELECT * FROM route ORDER BY route ASC";
$routes = $conn->query($routes_sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'];
    $weight = $_POST['weight'];
    $destination = $_POST['destination'];
    $route_id = $_POST['route_id'];
    $payment_method = $_POST['payment_method'];
    $date = date('Y-m-d');
    
    // Validate weight against route capacity
    $capacity_sql = "SELECT capacity, price FROM route WHERE id = ?";
    $capacity_stmt = $conn->prepare($capacity_sql);
    $capacity_stmt->bind_param("i", $route_id);
    $capacity_stmt->execute();
    $route_data = $capacity_stmt->get_result()->fetch_assoc();
    
    if ($weight > $route_data['capacity']) {
        $error = "Cargo weight exceeds airline capacity! Maximum allowed: " . $route_data['capacity'] . " kg";
    } else {
        // Insert cargo booking
        $sql = "INSERT INTO cargo (customer_id, type, weight, destination, date, status, route_id) 
                VALUES (?, ?, ?, ?, ?, 'pending', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdssi", $customer_id, $type, $weight, $destination, $date, $route_id);
        
        if ($stmt->execute()) {
            $cargo_id = $conn->insert_id;
            
            // Insert payment record
            $amount = $route_data['price'];
            $payment_status = ($payment_method == 'online') ? 'completed' : 'pending';
            
            $payment_sql = "INSERT INTO payment (cargo_id, amount, payment_method, status) 
                           VALUES (?, ?, ?, ?)";
            $payment_stmt = $conn->prepare($payment_sql);
            $payment_stmt->bind_param("idss", $cargo_id, $amount, $payment_method, $payment_status);
            $payment_stmt->execute();
            
            $success = "Cargo booked successfully! Booking ID: #" . $cargo_id;
            $booking_id = $cargo_id;
        } else {
            $error = "Booking failed: " . $conn->error;
        }
        $stmt->close();
    }
    $capacity_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Cargo - E-Cargo System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                <li><a href="book_cargo.php" class="active"><span class="icon">📦</span> Book Cargo</a></li>
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
            <h1>Book Cargo</h1>
            <p>Create a new cargo booking</p>
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
                <div class="alert alert-success">
                    <strong>✅ Success!</strong><br>
                    <?php echo $success; ?>
                    <div style="margin-top: 16px;">
                        <a href="receipt.php?id=<?php echo $booking_id; ?>" class="btn btn-secondary" style="margin-right: 8px;">View Receipt</a>
                        <a href="dashboard.php" class="btn btn-outline">Go to Dashboard</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <strong>⚠️ Error!</strong><br>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!isset($success)): ?>
            <div class="card" style="max-width: 800px;">
                <form method="POST" action="" id="bookingForm">
                    <div class="form-group">
                        <label for="route_id">Select Route *</label>
                        <select id="route_id" name="route_id" required>
                            <option value="">Choose a route</option>
                            <?php while ($route = $routes->fetch_assoc()): ?>
                                <option value="<?php echo $route['id']; ?>" 
                                        data-price="<?php echo $route['price']; ?>"
                                        data-capacity="<?php echo $route['capacity']; ?>">
                                    <?php echo htmlspecialchars($route['route']); ?> - 
                                    ₦<?php echo number_format($route['price'], 2); ?> 
                                    (Max: <?php echo $route['capacity']; ?>kg)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Cargo Type *</label>
                        <select id="type" name="type" required>
                            <option value="">Select type</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Food Items">Food Items</option>
                            <option value="Documents">Documents</option>
                            <option value="Medical Supplies">Medical Supplies</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="weight">Weight (kg) *</label>
                        <input type="number" id="weight" name="weight" step="0.01" required>
                        <small id="weightWarning" style="color: red; display: none;">Weight exceeds capacity!</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="destination">Destination Address *</label>
                        <textarea id="destination" name="destination" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method *</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Select payment method</option>
                            <option value="online">Pay Online</option>
                            <option value="on_delivery">Pay on Delivery</option>
                        </select>
                    </div>
                    
                    <div id="priceDisplay" style="background: #f7fafc; padding: 15px; border-radius: 5px; margin-bottom: 20px; display: none;">
                        <strong>Total Amount: ₦<span id="totalPrice">0.00</span></strong>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary" id="submitBtn" style="width: 100%;">Book Cargo</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        let maxCapacity = 0;
        
        $('#route_id').change(function() {
            const price = $(this).find(':selected').data('price');
            maxCapacity = $(this).find(':selected').data('capacity');
            
            if (price) {
                $('#totalPrice').text(parseFloat(price).toFixed(2));
                $('#priceDisplay').show();
            } else {
                $('#priceDisplay').hide();
            }
            
            validateWeight();
        });
        
        $('#weight').on('input', function() {
            validateWeight();
        });
        
        function validateWeight() {
            const weight = parseFloat($('#weight').val()) || 0;
            
            if (maxCapacity > 0 && weight > maxCapacity) {
                $('#weightWarning').show();
                $('#submitBtn').prop('disabled', true);
            } else {
                $('#weightWarning').hide();
                $('#submitBtn').prop('disabled', false);
            }
        }
    });
    </script>
    
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
