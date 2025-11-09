<?php
require_once 'includes/db.php';

// Image path helper function (same as in product.php)
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

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user_id'];

/* ---------------------------------------------------------
   FETCH CART ITEMS (No mysqlnd required)
---------------------------------------------------------- */
$stmt = $mysqli->prepare("
    SELECT c.cart_id, c.quantity, p.product_id,
           p.product_name, p.price, p.image_url, p.stock_quantity
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.user_id = ?
");
$stmt->bind_param('i', $uid);
$stmt->execute();
$stmt->bind_result($cart_id, $qty, $pid, $pname, $price, $img, $stock);

$items = [];
$total = 0;
$itemCount = 0;

while ($stmt->fetch()) {
    $subtotal = $price * $qty;
    $total += $subtotal;
    $itemCount += $qty;
    
    $items[] = [
        'cart_id'      => $cart_id,
        'quantity'     => $qty,
        'product_id'   => $pid,
        'product_name' => $pname,
        'price'        => $price,
        'image_url'    => $img,
        'stock_quantity' => $stock,
        'subtotal'     => $subtotal
    ];
}

require 'includes/header.php';
?>

<!-- Floating Background Elements -->
<div class="floating-elements">
    <div class="floating-circle circle-1"></div>
    <div class="floating-circle circle-2"></div>
    <div class="floating-circle circle-3"></div>
</div>

<div class="cart-container">
    <!-- Header Section -->
    <div class="cart-header">
        <div class="header-content">
            <div class="title-section">
                <h1 class="page-title">Shopping Cart</h1>
                <p class="page-subtitle">Review your items and proceed to checkout</p>
            </div>
            <div class="cart-stats">
                <div class="stat-item">
                    <span class="stat-value"><?= $itemCount ?></span>
                    <span class="stat-label">Items</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= count($items) ?></span>
                    <span class="stat-label">Products</span>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($items)): ?>
        <!-- Empty Cart State -->
        <div class="empty-cart-state">
            <div class="empty-icon">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 3H5L5.4 5M7 13H17L21 5H5.4M7 13L5.4 5M7 13L4.7 15.3C4.3 15.7 4.6 16.4 5.2 16.4H17M17 13V16.4M9 19C9 19.6 8.6 20 8 20C7.4 20 7 19.6 7 19C7 18.4 7.4 18 8 18C8.6 18 9 18.4 9 19ZM17 19C17 19.6 16.6 20 16 20C15.4 20 15 19.6 15 19C15 18.4 15.4 18 16 18C16.6 18 17 18.4 17 19Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3>Your cart is empty</h3>
            <p>Discover amazing products and add them to your cart</p>
            <div class="empty-actions">
                <a href="products.php" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 10H21M7 15H8M12 15H13M6 6V20C6 20.5304 6.21071 21.0391 6.58579 21.4142C6.96086 21.7893 7.46957 22 8 22H16C16.5304 22 17.0391 21.7893 17.4142 21.4142C17.7893 21.0391 18 20.5304 18 20V6M6 6H18M6 6H4M18 6H20M9 6V4C9 3.46957 9.21071 2.96086 9.58579 2.58579C9.96086 2.21071 10.4696 2 11 2H13C13.5304 2 14.0391 2.21071 14.4142 2.58579C14.7893 2.96086 15 3.46957 15 4V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Start Shopping
                </a>
                <a href="categories.php" class="btn btn-outline">Browse Categories</a>
            </div>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <!-- Cart Items Section -->
            <div class="cart-items-section">
                <div class="section-header">
                    <h2>Cart Items (<?= count($items) ?>)</h2>
                    <button type="button" class="clear-cart-btn" onclick="clearCart()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 6H5H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6H19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Clear Cart
                    </button>
                </div>

                <form method="post" action="update_cart.php" class="cart-form">
                    <div class="cart-items">
                        <?php foreach ($items as $index => $row): ?>
                            <div class="cart-item" data-cart-id="<?= $row['cart_id'] ?>">
                                <div class="item-checkbox">
                                    <input type="checkbox" 
                                           name="remove[]" 
                                           value="<?= $row['cart_id'] ?>" 
                                           class="remove-checkbox"
                                           onchange="updateSelection()">
                                </div>
                                
                                <div class="item-image">
                                    <img src="<?= esc(getProductImage($row['image_url'])) ?>" 
                                         alt="<?= esc($row['product_name']) ?>"
                                         class="product-image"
                                         onerror="this.src='assets/images/placeholder.png'">
                                    <div class="image-overlay"></div>
                                </div>

                                <div class="item-details">
                                    <h3 class="product-name"><?= esc($row['product_name']) ?></h3>
                                    <div class="product-meta">
                                        <span class="product-sku">SKU: PRD-<?= str_pad($row['product_id'], 6, '0', STR_PAD_LEFT) ?></span>
                                        <span class="stock-status <?= $row['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                                            <?= $row['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                                        </span>
                                    </div>
                                    <div class="item-actions">
                                        <a href="product.php?id=<?= $row['product_id'] ?>" class="action-link">View Product</a>
                                        <button type="button" class="action-link save-later">Save for Later</button>
                                    </div>
                                </div>

                                <div class="item-price">
                                    <span class="price-amount">₹<?= number_format($row['price'], 2) ?></span>
                                </div>

                                <div class="item-quantity">
                                    <div class="quantity-controls">
                                        <button type="button" class="qty-btn minus" onclick="adjustQuantity(<?= $row['cart_id'] ?>, -1)">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <input type="number" 
                                               name="qty[<?= $row['cart_id'] ?>]" 
                                               value="<?= $row['quantity'] ?>" 
                                               min="1" 
                                               max="<?= $row['stock_quantity'] ?>"
                                               class="qty-input"
                                               data-cart-id="<?= $row['cart_id'] ?>"
                                               onchange="updateQuantity(<?= $row['cart_id'] ?>, this.value)">
                                        <button type="button" class="qty-btn plus" onclick="adjustQuantity(<?= $row['cart_id'] ?>, 1)">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="stock-info">
                                        <?php if ($row['stock_quantity'] < 10 && $row['stock_quantity'] > 0): ?>
                                            <span class="low-stock">Only <?= $row['stock_quantity'] ?> left</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="item-subtotal">
                                    <span class="subtotal-amount">₹<?= number_format($row['subtotal'], 2) ?></span>
                                </div>

                                <div class="item-remove">
                                    <button type="button" class="remove-btn" onclick="removeItem(<?= $row['cart_id'] ?>)">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3 6H5H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6H19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-actions">
                        <button type="submit" class="btn btn-update">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Update Cart
                        </button>
                        <a href="products.php" class="btn btn-continue">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 10H21M7 15H8M12 15H13M6 6V20C6 20.5304 6.21071 21.0391 6.58579 21.4142C6.96086 21.7893 7.46957 22 8 22H16C16.5304 22 17.0391 21.7893 17.4142 21.4142C17.7893 21.0391 18 20.5304 18 20V6M6 6H18M6 6H4M18 6H20M9 6V4C9 3.46957 9.21071 2.96086 9.58579 2.58579C9.96086 2.21071 10.4696 2 11 2H13C13.5304 2 14.0391 2.21071 14.4142 2.58579C14.7893 2.96086 15 3.46957 15 4V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Continue Shopping
                        </a>
                    </div>
                </form>
            </div>

            <!-- Order Summary Section -->
            <div class="order-summary-section">
                <div class="summary-card">
                    <div class="summary-header">
                        <h3>Order Summary</h3>
                    </div>
                    
                    <div class="summary-content">
                        <div class="summary-row">
                            <span>Subtotal (<?= $itemCount ?> items)</span>
                            <span>₹<?= number_format($total, 2) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span class="free-shipping">FREE</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax</span>
                            <span>₹<?= number_format($total * 0.18, 2) ?></span>
                        </div>
                        <div class="summary-row discount">
                            <span>Discount</span>
                            <span>-₹<?= number_format($total * 0.05, 2) ?></span>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row total">
                            <span>Total Amount</span>
                            <span class="total-amount">₹<?= number_format($total * 0.95 + $total * 0.18, 2) ?></span>
                        </div>
                        
                        <div class="savings-badge">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            You save ₹<?= number_format($total * 0.05, 2) ?>
                        </div>
                    </div>

                    <form method="post" action="checkout.php" class="checkout-form">
                        <input type="hidden" name="cart_checkout" value="1">
                        <button type="submit" class="btn btn-checkout">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 13L12 20L19 13M12 4V20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Proceed to Checkout
                        </button>
                    </form>

                    <div class="security-features">
                        <div class="security-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 22C12 22 20 18 20 12V5L12 2L4 5V12C4 18 12 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Secure checkout
                        </div>
                        <div class="security-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 12L11 15L16 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            SSL encrypted
                        </div>
                    </div>
                </div>

                <!-- Trust Badges -->
                <div class="trust-badges">
                    <div class="trust-badge">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22C12 22 20 18 20 12V5L12 2L4 5V12C4 18 12 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>100% Secure</span>
                    </div>
                    <div class="trust-badge">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 12V22H4V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 7H2V12H22V7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 22V7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 7H16.5C17.163 7 17.7989 6.73661 18.2678 6.26777C18.7366 5.79893 19 5.16304 19 4.5C19 3.83696 18.7366 3.20107 18.2678 2.73223C17.7989 2.26339 17.163 2 16.5 2C14 2 12 7 12 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 7H7.5C6.83696 7 6.20107 6.73661 5.73223 6.26777C5.26339 5.79893 5 5.16304 5 4.5C5 3.83696 5.26339 3.20107 5.73223 2.73223C6.20107 2.26339 6.83696 2 7.5 2C10 2 12 7 12 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Free Shipping</span>
                    </div>
                    <div class="trust-badge">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>30-Day Returns</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
    --radius: 12px;
    --radius-lg: 16px;
    --radius-xl: 20px;
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
    line-height: 1.6;
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
    animation: float 8s ease-in-out infinite;
}

.circle-1 {
    width: 150px;
    height: 150px;
    top: 10%;
    left: 5%;
    animation-delay: 0s;
}

.circle-2 {
    width: 100px;
    height: 100px;
    top: 60%;
    right: 10%;
    background: var(--gradient-secondary);
    animation-delay: 2s;
}

.circle-3 {
    width: 120px;
    height: 120px;
    bottom: 20%;
    left: 15%;
    background: var(--gradient-accent);
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

/* Main Container */
.cart-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

/* Header Section */
.cart-header {
    margin-bottom: 40px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 20px;
}

.title-section .page-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 8px;
    background: linear-gradient(135deg, var(--text-primary) 0%, var(--primary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-subtitle {
    font-size: 1.1rem;
    color: var(--text-secondary);
}

.cart-stats {
    display: flex;
    gap: 24px;
}

.stat-item {
    text-align: right;
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Empty Cart State */
.empty-cart-state {
    text-align: center;
    padding: 80px 40px;
    background: var(--glass);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-xl);
    border: 1px solid var(--glass-border);
    box-shadow: var(--shadow-soft);
}

.empty-icon {
    margin-bottom: 24px;
    color: var(--text-light);
}

.empty-cart-state h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 12px;
    color: var(--text-primary);
}

.empty-cart-state p {
    font-size: 1rem;
    color: var(--text-secondary);
    margin-bottom: 32px;
}

.empty-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Cart Layout */
.cart-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
    align-items: start;
}

/* Cart Items Section */
.cart-items-section {
    background: var(--glass);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-xl);
    border: 1px solid var(--glass-border);
    box-shadow: var(--shadow-soft);
    overflow: hidden;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid var(--border-light);
    background: var(--bg-primary);
}

.section-header h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
}

.clear-cart-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: transparent;
    border: 1px solid var(--error);
    color: var(--error);
    border-radius: var(--radius);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.clear-cart-btn:hover {
    background: var(--error);
    color: white;
}

.cart-items {
    padding: 0;
}

.cart-item {
    display: grid;
    grid-template-columns: auto 80px 1fr auto auto auto;
    gap: 16px;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid var(--border-light);
    transition: all 0.3s ease;
    position: relative;
}

.cart-item:hover {
    background: rgba(99, 102, 241, 0.02);
}

.cart-item:last-child {
    border-bottom: none;
}

.item-checkbox {
    display: flex;
    align-items: center;
}

.remove-checkbox {
    width: 18px;
    height: 18px;
    border-radius: 4px;
    border: 2px solid var(--border);
    cursor: pointer;
    transition: all 0.3s ease;
}

.remove-checkbox:checked {
    background: var(--primary);
    border-color: var(--primary);
}

.item-image {
    position: relative;
    width: 80px;
    height: 80px;
    border-radius: var(--radius);
    overflow: hidden;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
    background-color: #f8fafc;
    background-image: linear-gradient(45deg, #f1f5f9 25%, transparent 25%), 
                      linear-gradient(-45deg, #f1f5f9 25%, transparent 25%), 
                      linear-gradient(45deg, transparent 75%, #f1f5f9 75%), 
                      linear-gradient(-45deg, transparent 75%, #f1f5f9 75%);
    background-size: 20px 20px;
    background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
}

.cart-item:hover .product-image {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.cart-item:hover .image-overlay {
    opacity: 1;
}

.item-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.product-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.3;
}

.product-meta {
    display: flex;
    gap: 12px;
    font-size: 0.875rem;
}

.product-sku {
    color: var(--text-secondary);
}

.stock-status {
    font-weight: 600;
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 12px;
}

.stock-status.in-stock {
    background: var(--success);
    color: white;
}

.stock-status.out-of-stock {
    background: var(--error);
    color: white;
}

.item-actions {
    display: flex;
    gap: 16px;
}

.action-link {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: color 0.3s ease;
}

.action-link:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

.item-price {
    text-align: center;
}

.price-amount {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
}

.item-quantity {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.quantity-controls {
    display: flex;
    align-items: center;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    background: white;
}

.qty-btn {
    background: var(--bg-tertiary);
    border: none;
    width: 36px;
    height: 36px;
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
    width: 50px;
    height: 36px;
    border: none;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 600;
    background: white;
    color: var(--text-primary);
}

.qty-input:focus {
    outline: none;
    background: var(--primary-light);
}

.stock-info {
    font-size: 0.75rem;
}

.low-stock {
    color: var(--warning);
    font-weight: 600;
}

.item-subtotal {
    text-align: center;
}

.subtotal-amount {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text-primary);
}

.item-remove {
    display: flex;
    align-items: center;
    justify-content: center;
}

.remove-btn {
    background: transparent;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    padding: 8px;
    border-radius: var(--radius);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.remove-btn:hover {
    background: var(--error);
    color: white;
    transform: scale(1.1);
}

.cart-actions {
    display: flex;
    gap: 16px;
    padding: 24px;
    border-top: 1px solid var(--border-light);
    background: var(--bg-primary);
}

/* Buttons */
.btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    border: none;
    border-radius: var(--radius);
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-primary {
    background: var(--primary);
    color: white;
    box-shadow: var(--shadow-soft);
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

.btn-outline {
    background: transparent;
    color: var(--primary);
    border: 2px solid var(--primary);
}

.btn-outline:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
}

.btn-update {
    background: var(--warning);
    color: white;
}

.btn-update:hover {
    background: #e68900;
    transform: translateY(-2px);
}

.btn-continue {
    background: transparent;
    color: var(--text-secondary);
    border: 2px solid var(--border);
}

.btn-continue:hover {
    background: var(--bg-tertiary);
    border-color: var(--text-secondary);
    transform: translateY(-2px);
}

/* Order Summary */
.order-summary-section {
    position: sticky;
    top: 40px;
}

.summary-card {
    background: var(--glass);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-xl);
    border: 1px solid var(--glass-border);
    box-shadow: var(--shadow-soft);
    overflow: hidden;
    margin-bottom: 24px;
}

.summary-header {
    padding: 24px;
    background: var(--bg-primary);
    border-bottom: 1px solid var(--border-light);
}

.summary-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
}

.summary-content {
    padding: 24px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    font-size: 0.875rem;
}

.summary-row:last-child {
    margin-bottom: 0;
}

.summary-row.total {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text-primary);
}

.total-amount {
    font-size: 1.25rem;
    color: var(--primary);
}

.free-shipping {
    color: var(--success);
    font-weight: 600;
}

.summary-row.discount {
    color: var(--success);
}

.summary-divider {
    height: 1px;
    background: var(--border);
    margin: 20px 0;
}

.savings-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: var(--success);
    color: white;
    border-radius: var(--radius);
    font-size: 0.875rem;
    font-weight: 600;
    margin-top: 16px;
}

.checkout-form {
    padding: 0 24px 24px;
}

.btn-checkout {
    width: 100%;
    background: var(--primary);
    color: white;
    padding: 16px 24px;
    font-size: 1rem;
    font-weight: 700;
}

.btn-checkout:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

.security-features {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 0 24px 24px;
}

.security-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.75rem;
    color: var(--text-secondary);
}

/* Trust Badges */
.trust-badges {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.trust-badge {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: var(--glass);
    backdrop-filter: blur(10px);
    border-radius: var(--radius);
    border: 1px solid var(--glass-border);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary);
}

.trust-badge svg {
    color: var(--primary);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .cart-layout {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .order-summary-section {
        position: static;
    }
}

@media (max-width: 768px) {
    .cart-container {
        padding: 20px 16px;
    }
    
    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .cart-stats {
        width: 100%;
        justify-content: space-between;
    }
    
    .cart-item {
        grid-template-columns: 1fr;
        gap: 12px;
        text-align: center;
        padding: 16px;
    }
    
    .item-checkbox {
        position: absolute;
        top: 16px;
        left: 16px;
    }
    
    .item-image {
        justify-self: center;
        width: 120px;
        height: 120px;
    }
    
    .item-actions {
        justify-content: center;
    }
    
    .cart-actions {
        flex-direction: column;
    }
    
    .empty-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .empty-actions .btn {
        width: 200px;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 2rem;
    }
    
    .cart-item {
        padding: 16px;
    }
    
    .section-header {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }
    
    .trust-badges {
        grid-template-columns: 1fr;
    }
    
    .product-name {
        font-size: 1rem;
    }
    
    .price-amount, .subtotal-amount {
        font-size: 1rem;
    }
}
</style>

<script>
// Quantity adjustment functions
function adjustQuantity(cartId, change) {
    const qtyInput = document.querySelector(`.qty-input[data-cart-id="${cartId}"]`);
    const currentValue = parseInt(qtyInput.value);
    const maxValue = parseInt(qtyInput.max);
    const newValue = Math.max(1, Math.min(maxValue, currentValue + change));
    
    qtyInput.value = newValue;
    
    // Add animation feedback
    qtyInput.style.transform = 'scale(1.1)';
    setTimeout(() => {
        qtyInput.style.transform = 'scale(1)';
    }, 150);
    
    // Update the form field
    updateQuantity(cartId, newValue);
}

function updateQuantity(cartId, quantity) {
    const formInput = document.querySelector(`input[name="qty[${cartId}]"]`);
    if (formInput) {
        formInput.value = quantity;
    }
}

// Remove item function
function removeItem(cartId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        const checkbox = document.querySelector(`.remove-checkbox[value="${cartId}"]`);
        if (checkbox) {
            checkbox.checked = true;
            document.querySelector('.cart-form').submit();
        }
    }
}

// Clear cart function
function clearCart() {
    if (confirm('Are you sure you want to clear your entire cart?')) {
        const checkboxes = document.querySelectorAll('.remove-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        document.querySelector('.cart-form').submit();
    }
}

// Update selection (for future features)
function updateSelection() {
    const selectedItems = document.querySelectorAll('.remove-checkbox:checked');
    console.log(`${selectedItems.length} items selected for removal`);
}

// Add smooth animations
document.addEventListener('DOMContentLoaded', function() {
    const cartItems = document.querySelectorAll('.cart-item');
    cartItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            item.style.transition = 'all 0.5s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, index * 100);
    });
});

// Handle broken images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.product-image');
    images.forEach(img => {
        img.addEventListener('error', function() {
            this.src = 'assets/images/placeholder.png';
        });
    });
});
</script>

<?php require 'includes/footer.php'; ?>