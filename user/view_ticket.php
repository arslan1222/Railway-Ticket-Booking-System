<?php
require_once '../config/database.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

$booking_id = $_GET['booking_id'];

// Fetch booking details
$stmt = $pdo->prepare("
    SELECT b.*, r.origin, r.destination, r.departure_time, r.arrival_time, r.duration, r.distance, r.fare, r.travel_date,
           t.train_name, t.train_number
    FROM bookings b
    JOIN routes r ON b.route_id = r.id
    JOIN trains t ON r.train_id = t.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    header("Location: my_bookings.php");
    exit();
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4>E-Ticket</h4>
                    <h5><?php echo $booking['booking_number']; ?></h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3><?php echo $booking['train_name']; ?></h3>
                        <p><?php echo $booking['train_number']; ?></p>
                    </div>
                    
                    <div class="row text-center mb-4">
                        <div class="col-md-5">
                            <h4><?php echo $booking['origin']; ?></h4>
                            <p>Departure: <?php echo date('h:i A', strtotime($booking['departure_time'])); ?></p>
                            <p>Date: <?php echo date('d M Y', strtotime($booking['travel_date'])); ?></p>
                        </div>
                        <div class="col-md-2">
                            <i class="fas fa-arrow-right fa-2x"></i>
                            <p><?php echo $booking['duration']; ?></p>
                        </div>
                        <div class="col-md-5">
                            <h4><?php echo $booking['destination']; ?></h4>
                            <p>Arrival: <?php echo date('h:i A', strtotime($booking['arrival_time'])); ?></p>
                            <p>Distance: <?php echo $booking['distance']; ?> km</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Passenger Details</h6>
                            <p><strong>Name:</strong> <?php echo $booking['passenger_name']; ?></p>
                            <p><strong>Age:</strong> <?php echo $booking['passenger_age']; ?></p>
                            <p><strong>Gender:</strong> <?php echo $booking['passenger_gender']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Booking Details</h6>
                            <p><strong>Booking Date:</strong> <?php echo date('d M Y h:i A', strtotime($booking['booking_date'])); ?></p>
                            <p><strong>Total Fare:</strong> ₹<?php echo number_format($booking['total_fare'], 2); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php echo $booking['status'] == 'confirmed' ? 'success' : 'danger'; ?>">
                                    <?php echo strtoupper($booking['status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <?php if($booking['status'] == 'cancelled'): ?>
                        <div class="alert alert-warning">
                            <strong>Cancelled on:</strong> <?php echo date('d M Y h:i A', strtotime($booking['cancellation_date'])); ?><br>
                            <strong>Refund Amount:</strong> ₹<?php echo number_format($booking['refund_amount'], 2); ?>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="text-center">
                        <button onclick="window.print()" class="btn btn-primary">Print Ticket</button>
                        <a href="my_bookings.php" class="btn btn-secondary">Back to Bookings</a>
                    </div>
                    
                    <div class="text-muted text-center mt-3">
                        <small>This is a computer-generated ticket. No signature required.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .navbar, footer, .btn, .card-header .btn {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .container {
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>