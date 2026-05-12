<?php
require_once '../config/database.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch user statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as active_bookings,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
        COALESCE(SUM(CASE WHEN status = 'confirmed' THEN total_fare ELSE 0 END), 0) as total_spent
    FROM bookings 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #610505; color: white;">
                    <h4 class="mb-0"><i class="fas fa-user-circle"></i> My Profile</h4>
                    <a href="edit_profile.php" class="btn btn-sm" style="background-color: #ffffff; color: #610505;">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted" style="border-left: 3px solid #610505; padding-left: 10px;">
                                <i class="fas fa-info-circle" style="color: #610505;"></i> Personal Information
                            </h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="35%" style="color: #610505;">Full Name:</th>
                                    <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                </tr>
                                <tr>
                                    <th style="color: #610505;">Email Address:</th>
                                    <td><i class="fas fa-envelope" style="color: #610505;"></i> <?php echo htmlspecialchars($user['email']); ?></td>
                                </tr>
                                <tr>
                                    <th style="color: #610505;">Phone Number:</th>
                                    <td>
                                        <i class="fas fa-phone" style="color: #610505;"></i> 
                                        <?php echo $user['phone'] ?? '<span class="text-muted">Not provided</span>'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="color: #610505;">Address:</th>
                                    <td>
                                        <i class="fas fa-map-marker-alt" style="color: #610505;"></i> 
                                        <?php echo $user['address'] ?? '<span class="text-muted">Not provided</span>'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="color: #610505;">Account Type:</th>
                                    <td>
                                        <span class="badge" style="background-color: <?php echo $user['role'] == 'admin' ? '#dc3545' : '#610505'; ?>;">
                                            <i class="fas <?php echo $user['role'] == 'admin' ? 'fa-shield-alt' : 'fa-user'; ?>"></i>
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="color: #610505;">Member Since:</th>
                                    <td>
                                        <i class="fas fa-calendar-alt" style="color: #610505;"></i> 
                                        <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="card" style="background: linear-gradient(135deg, #610505 0%, #8b1a1a 100%); color: white;">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-circle fa-5x" style="color: white;"></i>
                                    <h5 class="mt-3"><?php echo htmlspecialchars($user['name']); ?></h5>
                                    <p class="mb-2" style="color: rgba(255,255,255,0.9);">
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                                    </p>
                                    <hr style="background-color: rgba(255,255,255,0.3);">
                                    
                                    <!-- Quick Stats -->
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <h6 class="mb-1">Total Bookings</h6>
                                            <h4 class="mb-0"><?php echo $stats['total_bookings'] ?? 0; ?></h4>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="mb-1">Total Spent</h6>
                                            <h4 class="mb-0">₹<?php echo number_format($stats['total_spent'] ?? 0, 2); ?></h4>
                                        </div>
                                    </div>
                                    
                                    <hr style="background-color: rgba(255,255,255,0.3);">
                                    
                                    <div class="row">
                                        <div class="col-6">
                                            <small>Active Bookings</small>
                                            <br>
                                            <span class="badge" style="background-color: #28a745;"><?php echo $stats['active_bookings'] ?? 0; ?></span>
                                        </div>
                                        <div class="col-6">
                                            <small>Cancelled Bookings</small>
                                            <br>
                                            <span class="badge" style="background-color: #dc3545;"><?php echo $stats['cancelled_bookings'] ?? 0; ?></span>
                                        </div>
                                    </div>
                                    
                                    <hr style="background-color: rgba(255,255,255,0.3);">
                                    
                                    <a href="my_bookings.php" class="btn btn-sm" style="background-color: white; color: #610505; margin-top: 10px;">
                                        <i class="fas fa-ticket-alt"></i> View My Bookings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<style>
/* Card header styling */
.card-header[style*="background-color: #610505"] {
    border-bottom: none;
}

.card-header[style*="background-color: #7a1a1a"] {
    border-bottom: none;
}

/* Table styling */
.table-borderless th {
    font-weight: 600;
}

.table-borderless td {
    font-weight: 500;
}

/* Quick action buttons hover effect */
.btn-outline {
    transition: all 0.3s ease;
}

.btn-outline:hover {
    background-color: #610505 !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Profile card hover effect */
.card[style*="background: linear-gradient"] {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card[style*="background: linear-gradient"]:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

/* Animation for icons */
.fa-user-circle {
    transition: transform 0.3s ease;
}

.fa-user-circle:hover {
    transform: scale(1.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-borderless th, 
    .table-borderless td {
        display: block;
        width: 100%;
    }
    
    .table-borderless tr {
        margin-bottom: 15px;
        display: block;
    }
    
    .table-borderless th {
        margin-bottom: 5px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>