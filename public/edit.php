<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

requireLogin();

$page_title = 'Edit Product';
$errors = [];
$product_id = $_GET['id'] ?? 0;

// Fetch product details
if ($product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    // IMPLEMENTATION: Fetch uses prepared statement to avoid SQL injection.
    
    if (!$product) {
        setFlashMessage('error', 'Product not found');
        header('Location: index.php');
        exit;
    }
} else {
    setFlashMessage('error', 'Invalid product ID');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        // IMPLEMENTATION: CSRF token verification and server-side validation
        // occur here; the UPDATE below uses a prepared statement.
        // Get and sanitize input
        $product_name = trim($_POST['product_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $quantity = trim($_POST['quantity'] ?? '');
        
        // Validation
        if (empty($product_name)) {
            $errors[] = 'Product name is required';
        } elseif (strlen($product_name) > 100) {
            $errors[] = 'Product name must be less than 100 characters';
        }
        
        if (empty($category)) {
            $errors[] = 'Category is required';
        }
        
        if (empty($price)) {
            $errors[] = 'Price is required';
        } elseif (!is_numeric($price) || $price < 0) {
            $errors[] = 'Price must be a valid positive number';
        }
        
        if (empty($quantity)) {
            $errors[] = 'Quantity is required';
        } elseif (!is_numeric($quantity) || intval($quantity) != $quantity || intval($quantity) < 0) {
            $errors[] = 'Quantity must be a valid positive integer';
        }
        
        // If no errors, update database
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare(
                    "UPDATE products 
                     SET product_name = ?, description = ?, category = ?, price = ?, quantity = ?
                     WHERE product_id = ?"
                );
                $stmt->execute([$product_name, $description, $category, $price, $quantity, $product_id]);
                // IMPLEMENTATION: UPDATE uses a prepared statement to prevent SQL injection.
                
                setFlashMessage('success', 'Product updated successfully!');
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
} else {
    // Pre-fill form with existing data
    $product_name = $product['product_name'];
    $description = $product['description'];
    $category = $product['category'];
    $price = $product['price'];
    $quantity = $product['quantity'];
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>Edit Product</h1>
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

<div class="form-container">
    <form method="POST" action="" id="editProductForm" class="product-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="form-group">
            <label for="product_name">Product Name <span class="required">*</span></label>
            <input 
                type="text" 
                id="product_name" 
                name="product_name" 
                value="<?php echo escape($product_name); ?>"
                required
                maxlength="100"
            >
            <span class="form-help">Maximum 100 characters</span>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea 
                id="description" 
                name="description" 
                rows="4"
            ><?php echo escape($description); ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="category">Category <span class="required">*</span></label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="Electronics" <?php echo $category === 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                    <option value="Furniture" <?php echo $category === 'Furniture' ? 'selected' : ''; ?>>Furniture</option>
                    <option value="Accessories" <?php echo $category === 'Accessories' ? 'selected' : ''; ?>>Accessories</option>
                    <option value="Clothing" <?php echo $category === 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
                    <option value="Books" <?php echo $category === 'Books' ? 'selected' : ''; ?>>Books</option>
                    <option value="Toys" <?php echo $category === 'Toys' ? 'selected' : ''; ?>>Toys</option>
                    <option value="Sports" <?php echo $category === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                    <option value="Other" <?php echo $category === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="price">Price ($) <span class="required">*</span></label>
                <input 
                    type="number" 
                    id="price" 
                    name="price" 
                    step="0.01" 
                    min="0"
                    value="<?php echo escape($price); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="quantity">Quantity <span class="required">*</span></label>
                <input 
                    type="number" 
                    id="quantity" 
                    name="quantity" 
                    min="0"
                    value="<?php echo escape($quantity); ?>"
                    required
                >
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"> Update Product</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

 

<?php include '../includes/footer.php'; ?>