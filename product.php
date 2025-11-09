<?php
require_once 'includes/db.php';

// Image path helper function
function getProductImage($imageUrl) {
    if (empty($imageUrl)) {
        return 'assets/images/placeholder.png';
    }
    
    // If image_url is already a full path, use it
    if (strpos($imageUrl, 'http') === 0) {
        return $imageUrl;
    }
    
    // If image_url starts with uploads/, use it directly
    if (strpos($imageUrl, 'uploads/') === 0) {
        return $imageUrl;
    }
    
    // If image_url contains ../uploads/, extract filename
    if (strpos($imageUrl, '../uploads/') !== false) {
        $filename = basename($imageUrl);
        return 'uploads/' . $filename;
    }
    
    // Default: assume it's in uploads directory
    return 'uploads/' . basename($imageUrl);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $mysqli->prepare("SELECT p.*, c.category_name 
                          FROM products p 
                          LEFT JOIN categories c 
                          ON p.category_id = c.category_id 
                          WHERE p.product_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();

if (!$p) { 
    header('Location: index.php'); 
    exit; 
}

// Calculate discount if original_price exists
$discount = 0;
$hasDiscount = false;
if (isset($p['original_price']) && $p['original_price'] > $p['price'] && $p['original_price'] > 0) {
    $discount = round((($p['original_price'] - $p['price']) / $p['original_price']) * 100);
    $hasDiscount = true;
}

// Get actual rating and on_sale status from database
$rating = isset($p['rating']) ? (float)$p['rating'] : 0;
$on_sale = isset($p['on_sale']) ? (bool)$p['on_sale'] : false;

// Get related products
$related_stmt = $mysqli->prepare("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE p.category_id = ? AND p.product_id != ? 
    ORDER BY RAND() 
    LIMIT 4
");
$related_stmt->bind_param('ii', $p['category_id'], $id);
$related_stmt->execute();
$related_products = $related_stmt->get_result();

require 'includes/header.php';
?>

<!-- Floating Background Elements -->
<div class="floating-elements">
    <div class="floating-circle circle-1"></div>
    <div class="floating-circle circle-2"></div>
    <div class="floating-circle circle-3"></div>
    <div class="floating-circle circle-4"></div>
</div>

<div class="product-hero">
    <div class="container">
        <!-- Premium Breadcrumb -->
        <nav class="minimal-breadcrumb" aria-label="Breadcrumb">
                <a href="index.php" class="breadcrumb-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Home
                </a>
                <span class="breadcrumb-divider">/</span>
                <a href="categories.php" class="breadcrumb-item">Products</a>
                <span class="breadcrumb-divider">/</span>
                <a href="category.php?id=<?= $p['category_id'] ?>" class="breadcrumb-item"><?= esc($p['category_name']) ?></a>
                <span class="breadcrumb-divider">›</span>
                <span class="breadcrumb-current"><?= esc($p['product_name']) ?></span>
            </nav>

        <div class="product-layout">
            <!-- Product Image Section -->
            <div class="product-image-section">
                <div class="main-product-image">
                    <img src="<?= esc(getProductImage($p['image_url'])) ?>" 
                         alt="<?= esc($p['product_name']) ?>" 
                         class="product-image"
                         id="productImage">
                    <div class="image-overlay"></div>
                    <div class="image-badges">
                        <?php if($on_sale): ?>
                            <span class="badge sale">On Sale</span>
                        <?php endif; ?>
                        <?php if($hasDiscount): ?>
                            <span class="badge discount">-<?= $discount ?>%</span>
                        <?php endif; ?>
                        <?php if($rating >= 4.5): ?>
                            <span class="badge premium">Top Rated</span>
                        <?php endif; ?>
                    </div>
                    <button class="zoom-btn" onclick="openLightbox()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 15L21 21M10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10C17 13.866 13.866 17 10 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Premium Product Info -->
            <div class="premium-product-info">
                <div class="product-header">
                    <div class="product-meta-tags">
                        <span class="meta-tag category"><?= esc($p['category_name']) ?></span>
                        <span class="meta-tag sku">SKU: PRD-<?= str_pad($p['product_id'], 6, '0', STR_PAD_LEFT) ?></span>
                        <?php if($on_sale): ?>
                            <span class="meta-tag sale-tag">Sale</span>
                        <?php endif; ?>
                    </div>
                    <h1 class="product-title"><?= esc($p['product_name']) ?></h1>
                    
                    <div class="rating-section">
                        <div class="stars">
                            <?php 
                            for($i = 1; $i <= 5; $i++): 
                                $fill = '#E5E7EB';
                                if($i <= floor($rating)) {
                                    $fill = '#FFD700';
                                } elseif($i - 0.5 <= $rating) {
                                    $fill = 'url(#half-star)';
                                }
                            ?>
                                <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <defs>
                                        <linearGradient id="half-star" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="50%" stop-color="#FFD700"/>
                                            <stop offset="50%" stop-color="#E5E7EB"/>
                                        </linearGradient>
                                    </defs>
                                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="<?= $fill ?>"/>
                                </svg>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-value"><?= number_format($rating, 1) ?></span>
                        <span class="rating-count">(<?= rand(10, 500) ?> reviews)</span>
                        <span class="verified-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 12L11 14L15 10M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Verified Purchase
                        </span>
                    </div>
                </div>

                <!-- Price Section -->
                <div class="premium-price-section">
                    <div class="price-main">
                        <span class="current-price">₹<?= number_format($p['price'], 2) ?></span>
                        <?php if($hasDiscount): ?>
                            <span class="original-price">₹<?= number_format($p['original_price'], 2) ?></span>
                            <span class="discount-badge">-<?= $discount ?>%</span>
                        <?php endif; ?>
                    </div>
                    <?php if($hasDiscount): ?>
                        <div class="price-savings">
                            <span class="savings-text">You save ₹<?= number_format($p['original_price'] - $p['price'], 2) ?></span>
                            <span class="tax-text">+ Free Shipping & Tax Included</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Description -->
                <div class="product-description">
                    <p><?= esc($p['description']) ?></p>
                </div>

                <!-- Premium Features -->
                <div class="premium-features">
                    <div class="feature-grid">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 7H4C2.89543 7 2 7.89543 2 9V19C2 20.1046 2.89543 21 4 21H20C21.1046 21 22 20.1046 22 19V9C22 7.89543 21.1046 7 20 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M16 21V5C16 4.46957 15.7893 3.96086 15.4142 3.58579C15.0391 3.21071 14.5304 3 14 3H10C9.46957 3 8.96086 3.21071 8.58579 3.58579C8.21071 3.96086 8 4.46957 8 5V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="feature-content">
                                <h4>Free Shipping</h4>
                                <p>Delivery in 2-4 days</p>
                            </div>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="feature-content">
                                <h4>30-Day Returns</h4>
                                <p>No questions asked</p>
                            </div>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 12L11 14L15 10M12 3C13.2844 3 14.5438 3.3043 15.6669 3.87955C16.79 4.45479 17.7399 5.28317 18.4321 6.28797C19.1243 7.29278 19.5376 8.44029 19.6375 9.62927C19.7374 10.8183 19.5206 12.0128 19.0045 13.0982C18.4884 14.1836 17.6884 15.1241 16.6777 15.8268C15.667 16.5296 14.4786 16.9719 13.2307 17.1121C11.9828 17.2523 10.7149 17.0854 9.54827 16.6269C8.38164 16.1684 7.35404 15.4329 6.56234 14.4933C5.77064 13.5537 5.24135 12.4419 5.02252 11.2601C4.80369 10.0783 4.90217 8.86598 5.30863 7.73499" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="feature-content">
                                <h4>2-Year Warranty</h4>
                                <p>Full coverage</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Section -->
                <div class="premium-purchase-section">
                    <div class="quantity-section">
                        <label class="quantity-label">Quantity</label>
                        <div class="premium-quantity">
                            <button class="qty-btn minus" onclick="adjustQuantity(-1)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <input type="number" class="qty-input" value="1" min="1" max="10" readonly>
                            <button class="qty-btn plus" onclick="adjustQuantity(1)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <form method="post" action="add_to_cart.php" class="action-form">
                            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                            <input type="hidden" name="quantity" value="1" id="cartQuantity">
                            <button type="submit" class="btn btn-premium-cart">
                                <span class="btn-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 3H5L5.4 5M7 13H17L21 5H5.4M7 13L5.4 5M7 13L4.7 15.3C4.3 15.7 4.6 16.4 5.2 16.4H17M17 13V16.4M9 19C9 19.6 8.6 20 8 20C7.4 20 7 19.6 7 19C7 18.4 7.4 18 8 18C8.6 18 9 18.4 9 19ZM17 19C17 19.6 16.6 20 16 20C15.4 20 15 19.6 15 19C15 18.4 15.4 18 16 18C16.6 18 17 18.4 17 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="btn-text">Add to Cart</span>
                            </button>
                        </form>

                        <form method="post" action="checkout.php" class="action-form">
                            <input type="hidden" name="buy_product_id" value="<?= $p['product_id'] ?>">
                            <input type="hidden" name="buy_quantity" value="1" id="buyQuantity">
                            <button type="submit" class="btn btn-premium-buy">
                                <span class="btn-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 13L12 20L19 13M12 4V20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="btn-text">Buy Now</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Stock & Delivery -->
                <div class="stock-delivery-info">
                    <div class="stock-info">
                        <div class="stock-badge in-stock">
                            <div class="stock-indicator"></div>
                            In Stock - <?= $p['stock_quantity'] ?> units available
                        </div>
                    </div>
                    <div class="delivery-info">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8V12L15 15M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Delivery by <?= date('D, M j', strtotime('+3 days')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Products Section -->
<section class="premium-related">
    <div class="container">
        <div class="section-header-premium">
            <h2 class="section-title">You May Also Like</h2>
            <p class="section-subtitle">Discover similar premium products</p>
        </div>
        <div class="premium-products-grid">
            <?php if($related_products->num_rows > 0): ?>
                <?php while($related = $related_products->fetch_assoc()): 
                    $related_rating = isset($related['rating']) ? (float)$related['rating'] : 0;
                    $related_on_sale = isset($related['on_sale']) ? (bool)$related['on_sale'] : false;
                    $related_discount = 0;
                    $related_has_discount = false;
                    if (isset($related['original_price']) && $related['original_price'] > $related['price'] && $related['original_price'] > 0) {
                        $related_discount = round((($related['original_price'] - $related['price']) / $related['original_price']) * 100);
                        $related_has_discount = true;
                    }
                ?>
                    <div class="premium-product-card">
                        <div class="card-image-container">
                            <img src="<?= esc(getProductImage($related['image_url'])) ?>" 
                                 alt="<?= esc($related['product_name']) ?>"
                                 class="card-image">
                            <div class="card-overlay">
                                <div class="card-badges">
                                    <?php if($related_on_sale): ?>
                                        <span class="badge sale">Sale</span>
                                    <?php endif; ?>
                                    <?php if($related_has_discount): ?>
                                        <span class="badge discount">-<?= $related_discount ?>%</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-actions">
                                    <button class="action-btn wishlist-btn" title="Add to Wishlist">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M19 14C20 12 22 10.5 22 8.5C22 6.5 20.5 5 18.5 5C17.5 5 16.5 5.5 16 6C15.5 5.5 14.5 5 13.5 5C11.5 5 10 6.5 10 8.5C10 10.5 12 12 13 14L12 17L19 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <button class="action-btn quick-view-btn" title="Quick View">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-content">
                            <span class="card-category"><?= esc($related['category_name']) ?></span>
                            <h3 class="card-title"><?= esc($related['product_name']) ?></h3>
                            <div class="card-rating">
                                <div class="stars">
                                    <?php 
                                    for($i = 1; $i <= 5; $i++): 
                                        $fill = '#E5E7EB';
                                        if($i <= floor($related_rating)) {
                                            $fill = '#FFD700';
                                        } elseif($i - 0.5 <= $related_rating) {
                                            $fill = 'url(#half-star-related)';
                                        }
                                    ?>
                                        <svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <defs>
                                                <linearGradient id="half-star-related" x1="0%" y1="0%" x2="100%" y2="0%">
                                                    <stop offset="50%" stop-color="#FFD700"/>
                                                    <stop offset="50%" stop-color="#E5E7EB"/>
                                                </linearGradient>
                                            </defs>
                                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="<?= $fill ?>"/>
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-value"><?= number_format($related_rating, 1) ?></span>
                            </div>
                            <div class="card-price">
                                <span class="current-price">₹<?= number_format($related['price'], 2) ?></span>
                                <?php if($related_has_discount): ?>
                                    <span class="original-price">₹<?= number_format($related['original_price'], 2) ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="product.php?id=<?= $related['product_id'] ?>" class="card-add-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 3H5L5.4 5M7 13H17L21 5H5.4M7 13L5.4 5M7 13L4.7 15.3C4.3 15.7 4.6 16.4 5.2 16.4H17M17 13V16.4M9 19C9 19.6 8.6 20 8 20C7.4 20 7 19.6 7 19C7 18.4 7.4 18 8 18C8.6 18 9 18.4 9 19ZM17 19C17 19.6 16.6 20 16 20C15.4 20 15 19.6 15 19C15 18.4 15.4 18 16 18C16.6 18 17 18.4 17 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-products-message">
                    <p>No related products found at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="imageLightbox" class="lightbox">
    <div class="lightbox-content">
        <span class="close-lightbox" onclick="closeLightbox()">&times;</span>
        <img src="<?= esc(getProductImage($p['image_url'])) ?>" 
             alt="<?= esc($p['product_name']) ?>" 
             class="lightbox-image">
    </div>
</div>

<style>
:root {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --primary-light: #c7d2fe;
    --secondary: #8b5cf6;
    --accent: #06b6d4;
    --success: #10b981;
    --warning: #f59e0b;
    --error: #ef4444;
    --text-primary: #1e293b;
    --text-secondary: #475569;
    --text-light: #94a3b8;
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-tertiary: #f1f5f9;
    --border: #e2e8f0;
    --border-light: #f1f5f9;
    --glass: rgba(255, 255, 255, 0.25);
    --glass-border: rgba(255, 255, 255, 0.18);
    --shadow-soft: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    --shadow-medium: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
    --shadow-strong: 0 20px 50px 0 rgba(31, 38, 135, 0.25);
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --gradient-accent: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --radius: 16px;
    --radius-lg: 24px;
    --radius-xl: 32px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f1f5f9 100%);
    color: var(--text-primary);
    line-height: 1.7;
    overflow-x: hidden;
    position: relative;
}

/* Floating Background Elements */
.floating-elements {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: -1;
}

.floating-circle {
    position: absolute;
    border-radius: 50%;
    background: var(--gradient-primary);
    opacity: 0.1;
    animation: float 6s ease-in-out infinite;
}

.circle-1 {
    width: 200px;
    height: 200px;
    top: 10%;
    left: 5%;
    animation-delay: 0s;
}

.circle-2 {
    width: 150px;
    height: 150px;
    top: 60%;
    right: 10%;
    background: var(--gradient-secondary);
    animation-delay: 2s;
}

.circle-3 {
    width: 100px;
    height: 100px;
    bottom: 20%;
    left: 15%;
    background: var(--gradient-accent);
    animation-delay: 4s;
}

.circle-4 {
    width: 120px;
    height: 120px;
    top: 30%;
    right: 20%;
    animation-delay: 1s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Premium Breadcrumb */
 .breadcrumb-demo {
    margin: 40px 0;
    padding: 32px;
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid #e8ecef;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

/* Professional Minimalist Breadcrumb Styles */
.minimal-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    font-size: 14px;
    color: #64748b;
    font-weight: 400;
    line-height: 1.5;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
    color: #64748b;
    text-decoration: none;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 6px 0;
    position: relative;
}

.breadcrumb-item:hover {
    color: #334155;
    transform: translateY(-1px);
}

.breadcrumb-item:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
    border-radius: 4px;
}

.breadcrumb-item svg {
    margin-right: 8px;
    opacity: 0.8;
    transition: opacity 0.25s ease;
}

.breadcrumb-item:hover svg {
    opacity: 1;
}

.breadcrumb-divider {
    margin: 0 12px;
    color: #cbd5e1;
    font-weight: 300;
    font-size: 16px;
}

.breadcrumb-current {
    color: #1e293b;
    font-weight: 600;
    padding: 6px 0;
    position: relative;
}

.breadcrumb-current::before {
    content: '';
    position: absolute;
    bottom: 2px;
    left: 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    border-radius: 1px;
}

/* Alternative professional style */
.minimal-breadcrumb.alt {
    background: #f8fafc;
    padding: 16px 24px;
    border-radius: 8px;
    border: 1px solid #f1f5f9;
}

.minimal-breadcrumb.alt .breadcrumb-divider {
    margin: 0 10px;
    color: #94a3b8;
}

/* Compact professional style */
.minimal-breadcrumb.compact {
    font-size: 13px;
    font-weight: 500;
}

.minimal-breadcrumb.compact .breadcrumb-divider {
    margin: 0 8px;
    font-size: 14px;
}

.minimal-breadcrumb.compact .breadcrumb-item {
    padding: 4px 0;
}

.minimal-breadcrumb.compact .breadcrumb-current {
    padding: 4px 0;
}

.minimal-breadcrumb.compact .breadcrumb-current::before {
    bottom: 0;
    height: 1px;
}

/* Professional style variations */
.variations {
    display: flex;
    flex-direction: column;
    gap: 28px;
    margin-top: 48px;
}

.variation-title {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Enhanced divider animation */
.minimal-breadcrumb .breadcrumb-divider {
    transition: color 0.2s ease;
}

.minimal-breadcrumb:hover .breadcrumb-divider {
    color: #94a3b8;
}

/* Professional responsive adjustments */
@media (max-width: 768px) {
    .breadcrumb-demo {
        padding: 24px;
        margin: 32px 0;
    }
    
    .minimal-breadcrumb {
        font-size: 13px;
    }
    
    .breadcrumb-divider {
        margin: 0 10px;
    }
    
    .variations {
        gap: 24px;
        margin-top: 40px;
    }
}

@media (max-width: 480px) {
    .minimal-breadcrumb {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .breadcrumb-divider {
        display: none;
    }
    
    .breadcrumb-item, .breadcrumb-current {
        padding: 8px 0;
    }
    
    .breadcrumb-current::before {
        bottom: 6px;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .breadcrumb-item {
        color: #000000;
    }
    
    .breadcrumb-item:hover {
        color: #000000;
        text-decoration: underline;
    }
    
    .breadcrumb-current {
        color: #000000;
        font-weight: 700;
    }
    
    .breadcrumb-divider {
        color: #666666;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .breadcrumb-item {
        transition: none;
    }
    
    .breadcrumb-item:hover {
        transform: none;
    }
}
/* Product Layout */
.product-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    align-items: start;
}

/* Product Image Section */
.product-image-section {
    position: sticky;
    top: 40px;
}

.main-product-image {
    position: relative;
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-strong);
    background: white;
}

.product-image {
    width: 100%;
    height: 600px;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-image:hover {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
    pointer-events: none;
}

.image-badges {
    position: absolute;
    top: 20px;
    left: 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    backdrop-filter: blur(10px);
}

.badge.premium {
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: #7c2d12;
}

.badge.sale {
    background: linear-gradient(135deg, #ef4444, #f97316);
    color: white;
}

.badge.discount {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.zoom-btn {
    position: absolute;
    bottom: 20px;
    right: 20px;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: none;
    background: var(--glass);
    backdrop-filter: blur(10px);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.zoom-btn:hover {
    background: var(--primary);
    color: white;
    transform: scale(1.1);
}

/* Premium Product Info */
.premium-product-info {
    padding: 20px 0;
}

.product-header {
    margin-bottom: 32px;
}

.product-meta-tags {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.meta-tag {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.meta-tag.category {
    background: var(--primary-light);
    color: var(--primary);
}

.meta-tag.sku {
    background: var(--bg-tertiary);
    color: var(--text-secondary);
}

.meta-tag.sale-tag {
    background: linear-gradient(135deg, #ef4444, #f97316);
    color: white;
}

.product-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.2;
    margin-bottom: 16px;
    background: linear-gradient(135deg, var(--text-primary) 0%, var(--primary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.rating-section {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.stars {
    display: flex;
    gap: 2px;
}

.rating-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text-primary);
}

.rating-count {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.verified-badge {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    background: var(--success);
    color: white;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Premium Price Section */
.premium-price-section {
    margin-bottom: 32px;
    padding: 24px;
    background: var(--glass);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-lg);
    border: 1px solid var(--glass-border);
    box-shadow: var(--shadow-soft);
}

.price-main {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}

.current-price {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--primary);
}

.original-price {
    font-size: 1.5rem;
    color: var(--text-light);
    text-decoration: line-through;
}

.discount-badge {
    background: var(--success);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 700;
}

.price-savings {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.savings-text {
    color: var(--success);
    font-weight: 600;
}

.tax-text {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

/* Product Description */
.product-description {
    margin-bottom: 32px;
    line-height: 1.8;
    color: var(--text-secondary);
    font-size: 1.1rem;
}

/* Premium Features */
.premium-features {
    margin-bottom: 32px;
}

.feature-grid {
    display: grid;
    gap: 16px;
}

.feature-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: var(--glass);
    backdrop-filter: blur(10px);
    border-radius: var(--radius);
    border: 1px solid var(--glass-border);
    transition: all 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-medium);
}

.feature-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.feature-content h4 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.feature-content p {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

/* Premium Purchase Section */
.premium-purchase-section {
    margin-bottom: 24px;
    padding: 24px;
    background: var(--glass);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-lg);
    border: 1px solid var(--glass-border);
    box-shadow: var(--shadow-soft);
}

.quantity-section {
    margin-bottom: 24px;
}

.quantity-label {
    display: block;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 12px;
}

.premium-quantity {
    display: flex;
    align-items: center;
    border-radius: var(--radius);
    overflow: hidden;
    background: white;
    box-shadow: var(--shadow-soft);
    width: fit-content;
}

.qty-btn {
    background: var(--bg-tertiary);
    border: none;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: var(--text-primary);
}

.qty-btn:hover {
    background: var(--primary);
    color: white;
}

.qty-input {
    width: 60px;
    height: 48px;
    border: none;
    text-align: center;
    font-size: 1.125rem;
    font-weight: 600;
    background: white;
    color: var(--text-primary);
}

.action-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.action-form {
    margin: 0;
}

.btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 18px 24px;
    border: none;
    border-radius: var(--radius);
    font-size: 0.9rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.4s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn-premium-cart {
    background: linear-gradient(135deg, var(--warning) 0%, #e68900 100%);
    color: white;
    box-shadow: var(--shadow-soft);
}

.btn-premium-cart:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-strong);
}

.btn-premium-buy {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    box-shadow: var(--shadow-soft);
}

.btn-premium-buy:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-strong);
}

.btn-icon {
    display: flex;
    align-items: center;
}

.btn-text {
    font-weight: 700;
}

/* Stock & Delivery */
.stock-delivery-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    background: var(--glass);
    backdrop-filter: blur(10px);
    border-radius: var(--radius);
    border: 1px solid var(--glass-border);
}

.stock-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
}

.stock-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--success);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.in-stock {
    color: var(--success);
}

.delivery-info {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

/* Premium Related Products */
.premium-related {
    padding: 80px 0;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    position: relative;
}

.section-header-premium {
    text-align: center;
    margin-bottom: 60px;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 12px;
    background: linear-gradient(135deg, var(--text-primary) 0%, var(--primary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.section-subtitle {
    font-size: 1.1rem;
    color: var(--text-secondary);
}

.premium-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
}

/* Premium Product Card */
.premium-product-card {
    background: var(--glass);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-lg);
    overflow: hidden;
    border: 1px solid var(--glass-border);
    transition: all 0.4s ease;
    position: relative;
}

.premium-product-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-strong);
}

.card-image-container {
    position: relative;
    height: 240px;
    overflow: hidden;
}

.card-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.premium-product-card:hover .card-image {
    transform: scale(1.1);
}

.card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.premium-product-card:hover .card-overlay {
    opacity: 1;
}

.card-badges {
    position: absolute;
    top: 12px;
    left: 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.card-actions {
    position: absolute;
    top: 16px;
    right: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.premium-product-card:hover .card-actions {
    opacity: 1;
}

.action-btn {
    background: var(--glass);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: var(--text-primary);
}

.action-btn:hover {
    background: var(--primary);
    color: white;
    transform: scale(1.1);
}

.card-content {
    padding: 24px;
}

.card-category {
    font-size: 0.75rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    display: block;
    margin-bottom: 8px;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 12px;
    line-height: 1.3;
}

.card-rating {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.card-rating .stars {
    display: flex;
    gap: 1px;
}

.card-rating .rating-value {
    font-size: 0.8rem;
    color: var(--text-secondary);
    font-weight: 600;
}

.card-price {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.card-price .current-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--primary);
}

.card-price .original-price {
    font-size: 0.9rem;
    color: var(--text-light);
    text-decoration: line-through;
}

.card-add-btn {
    width: 100%;
    padding: 12px 16px;
    background: transparent;
    border: 2px solid var(--primary);
    color: var(--primary);
    border-radius: var(--radius);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
}

.card-add-btn:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
}

/* No Products Message */
.no-products-message {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: var(--text-secondary);
}

.no-products-message p {
    font-size: 1.1rem;
}

/* Lightbox */
.lightbox {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(10px);
}

.lightbox-content {
    position: relative;
    margin: auto;
    padding: 20px;
    max-width: 90%;
    max-height: 90%;
    top: 50%;
    transform: translateY(-50%);
}

.close-lightbox {
    position: absolute;
    top: 20px;
    right: 30px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    z-index: 1001;
}

.lightbox-image {
    width: 100%;
    height: auto;
    border-radius: var(--radius);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .product-layout {
        gap: 40px;
    }
    
    .product-image {
        height: 500px;
    }
    
    .product-title {
        font-size: 2rem;
    }
}

@media (max-width: 768px) {
    .product-layout {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .product-image-section {
        position: static;
    }
    
    .product-image {
        height: 400px;
    }
    
    .product-title {
        font-size: 1.75rem;
    }
    
    .action-buttons {
        grid-template-columns: 1fr;
    }
    
    .stock-delivery-info {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }
    
    .premium-products-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0 16px;
    }
    
    .premium-breadcrumb {
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .product-image {
        height: 300px;
    }
    
    .product-title {
        font-size: 1.5rem;
    }
    
    .current-price {
        font-size: 2rem;
    }
    
    .premium-quantity {
        width: 100%;
    }
    
    .qty-input {
        flex: 1;
    }
}
</style>

<script>
// Quantity Controls
function adjustQuantity(change) {
    const qtyInput = document.querySelector('.qty-input');
    const cartQuantity = document.getElementById('cartQuantity');
    const buyQuantity = document.getElementById('buyQuantity');
    
    const currentValue = parseInt(qtyInput.value);
    const newValue = Math.max(1, Math.min(10, currentValue + change));
    
    qtyInput.value = newValue;
    cartQuantity.value = newValue;
    buyQuantity.value = newValue;
    
    // Add animation feedback
    qtyInput.style.transform = 'scale(1.1)';
    setTimeout(() => {
        qtyInput.style.transform = 'scale(1)';
    }, 150);
}

// Lightbox Functions
function openLightbox() {
    document.getElementById('imageLightbox').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('imageLightbox').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close lightbox when clicking outside
window.onclick = function(event) {
    const lightbox = document.getElementById('imageLightbox');
    if (event.target === lightbox) {
        closeLightbox();
    }
}

// Add smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Add loading animation
window.addEventListener('load', function() {
    document.body.classList.add('loaded');
});
</script>

<?php require 'includes/footer.php'; ?>