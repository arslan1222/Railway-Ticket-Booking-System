<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
redirectIfNotAdmin();

// Get all trains with their route counts and booking statistics
$stmt = $pdo->query("
    SELECT 
        t.*,
        COUNT(DISTINCT r.id) as total_routes,
        COUNT(DISTINCT b.id) as total_bookings,
        COALESCE(SUM(b.total_fare), 0) as total_revenue
    FROM trains t
    LEFT JOIN routes r ON t.id = r.train_id
    LEFT JOIN bookings b ON r.id = b.route_id AND b.status = 'confirmed'
    GROUP BY t.id
    ORDER BY t.train_name
");
$trains = $stmt->fetchAll();
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #610505; color: white;">
            <h4 class="mb-0"><i class="fas fa-train"></i> Train List Report</h4>
            <button onclick="window.print()" class="btn btn-light" style="color: #610505;">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead style="background-color: #610505; color: white;">
                        <tr>
                            <th>ID</th>
                            <th>Train Number</th>
                            <th>Train Name</th>
                            <th>Total Seats</th>
                            <th>Total Routes</th>
                            <th>Total Bookings</th>
                            <th>Total Revenue (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($trains as $train): ?>
                        <tr>
                            <td><?php echo $train['id']; ?></td>
                            <td><strong><?php echo $train['train_number']; ?></strong></td>
                            <td><?php echo $train['train_name']; ?></td>
                            <td><?php echo $train['total_seats']; ?></td>
                            <td><?php echo $train['total_routes']; ?></td>
                            <td><?php echo $train['total_bookings']; ?></td>
                            <td><strong style="color: #610505;">₹<?php echo number_format($train['total_revenue'], 2); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot style="background-color: #f8f9fa;">
                        <tr>
                            <th colspan="5" class="text-end">Grand Total:</th>
                            <th><strong><?php echo array_sum(array_column($trains, 'total_bookings')); ?></strong></th>
                            <th><strong style="color: #610505;">₹<?php echo number_format(array_sum(array_column($trains, 'total_revenue')), 2); ?></strong></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.table-hover tbody tr:hover {
    background-color: rgba(97, 5, 5, 0.05);
}
.table thead th {
    border-bottom: 2px solid #610505;
}
@media print {
    .navbar, footer, .btn, .card-header .btn {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
