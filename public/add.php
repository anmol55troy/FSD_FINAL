<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

requireLogin();

$page_title = 'Add New Product';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
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
        
        if (empty($description)) {
            $errors[] = 'Description is required';
        } elseif (strlen($description) > 500) {
            $errors[] = 'Description must be less than 500 characters';
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
        
        // If no errors, insert into database
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO products (product_name, description, category, price, quantity) 
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([$product_name, $description, $category, $price, $quantity]);
                
                setFlashMessage('success', 'Product added successfully!');
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>➕ Add New Product</h1>
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
    <form method="POST" action="" id="addProductForm" class="product-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="form-group">
            <label for="product_name">Product Name <span class="required">*</span></label>
            <input 
                type="text" 
                id="product_name" 
                name="product_name" 
                value="<?php echo isset($product_name) ? escape($product_name) : ''; ?>"
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
            ><?php echo isset($description) ? escape($description) : ''; ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="category">Category <span class="required">*</span></label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="Electronics" <?php echo (isset($category) && $category === 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
                    <option value="Furniture" <?php echo (isset($category) && $category === 'Furniture') ? 'selected' : ''; ?>>Furniture</option>
                    <option value="Accessories" <?php echo (isset($category) && $category === 'Accessories') ? 'selected' : ''; ?>>Accessories</option>
                    <option value="Clothing" <?php echo (isset($category) && $category === 'Clothing') ? 'selected' : ''; ?>>Clothing</option>
                    <option value="Books" <?php echo (isset($category) && $category === 'Books') ? 'selected' : ''; ?>>Books</option>
                    <option value="Toys" <?php echo (isset($category) && $category === 'Toys') ? 'selected' : ''; ?>>Toys</option>
                    <option value="Sports" <?php echo (isset($category) && $category === 'Sports') ? 'selected' : ''; ?>>Sports</option>
                    <option value="Other" <?php echo (isset($category) && $category === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="price">Price(Rs) <span class="required">*</span></label>
                <input 
                    type="number" 
                    id="price" 
                    name="price" 
                    step="0.01" 
                    min="0"
                    value="<?php echo isset($price) ? escape($price) : ''; ?>"
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
                    value="<?php echo isset($quantity) ? escape($quantity) : ''; ?>"
                    required
                >
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"> Add Product</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
// Client-side validation
document.getElementById('addProductForm').addEventListener('submit', function(e) {
    const price = parseFloat(document.getElementById('price').value);
    const quantity = parseInt(document.getElementById('quantity').value);
    
    if (price < 0) {
        e.preventDefault();
        alert('Price must be a positive number');
        return false;
    }
    
    if (quantity < 0) {
        e.preventDefault();
        alert('Quantity must be a positive number');
        return false;
    }
});
</script>

<?php include '../includes/footer.php'; ?>