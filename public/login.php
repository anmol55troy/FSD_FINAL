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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (empty($username)) {
            $errors[] = 'Username is required';
        }
        if (empty($password)) {
            $errors[] = 'Password is required';
        }
        
        if (empty($errors)) {
        // Prepared statement to prevent SQL injection
        $stmt = $pdo->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            setFlashMessage('success', 'Welcome back, ' . $user['username'] . '!');
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Invalid username or password';
        }
        }
    }
}

$page_title = 'Login';
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
            <h1> Product Inventory System Login</h1>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo escape($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?php echo isset($username) ? escape($username) : ''; ?>"
                        required
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                    >
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <div class="login-info"></div>
        </div>
    </div>
    
    <script src="/FSD_Final/assets/script.js"></script>
</body>
</html>