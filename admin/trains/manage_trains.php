<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
redirectIfNotAdmin();

$stmt = $pdo->query("SELECT * FROM trains ORDER BY created_at DESC");
$trains = $stmt->fetchAll();
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="container mt-4">
    <!-- Display success message -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <!-- Display error message -->
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #610505; color: white;">
            <h4 class="mb-0"><i class="fas fa-train"></i> Manage Trains</h4>
            <a href="add_train.php" class="btn btn-light" style="color: #610505;">
                <i class="fas fa-plus"></i> Add New Train
            </a>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($trains) > 0): ?>
                            <?php foreach($trains as $train): ?>
                            <tr>
                                <td><?php echo $train['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($train['train_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($train['train_name']); ?></td>
                                <td><?php echo $train['total_seats']; ?></td>
                                <td>
                                    <a href="edit_train.php?id=<?php echo $train['id']; ?>" class="btn btn-sm" style="background-color: #ffc107; color: #000;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_train.php?id=<?php echo $train['id']; ?>" class="btn btn-sm" style="background-color: #dc3545; color: white;" 
                                       onclick="return confirm('Are you sure you want to delete this train? This action cannot be undone.\n\nNote: Train cannot be deleted if it has existing routes or bookings.')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No trains found. <a href="add_train.php">Add your first train</a></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
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
</script>

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
