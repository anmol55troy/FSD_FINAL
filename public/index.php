<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

requireLogin();

$page_title = 'Dashboard - All Products';

// Fetch all products with sorting
$sort_by = $_GET['sort'] ?? 'product_name';
$order = $_GET['order'] ?? 'ASC';

// Whitelist allowed sort columns
$allowed_sorts = ['product_name', 'category', 'price', 'quantity', 'created_at'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'product_name';
}

// Whitelist order direction
$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

$stmt = $pdo->prepare("SELECT * FROM products ORDER BY $sort_by $order");
$stmt->execute();
$products = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h1>Product Inventory Dashboard</h1>
    <a href="add.php" class="btn btn-primary"> Add New Product</a>
</div>

<div class="stats-container">
    <div class="stat-card">
        <h3>Total Products</h3>
        <p class="stat-number"><?php echo count($products); ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Value</h3>
        <p class="stat-number">
            <?php 
            $total_value = array_sum(array_map(function($p) {
                return $p['price'] * $p['quantity'];
            }, $products));
            echo formatPrice($total_value);
            ?>
        </p>
    </div>
    <div class="stat-card">
        <h3>Categories</h3>
        <p class="stat-number">
            <?php 
            $categories = array_unique(array_column($products, 'category'));
            echo count($categories);
            ?>
        </p>
    </div>
    <div class="stat-card">
        <h3>Low Stock</h3>
        <p class="stat-number">
            <?php 
            $low_stock = array_filter($products, function($p) {
                return $p['quantity'] < 10;
            });
            echo count($low_stock);
            ?>
        </p>
    </div>
</div>

<div class="table-controls">
    <div class="sort-controls">
        <label>Sort by:</label>
        <select id="sortSelect" onchange="updateSort()">
            <option value="product_name" <?php echo $sort_by === 'product_name' ? 'selected' : ''; ?>>Name</option>
            <option value="category" <?php echo $sort_by === 'category' ? 'selected' : ''; ?>>Category</option>
            <option value="price" <?php echo $sort_by === 'price' ? 'selected' : ''; ?>>Price</option>
            <option value="quantity" <?php echo $sort_by === 'quantity' ? 'selected' : ''; ?>>Quantity</option>
            <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>Date Added</option>
        </select>
        
        <select id="orderSelect" onchange="updateSort()">
            <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
            <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
        </select>
    </div>
</div>

<?php if (empty($products)): ?>
    <div class="empty-state">
        <p>No products found. <a href="add.php">Add your first product</a></p>
    </div>
<?php else: ?>
<div class="table-responsive">
    <table class="product-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Description</th>
                <th>Category</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Stock Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?php echo escape($product['product_id']); ?></td>
                <td><strong><?php echo escape($product['product_name']); ?></strong></td>
                <td><?php echo escape(substr($product['description'], 0, 50)) . (strlen($product['description']) > 50 ? '...' : ''); ?></td>
                <td><span class="badge badge-category"><?php echo escape($product['category']); ?></span></td>
                <td><strong><?php echo formatPrice($product['price']); ?></strong></td>
                <td><?php echo escape($product['quantity']); ?></td>
                <td>
                    <?php if ($product['quantity'] == 0): ?>
                        <span class="badge badge-danger">Out of Stock</span>
                    <?php elseif ($product['quantity'] < 10): ?>
                        <span class="badge badge-warning">Low Stock</span>
                    <?php else: ?>
                        <span class="badge badge-success">In Stock</span>
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <a href="edit.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-edit">✏️ Edit</a>
                    <form method="POST" action="delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                        <input type="hidden" name="id" value="<?php echo $product['product_id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <button type="submit" class="btn btn-sm btn-delete">🗑️ Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
function updateSort() {
    const sort = document.getElementById('sortSelect').value;
    const order = document.getElementById('orderSelect').value;
    window.location.href = `index.php?sort=${sort}&order=${order}`;
}
</script>

<?php include '../includes/footer.php'; ?>