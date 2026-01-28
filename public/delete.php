<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

requireLogin();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method');
    header('Location: index.php');
    exit;
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Invalid CSRF token');
    header('Location: index.php');
    exit;
}

// IMPLEMENTATION: Deletion enforces POST requests, verifies CSRF token,
// and uses prepared statements for safe deletion.

$product_id = $_POST['id'] ?? 0;

if ($product_id) {
    // Verify product exists
    $stmt = $pdo->prepare("SELECT product_name FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if ($product) {
        try {
            // Delete product using prepared statement
            $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            
            setFlashMessage('success', 'Product "' . $product['product_name'] . '" deleted successfully!');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Error deleting product: ' . $e->getMessage());
        }
    } else {
        setFlashMessage('error', 'Product not found');
    }
} else {
    setFlashMessage('error', 'Invalid product ID');
}

header('Location: index.php');
exit;
?>