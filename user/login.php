<?php
require_once '../config/database.php';
require_once '../includes/session.php';

if (isLoggedIn()) {
    if($_SESSION['user_role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        
        if($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header" style="background-color: #610505; color: white;">
                    <h4 class="mb-0"><i class="fas fa-sign-in-alt"></i> Login</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label style="color: #610505;">Email Address</label>
                            <input type="email" name="email" class="form-control" required style="border-left: 3px solid #610505;">
                        </div>
                        <div class="mb-3">
                            <label style="color: #610505;">Password</label>
                            <input type="password" name="password" class="form-control" required style="border-left: 3px solid #610505;">
                        </div>
                        <button type="submit" class="btn w-100" style="background-color: #610505; color: white;">Login</button>
                    </form>
                    <p class="mt-3 text-center">Don't have an account? <a href="register.php" style="color: #610505;">Register here</a></p>
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
.btn:hover {
    background-color: #4a0404 !important;
    transform: translateY(-2px);
    transition: all 0.3s ease;
}
</style>

<?php include '../includes/footer.php'; ?>