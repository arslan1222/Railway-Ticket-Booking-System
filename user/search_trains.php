<?php
require_once '../config/database.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

$trains = [];
$searchPerformed = false;
$origin = '';
$destination = '';
$travel_date = '';

// Handle search
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $origin = trim($_POST['origin']);
    $destination = trim($_POST['destination']);
    $travel_date = $_POST['travel_date'];
    
    $searchPerformed = true;
    
    $stmt = $pdo->prepare("
        SELECT r.*, t.train_name, t.train_number, t.train_image 
        FROM routes r
        JOIN trains t ON r.train_id = t.id
        WHERE r.origin LIKE ? 
        AND r.destination LIKE ? 
        AND r.travel_date = ? 
        AND r.available_seats > 0
        ORDER BY r.departure_time
    ");
    $stmt->execute(["%$origin%", "%$destination%", $travel_date]);
    $trains = $stmt->fetchAll();
} else {
    // Show all upcoming trains (next 30 days)
    $stmt = $pdo->prepare("
        SELECT r.*, t.train_name, t.train_number, t.train_image 
        FROM routes r
        JOIN trains t ON r.train_id = t.id
        WHERE r.travel_date >= CURDATE() 
        AND r.travel_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        AND r.available_seats > 0
        ORDER BY r.travel_date, r.departure_time
    ");
    $stmt->execute();
    $trains = $stmt->fetchAll();
}

// Get popular routes for suggestions
$popularRoutes = $pdo->query("
    SELECT DISTINCT origin, destination, COUNT(*) as count 
    FROM routes 
    GROUP BY origin, destination 
    ORDER BY count DESC 
    LIMIT 10
")->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <!-- Search Form -->
    <div class="card shadow mb-4">
        <div class="card-header" style="background-color: #610505; color: white;">
            <h4 class="mb-0"><i class="fas fa-search"></i> Search Trains</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="" id="searchForm">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">From</label>
                        <input type="text" name="origin" id="origin" class="form-control" 
                               placeholder="Enter origin city" value="<?php echo htmlspecialchars($origin); ?>" 
                               list="citySuggestions" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">To</label>
                        <input type="text" name="destination" id="destination" class="form-control" 
                               placeholder="Enter destination city" value="<?php echo htmlspecialchars($destination); ?>" 
                               list="citySuggestions" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Date of Journey</label>
                        <input type="date" name="travel_date" class="form-control" 
                               value="<?php echo $travel_date ?: date('Y-m-d', strtotime('+1 day')); ?>" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn w-100" style="background-color: #610505; color: white;">
                            <i class="fas fa-search"></i> Search Trains
                        </button>
                    </div>
                </div>
            </form>
            
            <datalist id="citySuggestions">
                <option value="Karachi">
                <option value="Lahore">
                <option value="Islamabad">
                <option value="Rawalpindi">
                <option value="Peshawar">
                <option value="Quetta">
                <option value="Multan">
                <option value="Faisalabad">
                <option value="Hyderabad">
                <option value="Sialkot">
                <option value="Gujranwala">
            </datalist>
        </div>
    </div>
    
    <!-- Popular Routes Section (Only show when no search performed) -->
    <?php if(!$searchPerformed && count($popularRoutes) > 0): ?>
    <div class="card shadow mb-4">
        <div class="card-header" style="background-color: #7a1a1a; color: white;">
            <h5 class="mb-0"><i class="fas fa-fire"></i> Popular Routes</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach(array_slice($popularRoutes, 0, 6) as $route): ?>
                <div class="col-md-4 col-lg-2 mb-2">
                    <button class="btn btn-sm w-100 popular-route-btn" 
                            style="border-color: #610505; color: #610505;"
                            data-origin="<?php echo htmlspecialchars($route['origin']); ?>" 
                            data-destination="<?php echo htmlspecialchars($route['destination']); ?>">
                        <i class="fas fa-train"></i> <?php echo $route['origin']; ?> → <?php echo $route['destination']; ?>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Results Section -->
    <div class="mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>
                <?php if($searchPerformed): ?>
                    <i class="fas fa-search" style="color: #610505;"></i> Search Results
                    <small class="text-muted">(<?php echo count($trains); ?> trains found)</small>
                <?php else: ?>
                    <i class="fas fa-calendar-alt" style="color: #610505;"></i> Upcoming Trains (Next 30 Days)
                    <small class="text-muted">(<?php echo count($trains); ?> available)</small>
                <?php endif; ?>
            </h4>
            <?php if($searchPerformed): ?>
                <a href="search_trains.php" class="btn btn-sm" style="background-color: #610505; color: white;">
                    <i class="fas fa-eye"></i> Show All Trains
                </a>
            <?php endif; ?>
        </div>
        
        <?php if(count($trains) > 0): ?>
            <div class="row">
                <?php foreach($trains as $train): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm train-card">
                            <?php if($train['train_image']): ?>
                                <img src="../assets/uploads/trains/<?php echo $train['train_image']; ?>" 
                                     class="card-img-top" alt="<?php echo $train['train_name']; ?>" 
                                     style="height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" 
                                     style="height: 150px;">
                                    <i class="fas fa-train fa-4x"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title" style="color: #610505;">
                                    <?php echo htmlspecialchars($train['train_name']); ?>
                                    <small class="text-muted">(<?php echo $train['train_number']; ?>)</small>
                                </h5>
                                
                                <div class="route-info mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>
                                            <strong><?php echo htmlspecialchars($train['origin']); ?></strong>
                                            <br>
                                            <small><?php echo date('h:i A', strtotime($train['departure_time'])); ?></small>
                                        </div>
                                        <div class="text-center">
                                            <i class="fas fa-arrow-right" style="color: #610505;"></i>
                                            <br>
                                            <small class="text-muted"><?php echo $train['duration']; ?></small>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($train['destination']); ?></strong>
                                            <br>
                                            <small><?php echo date('h:i A', strtotime($train['arrival_time'])); ?></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="train-details">
                                    <p class="mb-1">
                                        <i class="fas fa-calendar"></i> 
                                        <strong>Date:</strong> <?php echo date('d M Y', strtotime($train['travel_date'])); ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-road"></i> 
                                        <strong>Distance:</strong> <?php echo number_format($train['distance']); ?> km
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-chair"></i> 
                                        <strong>Available Seats:</strong> 
                                        <span class="badge" style="background-color: <?php echo $train['available_seats'] > 50 ? '#28a745' : ($train['available_seats'] > 20 ? '#ffc107' : '#dc3545'); ?>;">
                                            <?php echo $train['available_seats']; ?>
                                        </span>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-tag"></i> 
                                        <strong>Fare:</strong> 
                                        <span style="color: #610505; font-weight: bold;">₹<?php echo number_format($train['fare'], 2); ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="book_ticket.php?route_id=<?php echo $train['id']; ?>" 
                                   class="btn w-100" style="background-color: #28a745; color: white;">
                                    <i class="fas fa-ticket-alt"></i> Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                <h5>No trains found!</h5>
                <p>
                    <?php if($searchPerformed): ?>
                        No trains available for the selected route and date. Please try different search criteria.
                    <?php else: ?>
                        No upcoming trains available in the next 30 days. Please check back later.
                    <?php endif; ?>
                </p>
                <?php if($searchPerformed): ?>
                    <a href="search_trains.php" class="btn" style="background-color: #610505; color: white;">View All Trains</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-populate popular routes when clicked
document.querySelectorAll('.popular-route-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('origin').value = this.dataset.origin;
        document.getElementById('destination').value = this.dataset.destination;
        document.getElementById('searchForm').submit();
    });
});

// Add loading effect on form submit
document.getElementById('searchForm').addEventListener('submit', function() {
    const btn = this.querySelector('button[type="submit"]');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
    btn.disabled = true;
});

// Add hover effect for popular route buttons
document.querySelectorAll('.popular-route-btn').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
        this.style.backgroundColor = '#610505';
        this.style.color = 'white';
    });
    btn.addEventListener('mouseleave', function() {
        this.style.backgroundColor = 'transparent';
        this.style.color = '#610505';
    });
});
</script>

<style>
.train-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.train-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.popular-route-btn {
    transition: all 0.3s ease;
}

.popular-route-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.route-info {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
}

.card-footer {
    border-top: none;
}

/* Custom button hover effect */
.btn[style*="background-color: #610505"]:hover {
    background-color: #4a0404 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Card header custom styling */
.card-header[style*="background-color: #610505"] {
    border-bottom: none;
}

.card-header[style*="background-color: #7a1a1a"] {
    border-bottom: none;
}

/* Animation for loading */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spin {
    animation: spin 1s linear infinite;
}
</style>

<?php include '../includes/footer.php'; ?>