<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? escape($page_title) : 'Product Inventory System'; ?></title>
    <link rel="stylesheet" href="/FSD_Final/assets/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h2>📦 Product Inventory</h2>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
            <?php if (isLoggedIn()): ?>
            <ul class="nav-menu" id="navMenu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="add.php">Add Product</a></li>
                <li><a href="search.php">Search</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php" class="logout-btn">Logout</a></li>
            </ul>
            <?php endif; ?>
        </div>
    </nav>
    
    <main class="main-content">
        <div class="container">
            <?php
            $flash = getFlashMessage();
            if ($flash):
            ?>
            <div class="alert alert-<?php echo escape($flash['type']); ?>">
                <?php echo escape($flash['message']); ?>
            </div>
            <?php endif; ?>