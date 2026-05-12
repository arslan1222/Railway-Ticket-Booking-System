<?php
require_once '../config/database.php';
require_once '../includes/session.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error = "Please enter a valid phone number (10-15 digits)!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password, $phone, $address]);
            $success = "Registration successful! Please login.";
        } catch(PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = "Email already exists! Please use a different email address.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
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
                    <h4 class="mb-0"><i class="fas fa-user-plus"></i> User Registration</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <script>
                            setTimeout(function() {
                                window.location.href = 'login.php';
                            }, 2000);
                        </script>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="registerForm">
                        <div class="mb-3">
                            <label class="form-label" style="color: #610505; font-weight: 500;">
                                <i class="fas fa-user"></i> Full Name
                            </label>
                            <input type="text" name="name" class="form-control" 
                                   placeholder="Enter your full name" 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                   required style="border-left: 3px solid #610505;">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #610505; font-weight: 500;">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email" name="email" class="form-control" 
                                   placeholder="Enter your email address"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   required style="border-left: 3px solid #610505;">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #610505; font-weight: 500;">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" 
                                       placeholder="Create a password (min. 6 characters)"
                                       required style="border-left: 3px solid #610505;">
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #610505; font-weight: 500;">
                                <i class="fas fa-lock"></i> Confirm Password
                            </label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                                       placeholder="Confirm your password"
                                       required style="border-left: 3px solid #610505;">
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirm_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="small mt-1"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #610505; font-weight: 500;">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input type="tel" name="phone" class="form-control" 
                                   placeholder="Enter your mobile number (10-15 digits)"
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                   required style="border-left: 3px solid #610505;">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #610505; font-weight: 500;">
                                <i class="fas fa-map-marker-alt"></i> Address
                            </label>
                            <textarea name="address" class="form-control" rows="3" 
                                      placeholder="Enter your complete address"
                                      style="border-left: 3px solid #610505;"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn w-100" style="background-color: #610505; color: white;">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p class="mb-0">Already have an account? 
                            <a href="login.php" style="color: #610505; font-weight: 500;">Login here</a>
                        </p>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center">
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});

// Password match validation
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirm_password');
const passwordMatchDiv = document.getElementById('passwordMatch');

function validatePasswordMatch() {
    if (password.value !== confirmPassword.value) {
        passwordMatchDiv.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-times-circle"></i> Passwords do not match!</span>';
        return false;
    } else if (password.value.length > 0 && confirmPassword.value.length > 0) {
        passwordMatchDiv.innerHTML = '<span style="color: #28a745;"><i class="fas fa-check-circle"></i> Passwords match!</span>';
        return true;
    } else {
        passwordMatchDiv.innerHTML = '';
        return false;
    }
}

password.addEventListener('keyup', validatePasswordMatch);
confirmPassword.addEventListener('keyup', validatePasswordMatch);

// Form validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const name = document.querySelector('input[name="name"]').value.trim();
    const email = document.querySelector('input[name="email"]').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const phone = document.querySelector('input[name="phone"]').value.trim();
    const terms = document.getElementById('terms').checked;
    
    if (name.length < 3) {
        e.preventDefault();
        alert('Please enter a valid name (minimum 3 characters)');
        return false;
    }
    
    if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        e.preventDefault();
        alert('Please enter a valid email address');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long');
        return false;
    }
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match');
        return false;
    }
    
    if (!phone.match(/^[0-9]{10,15}$/)) {
        e.preventDefault();
        alert('Please enter a valid phone number (10-15 digits)');
        return false;
    }
    
    if (!terms) {
        e.preventDefault();
        alert('Please agree to the Terms and Conditions');
        return false;
    }
    
    // Show loading effect
    const btn = this.querySelector('button[type="submit"]');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
    btn.disabled = true;
});

// Phone number validation - only allow numbers
document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 3000);
    });
}, 3000);
</script>

<style>
/* Form control focus effect */
.form-control:focus, .form-check-input:focus {
    border-color: #610505;
    box-shadow: 0 0 0 0.2rem rgba(97, 5, 5, 0.25);
}

/* Card header styling */
.card-header[style*="background-color: #610505"] {
    border-bottom: none;
}

/* Benefits card hover effect */
.card[style*="border-left: 4px solid #610505"]:hover {
    transform: translateX(5px);
    transition: all 0.3s ease;
}

/* Button hover effect */
.btn[style*="background-color: #610505"]:hover {
    background-color: #4a0404 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

/* Toggle password button hover */
.toggle-password:hover {
    background-color: #610505;
    color: white;
    border-color: #610505;
}

/* Form check styling */
.form-check-input:checked {
    background-color: #610505;
    border-color: #610505;
}

/* Animation for alerts */
.alert {
    animation: slideDown 0.5s ease;
}

@keyframes slideDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Loading spinner animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spin {
    animation: spin 1s linear infinite;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .container {
        padding: 0 15px;
    }
    
    .card-body {
        padding: 1.5rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>