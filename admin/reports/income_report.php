<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
redirectIfNotAdmin();

// Get report parameters
$report_type = $_GET['type'] ?? 'monthly';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

$title = '';
$income_data = [];

if($report_type == 'monthly') {
    $title = "Income Report for " . date('F Y', strtotime("$year-$month-01"));
    $stmt = $pdo->prepare("
        SELECT DATE(booking_date) as date, COUNT(*) as bookings, SUM(total_fare) as income
        FROM bookings 
        WHERE YEAR(booking_date) = ? AND MONTH(booking_date) = ? AND status = 'confirmed'
        GROUP BY DATE(booking_date)
        ORDER BY date
    ");
    $stmt->execute([$year, $month]);
    $income_data = $stmt->fetchAll();
} elseif($report_type == 'quarterly') {
    $quarter = $_GET['quarter'] ?? 1;
    $start_month = ($quarter - 1) * 3 + 1;
    $end_month = $start_month + 2;
    $title = "Income Report for Q$quarter $year";
    
    $stmt = $pdo->prepare("
        SELECT MONTH(booking_date) as month, COUNT(*) as bookings, SUM(total_fare) as income
        FROM bookings 
        WHERE YEAR(booking_date) = ? AND MONTH(booking_date) BETWEEN ? AND ? AND status = 'confirmed'
        GROUP BY MONTH(booking_date)
    ");
    $stmt->execute([$year, $start_month, $end_month]);
    $income_data = $stmt->fetchAll();
} else { // yearly
    $title = "Income Report for $year";
    $stmt = $pdo->prepare("
        SELECT MONTH(booking_date) as month, COUNT(*) as bookings, SUM(total_fare) as income
        FROM bookings 
        WHERE YEAR(booking_date) = ? AND status = 'confirmed'
        GROUP BY MONTH(booking_date)
        ORDER BY month
    ");
    $stmt->execute([$year]);
    $income_data = $stmt->fetchAll();
}

// Get total income
$total_income = array_sum(array_column($income_data, 'income'));
$total_bookings = array_sum(array_column($income_data, 'bookings'));
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header" style="background-color: #610505; color: white;">
            <h4 class="mb-0"><i class="fas fa-chart-line"></i> Income Reports</h4>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label style="color: #610505;">Report Type</label>
                        <select name="type" class="form-control" onchange="this.form.submit()" style="border-left: 3px solid #610505;">
                            <option value="monthly" <?php echo $report_type == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                            <option value="quarterly" <?php echo $report_type == 'quarterly' ? 'selected' : ''; ?>>Quarterly</option>
                            <option value="yearly" <?php echo $report_type == 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label style="color: #610505;">Year</label>
                        <select name="year" class="form-control" onchange="this.form.submit()" style="border-left: 3px solid #610505;">
                            <?php for($y = 2020; $y <= date('Y'); $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <?php if($report_type == 'monthly'): ?>
                    <div class="col-md-3">
                        <label style="color: #610505;">Month</label>
                        <select name="month" class="form-control" onchange="this.form.submit()" style="border-left: 3px solid #610505;">
                            <?php for($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo sprintf('%02d', $m); ?>" <?php echo $m == $month ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0,0,0,$m,1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <?php elseif($report_type == 'quarterly'): ?>
                    <div class="col-md-3">
                        <label style="color: #610505;">Quarter</label>
                        <select name="quarter" class="form-control" onchange="this.form.submit()" style="border-left: 3px solid #610505;">
                            <option value="1" <?php echo ($_GET['quarter'] ?? 1) == 1 ? 'selected' : ''; ?>>Q1 (Jan-Mar)</option>
                            <option value="2" <?php echo ($_GET['quarter'] ?? 1) == 2 ? 'selected' : ''; ?>>Q2 (Apr-Jun)</option>
                            <option value="3" <?php echo ($_GET['quarter'] ?? 1) == 3 ? 'selected' : ''; ?>>Q3 (Jul-Sep)</option>
                            <option value="4" <?php echo ($_GET['quarter'] ?? 1) == 4 ? 'selected' : ''; ?>>Q4 (Oct-Dec)</option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
            
            <h5 style="color: #610505;"><?php echo $title; ?></h5>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="alert" style="background-color: #28a745; color: white;">
                        <strong><i class="fas fa-rupee-sign"></i> Total Income:</strong> ₹<?php echo number_format($total_income, 2); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert" style="background-color: #610505; color: white;">
                        <strong><i class="fas fa-ticket-alt"></i> Total Bookings:</strong> <?php echo $total_bookings; ?>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead style="background-color: #610505; color: white;">
                        <tr>
                            <th>Period</th>
                            <th>Number of Bookings</th>
                            <th>Income (Rs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($income_data as $data): ?>
                        <tr>
                            <td>
                                <?php 
                                if($report_type == 'monthly') {
                                    echo date('d M Y', strtotime($data['date']));
                                } elseif($report_type == 'quarterly') {
                                    echo date('F', mktime(0,0,0,$data['month'],1));
                                } else {
                                    echo date('F', mktime(0,0,0,$data['month'],1));
                                }
                                ?>
                            </td>
                            <td><?php echo $data['bookings']; ?></td>
                            <td><strong style="color: #610505;">Rs. <?php echo number_format($data['income'], 2); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.form-control:focus, select:focus {
    border-color: #610505;
    box-shadow: 0 0 0 0.2rem rgba(97, 5, 5, 0.25);
}
.table-hover tbody tr:hover {
    background-color: rgba(97, 5, 5, 0.05);
}
.table thead th {
    border-bottom: 2px solid #610505;
}
</style>
