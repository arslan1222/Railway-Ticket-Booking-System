<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
redirectIfNotAdmin();

$error = '';
$success = '';

// Get all trains for dropdown
$trains = $pdo->query("SELECT id, train_name, train_number, total_seats FROM trains ORDER BY train_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $train_id = $_POST['train_id'];
    $origin = $_POST['origin'];
    $destination = $_POST['destination'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $duration = $_POST['duration'];
    $distance = $_POST['distance'];
    $fare = $_POST['fare'];
    $travel_date = $_POST['travel_date'];
    
    // Get total seats from train
    $stmt = $pdo->prepare("SELECT total_seats FROM trains WHERE id = ?");
    $stmt->execute([$train_id]);
    $train = $stmt->fetch();
    $available_seats = $train['total_seats'];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO routes (train_id, origin, destination, departure_time, arrival_time, duration, distance, fare, available_seats, travel_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$train_id, $origin, $destination, $departure_time, $arrival_time, $duration, $distance, $fare, $available_seats, $travel_date]);
        $success = "Route added successfully!";
    } catch(PDOException $e) {
        $error = "Failed to add route: " . $e->getMessage();
    }
}
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header" style="background-color: #610505; color: white;">
                    <h4 class="mb-0"><i class="fas fa-plus"></i> Add New Route</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="color: #610505;">Select Train</label>
                                <select name="train_id" class="form-control" required style="border-left: 3px solid #610505;">
                                    <option value="">Select Train</option>
                                    <?php foreach($trains as $train): ?>
                                        <option value="<?php echo $train['id']; ?>">
                                            <?php echo $train['train_name'] . " (" . $train['train_number'] . ") - Seats: " . $train['total_seats']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="color: #610505;">Travel Date</label>
                                <input type="date" name="travel_date" class="form-control" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="color: #610505;">Origin</label>
                                <input type="text" name="origin" class="form-control" placeholder="e.g., Karachi" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="color: #610505;">Destination</label>
                                <input type="text" name="destination" class="form-control" placeholder="e.g., Lahore" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="color: #610505;">Departure Time</label>
                                <input type="time" name="departure_time" class="form-control" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="color: #610505;">Arrival Time</label>
                                <input type="time" name="arrival_time" class="form-control" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="color: #610505;">Duration</label>
                                <input type="text" name="duration" class="form-control" placeholder="e.g., 16 hours" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="color: #610505;">Distance (km)</label>
                                <input type="number" name="distance" class="form-control" placeholder="e.g., 1400" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="color: #610505;">Fare (Rs)</label>
                                <input type="number" step="0.01" name="fare" class="form-control" placeholder="e.g., 2500" required style="border-left: 3px solid #610505;">
                            </div>
                        </div>
                        <button type="submit" class="btn" style="background-color: #610505; color: white;">Add Route</button>
                        <a href="manage_routes.php" class="btn" style="background-color: #610505; color: white;">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-control:focus, select:focus {
    border-color: #610505;
    box-shadow: 0 0 0 0.2rem rgba(97, 5, 5, 0.25);
}
.btn {
    transition: all 0.3s ease;
}
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
</style>
