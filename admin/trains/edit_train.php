<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
redirectIfNotAdmin();

$train_id = $_GET['id'];
$error = '';
$success = '';

// Fetch train details
$stmt = $pdo->prepare("SELECT * FROM trains WHERE id = ?");
$stmt->execute([$train_id]);
$train = $stmt->fetch();

if (!$train) {
    header("Location: manage_trains.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $train_number = $_POST['train_number'];
    $train_name = $_POST['train_name'];
    $total_seats = $_POST['total_seats'];
    
    // Handle image upload
    $train_image = $train['train_image'];
    if(isset($_FILES['train_image']) && $_FILES['train_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['train_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            // Delete old image
            if($train_image && file_exists('../../assets/uploads/trains/' . $train_image)) {
                unlink('../../assets/uploads/trains/' . $train_image);
            }
            $train_image = time() . '_' . $filename;
            move_uploaded_file($_FILES['train_image']['tmp_name'], '../../assets/uploads/trains/' . $train_image);
        }
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE trains SET train_number = ?, train_name = ?, total_seats = ?, train_image = ? WHERE id = ?");
        $stmt->execute([$train_number, $train_name, $total_seats, $train_image, $train_id]);
        $success = "Train updated successfully!";
        
        // Refresh train data
        $stmt = $pdo->prepare("SELECT * FROM trains WHERE id = ?");
        $stmt->execute([$train_id]);
        $train = $stmt->fetch();
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
                    <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Train</h4>
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
                            <input type="text" name="train_number" class="form-control" value="<?php echo $train['train_number']; ?>" required style="border-left: 3px solid #610505;">
                        </div>
                        <div class="mb-3">
                            <label style="color: #610505;">Train Name</label>
                            <input type="text" name="train_name" class="form-control" value="<?php echo $train['train_name']; ?>" required style="border-left: 3px solid #610505;">
                        </div>
                        <div class="mb-3">
                            <label style="color: #610505;">Total Seats</label>
                            <input type="number" name="total_seats" class="form-control" value="<?php echo $train['total_seats']; ?>" required style="border-left: 3px solid #610505;">
                        </div>
                        <div class="mb-3">
                            <label style="color: #610505;">Current Image</label>
                            <?php if($train['train_image']): ?>
                                <div>
                                    <img src="../../assets/uploads/trains/<?php echo $train['train_image']; ?>" alt="Train Image" style="max-width: 200px;" class="mb-2">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="train_image" class="form-control" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                        <button type="submit" class="btn" style="background-color: #610505; color: white;">Update Train</button>
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
