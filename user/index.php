<?php
require_once '../config/database.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

// Get user's recent bookings
$stmt = $pdo->prepare("
    SELECT b.*, r.origin, r.destination, t.train_name 
    FROM bookings b
    JOIN routes r ON b.route_id = r.id
    JOIN trains t ON r.train_id = t.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_bookings = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header" style="background-color: #610505; color: white;">
                    <h4><i class="fas fa-user-circle"></i> Welcome, <?php echo $_SESSION['user_name']; ?>!</h4>
                </div>
                <div class="card-body">
                    <p>Welcome to the Railway Ticket Booking System. You can:</p>
                    <ul>
                        <li><i class="fas fa-search" style="color: #610505;"></i> Search and book train tickets</li>
                        <li><i class="fas fa-history" style="color: #610505;"></i> View your booking history</li>
                        <li><i class="fas fa-ban" style="color: #610505;"></i> Cancel bookings (with 80% refund)</li>
                        <li><i class="fas fa-user-edit" style="color: #610505;"></i> Update your profile information</li>
                    </ul>
                    <a href="search_trains.php" class="btn" style="background-color: #610505; color: white;">Book a Ticket Now</a>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header" style="background-color: #7a1a1a; color: white;">
                    <h5><i class="fas fa-clock"></i> Recent Bookings</h5>
                </div>
                <div class="card-body">
                    <?php if(count($recent_bookings) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead style="background-color: #610505; color: white;">
                                    <tr>
                                        <th>Booking No</th>
                                        <th>Train</th>
                                        <th>Route</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_bookings as $booking): ?>
                                        <tr>
                                            <td><?php echo $booking['booking_number']; ?></td>
                                            <td><?php echo $booking['train_name']; ?></td>
                                            <td><?php echo $booking['origin']; ?> → <?php echo $booking['destination']; ?></td>
                                            <td><?php echo $booking['travel_date']; ?></td>
                                            <td>
                                                <span class="badge" style="background-color: <?php echo $booking['status'] == 'confirmed' ? '#28a745' : '#dc3545'; ?>;">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="my_bookings.php" style="color: #610505;">View All Bookings →</a>
                    <?php else: ?>
                        <p class="text-muted">No bookings yet. <a href="search_trains.php" style="color: #610505;">Book your first ticket now!</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header" style="background-color: #610505; color: white;">
                    <h5><i class="fas fa-chart-line"></i> Stats</h5>
                </div>
                <div class="card-body">
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ? AND status = 'confirmed'");
                    $stmt->execute([$_SESSION['user_id']]);
                    $total_bookings = $stmt->fetch()['total'];
                    
                    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_fare), 0) as total FROM bookings WHERE user_id = ? AND status = 'confirmed'");
                    $stmt->execute([$_SESSION['user_id']]);
                    $total_spent = $stmt->fetch()['total'];
                    ?>
                    <p><strong style="color: #610505;">Total Bookings:</strong> <?php echo $total_bookings; ?></p>
                    <p><strong style="color: #610505;">Total Spent:</strong> Rs. <?php echo number_format($total_spent, 2); ?></p>
                    <hr>
                    <a href="profile.php" class="btn btn-sm w-100" style="border-color: #610505; color: #610505;">Update Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table thead th {
    border-bottom: 2px solid #610505;
}
.btn:hover {
    background-color: #4a0404 !important;
    color: white !important;
    transform: translateY(-2px);
    transition: all 0.3s ease;
}
.btn-outline:hover {
    background-color: #610505 !important;
    color: white !important;
}
</style>

<?php include '../includes/footer.php'; ?>