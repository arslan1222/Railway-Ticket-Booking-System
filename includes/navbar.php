<?php
// Determine the base path based on current file location
$current_file = $_SERVER['PHP_SELF'];
$base_path = '';

if (strpos($current_file, '/admin/') !== false) {
    // If we're in admin directory, go up one level
    $base_path = '../';
} elseif (strpos($current_file, '/user/') !== false) {
    // If we're in user directory, go up one level
    $base_path = '../';
} else {
    // If we're in root directory
    $base_path = '';
}

// For login/register pages that are in user folder
if (basename($current_file) == 'login.php' || basename($current_file) == 'register.php') {
    $base_path = '../';
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $base_path; ?>index.php">
            <b>Railway Booking System</b>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['user_role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_path; ?>dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_path; ?>admin/trains/manage_trains.php">
                                <i class="fas fa-train"></i> Trains
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_path; ?>routes/manage_routes.php">
                                <i class="fas fa-route"></i> Routes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_path; ?>reports/income_report.php">
                                <i class="fas fa-chart-line"></i> Reports
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_path; ?>user/search_trains.php">
                                <i class="fas fa-search"></i> Search Trains
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_path; ?>user/my_bookings.php">
                                <i class="fas fa-ticket-alt"></i> My Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_path; ?>user/profile.php">
                                <i class="fas fa-user"></i> Profile
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_path; ?>logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_path; ?>user/login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_path; ?>user/register.php">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>