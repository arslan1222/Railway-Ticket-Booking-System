<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
redirectIfNotAdmin();

$stmt = $pdo->query("
    SELECT r.*, t.train_name, t.train_number 
    FROM routes r
    JOIN trains t ON r.train_id = t.id
    ORDER BY r.travel_date DESC
");
$routes = $stmt->fetchAll();
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #610505; color: white;">
            <h4 class="mb-0"><i class="fas fa-route"></i> Manage Routes</h4>
            <a href="add_route.php" class="btn btn-light" style="color: #610505;">
                <i class="fas fa-plus"></i> Add New Route
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead style="background-color: #610505; color: white;">
                        <tr>
                            <th>ID</th>
                            <th>Train</th>
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Fare</th>
                            <th>Date</th>
                            <th>Seats</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($routes as $route): ?>
                        <tr>
                            <td><?php echo $route['id']; ?></td>
                            <td><strong><?php echo $route['train_name']; ?></strong><br><small class="text-muted">(<?php echo $route['train_number']; ?>)</small>
                            <td><?php echo $route['origin']; ?></td>
                            <td><?php echo $route['destination']; ?></td>
                            <td><?php echo date('h:i A', strtotime($route['departure_time'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($route['arrival_time'])); ?></td>
                            <td><span style="color: #610505; font-weight: bold;">Rs. <?php echo number_format($route['fare'], 2); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($route['travel_date'])); ?></td>
                            <td>
                                <span class="badge" style="background-color: <?php echo $route['available_seats'] > 50 ? '#28a745' : ($route['available_seats'] > 20 ? '#ffc107' : '#dc3545'); ?>;">
                                    <?php echo $route['available_seats']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_route.php?id=<?php echo $route['id']; ?>" class="btn btn-sm" style="background-color: #ffc107; color: #000;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_route.php?id=<?php echo $route['id']; ?>" class="btn btn-sm" style="background-color: #dc3545; color: white;" onclick="return confirm('Delete this route?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
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
.btn {
    transition: all 0.3s ease;
}
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
</style>
