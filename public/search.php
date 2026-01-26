<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

requireLogin();

$page_title = 'Search Products';

// Handle AJAX autocomplete request
if (isset($_GET['autocomplete'])) {
    $term = $_GET['term'] ?? '';
    
    if (strlen($term) >= 2) {
        $stmt = $pdo->prepare(
            "SELECT DISTINCT product_name 
             FROM products 
             WHERE product_name LIKE ? 
             LIMIT 10"
        );
        $stmt->execute(['%' . $term . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        header('Content-Type: application/json');
        echo json_encode($results);
    }
    exit;
}

// Handle search request
$search_results = [];
$searched = false;
$search_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $search_term = trim($_GET['keyword'] ?? '');
    $category_filter = $_GET['category'] ?? '';
    $min_price = $_GET['min_price'] ?? '';
    $max_price = $_GET['max_price'] ?? '';
    
    // Validate that at least one search criteria is provided
    if (empty($search_term) && empty($category_filter) && empty($min_price) && empty($max_price)) {
        $search_error = 'Please enter a search keyword or select filters';
    } else {
        $searched = true;
        
        // Build dynamic query
        $query = "SELECT * FROM products WHERE 1=1";
        $params = [];
        
        // Keyword search
        if (!empty($search_term)) {
            $query .= " AND (product_name LIKE ? OR description LIKE ?)";
            $params[] = '%' . $search_term . '%';
            $params[] = '%' . $search_term . '%';
        }
        
        // Category filter
        if (!empty($category_filter)) {
            $query .= " AND category = ?";
            $params[] = $category_filter;
        }
        
        // Price range filter
        if (is_numeric($min_price) && $min_price >= 0) {
            $query .= " AND price >= ?";
            $params[] = $min_price;
        }
        
        if (is_numeric($max_price) && $max_price >= 0) {
            $query .= " AND price <= ?";
            $params[] = $max_price;
        }
        
        $query .= " ORDER BY product_name ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $search_results = $stmt->fetchAll();
    }
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1> Search Products</h1>
    <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<div class="search-container">
    <form method="GET" action="" class="search-form" id="searchForm">
        <input type="hidden" name="search" value="1">
        
        <div class="search-header">
            <h2>Advanced Search</h2>
        </div>
        
        <div class="form-group">
            <label for="keyword">Search Keyword</label>
            <input 
                type="text" 
                id="keyword" 
                name="keyword" 
                placeholder="Search by product name or description..."
                value="<?php echo isset($search_term) ? escape($search_term) : ''; ?>"
                autocomplete="off"
            >
            <div id="autocomplete-results" class="autocomplete-results"></div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="">All Categories</option>
                    <option value="Electronics" <?php echo (isset($category_filter) && $category_filter === 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
                    <option value="Furniture" <?php echo (isset($category_filter) && $category_filter === 'Furniture') ? 'selected' : ''; ?>>Furniture</option>
                    <option value="Accessories" <?php echo (isset($category_filter) && $category_filter === 'Accessories') ? 'selected' : ''; ?>>Accessories</option>
                    <option value="Clothing" <?php echo (isset($category_filter) && $category_filter === 'Clothing') ? 'selected' : ''; ?>>Clothing</option>
                    <option value="Books" <?php echo (isset($category_filter) && $category_filter === 'Books') ? 'selected' : ''; ?>>Books</option>
                    <option value="Toys" <?php echo (isset($category_filter) && $category_filter === 'Toys') ? 'selected' : ''; ?>>Toys</option>
                    <option value="Sports" <?php echo (isset($category_filter) && $category_filter === 'Sports') ? 'selected' : ''; ?>>Sports</option>
                    <option value="Other" <?php echo (isset($category_filter) && $category_filter === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="min_price">Min Price (Rs.)</label>
                <input 
                    type="number" 
                    id="min_price" 
                    name="min_price" 
                    step="0.01" 
                    min="0"
                    placeholder="0.00"
                    value="<?php echo isset($min_price) ? escape($min_price) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="max_price">Max Price (Rs.)</label>
                <input 
                    type="number" 
                    id="max_price" 
                    name="max_price" 
                    step="0.01" 
                    min="0"
                    placeholder="999.99"
                    value="<?php echo isset($max_price) ? escape($max_price) : ''; ?>"
                >
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Search</button>
            <button type="button" class="btn btn-secondary" onclick="resetSearch()">Clear</button>
        </div>
    </form>
</div>

<?php if (!empty($search_error)): ?>
    <div class="alert alert-error">
        <?php echo escape($search_error); ?>
    </div>
<?php endif; ?>

<?php if ($searched): ?>
    <div class="search-results">
        <h2>Search Results (<?php echo count($search_results); ?> found)</h2>
        
        <?php if (empty($search_results)): ?>
            <div class="empty-state">
                <p>No products found matching your search criteria.</p>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $product): ?>
                        <tr>
                            <td><?php echo escape($product['product_id']); ?></td>
                            <td><strong><?php echo escape($product['product_name']); ?></strong></td>
                            <td><?php echo escape(substr($product['description'], 0, 50)) . (strlen($product['description']) > 50 ? '...' : ''); ?></td>
                            <td><span class="badge badge-category"><?php echo escape($product['category']); ?></span></td>
                            <td><strong><?php echo formatPrice($product['price']); ?></strong></td>
                            <td><?php echo escape($product['quantity']); ?></td>
                            <td class="actions">
                                <a href="edit.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-edit">✏️ Edit</a>
                                <a href="delete.php?id=<?php echo $product['product_id']; ?>" 
                                   class="btn btn-sm btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this product?');">
                                    🗑️ Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
// Ajax Autocomplete
let autocompleteTimer;
const keywordInput = document.getElementById('keyword');
const autocompleteResults = document.getElementById('autocomplete-results');

keywordInput.addEventListener('input', function() {
    clearTimeout(autocompleteTimer);
    const term = this.value;
    
    if (term.length < 2) {
        autocompleteResults.innerHTML = '';
        autocompleteResults.style.display = 'none';
        return;
    }
    
    autocompleteTimer = setTimeout(() => {
        fetch(`search.php?autocomplete=1&term=${encodeURIComponent(term)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    autocompleteResults.innerHTML = data.map(item => 
                        `<div class="autocomplete-item" onclick="selectAutocomplete('${item}')">${item}</div>`
                    ).join('');
                    autocompleteResults.style.display = 'block';
                } else {
                    autocompleteResults.innerHTML = '';
                    autocompleteResults.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Autocomplete error:', error);
            });
    }, 300);
});

function selectAutocomplete(value) {
    keywordInput.value = value;
    autocompleteResults.innerHTML = '';
    autocompleteResults.style.display = 'none';
}

// Close autocomplete when clicking outside
document.addEventListener('click', function(e) {
    if (e.target !== keywordInput) {
        autocompleteResults.style.display = 'none';
    }
});

function resetSearch() {
    window.location.href = 'search.php';
}
</script>

<?php include '../includes/footer.php'; ?>