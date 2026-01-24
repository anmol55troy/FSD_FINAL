<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';


// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        // Get and sanitize input
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        } elseif (strlen($username) > 50) {
            $errors[] = 'Username must be less than 50 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        // Check if username already exists (Ajax validation is also available)
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = 'Username already exists';
            }
        }
        
        // Check if email already exists
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered';
            }
        }
        
        // If no errors, create user
        if (empty($errors)) {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare(
                    "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
                );
                $stmt->execute([$username, $email, $hashed_password]);
                
                // Auto-login after registration
                $user_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                session_regenerate_id(true);
                
                setFlashMessage('success', 'Registration successful! Welcome, ' . $username . '!');
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($page_title); ?></title>
    <link rel="stylesheet" href="/FSD_Final/assets/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1>📝 Create Account</h1>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo escape($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?php echo isset($username) ? escape($username) : ''; ?>"
                        required
                        minlength="3"
                        maxlength="50"
                        pattern="[a-zA-Z0-9_]+"
                        title="Only letters, numbers, and underscores allowed"
                        autofocus
                    >
                    <span class="form-help register-feedback" id="username-feedback"></span>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo isset($email) ? escape($email) : ''; ?>"
                        required
                    >
                    <span class="form-help register-feedback" id="email-feedback"></span>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        minlength="6"
                    >
                    <span class="form-help">Minimum 6 characters</span>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                    >
                    <span class="form-help register-feedback" id="password-feedback"></span>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>
            
            <div class="login-info">
                <p>Already have an account? <a href="login.php" style="color: #4F46E5; font-weight: bold;">Login here</a></p>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // Live username validation (Ajax)
        const usernameInput = document.getElementById('username');
        const usernameFeedback = document.getElementById('username-feedback');
        let usernameTimeout;
        
        usernameInput.addEventListener('input', function() {
            clearTimeout(usernameTimeout);
            const username = this.value;
            
            // Reset classes
            usernameFeedback.className = 'form-help register-feedback';
            
            if (username.length < 3) {
                usernameFeedback.textContent = 'Username must be at least 3 characters';
                usernameFeedback.classList.add('warning');
                return;
            }
            
            if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                usernameFeedback.textContent = 'Only letters, numbers, and underscores allowed';
                usernameFeedback.classList.add('error');
                return;
            }
            
            usernameFeedback.textContent = 'Checking availability...';
            usernameFeedback.classList.add('warning');
            
            usernameTimeout = setTimeout(() => {
                fetch(`check_availability.php?username=${encodeURIComponent(username)}`)
                    .then(response => response.json())
                    .then(data => {
                        usernameFeedback.className = 'form-help register-feedback';
                        if (data.available) {
                            usernameFeedback.textContent = '✓ Username available';
                            usernameFeedback.classList.add('success');
                        } else {
                            usernameFeedback.textContent = '✗ Username already taken';
                            usernameFeedback.classList.add('error');
                        }
                    })
                    .catch(error => {
                        console.error('Error checking username:', error);
                        usernameFeedback.className = 'form-help register-feedback';
                        usernameFeedback.textContent = 'Error checking availability';
                        usernameFeedback.classList.add('error');
                    });
            }, 500);
        });
        
        // Live email validation (Ajax)
        const emailInput = document.getElementById('email');
        const emailFeedback = document.getElementById('email-feedback');
        let emailTimeout;
        
        emailInput.addEventListener('input', function() {
            clearTimeout(emailTimeout);
            const email = this.value;
            
            // Reset classes
            emailFeedback.className = 'form-help register-feedback';
            
            if (!email.includes('@')) {
                emailFeedback.textContent = '';
                return;
            }
            
            emailFeedback.textContent = 'Checking availability...';
            emailFeedback.classList.add('warning');
            
            emailTimeout = setTimeout(() => {
                fetch(`check_availability.php?email=${encodeURIComponent(email)}`)
                    .then(response => response.json())
                    .then(data => {
                        emailFeedback.className = 'form-help register-feedback';
                        if (data.available) {
                            emailFeedback.textContent = '✓ Email available';
                            emailFeedback.classList.add('success');
                        } else {
                            emailFeedback.textContent = '✗ Email already registered';
                            emailFeedback.classList.add('error');
                        }
                    })
                    .catch(error => {
                        console.error('Error checking email:', error);
                        emailFeedback.className = 'form-help register-feedback';
                        emailFeedback.textContent = 'Error checking availability';
                        emailFeedback.classList.add('error');
                    });
            }, 500);
        });
        
        // Password confirmation validation
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordFeedback = document.getElementById('password-feedback');
        
        function checkPasswordMatch() {
            // Reset classes
            passwordFeedback.className = 'form-help register-feedback';
            
            if (confirmPasswordInput.value === '') {
                passwordFeedback.textContent = '';
                return;
            }
            
            if (passwordInput.value === confirmPasswordInput.value) {
                passwordFeedback.textContent = '✓ Passwords match';
                passwordFeedback.classList.add('success');
            } else {
                passwordFeedback.textContent = '✗ Passwords do not match';
                passwordFeedback.classList.add('error');
            }
        }
        
        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        
        // Form validation on submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (passwordInput.value.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters!');
                return false;
            }
        });
    </script>
</body>
</html>