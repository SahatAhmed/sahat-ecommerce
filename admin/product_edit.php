<?php
require_once __DIR__ . '/../includes/db.php';
if(!isset($_SESSION['admin_id'])){ header('Location: index.php'); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle delete product
if(isset($_GET['delete'])){
    $did = (int)$_GET['delete'];
    $d = $mysqli->prepare("DELETE FROM products WHERE product_id = ?");
    $d->bind_param('i', $did); 
    if($d->execute()) {
        $_SESSION['flash_message'] = "Product deleted successfully!";
        $_SESSION['flash_type'] = "success";
    } else {
        $_SESSION['flash_message'] = "Failed to delete product.";
        $_SESSION['flash_type'] = "error";
    }
    header('Location: products.php'); 
    exit;
}

// Fetch product data
$product = null;
if($id){
    $s = $mysqli->prepare("SELECT * FROM products WHERE product_id = ?");
    $s->bind_param('i', $id); 
    $s->execute();
    $product = $s->get_result()->fetch_assoc();
}

// Fetch categories
$categories = $mysqli->query("SELECT * FROM categories ORDER BY category_name ASC")->fetch_all(MYSQLI_ASSOC);

// Handle product update
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])){
    $pid = (int)$_POST['product_id'];
    $name = trim($_POST['product_name']);
    $desc = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $original_price = (float)$_POST['original_price'];
    $cat = (int)$_POST['category_id'];
    $stock = (int)$_POST['stock_quantity'];
    $rating = isset($_POST['rating']) ? (float)$_POST['rating'] : 0;
    $on_sale = isset($_POST['on_sale']) ? 1 : 0;

    // Update product in database (image remains unchanged)
    $u = $mysqli->prepare("
        UPDATE products 
        SET product_name=?, description=?, price=?, original_price=?, category_id=?, stock_quantity=?, rating=?, on_sale=?
        WHERE product_id=?
    ");
    $u->bind_param('ssddiiidi', $name, $desc, $price, $original_price, $cat, $stock, $rating, $on_sale, $pid);
    
    if($u->execute()) {
        $_SESSION['flash_message'] = "Product updated successfully!";
        $_SESSION['flash_type'] = "success";
    } else {
        $_SESSION['flash_message'] = "Failed to update product.";
        $_SESSION['flash_type'] = "error";
    }

    header('Location: products.php'); 
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product ? 'Edit Product' : 'Product Not Found' ?> | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Main Container */
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark);
            background: linear-gradient(135deg, var(--primary) 0%, #6366f1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Form Card */
        .form-card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .card-header h3 {
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 2rem;
        }

        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        /* Checkbox Styles */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-weight: 500;
            color: var(--dark);
        }

        .checkbox-custom {
            width: 18px;
            height: 18px;
            border: 2px solid var(--border);
            border-radius: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .checkbox-input:checked + .checkbox-custom {
            background: var(--primary);
            border-color: var(--primary);
        }

        .checkbox-input:checked + .checkbox-custom::after {
            content: '✓';
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .checkbox-input {
            display: none;
        }

        /* Rating Styles */
        .rating-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            background: white;
        }

        .rating-stars {
            display: flex;
            gap: 0.25rem;
            margin-top: 0.5rem;
        }

        .rating-star {
            color: #e2e8f0;
            font-size: 1.25rem;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .rating-star.active {
            color: #f59e0b;
        }

        .rating-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .rating-value {
            font-weight: 600;
            color: var(--dark);
        }

        .rating-stars-display {
            display: flex;
            gap: 0.125rem;
        }

        .rating-star-display {
            color: #e2e8f0;
            font-size: 0.875rem;
        }

        .rating-star-display.active {
            color: #f59e0b;
        }

        /* Price Display */
        .price-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }

        .current-price {
            font-weight: 700;
            color: var(--dark);
        }

        .original-price {
            text-decoration: line-through;
            color: var(--secondary);
        }

        .discount-badge {
            background: var(--danger);
            color: white;
            padding: 0.125rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.625rem;
            font-weight: 600;
        }

        .sale-badge {
            background: var(--danger);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.625rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Current Image Display */
        .current-image-container {
            margin-top: 1rem;
            text-align: center;
        }

        .current-image {
            width: 200px;
            height: 200px;
            border-radius: 0.75rem;
            object-fit: cover;
            border: 2px solid var(--border);
            box-shadow: var(--shadow);
        }

        .image-placeholder {
            width: 200px;
            height: 200px;
            border-radius: 0.75rem;
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed var(--border);
            color: var(--secondary);
        }

        .image-info {
            font-size: 0.875rem;
            color: var(--secondary);
            margin-top: 0.5rem;
        }

        /* Buttons */
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: var(--secondary);
            border: 1px solid var(--border);
        }

        .btn-outline:hover {
            background: var(--light);
            color: var(--dark);
        }

        /* Stock Status */
        .stock-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .stock-high { background: #d1fae5; color: #065f46; }
        .stock-medium { background: #fef3c7; color: #92400e; }
        .stock-low { background: #fee2e2; color: #991b1b; }
        .stock-out { background: #f3f4f6; color: #6b7280; }

        /* Not Found State */
        .not-found {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--secondary);
        }

        .not-found i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }

        .not-found h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        /* Flash Messages */
        .flash-message {
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            border-left: 4px solid;
        }

        .flash-success {
            background: #ecfdf5;
            color: #065f46;
            border-left-color: var(--success);
        }

        .flash-error {
            background: #fef2f2;
            color: #991b1b;
            border-left-color: var(--danger);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="flash-message flash-<?= $_SESSION['flash_type'] ?>">
            <i class="fas fa-<?= $_SESSION['flash_type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= $_SESSION['flash_message'] ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-edit"></i>
            <?= $product ? 'Edit Product' : 'Product Not Found' ?>
        </h1>
        <?php if($product): ?>
            <a href="products.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Back to Products
            </a>
        <?php endif; ?>
    </div>

    <?php if(!$product): ?>
        <div class="not-found">
            <i class="fas fa-exclamation-triangle"></i>
            <h2>Product Not Found</h2>
            <p>The product you're looking for doesn't exist or has been removed.</p>
            <a href="products.php" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-arrow-left"></i>
                Return to Products
            </a>
        </div>
    <?php else: ?>
        <div class="form-card">
            <div class="card-header">
                <h3><i class="fas fa-pencil-alt"></i> Product Information</h3>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="update_product" value="1">
                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="product_name" class="form-control" 
                                   value="<?= esc($product['product_name']) ?>" required 
                                   placeholder="Enter product name">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $c): ?>
                                    <option value="<?= $c['category_id'] ?>" 
                                        <?= ($c['category_id']==$product['category_id'])?'selected':'' ?>>
                                        <?= esc($c['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Original Price (₹)</label>
                            <input type="number" name="original_price" step="0.01" class="form-control" 
                                   value="<?= isset($product['original_price']) ? $product['original_price'] : $product['price'] ?>" required 
                                   placeholder="0.00" min="0">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Selling Price (₹)</label>
                            <input type="number" name="price" step="0.01" class="form-control" 
                                   value="<?= $product['price'] ?>" required 
                                   placeholder="0.00" min="0">
                            <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                <div class="price-display">
                                    <span class="current-price">₹<?= number_format($product['price'], 2) ?></span>
                                    <span class="original-price">₹<?= number_format($product['original_price'], 2) ?></span>
                                    <?php
                                    $discount_percent = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                                    ?>
                                    <span class="discount-badge"><?= $discount_percent ?>% OFF</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Rating (0-5)</label>
                            <input type="number" name="rating" step="0.1" min="0" max="5" class="form-control rating-input" 
                                   value="<?= isset($product['rating']) ? $product['rating'] : 0 ?>" 
                                   placeholder="0.0">
                            <div class="rating-stars" id="ratingStars">
                                <span class="rating-star" data-value="1">★</span>
                                <span class="rating-star" data-value="2">★</span>
                                <span class="rating-star" data-value="3">★</span>
                                <span class="rating-star" data-value="4">★</span>
                                <span class="rating-star" data-value="5">★</span>
                            </div>
                            <div class="rating-display">
                                <span class="rating-value">Current: <?= number_format(isset($product['rating']) ? $product['rating'] : 0, 1) ?></span>
                                <div class="rating-stars-display">
                                    <?php
                                    $current_rating = isset($product['rating']) ? $product['rating'] : 0;
                                    for ($i = 1; $i <= 5; $i++):
                                        $starClass = $i <= $current_rating ? 'active' : '';
                                        if ($i > floor($current_rating) && $i <= ceil($current_rating) && fmod($current_rating, 1) != 0) {
                                            $starClass = 'active';
                                        }
                                    ?>
                                        <span class="rating-star-display <?= $starClass ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="stock_quantity" class="form-control" 
                                   value="<?= $product['stock_quantity'] ?>" min="0">
                            <?php
                            $stock_class = 'stock-high';
                            if ($product['stock_quantity'] == 0) {
                                $stock_class = 'stock-out';
                            } elseif ($product['stock_quantity'] <= 10) {
                                $stock_class = 'stock-low';
                            } elseif ($product['stock_quantity'] <= 25) {
                                $stock_class = 'stock-medium';
                            }
                            ?>
                            <div class="stock-status <?= $stock_class ?>">
                                <i class="fas fa-<?= $stock_class === 'stock-out' ? 'times' : 'check' ?>-circle"></i>
                                <?= $product['stock_quantity'] ?> units available
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="on_sale" value="1" class="checkbox-input" 
                                       <?= (isset($product['on_sale']) && $product['on_sale']) ? 'checked' : '' ?>>
                                <span class="checkbox-custom"></span>
                                This product is on sale
                            </label>
                            <?php if (isset($product['on_sale']) && $product['on_sale']): ?>
                                <span class="sale-badge" style="margin-left: 0.5rem;">ON SALE</span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" 
                                      placeholder="Enter product description"><?= esc($product['description']) ?></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Product Image</label>
                            <div class="current-image-container">
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="<?= $product['image_url'] ?>" 
                                         alt="<?= esc($product['product_name']) ?>" 
                                         class="current-image">
                                    <p class="image-info">Current product image</p>
                                <?php else: ?>
                                    <div class="image-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <p class="image-info">No image available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="products.php" class="btn btn-outline">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                        <a href="product_edit.php?delete=<?= $product['product_id'] ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                            <i class="fas fa-trash"></i>
                            Delete Product
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
    // Rating stars interaction
    const ratingStars = document.querySelectorAll('#ratingStars .rating-star');
    const ratingInput = document.querySelector('input[name="rating"]');
    
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            ratingInput.value = value;
            updateStars(value);
        });
        
        star.addEventListener('mouseover', function() {
            const value = this.getAttribute('data-value');
            updateStars(value);
        });
    });
    
    document.getElementById('ratingStars').addEventListener('mouseleave', function() {
        const currentValue = ratingInput.value;
        updateStars(currentValue);
    });
    
    function updateStars(value) {
        ratingStars.forEach(star => {
            const starValue = star.getAttribute('data-value');
            if (starValue <= value) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }
    
    // Initialize stars on page load
    updateStars(ratingInput.value);
</script>

</body>
</html>