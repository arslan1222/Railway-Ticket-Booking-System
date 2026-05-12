<?php
require_once '../config/database.php';
require_once '../includes/session.php';
redirectIfNotAdmin();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
$total_users = $stmt->fetch()['total_users'];

$stmt = $pdo->query("SELECT COUNT(*) as total_trains FROM trains");
$total_trains = $stmt->fetch()['total_trains'];

$stmt = $pdo->query("SELECT COUNT(*) as total_bookings FROM bookings WHERE status = 'confirmed'");
$total_bookings = $stmt->fetch()['total_bookings'];

$stmt = $pdo->query("SELECT SUM(total_fare) as total_revenue FROM bookings WHERE status = 'confirmed'");
$total_revenue = $stmt->fetch()['total_revenue'];

// Get recent bookings
$stmt = $pdo->query("
    SELECT b.*, u.name as user_name, r.origin, r.destination, t.train_name 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN routes r ON b.route_id = r.id
    JOIN trains t ON r.train_id = t.id
    WHERE b.status = 'confirmed'
    ORDER BY b.booking_date DESC
    LIMIT 5
");
$recent_bookings = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <h2 style="color: #610505;"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
    
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-white mb-3" style="background-color: #610505;">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users"></i> Total Users</h5>
                    <h2><?php echo $total_users; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white mb-3" style="background-color: #7a1a1a;">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-train"></i> Total Trains</h5>
                    <h2><?php echo $total_trains; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white mb-3" style="background-color: #8b2a2a;">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-ticket-alt"></i> Total Bookings</h5>
                    <h2><?php echo $total_bookings; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white mb-3" style="background-color: #9c3a3a;">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-rupee-sign"></i> Total Revenue</h5>
                    <h2>₹<?php echo number_format($total_revenue ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header" style="background-color: #610505; color: white;">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="trains/add_train.php" class="btn" style="background-color: #610505; color: white; margin: 5px;">
                        <i class="fas fa-plus"></i> Add New Train
                    </a>
                    <a href="routes/add_route.php" class="btn" style="background-color: #7a1a1a; color: white; margin: 5px;">
                        <i class="fas fa-plus"></i> Add New Route
                    </a>
                    <a href="reports/income_report.php" class="btn" style="background-color: #8b2a2a; color: white; margin: 5px;">
                        <i class="fas fa-chart-line"></i> View Reports
                    </a>
                    <a href="trains/manage_trains.php" class="btn" style="background-color: #9c3a3a; color: white; margin: 5px;">
                        <i class="fas fa-edit"></i> Manage Trains
                    </a>
                    <a href="routes/manage_routes.php" class="btn" style="background-color: #ad4a4a; color: white; margin: 5px;">
                        <i class="fas fa-route"></i> Manage Routes
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header" style="background-color: #610505; color: white;">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Recent Bookings</h5>
                </div>
                <div class="card-body">
                    <?php if(count($recent_bookings) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead style="background-color: #610505; color: white;">
                                    <tr>
                                        <th>Booking No</th>
                                        <th>User</th>
                                        <th>Train</th>
                                        <th>Route</th>
                                        <th>Fare</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_bookings as $booking): ?>
                                        <tr>
                                            <td><?php echo $booking['booking_number']; ?></td>
                                            <td><?php echo $booking['user_name']; ?></td>
                                            <td><?php echo $booking['train_name']; ?></td>
                                            <td><?php echo $booking['origin']; ?> → <?php echo $booking['destination']; ?></td>
                                            <td>₹<?php echo number_format($booking['total_fare'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="reports/income_report.php" style="color: #610505;">View All Reports →</a>
                    <?php else: ?>
                        <p class="text-muted">No bookings yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.btn {
    transition: all 0.3s ease;
}
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
.table thead th {
    border-bottom: 2px solid #610505;
}
</style>

<?php include '../includes/footer.php'; ?>