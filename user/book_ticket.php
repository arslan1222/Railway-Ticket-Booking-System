<?php
require_once '../config/database.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

$route_id = $_GET['route_id'];
$error = '';
$success = '';

// Get route details
$stmt = $pdo->prepare("
    SELECT r.*, t.train_name, t.train_number, t.train_image
    FROM routes r
    JOIN trains t ON r.train_id = t.id
    WHERE r.id = ?
");
$stmt->execute([$route_id]);
$route = $stmt->fetch();

if (!$route) {
    header("Location: search_trains.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $passenger_name = $_POST['passenger_name'];
    $passenger_age = $_POST['passenger_age'];
    $passenger_gender = $_POST['passenger_gender'];
    $total_fare = $route['fare'];
    $booking_number = 'TKT' . time() . rand(100, 999);
    
    try {
        $pdo->beginTransaction();
        
        // Check seat availability again
        $stmt = $pdo->prepare("SELECT available_seats FROM routes WHERE id = ? FOR UPDATE");
        $stmt->execute([$route_id]);
        $current = $stmt->fetch();
        
        if($current['available_seats'] <= 0) {
            throw new Exception("No seats available!");
        }
        
        // Create booking
        $stmt = $pdo->prepare("
            INSERT INTO bookings (booking_number, user_id, route_id, passenger_name, passenger_age, passenger_gender, travel_date, total_fare, payment_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'paid')
        ");
        $stmt->execute([$booking_number, $_SESSION['user_id'], $route_id, $passenger_name, $passenger_age, $passenger_gender, $route['travel_date'], $total_fare]);
        
        // Update available seats
        $stmt = $pdo->prepare("UPDATE routes SET available_seats = available_seats - 1 WHERE id = ?");
        $stmt->execute([$route_id]);
        
        $pdo->commit();
        $success = "Booking successful! Your ticket number is: " . $booking_number;
        header("refresh:3;url=view_ticket.php?booking_id=" . $pdo->lastInsertId());
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header" style="background-color: #610505; color: white;">
                    <h4 class="mb-0"><i class="fas fa-ticket-alt"></i> Book Ticket</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Trip Details Section -->
                    <div class="card mb-4" style="border-left: 4px solid #610505;">
                        <div class="card-header" style="background-color: #f8f9fa;">
                            <h5 class="mb-0" style="color: #610505;">
                                <i class="fas fa-info-circle"></i> Trip Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="35%"><strong>Train:</strong></td>
                                            <td>
                                                <strong style="color: #610505;"><?php echo $route['train_name']; ?></strong>
                                                <br>
                                                <small class="text-muted">(<?php echo $route['train_number']; ?>)</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>From:</strong></td>
                                            <td>
                                                <i class="fas fa-map-marker-alt" style="color: #610505;"></i>
                                                <?php echo $route['origin']; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>To:</strong></td>
                                            <td>
                                                <i class="fas fa-map-marker-alt" style="color: #610505;"></i>
                                                <?php echo $route['destination']; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date:</strong></td>
                                            <td>
                                                <i class="fas fa-calendar-alt" style="color: #610505;"></i>
                                                <?php echo date('l, d M Y', strtotime($route['travel_date'])); ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="35%"><strong>Departure:</strong></td>
                                            <td>
                                                <i class="fas fa-clock" style="color: #610505;"></i>
                                                <?php echo date('h:i A', strtotime($route['departure_time'])); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Arrival:</strong></td>
                                            <td>
                                                <i class="fas fa-clock" style="color: #610505;"></i>
                                                <?php echo date('h:i A', strtotime($route['arrival_time'])); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Duration:</strong></td>
                                            <td>
                                                <i class="fas fa-hourglass-half" style="color: #610505;"></i>
                                                <?php echo $route['duration']; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Fare:</strong></td>
                                            <td>
                                                <h5 class="mb-0" style="color: #610505;">
                                                    ₹<?php echo number_format($route['fare'], 2); ?>
                                                    <small class="text-muted">/person</small>
                                                </h5>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Passenger Details Form -->
                    <form method="POST" action="" id="bookingForm">
                        <div class="card" style="border-left: 4px solid #610505;">
                            <div class="card-header" style="background-color: #f8f9fa;">
                                <h5 class="mb-0" style="color: #610505;">
                                    <i class="fas fa-user"></i> Passenger Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" style="color: #610505; font-weight: 500;">
                                            <i class="fas fa-user"></i> Passenger Name
                                        </label>
                                        <input type="text" name="passenger_name" class="form-control" 
                                               placeholder="Enter full name" required
                                               style="border-left: 3px solid #610505;">
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label" style="color: #610505; font-weight: 500;">
                                            <i class="fas fa-calendar"></i> Age
                                        </label>
                                        <input type="number" name="passenger_age" class="form-control" 
                                               placeholder="Age" min="1" max="120" required
                                               style="border-left: 3px solid #610505;">
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label" style="color: #610505; font-weight: 500;">
                                            <i class="fas fa-venus-mars"></i> Gender
                                        </label>
                                        <select name="passenger_gender" class="form-control" required
                                                style="border-left: 3px solid #610505;">
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Summary -->
                        <div class="card mt-3" style="background: linear-gradient(135deg, #610505 0%, #8b1a1a 100%); color: white;">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-1">Total Amount to Pay</h6>
                                        <h3 class="mb-0">₹<?php echo number_format($route['fare'], 2); ?></h3>
                                        <small>Including all taxes</small>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <button type="submit" class="btn btn-light btn-lg" 
                                                style="color: #610505; font-weight: bold;">
                                            <i class="fas fa-credit-card"></i> Confirm Booking & Pay
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    const name = document.querySelector('input[name="passenger_name"]').value;
    const age = document.querySelector('input[name="passenger_age"]').value;
    const gender = document.querySelector('select[name="passenger_gender"]').value;
    
    if (name.trim().length < 3) {
        e.preventDefault();
        alert('Please enter a valid passenger name (minimum 3 characters)');
        return false;
    }
    
    if (age < 1 || age > 120) {
        e.preventDefault();
        alert('Please enter a valid age between 1 and 120');
        return false;
    }
    
    if (!gender) {
        e.preventDefault();
        alert('Please select a gender');
        return false;
    }
    
    // Show loading effect on button
    const btn = this.querySelector('button[type="submit"]');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Payment...';
    btn.disabled = true;
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        setTimeout(function() {
            bsAlert.close();
        }, 3000);
    });
}, 3000);

// Age validation on input
document.querySelector('input[name="passenger_age"]').addEventListener('input', function() {
    if (this.value < 1) this.value = 1;
    if (this.value > 120) this.value = 120;
});
</script>

<style>
/* Form control focus effect */
.form-control:focus, select:focus {
    border-color: #610505;
    box-shadow: 0 0 0 0.2rem rgba(97, 5, 5, 0.25);
}

/* Card header styling */
.card-header[style*="background-color: #610505"],
.card-header[style*="background-color: #7a1a1a"] {
    border-bottom: none;
}

/* Trip details card hover effect */
.card[style*="border-left: 4px solid #610505"]:hover,
.card[style*="border-left: 4px solid #28a745"]:hover {
    transform: translateX(5px);
    transition: all 0.3s ease;
}

/* Button hover effect */
.btn-light:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

/* Payment summary card animation */
.card[style*="background: linear-gradient"] {
    transition: transform 0.3s ease;
}

.card[style*="background: linear-gradient"]:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}

/* Table styling */
.table-borderless td {
    padding: 8px 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-light {
        width: 100%;
        margin-top: 15px;
    }
    
    .text-md-end {
        text-align: center !important;
    }
    
    .card[style*="background: linear-gradient"] .row {
        text-align: center;
    }
}

/* Animation for loading spinner */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spin {
    animation: spin 1s linear infinite;
}
</style>

<?php include '../includes/footer.php'; ?>