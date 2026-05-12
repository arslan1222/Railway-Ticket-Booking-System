<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
redirectIfNotAdmin();

$route_id = $_GET['id'];
$error = '';
$success = '';

// Fetch route details
$stmt = $pdo->prepare("
    SELECT r.*, t.train_name, t.train_number, t.total_seats 
    FROM routes r
    JOIN trains t ON r.train_id = t.id
    WHERE r.id = ?
");
$stmt->execute([$route_id]);
$route = $stmt->fetch();

if (!$route) {
    header("Location: manage_routes.php");
    exit();
}

// Get all trains for dropdown
$trains = $pdo->query("SELECT id, train_name, train_number FROM trains ORDER BY train_name")->fetchAll();

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
    $available_seats = $_POST['available_seats'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE routes 
            SET train_id = ?, origin = ?, destination = ?, departure_time = ?, arrival_time = ?, 
                duration = ?, distance = ?, fare = ?, travel_date = ?, available_seats = ?
            WHERE id = ?
        ");
        $stmt->execute([$train_id, $origin, $destination, $departure_time, $arrival_time, 
                       $duration, $distance, $fare, $travel_date, $available_seats, $route_id]);
        $success = "Route updated successfully!";
        
        // Refresh route data
        $stmt = $pdo->prepare("SELECT * FROM routes WHERE id = ?");
        $stmt->execute([$route_id]);
        $route = $stmt->fetch();
    } catch(PDOException $e) {
        $error = "Failed to update route: " . $e->getMessage();
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
                    <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Route</h4>
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
                                    <?php foreach($trains as $train): ?>
                                        <option value="<?php echo $train['id']; ?>" <?php echo $train['id'] == $route['train_id'] ? 'selected' : ''; ?>>
                                            <?php echo $train['train_name'] . " (" . $train['train_number'] . ")"; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="color: #610505;">Travel Date</label>
                                <input type="date" name="travel_date" class="form-control" value="<?php echo $route['travel_date']; ?>" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="color: #610505;">Origin</label>
                                <input type="text" name="origin" class="form-control" value="<?php echo $route['origin']; ?>" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="color: #610505;">Destination</label>
                                <input type="text" name="destination" class="form-control" value="<?php echo $route['destination']; ?>" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="color: #610505;">Departure Time</label>
                                <input type="time" name="departure_time" class="form-control" value="<?php echo $route['departure_time']; ?>" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="color: #610505;">Arrival Time</label>
                                <input type="time" name="arrival_time" class="form-control" value="<?php echo $route['arrival_time']; ?>" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="color: #610505;">Duration</label>
                                <input type="text" name="duration" class="form-control" value="<?php echo $route['duration']; ?>" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="color: #610505;">Distance (km)</label>
                                <input type="number" name="distance" class="form-control" value="<?php echo $route['distance']; ?>" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="color: #610505;">Fare (₹)</label>
                                <input type="number" step="0.01" name="fare" class="form-control" value="<?php echo $route['fare']; ?>" required style="border-left: 3px solid #610505;">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label style="color: #610505;">Available Seats</label>
                                <input type="number" name="available_seats" class="form-control" value="<?php echo $route['available_seats']; ?>" required style="border-left: 3px solid #610505;">
                            </div>
                        </div>
                        <button type="submit" class="btn" style="background-color: #610505; color: white;">Update Route</button>
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
