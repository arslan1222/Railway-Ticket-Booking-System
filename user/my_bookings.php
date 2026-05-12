<?php
require_once '../config/database.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

$stmt = $pdo->prepare("
    SELECT b.*, r.origin, r.destination, r.departure_time, r.arrival_time, t.train_name, t.train_number
    FROM bookings b
    JOIN routes r ON b.route_id = r.id
    JOIN trains t ON r.train_id = t.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header" style="background-color: #610505; color: white;">
            <h4 class="mb-0"><i class="fas fa-ticket-alt"></i> My Bookings</h4>
        </div>
        <div class="card-body">
            <?php if(count($bookings) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead style="background-color: #610505; color: white;">
                            <tr>
                                <th>Booking No</th>
                                <th>Train</th>
                                <th>Route</th>
                                <th>Travel Date</th>
                                <th>Passenger</th>
                                <th>Fare</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $booking['booking_number']; ?></strong>
                                    </td>
                                    <td>
                                        <?php echo $booking['train_name']; ?>
                                        <br>
                                        <small class="text-muted">(<?php echo $booking['train_number']; ?>)</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-map-marker-alt" style="color: #610505;"></i> <?php echo $booking['origin']; ?>
                                        <i class="fas fa-arrow-right" style="color: #610505;"></i>
                                        <?php echo $booking['destination']; ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($booking['departure_time'])); ?> → 
                                            <?php echo date('h:i A', strtotime($booking['arrival_time'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar-alt" style="color: #610505;"></i>
                                        <?php echo date('d M Y', strtotime($booking['travel_date'])); ?>
                                    </td>
                                    <td>
                                        <?php echo $booking['passenger_name']; ?>
                                        <br>
                                        <small class="text-muted">Age: <?php echo $booking['passenger_age']; ?></small>
                                    </td>
                                    <td>
                                        <strong style="color: #610505;">₹<?php echo number_format($booking['total_fare'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <?php if($booking['status'] == 'confirmed'): ?>
                                            <span class="badge" style="background-color: #28a745;">
                                                <i class="fas fa-check-circle"></i> Confirmed
                                            </span>
                                        <?php elseif($booking['status'] == 'cancelled'): ?>
                                            <span class="badge" style="background-color: #dc3545;">
                                                <i class="fas fa-times-circle"></i> Cancelled
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background-color: #ffc107; color: #000;">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="view_ticket.php?booking_id=<?php echo $booking['id']; ?>" 
                                           class="btn btn-sm" 
                                           style="background-color: #17a2b8; color: white; margin-bottom: 5px;">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <?php if($booking['status'] == 'confirmed'): ?>
                                            <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" 
                                               class="btn btn-sm" 
                                               style="background-color: #dc3545; color: white;"
                                               onclick="return confirm('Are you sure you want to cancel this booking? 80% amount will be refunded.')">
                                                <i class="fas fa-ban"></i> Cancel
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary Section -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card" style="border-left: 4px solid #610505;">
                            <div class="card-body">
                                <h6 class="text-muted">Total Bookings</h6>
                                <h3 style="color: #610505;"><?php echo count($bookings); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card" style="border-left: 4px solid #28a745;">
                            <div class="card-body">
                                <h6 class="text-muted">Active Bookings</h6>
                                <h3 style="color: #28a745;">
                                    <?php echo count(array_filter($bookings, function($b) { return $b['status'] == 'confirmed'; })); ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card" style="border-left: 4px solid #dc3545;">
                            <div class="card-body">
                                <h6 class="text-muted">Cancelled Bookings</h6>
                                <h3 style="color: #dc3545;">
                                    <?php echo count(array_filter($bookings, function($b) { return $b['status'] == 'cancelled'; })); ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-ticket-alt fa-5x" style="color: #610505; opacity: 0.5;"></i>
                    <h4 class="mt-3">No Bookings Found</h4>
                    <p class="text-muted">You haven't made any bookings yet.</p>
                    <a href="search_trains.php" class="btn" style="background-color: #610505; color: white;">
                        <i class="fas fa-search"></i> Book Your First Ticket
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Table hover effect */
.table-hover tbody tr:hover {
    background-color: rgba(97, 5, 5, 0.05);
    transition: all 0.3s ease;
}

/* Table styling */
.table {
    vertical-align: middle;
}

.table thead th {
    border-bottom: 2px solid #610505;
    font-weight: 600;
    padding: 12px;
}

.table tbody td {
    padding: 12px;
    vertical-align: middle;
}

/* Button hover effects */
.btn[style*="background-color: #17a2b8"]:hover,
.btn[style*="background-color: #dc3545"]:hover,
.btn[style*="background-color: #610505"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.btn {
    transition: all 0.3s ease;
}

/* Card header styling */
.card-header[style*="background-color: #610505"] {
    border-bottom: none;
}

/* Badge styling */
.badge {
    padding: 6px 10px;
    font-weight: 500;
}

/* Summary cards animation */
.card[style*="border-left"] {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card[style*="border-left"]:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Responsive table */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 14px;
    }
    
    .btn-sm {
        padding: 4px 8px;
        font-size: 12px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>