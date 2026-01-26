<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

requireLogin();

$page_title = 'My Profile';
$errors = [];
$success = false;

// Fetch current user data
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('error', 'User not found');
    header('Location: logout.php');
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // CSRF protection
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        $email = trim($_POST['email'] ?? '');
        
        // Validation
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        // Check if email is taken by another user
        if (empty($errors) && $email !== $user['email']) {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered to another user';
            }
        }
        
        // Update email
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE user_id = ?");
                $stmt->execute([$email, $_SESSION['user_id']]);
                
                setFlashMessage('success', 'Profile updated successfully!');
                header('Location: profile.php');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Update failed: ' . $e->getMessage();
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    // CSRF protection
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($current_password)) {
            $errors[] = 'Current password is required';
        }
        
        if (empty($new_password)) {
            $errors[] = 'New password is required';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters';
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match';
        }
        
        // Verify current password
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user_data = $stmt->fetch();
            
            if (!password_verify($current_password, $user_data['password'])) {
                $errors[] = 'Current password is incorrect';
            }
        }
        
        // Update password
        if (empty($errors)) {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                
                setFlashMessage('success', 'Password changed successfully!');
                header('Location: profile.php');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Password change failed: ' . $e->getMessage();
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1> My Profile</h1>
    <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <strong>Please correct the following errors:</strong>
    <ul>
        <?php foreach ($errors as $error): ?>
        <li><?php echo escape($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="profile-container">
    <!-- Profile Information Card -->
    <div class="profile-card">
        <h2>Profile Information</h2>
        <div class="profile-info">
            <div class="info-row">
                <span class="info-label">Username:</span>
                <span class="info-value"><strong><?php echo escape($user['username']); ?></strong></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo escape($user['email']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Member Since:</span>
                <span class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Update Email Form -->
    <div class="profile-card">
        <h2>Update Email</h2>
        <form method="POST" action="" class="profile-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="update_profile" value="1">
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?php echo escape($user['email']); ?>"
                    required
                >
            </div>
            
            <button type="submit" class="btn btn-primary"> Update Email</button>
        </form>
    </div>
    
    <!-- Change Password Form -->
    <div class="profile-card">
        <h2>Change Password</h2>
        <form method="POST" action="" class="profile-form" id="passwordForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="change_password" value="1">
            
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    required
                    minlength="6"
                >
                <span class="form-help">Minimum 6 characters</span>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required
                >
                <span class="form-help" id="password-match"></span>
            </div>
            
            <button type="submit" class="btn btn-primary"> Change Password</button>
        </form>
    </div>
    
    <!-- User Statistics -->
    <div class="profile-card">
        <h2>Your Activity</h2>
        <div class="activity-stats">
            <?php
            // Get user's product count if you want to track who added what
            // For now, showing total products in system
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products");
            $stmt->execute();
            $stats = $stmt->fetch();
            ?>
            <div class="stat-item">
                <div class="stat-icon"></div>
                <div class="stat-details">
                    <div class="stat-label">Total Products in System</div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                </div>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon"></div>
                <div class="stat-details">
                    <div class="stat-label">Days as Member</div>
                    <div class="stat-value">
                        <?php 
                        $diff = time() - strtotime($user['created_at']);
                        $days = max(0, floor($diff / (60 * 60 * 24))); 
                        echo $days;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    max-width: 1200px;
}

.profile-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

.profile-card h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

.profile-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 1rem;
    background: var(--bg-color);
    border-radius: 8px;
}

.info-label {
    font-weight: 600;
    color: var(--secondary-color);
}

.info-value {
    color: var(--text-color);
}

.profile-form .form-group {
    margin-bottom: 1.5rem;
}

.activity-stats {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--bg-color);
    border-radius: 8px;
}

.stat-icon {
    font-size: 2rem;
}

.stat-details {
    flex: 1;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--secondary-color);
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-color);
}

@media (max-width: 768px) {
    .profile-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Password confirmation validation
const newPasswordInput = document.getElementById('new_password');
const confirmPasswordInput = document.getElementById('confirm_password');
const passwordMatch = document.getElementById('password-match');

function checkPasswordMatch() {
    if (confirmPasswordInput.value === '') {
        passwordMatch.textContent = '';
        return;
    }
    
    if (newPasswordInput.value === confirmPasswordInput.value) {
        passwordMatch.textContent = '✓ Passwords match';
        passwordMatch.style.color = 'var(--success-color)';
    } else {
        passwordMatch.textContent = '✗ Passwords do not match';
        passwordMatch.style.color = 'var(--danger-color)';
    }
}

if (newPasswordInput && confirmPasswordInput) {
    newPasswordInput.addEventListener('input', checkPasswordMatch);
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
}

// Form validation on submit
const passwordForm = document.getElementById('passwordForm');
if (passwordForm) {
    passwordForm.addEventListener('submit', function(e) {
        if (newPasswordInput.value !== confirmPasswordInput.value) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>