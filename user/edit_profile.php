<?php
require_once '../config/database.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

$error = '';
$success = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    // Verify current password if changing password
    if(!empty($current_password) || !empty($new_password)) {
        if(empty($current_password) || empty($new_password)) {
            $error = "Please provide both current and new password to update password!";
        } elseif(!password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect!";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $address, $hashed_password, $_SESSION['user_id']]);
            $success = "Profile updated successfully including password!";
        }
    } else {
        // Update without password change
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $_SESSION['user_id']]);
        $success = "Profile updated successfully!";
    }
    
    if($success) {
        $_SESSION['user_name'] = $name;
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header" style="background-color: #610505; color: white;">
                    <h4 class="mb-0"><i class="fas fa-user-edit"></i> Edit Profile</h4>
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
                                window.location.href = 'profile.php';
                            }, 2000);
                        </script>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="editProfileForm">
                        <div class="mb-3">
                            <label class="form-label" style="color: #610505; font-weight: 500;">
                                <i class="fas fa-user"></i> Full Name
                            </label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" 
                                   required style="border-left: 3px solid #610505;">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #610505; font-weight: 500;">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   disabled style="background-color: #e9ecef;">
                            <small class="text-muted">Email address cannot be changed</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #610505; font-weight: 500;">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>" 
                                   pattern="[0-9]{10,15}" 
                                   placeholder="Enter your phone number"
                                   style="border-left: 3px solid #610505;">
                            <small class="text-muted">Enter 10-15 digit mobile number</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #610505; font-weight: 500;">
                                <i class="fas fa-map-marker-alt"></i> Address
                            </label>
                            <textarea name="address" class="form-control" rows="3" 
                                      placeholder="Enter your complete address"
                                      style="border-left: 3px solid #610505;"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        
                        <hr style="border-top: 2px solid #610505;">
                        
                        <div class="mb-3">
                            <h6 style="color: #610505;">
                                <i class="fas fa-lock"></i> Change Password (Optional)
                            </h6>
                            <div class="alert alert-info" role="alert">
                                <small>
                                    <i class="fas fa-info-circle"></i> 
                                    Leave password fields empty to keep current password
                                </small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" name="current_password" id="current_password" class="form-control">
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" name="new_password" id="new_password" class="form-control">
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Password should be at least 6 characters long</small>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="profile.php" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn" style="background-color: #610505; color: white;">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
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

// Form validation
document.getElementById('editProfileForm').addEventListener('submit', function(e) {
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    
    if ((currentPassword && !newPassword) || (!currentPassword && newPassword)) {
        e.preventDefault();
        alert('Please provide both current and new password to update password');
        return false;
    }
    
    if (newPassword && newPassword.length < 6) {
        e.preventDefault();
        alert('New password must be at least 6 characters long');
        return false;
    }
    
    // Phone number validation
    const phone = document.querySelector('input[name="phone"]').value;
    if (phone && !phone.match(/^[0-9]{10,15}$/)) {
        e.preventDefault();
        alert('Please enter a valid phone number (10-15 digits only)');
        return false;
    }
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        setTimeout(function() {
            bsAlert.close();
        }, 3000);
    });
}, 3000);
</script>

<style>
/* Form control focus effect */
.form-control:focus {
    border-color: #610505;
    box-shadow: 0 0 0 0.2rem rgba(97, 5, 5, 0.25);
}

/* Input group focus */
.input-group:focus-within {
    box-shadow: 0 0 0 0.2rem rgba(97, 5, 5, 0.25);
    border-radius: 0.375rem;
}

/* Button hover effect */
.btn[style*="background-color: #610505"]:hover {
    background-color: #4a0404 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

/* Card header styling */
.card-header[style*="background-color: #610505"] {
    border-bottom: none;
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

/* Toggle password button */
.toggle-password {
    cursor: pointer;
}

.toggle-password:hover {
    background-color: #610505;
    color: white;
    border-color: #610505;
}

/* Tips card hover */
.card[style*="border-left: 4px solid #610505"]:hover {
    transform: translateX(5px);
    transition: all 0.3s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .d-grid {
        gap: 10px;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 5px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>