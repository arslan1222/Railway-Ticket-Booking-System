<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
redirectIfNotAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $train_number = $_POST['train_number'];
    $train_name = $_POST['train_name'];
    $total_seats = $_POST['total_seats'];
    
    // Handle image upload
    $train_image = '';
    if(isset($_FILES['train_image']) && $_FILES['train_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['train_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $train_image = time() . '_' . $filename;
            move_uploaded_file($_FILES['train_image']['tmp_name'], '../../assets/uploads/trains/' . $train_image);
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO trains (train_number, train_name, total_seats, train_image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$train_number, $train_name, $total_seats, $train_image]);
        $success = "Train added successfully!";
    } catch(PDOException $e) {
        $error = "Train number already exists!";
    }
}
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header" style="background-color: #610505; color: white;">
                    <h4 class="mb-0"><i class="fas fa-plus"></i> Add New Train</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label style="color: #610505;">Train Number</label>
                            <input type="text" name="train_number" class="form-control" required style="border-left: 3px solid #610505;">
                        </div>
                        <div class="mb-3">
                            <label style="color: #610505;">Train Name</label>
                            <input type="text" name="train_name" class="form-control" required style="border-left: 3px solid #610505;">
                        </div>
                        <div class="mb-3">
                            <label style="color: #610505;">Total Seats</label>
                            <input type="number" name="total_seats" class="form-control" required style="border-left: 3px solid #610505;">
                        </div>
                        <div class="mb-3">
                            <label style="color: #610505;">Train Image</label>
                            <input type="file" name="train_image" class="form-control" accept="image/*">
                        </div>
                        <button type="submit" class="btn" style="background-color: #610505; color: white;">Add Train</button>
                        <a href="manage_trains.php" class="btn" style="background-color: #610505; color: white;">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-control:focus {
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
