<?php
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Fetch upcoming trains for the next 7 days
$upcoming_trains = [];
$stmt = $pdo->prepare("
    SELECT r.*, t.train_name, t.train_number, t.train_image 
    FROM routes r
    JOIN trains t ON r.train_id = t.id
    WHERE r.travel_date >= CURDATE() 
    AND r.travel_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND r.available_seats > 0
    ORDER BY r.travel_date, r.departure_time
    LIMIT 6
");
$stmt->execute();
$upcoming_trains = $stmt->fetchAll();

// Get popular routes
$popular_routes = $pdo->query("
    SELECT origin, destination, COUNT(*) as route_count 
    FROM routes 
    GROUP BY origin, destination 
    ORDER BY route_count DESC 
    LIMIT 6
")->fetchAll();

// Get statistics
$stats = [];
$stats['total_trains'] = $pdo->query("SELECT COUNT(*) FROM trains")->fetchColumn();
$stats['total_routes'] = $pdo->query("SELECT COUNT(*) FROM routes WHERE travel_date >= CURDATE()")->fetchColumn();
$stats['total_bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn();
?>

<div class="hero-section" style="background: linear-gradient(135deg, #610505 0%, #8b1a1a 100%); color: white; padding: 80px 0;">
    <div class="container text-center">
        <h1 class="display-4">Welcome to Railway Ticket Booking System</h1>
        <p class="lead">Book your train tickets online easily and securely</p>
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="user/register.php" class="btn btn-light btn-lg">Get Started</a>
            <a href="user/login.php" class="btn btn-outline-light btn-lg">Login</a>
        <?php else: ?>
            <a href="user/search_trains.php" class="btn btn-light btn-lg">Book Now</a>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Search Section -->
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header" style="background-color: #610505; color: white;">
            <h5 class="mb-0"><i class="fas fa-search"></i> Quick Search</h5>
        </div>
        <div class="card-body">
            <form action="user/search_trains.php" method="POST" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">From</label>
                    <input type="text" name="origin" class="form-control" placeholder="Enter origin city" list="cityList" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">To</label>
                    <input type="text" name="destination" class="form-control" placeholder="Enter destination city" list="cityList" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Travel Date</label>
                    <input type="date" name="travel_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn w-100" style="background-color: #610505; color: white;">Search</button>
                </div>
            </form>
            <datalist id="cityList">
                <option value="Karachi">
                <option value="Lahore">
                <option value="Islamabad">
                <option value="Rawalpindi">
                <option value="Peshawar">
                <option value="Quetta">
                <option value="Multan">
                <option value="Faisalabad">
                <option value="Hyderabad">
            </datalist>
        </div>
    </div>
</div>

<!-- Statistics Section -->
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card text-center text-white" style="background-color: #610505;">
                <div class="card-body">
                    <i class="fas fa-train fa-2x"></i>
                    <h3><?php echo $stats['total_trains']; ?></h3>
                    <p>Total Trains</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center text-white" style="background-color: #7a1a1a;">
                <div class="card-body">
                    <i class="fas fa-route fa-2x"></i>
                    <h3><?php echo $stats['total_routes']; ?></h3>
                    <p>Active Routes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center text-white" style="background-color: #8b2a2a;">
                <div class="card-body">
                    <i class="fas fa-ticket-alt fa-2x"></i>
                    <h3><?php echo $stats['total_bookings']; ?></h3>
                    <p>Happy Passengers</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center text-white" style="background-color: #9c3a3a;">
                <div class="card-body">
                    <i class="fas fa-building fa-2x"></i>
                    <h3>100+</h3>
                    <p>Cities Covered</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Trains Section -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="fas fa-calendar-alt" style="color: #610505;"></i> Upcoming Trains (Next 7 Days)</h3>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="user/search_trains.php" class="btn" style="background-color: #610505; color: white;">View All Trains →</a>
        <?php endif; ?>
    </div>
    
    <?php if(count($upcoming_trains) > 0): ?>
        <div class="row">
            <?php foreach($upcoming_trains as $train): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm train-card">
                        <?php if($train['train_image'] && file_exists('assets/uploads/trains/' . $train['train_image'])): ?>
                            <img src="assets/uploads/trains/<?php echo $train['train_image']; ?>" 
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
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-center">
                                        <strong><?php echo htmlspecialchars($train['origin']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($train['departure_time'])); ?></small>
                                    </div>
                                    <div class="text-center">
                                        <i class="fas fa-arrow-right" style="color: #610505;"></i>
                                        <br>
                                        <small class="text-muted"><?php echo $train['duration']; ?></small>
                                    </div>
                                    <div class="text-center">
                                        <strong><?php echo htmlspecialchars($train['destination']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($train['arrival_time'])); ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="train-details">
                                <p class="mb-1">
                                    <i class="fas fa-calendar"></i> 
                                    <strong>Date:</strong> <?php echo date('d M Y', strtotime($train['travel_date'])); ?>
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-chair"></i> 
                                    <strong>Available:</strong> 
                                    <span class="badge" style="background-color: <?php echo $train['available_seats'] > 50 ? '#28a745' : ($train['available_seats'] > 20 ? '#ffc107' : '#dc3545'); ?>;">
                                        <?php echo $train['available_seats']; ?> seats
                                    </span>
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-tag"></i> 
                                    <strong>Fare:</strong> 
                                    <span style="color: #610505; font-weight: bold;">Rs. <?php echo number_format($train['fare'], 2); ?></span>
                                    <small class="text-muted">/person</small>
                                </p>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <a href="user/book_ticket.php?route_id=<?php echo $train['id']; ?>" 
                                   class="btn w-100" style="background-color: #28a745; color: white;">
                                    <i class="fas fa-ticket-alt"></i> Book Now
                                </a>
                            <?php else: ?>
                                <a href="user/login.php" class="btn w-100" style="background-color: #610505; color: white;">
                                    <i class="fas fa-sign-in-alt"></i> Login to Book
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-2x mb-2"></i>
            <p>No upcoming trains available. Please check back later.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Popular Routes Section -->
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header" style="background-color: #610505; color: white;">
            <h5 class="mb-0"><i class="fas fa-fire"></i> Popular Routes</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach($popular_routes as $route): ?>
                    <div class="col-md-4 col-lg-2 mb-2">
                        <a href="user/search_trains.php?origin=<?php echo urlencode($route['origin']); ?>&destination=<?php echo urlencode($route['destination']); ?>&travel_date=<?php echo date('Y-m-d', strtotime('+1 day')); ?>" 
                           class="btn btn-sm w-100" style="border-color: #610505; color: #610505;">
                            <i class="fas fa-train"></i> <?php echo htmlspecialchars($route['origin']); ?> → <?php echo htmlspecialchars($route['destination']); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container mt-5 mb-5">
    <h3 class="text-center mb-4" style="color: #610505;">Why Choose Us?</h3>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-train fa-3x" style="color: #610505;"></i>
                    <h5 class="mt-3">Easy Booking</h5>
                    <p>Book tickets in just a few clicks with our user-friendly interface</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-clock fa-3x" style="color: #610505;"></i>
                    <h5 class="mt-3">Real-time Availability</h5>
                    <p>Check seat availability instantly and book your preferred seats</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-shield-alt fa-3x" style="color: #610505;"></i>
                    <h5 class="mt-3">Secure Payments</h5>
                    <p>Safe and secure payment gateway with multiple payment options</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-headset fa-3x" style="color: #610505;"></i>
                    <h5 class="mt-3">24/7 Support</h5>
                    <p>Round-the-clock customer support for your convenience</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-ticket-alt fa-3x" style="color: #610505;"></i>
                    <h5 class="mt-3">Instant E-Ticket</h5>
                    <p>Get your e-ticket instantly after successful booking</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-percent fa-3x" style="color: #610505;"></i>
                    <h5 class="mt-3">Best Offers</h5>
                    <p>Exclusive deals and discounts on regular bookings</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.train-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.train-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.route-info {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
}

.card-footer {
    border-top: none;
}

.hero-section {
    margin-top: -20px;
}

.stat-card {
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
}

.btn-outline-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    background-color: #610505 !important;
    color: white !important;
    border-color: #610505 !important;
}

.btn-outline-primary {
    transition: all 0.3s ease;
}

/* Custom button hover effects */
.btn[style*="background-color: #610505"]:hover {
    background-color: #4a0404 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Card header with maroon color */
.card-header[style*="background-color: #610505"] {
    border-bottom: none;
}
</style>

<?php include 'includes/footer.php'; ?>