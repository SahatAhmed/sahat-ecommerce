<?php
require_once __DIR__ . '/../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name  = trim($_POST['product_name']);
    $desc  = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $original_price = (float)$_POST['original_price'];
    $cat   = (int)$_POST['category_id'];
    $stock = (int)$_POST['stock_quantity'];
    $rating = isset($_POST['rating']) ? (float)$_POST['rating'] : 0;
    $on_sale = isset($_POST['on_sale']) ? 1 : 0;
    
    // Handle Image Upload
    $imgName = $_FILES['product_image']['name'];
    $imgTmp  = $_FILES['product_image']['tmp_name'];
    $uploadPath = '../uploads/' . time() . '_' . basename($imgName);
    
    if (move_uploaded_file($imgTmp, $uploadPath)) {
        $image = $uploadPath;
        $ins = $mysqli->prepare("INSERT INTO products (product_name, description, price, original_price, image_url, category_id, stock_quantity, rating, on_sale)
                                 VALUES (?,?,?,?,?,?,?,?,?)");
        $ins->bind_param('ssddsiidi', $name, $desc, $price, $original_price, $image, $cat, $stock, $rating, $on_sale);
        if ($ins->execute()) {
            $_SESSION['flash_message'] = "Product added successfully!";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Failed to add product.";
            $_SESSION['flash_type'] = "error";
        }
    } else {
        $_SESSION['flash_message'] = "Failed to upload image.";
        $_SESSION['flash_type'] = "error";
    }
    header('Location: products.php'); exit;
}

// Fetch data
$products   = $mysqli->query("SELECT p.*, c.category_name FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.category_id
                 ORDER BY p.product_id DESC")->fetch_all(MYSQLI_ASSOC);
$categories = $mysqli->query("SELECT * FROM categories ORDER BY category_name ASC")
                     ->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management | Admin Panel</title>
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
            max-width: 1400px;
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

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Form Card */
        .form-card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            overflow: hidden;
            height: fit-content;
            position: sticky;
            top: 100px;
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
        .form-group {
            margin-bottom: 1.5rem;
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
            min-height: 100px;
            resize: vertical;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload input[type="file"] {
            position: absolute;
            left: -9999px;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border: 2px dashed var(--border);
            border-radius: 0.5rem;
            background: var(--light);
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            justify-content: center;
        }

        .file-upload-label:hover {
            border-color: var(--primary);
            background: #f0f4ff;
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
            width: 100%;
            justify-content: center;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
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
            transition: color 0.2s ease;
        }

        .rating-star.active {
            color: #f59e0b;
        }

        /* Products Table */
        .products-card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .table-container {
            overflow-x: auto;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th {
            background: var(--light);
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: var(--secondary);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border);
        }

        .products-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.875rem;
        }

        .products-table tbody tr {
            transition: all 0.3s ease;
        }

        .products-table tbody tr:hover {
            background: #f8fafc;
        }

        .products-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Product Image */
        .product-image {
            width: 50px;
            height: 50px;
            border-radius: 0.5rem;
            object-fit: cover;
            border: 1px solid var(--border);
        }

        /* Status Badges */
        .stock-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .stock-high { background: #d1fae5; color: #065f46; }
        .stock-medium { background: #fef3c7; color: #92400e; }
        .stock-low { background: #fee2e2; color: #991b1b; }
        .stock-out { background: #f3f4f6; color: #6b7280; }

        .sale-badge {
            background: var(--danger);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.625rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 0.5rem;
        }

        /* Price Styling */
        .current-price {
            font-weight: 700;
            color: var(--dark);
        }

        .original-price {
            text-decoration: line-through;
            color: var(--secondary);
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        .discount-badge {
            background: var(--danger);
            color: white;
            padding: 0.125rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.625rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        /* Rating Display */
        .rating-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            padding: 0.5rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-edit {
            background: #dbeafe;
            color: var(--primary);
        }

        .btn-edit:hover {
            background: var(--primary);
            color: white;
        }

        .btn-delete {
            background: #fee2e2;
            color: var(--danger);
        }

        .btn-delete:hover {
            background: var(--danger);
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
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

            .content-grid {
                grid-template-columns: 1fr;
            }

            .form-card {
                position: static;
            }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <h1 class="page-title">Product Management</h1>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="flash-message flash-<?= $_SESSION['flash_type'] ?>">
            <i class="fas fa-<?= $_SESSION['flash_type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= $_SESSION['flash_message'] ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="content-grid">
        <!-- Add Product Form -->
        <div class="form-card">
            <div class="card-header">
                <h3><i class="fas fa-plus-circle"></i> Add New Product</h3>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="add_product" value="1">
                    
                    <div class="form-group">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="product_name" class="form-control" required placeholder="Enter product name">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" placeholder="Enter product description"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Original Price (₹)</label>
                        <input type="number" name="original_price" step="0.01" class="form-control" required placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Selling Price (₹)</label>
                        <input type="number" name="price" step="0.01" class="form-control" required placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rating (0-5)</label>
                        <input type="number" name="rating" step="0.1" min="0" max="5" class="form-control" value="0" placeholder="0.0">
                        <div class="rating-stars" id="ratingStars">
                            <span class="rating-star" data-value="1">★</span>
                            <span class="rating-star" data-value="2">★</span>
                            <span class="rating-star" data-value="3">★</span>
                            <span class="rating-star" data-value="4">★</span>
                            <span class="rating-star" data-value="5">★</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Product Image</label>
                        <div class="file-upload">
                            <input type="file" name="product_image" id="product_image" accept="image/*" required>
                            <label for="product_image" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                Choose Image
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['category_id'] ?>"><?= esc($c['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control" value="0" min="0">
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="on_sale" value="1" class="checkbox-input">
                            <span class="checkbox-custom"></span>
                            This product is on sale
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Product
                    </button>
                </form>
            </div>
        </div>

        <!-- Products List -->
        <div class="products-card">
            <div class="card-header" style="background: white; border-bottom: 1px solid var(--border);">
                <h3 style="color: var(--dark);">
                    <i class="fas fa-boxes"></i>
                    Product Inventory (<?= count($products) ?>)
                </h3>
            </div>
            <div class="table-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Rating</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <h3>No Products Found</h3>
                                        <p>Add your first product to get started</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <?php if ($p['image_url']): ?>
                                                <img src="<?= $p['image_url'] ?>" alt="<?= esc($p['product_name']) ?>" class="product-image">
                                            <?php else: ?>
                                                <div class="product-image" style="background: var(--light); display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image" style="color: var(--secondary);"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div style="font-weight: 600; color: var(--dark); display: flex; align-items: center;">
                                                    <?= esc($p['product_name']) ?>
                                                    <?php if ($p['on_sale']): ?>
                                                        <span class="sale-badge">SALE</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="font-size: 0.75rem; color: var(--secondary);">ID: <?= $p['product_id'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="background: #f0f4ff; color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">
                                            <?= esc($p['category_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="current-price">
                                            ₹<?= number_format($p['price'], 2) ?>
                                        </div>
                                        <?php if ($p['original_price'] > $p['price']): ?>
                                            <div style="display: flex; align-items: center; margin-top: 0.25rem;">
                                                <span class="original-price">
                                                    ₹<?= number_format($p['original_price'], 2) ?>
                                                </span>
                                                <?php
                                                $discount_percent = round((($p['original_price'] - $p['price']) / $p['original_price']) * 100);
                                                ?>
                                                <span class="discount-badge">
                                                    <?= $discount_percent ?>% OFF
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="rating-display">
                                            <span class="rating-value"><?= number_format($p['rating'], 1) ?></span>
                                            <div class="rating-stars-display">
                                                <?php
                                                $rating = $p['rating'];
                                                for ($i = 1; $i <= 5; $i++):
                                                    $starClass = $i <= $rating ? 'active' : '';
                                                    if ($i > floor($rating) && $i <= ceil($rating) && fmod($rating, 1) != 0) {
                                                        $starClass = 'active';
                                                    }
                                                ?>
                                                    <span class="rating-star-display <?= $starClass ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $stock_class = 'stock-high';
                                        if ($p['stock_quantity'] == 0) {
                                            $stock_class = 'stock-out';
                                        } elseif ($p['stock_quantity'] <= 10) {
                                            $stock_class = 'stock-low';
                                        } elseif ($p['stock_quantity'] <= 25) {
                                            $stock_class = 'stock-medium';
                                        }
                                        ?>
                                        <span class="stock-badge <?= $stock_class ?>">
                                            <?= $p['stock_quantity'] ?> in stock
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="product_edit.php?id=<?= $p['product_id'] ?>" class="btn-icon btn-edit" title="Edit Product">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="product_edit.php?delete=<?= $p['product_id'] ?>" class="btn-icon btn-delete" title="Delete Product" onclick="return confirm('Are you sure you want to delete this product?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
    // File upload preview
    document.getElementById('product_image').addEventListener('change', function(e) {
        const label = this.nextElementSibling;
        if (this.files.length > 0) {
            label.innerHTML = `<i class="fas fa-check"></i> ${this.files[0].name}`;
            label.style.borderColor = '#10b981';
            label.style.background = '#ecfdf5';
        } else {
            label.innerHTML = `<i class="fas fa-cloud-upload-alt"></i> Choose Image`;
            label.style.borderColor = '';
            label.style.background = '';
        }
    });

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