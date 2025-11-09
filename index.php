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

// filters
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = $_GET['sort'] ?? '';

$where = '';
$params = [];
$types = '';

if ($category) {
    $where = " WHERE p.category_id = ?";
    $types .= 'i';
    $params[] = $category;
}

// Sorting
$order = " ORDER BY p.created_at DESC";
if ($sort === 'price_asc') $order = " ORDER BY p.price ASC";
if ($sort === 'price_desc') $order = " ORDER BY p.price DESC";
if ($sort === 'name_az') $order = " ORDER BY p.product_name ASC";
if ($sort === 'rating_desc') $order = " ORDER BY p.rating DESC";

// ✅ FIXED SQL - Include all fields including rating and on_sale
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id";

if ($category) {
    $sql .= $where;
}

$sql .= " " . $order;

// ✅ Prepare statement
$stmt = $mysqli->prepare($sql);

// ✅ If prepare fails
if (!$stmt) {
    die("SQL ERROR: " . $mysqli->error);
}

// Run SQL
if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

// ✅ FIX: use a SAFE variable name
$productResult = $stmt->get_result();

// Fetch categories
$categories = $mysqli->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

// Sample product data for demonstration
$sampleProducts = [];
if (is_object($productResult) && $productResult->num_rows > 0) {
    while($p = $productResult->fetch_assoc()) {
        // Calculate real discount based on original_price and price
        $p['discount'] = 0;
        if ($p['original_price'] > $p['price'] && $p['original_price'] > 0) {
            $p['discount'] = round((($p['original_price'] - $p['price']) / $p['original_price']) * 100);
        }
        
        // Use actual database values for rating and on_sale
        $p['rating'] = isset($p['rating']) ? (float)$p['rating'] : 0;
        $p['on_sale'] = isset($p['on_sale']) ? (bool)$p['on_sale'] : false;
        
        // Add sample data for other filters (you can remove these if you have real data)
        $p['is_new'] = rand(0, 1);
        $p['free_shipping'] = rand(0, 1);
        $sampleProducts[] = $p;
    }
}

require 'includes/header.php';
?>

<div class="container">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Discover Amazing Products</h1>
            <p class="hero-subtitle">Find exactly what you're looking for in our curated collection</p>
        </div>
        <div class="hero-visual">
            <div class="floating-card card-1">
                <div class="card-icon">🔥</div>
                <span>Hot Deals</span>
            </div>
            <div class="floating-card card-2">
                <div class="card-icon">🚚</div>
                <span>Free Shipping</span>
            </div>
            <div class="floating-card card-3">
                <div class="card-icon">⭐</div>
                <span>Top Rated</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <!-- Sidebar Filters -->
        <aside class="sidebar">
            <div class="sidebar-section">
                <h3 class="sidebar-title">Categories</h3>
                <div class="category-list">
                    <a href="?" class="category-item <?= $category == 0 ? 'active' : '' ?>" data-category="0">
                        <span class="category-name">All Categories</span>
                        <span class="category-count"><?= count($sampleProducts) ?></span>
                    </a>
                    <?php foreach($categories as $cat): 
                        $catCount = array_filter($sampleProducts, function($p) use ($cat) {
                            return $p['category_id'] == $cat['category_id'];
                        });
                    ?>
                        <a href="?category=<?= $cat['category_id'] ?>" class="category-item <?= $category == $cat['category_id'] ? 'active' : '' ?>" data-category="<?= $cat['category_id'] ?>">
                            <span class="category-name"><?= htmlspecialchars($cat['category_name']) ?></span>
                            <span class="category-count"><?= count($catCount) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">Price Range</h3>
                <div class="price-range">
                    <div class="range-values">
                        <span id="minPrice">₹0</span>
                        <span id="maxPrice">₹10,000</span>
                    </div>
                    <div class="range-inputs">
                        <input type="range" class="range-slider" id="priceRange" min="0" max="10000" value="10000">
                    </div>
                    <div class="price-inputs">
                        <input type="number" id="minPriceInput" placeholder="Min" min="0" max="10000" value="0">
                        <span>-</span>
                        <input type="number" id="maxPriceInput" placeholder="Max" min="0" max="10000" value="10000">
                    </div>
                </div>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">Special Offers</h3>
                <div class="offer-tags">
                    <label class="offer-tag">
                        <input type="checkbox" id="onSaleFilter">
                        <span class="checkmark"></span>
                        <span>On Sale</span>
                    </label>
                    <label class="offer-tag">
                        <input type="checkbox" id="freeShippingFilter">
                        <span class="checkmark"></span>
                        <span>Free Shipping</span>
                    </label>
                    <label class="offer-tag">
                        <input type="checkbox" id="newArrivalsFilter">
                        <span class="checkmark"></span>
                        <span>New Arrivals</span>
                    </label>
                </div>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">Ratings</h3>
                <div class="rating-filters">
                    <label class="rating-filter">
                        <input type="radio" name="rating" value="4">
                        <span class="checkmark radio"></span>
                        <span class="rating-stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= 4 ? 'filled' : '' ?>">★</span>
                            <?php endfor; ?>
                        </span>
                        <span class="rating-text">& Up</span>
                    </label>
                    <label class="rating-filter">
                        <input type="radio" name="rating" value="3">
                        <span class="checkmark radio"></span>
                        <span class="rating-stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= 3 ? 'filled' : '' ?>">★</span>
                            <?php endfor; ?>
                        </span>
                        <span class="rating-text">& Up</span>
                    </label>
                    <label class="rating-filter">
                        <input type="radio" name="rating" value="2">
                        <span class="checkmark radio"></span>
                        <span class="rating-stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= 2 ? 'filled' : '' ?>">★</span>
                            <?php endfor; ?>
                        </span>
                        <span class="rating-text">& Up</span>
                    </label>
                    <label class="rating-filter">
                        <input type="radio" name="rating" value="0" checked>
                        <span class="checkmark radio"></span>
                        <span class="rating-text">All Ratings</span>
                    </label>
                </div>
            </div>

            <button class="filter-reset" onclick="resetAllFilters()">Reset All Filters</button>
        </aside>

        <!-- Products Section -->
        <main class="main-content">
            <!-- Header with Filters -->
            <div class="content-header">
                <div class="results-info">
                    <span class="results-count" id="resultsCount"><?= count($sampleProducts) ?> products found</span>
                    <span class="results-sort">Sorted by: 
                        <select name="sort" id="sortSelect" class="sort-select">
                            <option value="">Featured</option>
                            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="name_az" <?= $sort === 'name_az' ? 'selected' : '' ?>>Name: A-Z</option>
                            <option value="rating_desc" <?= $sort === 'rating_desc' ? 'selected' : '' ?>>Top Rated</option>
                        </select>
                    </span>
                </div>
                <div class="view-options">
                    <button class="view-btn active" data-view="grid">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 3h7v7H3zm0 11h7v7H3zm11 0h7v7h-7zm0-11h7v7h-7z"/>
                        </svg>
                    </button>
                    <button class="view-btn" data-view="list">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 13h18v-2H3zm0 6h18v-2H3zm0 6h18v-2H3z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-grid" id="productsView">
                <?php if (count($sampleProducts) > 0): ?>
                    <?php foreach($sampleProducts as $p): 
                        // Use actual original_price from database
                        $hasDiscount = isset($p['original_price']) && $p['original_price'] > $p['price'];
                        $imagePath = getProductImage($p['image_url']);
                    ?>
                        <div class="product-card" 
                             data-category="<?= $p['category_id'] ?>"
                             data-price="<?= $p['price'] ?>"
                             data-discount="<?= $hasDiscount ? $p['discount'] : 0 ?>"
                             data-new="<?= $p['is_new'] ? '1' : '0' ?>"
                             data-rating="<?= $p['rating'] ?>"
                             data-on-sale="<?= $p['on_sale'] ? '1' : '0' ?>"
                             data-free-shipping="<?= $p['free_shipping'] ? '1' : '0' ?>"
                             data-name="<?= htmlspecialchars($p['product_name']) ?>">
                            <div class="product-image">
                                <a href="product.php?id=<?= $p['product_id'] ?>">
                                    <img src="<?= htmlspecialchars($imagePath) ?>" 
                                         alt="<?= htmlspecialchars($p['product_name']) ?>"
                                         onerror="this.src='assets/images/placeholder.png'">
                                    <div class="image-overlay">
                                        <button class="quick-view">Quick View</button>
                                    </div>
                                </a>
                                <div class="product-badges">
                                    <?php if($p['on_sale']): ?>
                                        <span class="badge sale">On Sale</span>
                                    <?php endif; ?>
                                    <?php if($hasDiscount): ?>
                                        <span class="badge discount">-<?= $p['discount'] ?>%</span>
                                    <?php endif; ?>
                                    <?php if($p['is_new']): ?>
                                        <span class="badge new">New</span>
                                    <?php endif; ?>
                                    <?php if($p['free_shipping']): ?>
                                        <span class="badge shipping">Free Shipping</span>
                                    <?php endif; ?>
                                </div>
                                <button class="wishlist-btn">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" 
                                              stroke="currentColor" stroke-width="1.5" fill="none"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="product-info">
                                <div class="product-category"><?= htmlspecialchars($p['category_name']) ?></div>
                                <h3 class="product-title">
                                    <a href="product.php?id=<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></a>
                                </h3>
                                <div class="product-rating">
                                    <?php 
                                    $rating = $p['rating'];
                                    for($i = 1; $i <= 5; $i++): 
                                        if($i <= floor($rating)): ?>
                                            <span class="star filled">★</span>
                                        <?php elseif($i - 0.5 <= $rating): ?>
                                            <span class="star half">★</span>
                                        <?php else: ?>
                                            <span class="star">★</span>
                                        <?php endif;
                                    endfor; ?>
                                    <span class="rating-count">(<?= number_format($rating, 1) ?>)</span>
                                </div>
                                
                                <div class="product-pricing">
                                    <div class="price-current">₹<?= number_format($p['price'], 2) ?></div>
                                    <?php if($hasDiscount): ?>
                                        <div class="price-old">₹<?= number_format($p['original_price'], 2) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-actions">
                                    <form method="post" action="add_to_cart.php" class="add-to-cart-form">
                                        <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                        <button type="submit" class="btn-cart">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                                <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" 
                                                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Add to Cart
                                        </button>
                                    </form>
                                    
                                    <a class="btn-buy" href="product.php?id=<?= $p['product_id'] ?>">Buy Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <div class="no-products-icon">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none">
                                <path d="M15 5V7M9 5V7M3 7H21V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7Z" 
                                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 12H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>No products found</h3>
                        <p>Try adjusting your filters or browse our full collection</p>
                        <a href="?" class="btn-reset">Reset Filters</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Load More -->
            <div class="load-more">
                <button class="load-more-btn" id="loadMore">Load More Products</button>
            </div>
        </main>
    </div>
</div>

<style>
:root {
    --primary: #8B5CF6;
    --primary-dark: #7C3AED;
    --primary-light: #A78BFA;
    --secondary: #F59E0B;
    --secondary-dark: #D97706;
    --secondary-light: #FBBF24;
    --accent: #10B981;
    --accent-dark: #059669;
    --accent-light: #34D399;
    --danger: #EF4444;
    --danger-dark: #DC2626;
    --warning: #F59E0B;
    --info: #3B82F6;
    
    --light: #F8FAFC;
    --dark: #1F2937;
    --gray-50: #F9FAFB;
    --gray-100: #F3F4F6;
    --gray-200: #E5E7EB;
    --gray-300: #D1D5DB;
    --gray-400: #9CA3AF;
    --gray-500: #6B7280;
    --gray-600: #4B5563;
    --gray-700: #374151;
    --gray-800: #1F2937;
    --gray-900: #111827;
    
    --border: #E5E7EB;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-md: 0 6px 12px -2px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    
    --radius-sm: 4px;
    --radius: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --radius-xl: 24px;
    
    --gradient-primary: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
    --gradient-secondary: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    --gradient-accent: linear-gradient(135deg, #10B981 0%, #059669 100%);
    --gradient-danger: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
    --gradient-hero: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 50%, #F59E0B 100%);
    --gradient-card: linear-gradient(135deg, #FFFFFF 0%, #F8FAFC 100%);
    --gradient-glass: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    line-height: 1.6;
    color: var(--gray-800);
    background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
    min-height: 100vh;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* Hero Section */
.hero-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: center;
    padding: 4rem 0;
    margin-bottom: 2rem;
}

.hero-content {
    max-width: 500px;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 1.5rem;
    background: var(--gradient-hero);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.025em;
}

.hero-subtitle {
    font-size: 1.25rem;
    color: var(--gray-600);
    margin-bottom: 2.5rem;
    font-weight: 500;
}

.hero-visual {
    position: relative;
    height: 300px;
}

.floating-card {
    position: absolute;
    background: var(--gradient-glass);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-lg);
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    color: var(--gray-700);
    box-shadow: var(--shadow-lg);
    animation: float 3s ease-in-out infinite;
}

.card-1 {
    top: 20px;
    left: 0;
    animation-delay: 0s;
}

.card-2 {
    top: 120px;
    right: 40px;
    animation-delay: 1s;
}

.card-3 {
    bottom: 40px;
    left: 60px;
    animation-delay: 2s;
}

.card-icon {
    font-size: 1.5rem;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Content Layout */
.content-wrapper {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    margin-bottom: 4rem;
}

/* Sidebar */
.sidebar {
    background: var(--gradient-card);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.sidebar-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border);
}

.sidebar-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.sidebar-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--gray-700);
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.category-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.category-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--gray-600);
    transition: all 0.3s ease;
    border: 1px solid transparent;
    cursor: pointer;
}

.category-item:hover {
    background: var(--gray-50);
    color: var(--gray-700);
}

.category-item.active {
    background: var(--gradient-primary);
    color: white;
    border-color: var(--primary);
}

.category-name {
    font-weight: 500;
}

.category-count {
    font-size: 0.875rem;
    opacity: 0.7;
}

.price-range {
    padding: 0.5rem 0;
}

.range-values {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    color: var(--gray-600);
    font-weight: 600;
}

.range-inputs {
    margin-bottom: 1rem;
}

.range-slider {
    width: 100%;
    height: 6px;
    border-radius: 3px;
    background: var(--gray-200);
    outline: none;
    -webkit-appearance: none;
}

.range-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--gradient-primary);
    cursor: pointer;
    box-shadow: var(--shadow);
    border: 2px solid white;
}

.price-inputs {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.price-inputs input {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    text-align: center;
    font-size: 0.875rem;
}

.price-inputs input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.1);
}

.offer-tags {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.offer-tag {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: var(--radius);
    transition: background 0.3s ease;
}

.offer-tag:hover {
    background: var(--gray-50);
}

.offer-tag input {
    display: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid var(--gray-300);
    border-radius: 4px;
    position: relative;
    transition: all 0.3s ease;
}

.checkmark.radio {
    border-radius: 50%;
}

.offer-tag input:checked + .checkmark {
    background: var(--gradient-primary);
    border-color: var(--primary);
}

.offer-tag input:checked + .checkmark::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
}

.rating-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.rating-filter {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: var(--radius);
    transition: background 0.3s ease;
}

.rating-filter:hover {
    background: var(--gray-50);
}

.rating-filter input {
    display: none;
}

.rating-stars {
    display: flex;
    gap: 0.1rem;
}

.rating-text {
    font-size: 0.875rem;
    color: var(--gray-600);
}

.rating-filter input:checked + .checkmark + .rating-stars + .rating-text,
.rating-filter input:checked + .checkmark + .rating-text {
    color: var(--primary);
    font-weight: 600;
}

.filter-reset {
    width: 100%;
    padding: 0.875rem;
    background: var(--gray-100);
    color: var(--gray-700);
    border: none;
    border-radius: var(--radius);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 1rem;
}

.filter-reset:hover {
    background: var(--gray-200);
    transform: translateY(-1px);
}

/* Main Content */
.main-content {
    background: var(--gradient-card);
    border-radius: var(--radius-lg);
    padding: 2rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
}

.results-info {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.results-count {
    font-weight: 600;
    color: var(--gray-700);
}

.results-sort {
    color: var(--gray-600);
    font-size: 0.875rem;
}

.sort-select {
    border: none;
    background: transparent;
    font-weight: 600;
    color: var(--primary);
    outline: none;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius);
    border: 1px solid var(--border);
}

.view-options {
    display: flex;
    gap: 0.5rem;
    background: var(--gray-100);
    padding: 0.25rem;
    border-radius: var(--radius);
}

.view-btn {
    padding: 0.5rem;
    border: none;
    background: transparent;
    border-radius: var(--radius-sm);
    color: var(--gray-500);
    cursor: pointer;
    transition: all 0.3s ease;
}

.view-btn.active {
    background: white;
    color: var(--primary);
    box-shadow: var(--shadow-sm);
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.products-grid.list-view {
    grid-template-columns: 1fr;
}

.products-grid.list-view .product-card {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 1.5rem;
}

.products-grid.list-view .product-image {
    height: 160px;
}

.product-card {
    background: white;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: all 0.4s ease;
    border: 1px solid var(--border);
    position: relative;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
}

.product-image {
    position: relative;
    overflow: hidden;
    height: 200px;
    background: var(--gray-100);
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.7s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.1);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.9) 0%, rgba(245, 158, 11, 0.9) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.product-card:hover .image-overlay {
    opacity: 1;
}

.quick-view {
    padding: 0.75rem 1.5rem;
    background: white;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    color: var(--primary);
    cursor: pointer;
    transition: all 0.3s ease;
    transform: translateY(10px);
}

.product-card:hover .quick-view {
    transform: translateY(0);
}

.product-badges {
    position: absolute;
    top: 12px;
    left: 12px;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge.discount {
    background: var(--gradient-danger);
    color: white;
}

.badge.sale {
    background: var(--gradient-secondary);
    color: white;
}

.badge.new {
    background: var(--gradient-accent);
    color: white;
}

.badge.shipping {
    background: var(--gradient-primary);
    color: white;
}

.wishlist-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 36px;
    height: 36px;
    background: white;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-400);
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: var(--shadow);
}

.wishlist-btn:hover {
    color: var(--danger);
    transform: scale(1.1);
}

.wishlist-btn.active svg {
    fill: var(--danger) !important;
    stroke: var(--danger) !important;
}

.product-info {
    padding: 1.5rem;
}

.product-category {
    font-size: 0.75rem;
    color: var(--primary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.product-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

.product-title a {
    color: var(--gray-800);
    text-decoration: none;
    transition: color 0.3s;
}

.product-title a:hover {
    color: var(--primary);
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-bottom: 1rem;
}

.star {
    color: var(--gray-300);
    font-size: 1rem;
}

.star.filled {
    color: var(--warning);
}

.star.half {
    background: linear-gradient(90deg, var(--warning) 50%, var(--gray-300) 50%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.rating-count {
    font-size: 0.875rem;
    color: var(--gray-500);
    margin-left: 0.5rem;
}

.product-pricing {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.price-current {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
}

.price-old {
    font-size: 1rem;
    color: var(--gray-400);
    text-decoration: line-through;
}

.product-actions {
    display: flex;
    gap: 0.75rem;
}

.add-to-cart-form {
    flex: 1;
}

.btn-cart {
    width: 100%;
    padding: 0.75rem;
    background: white;
    color: var(--primary);
    border: 2px solid var(--primary);
    border-radius: var(--radius);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-cart:hover {
    background: var(--gradient-primary);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.btn-buy {
    flex: 1;
    padding: 0.75rem;
    background: var(--gradient-secondary);
    color: white;
    text-align: center;
    border-radius: var(--radius);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-buy:hover {
    background: var(--secondary-dark);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* No Products */
.no-products {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
}

.no-products-icon {
    margin-bottom: 1.5rem;
    color: var(--gray-400);
}

.no-products h3 {
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
    color: var(--gray-800);
    font-weight: 600;
}

.no-products p {
    color: var(--gray-600);
    margin-bottom: 2rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.btn-reset {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: var(--gradient-primary);
    color: white;
    text-decoration: none;
    border-radius: var(--radius);
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-reset:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Load More */
.load-more {
    text-align: center;
    margin-top: 2rem;
}

.load-more-btn {
    padding: 1rem 2rem;
    background: var(--gradient-primary);
    color: white;
    border: none;
    border-radius: var(--radius);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: var(--shadow);
}

.load-more-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.load-more-btn.hidden {
    display: none;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .content-wrapper {
        grid-template-columns: 1fr;
    }
    
    .sidebar {
        position: static;
    }
    
    .hero-section {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .hero-content {
        max-width: 100%;
    }
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .content-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .results-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
    
    .products-grid.list-view .product-card {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0 1rem;
    }
    
    .main-content {
        padding: 1.5rem;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .product-actions {
        flex-direction: column;
    }
}

/* Animation for page load */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-section, .sidebar, .main-content {
    animation: fadeInUp 0.6s ease forwards;
}

.product-card {
    animation: fadeInUp 0.6s ease forwards;
}

.product-card.hidden {
    display: none !important;
}

.product-card.fade-out {
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.3s ease;
}

.product-card:nth-child(1) { animation-delay: 0.1s; }
.product-card:nth-child(2) { animation-delay: 0.2s; }
.product-card:nth-child(3) { animation-delay: 0.3s; }
.product-card:nth-child(4) { animation-delay: 0.4s; }
.product-card:nth-child(5) { animation-delay: 0.5s; }
.product-card:nth-child(6) { animation-delay: 0.6s; }
</style>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const products = document.querySelectorAll('.product-card');
    const categoryItems = document.querySelectorAll('.category-item');
    const priceRange = document.getElementById('priceRange');
    const minPriceInput = document.getElementById('minPriceInput');
    const maxPriceInput = document.getElementById('maxPriceInput');
    const minPriceDisplay = document.getElementById('minPrice');
    const maxPriceDisplay = document.getElementById('maxPrice');
    const onSaleFilter = document.getElementById('onSaleFilter');
    const freeShippingFilter = document.getElementById('freeShippingFilter');
    const newArrivalsFilter = document.getElementById('newArrivalsFilter');
    const ratingFilters = document.querySelectorAll('input[name="rating"]');
    const sortSelect = document.getElementById('sortSelect');
    const resultsCount = document.getElementById('resultsCount');
    const loadMoreBtn = document.getElementById('loadMore');
    const productsGrid = document.getElementById('productsView');

    let currentMaxPrice = 10000;
    let visibleProducts = 8;

    // Initialize price inputs
    updatePriceDisplay();

    // Category filter
    categoryItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            categoryItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            applyFilters();
        });
    });

    // Price range filter
    priceRange.addEventListener('input', function() {
        currentMaxPrice = parseInt(this.value);
        maxPriceInput.value = currentMaxPrice;
        updatePriceDisplay();
        applyFilters();
    });

    minPriceInput.addEventListener('input', function() {
        const min = parseInt(this.value) || 0;
        const max = parseInt(maxPriceInput.value) || 10000;
        
        if (min > max) {
            this.value = max;
        }
        applyFilters();
    });

    maxPriceInput.addEventListener('input', function() {
        const min = parseInt(minPriceInput.value) || 0;
        const max = parseInt(this.value) || 10000;
        
        if (max < min) {
            this.value = min;
        }
        currentMaxPrice = max;
        priceRange.value = max;
        updatePriceDisplay();
        applyFilters();
    });

    // Special offers filters
    [onSaleFilter, freeShippingFilter, newArrivalsFilter].forEach(filter => {
        filter.addEventListener('change', applyFilters);
    });

    // Rating filters
    ratingFilters.forEach(filter => {
        filter.addEventListener('change', applyFilters);
    });

    // Sort functionality
    sortSelect.addEventListener('change', function() {
        applyFilters();
    });

    // Load more functionality
    loadMoreBtn.addEventListener('click', function() {
        visibleProducts += 8;
        applyFilters();
        updateLoadMoreButton();
    });

    function updatePriceDisplay() {
        minPriceDisplay.textContent = `₹${minPriceInput.value || 0}`;
        maxPriceDisplay.textContent = `₹${currentMaxPrice}`;
    }

    function applyFilters() {
        const selectedCategory = document.querySelector('.category-item.active')?.dataset.category || '0';
        const minPrice = parseInt(minPriceInput.value) || 0;
        const maxPrice = parseInt(maxPriceInput.value) || 10000;
        const onSale = onSaleFilter.checked;
        const freeShipping = freeShippingFilter.checked;
        const newArrivals = newArrivalsFilter.checked;
        const minRating = getSelectedRating();
        const sortBy = sortSelect.value;

        let filteredProducts = Array.from(products);
        let visibleCount = 0;

        console.log('Applying filters:', {
            selectedCategory, minPrice, maxPrice, onSale, freeShipping, newArrivals, minRating, sortBy
        });

        // Apply filters
        filteredProducts = filteredProducts.filter(product => {
            const productCategory = product.dataset.category;
            const productPrice = parseFloat(product.dataset.price);
            const productDiscount = parseFloat(product.dataset.discount);
            const productIsNew = product.dataset.new === '1';
            const productFreeShipping = product.dataset.freeShipping === '1';
            const productRating = parseFloat(product.dataset.rating);
            const productOnSale = product.dataset.onSale === '1';

            console.log('Product:', {
                productCategory, productPrice, productDiscount, productIsNew, productFreeShipping, productRating, productOnSale
            });

            // Category filter
            if (selectedCategory !== '0' && productCategory !== selectedCategory) {
                console.log('Filtered by category');
                return false;
            }

            // Price filter
            if (productPrice < minPrice || productPrice > maxPrice) {
                console.log('Filtered by price');
                return false;
            }

            // Special offers filters
            if (onSale && !productOnSale) {
                console.log('Filtered by sale');
                return false;
            }

            if (freeShipping && !productFreeShipping) {
                console.log('Filtered by shipping');
                return false;
            }

            if (newArrivals && !productIsNew) {
                console.log('Filtered by new arrivals');
                return false;
            }

            // Rating filter
            if (minRating > 0 && productRating < minRating) {
                console.log('Filtered by rating');
                return false;
            }

            return true;
        });

        console.log('Filtered products count:', filteredProducts.length);

        // Apply sorting
        filteredProducts.sort((a, b) => {
            const priceA = parseFloat(a.dataset.price);
            const priceB = parseFloat(b.dataset.price);
            const nameA = a.querySelector('.product-title a').textContent.toLowerCase();
            const nameB = b.querySelector('.product-title a').textContent.toLowerCase();
            const ratingA = parseFloat(a.dataset.rating);
            const ratingB = parseFloat(b.dataset.rating);

            switch(sortBy) {
                case 'price_asc':
                    return priceA - priceB;
                case 'price_desc':
                    return priceB - priceA;
                case 'name_az':
                    return nameA.localeCompare(nameB);
                case 'rating_desc':
                    return ratingB - ratingA; // Higher ratings first
                default:
                    return 0; // Featured - maintain original order
            }
        });

        // Show/hide products with animation
        products.forEach(product => {
            if (filteredProducts.includes(product)) {
                product.classList.remove('hidden');
                product.style.opacity = '1';
                product.style.transform = 'scale(1)';
            } else {
                product.classList.add('hidden');
            }
        });

        // Reorder products in DOM based on sorting
        const productsContainer = document.querySelector('.products-grid');
        filteredProducts.forEach(product => {
            productsContainer.appendChild(product);
        });

        // Limit visible products
        filteredProducts.forEach((product, index) => {
            if (index < visibleProducts) {
                product.classList.remove('hidden');
            } else {
                product.classList.add('hidden');
            }
        });

        visibleCount = Math.min(filteredProducts.length, visibleProducts);

        // Update results count
        resultsCount.textContent = `${filteredProducts.length} products found`;

        // Show no products message if needed
        const noProducts = document.querySelector('.no-products');
        if (filteredProducts.length === 0) {
            if (!noProducts) {
                productsGrid.innerHTML = `
                    <div class="no-products">
                        <div class="no-products-icon">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none">
                                <path d="M15 5V7M9 5V7M3 7H21V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7Z" 
                                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 12H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>No products found</h3>
                        <p>Try adjusting your filters or browse our full collection</p>
                        <button class="btn-reset" onclick="resetAllFilters()">Reset Filters</button>
                    </div>
                `;
            }
        } else if (noProducts) {
            noProducts.remove();
        }

        updateLoadMoreButton();
    }

    function getSelectedRating() {
        for (let filter of ratingFilters) {
            if (filter.checked) {
                return parseFloat(filter.value);
            }
        }
        return 0;
    }

    function updateLoadMoreButton() {
        const totalProducts = document.querySelectorAll('.product-card:not(.hidden)').length;
        const allProducts = document.querySelectorAll('.product-card').length;
        
        if (totalProducts >= allProducts || totalProducts === 0) {
            loadMoreBtn.classList.add('hidden');
        } else {
            loadMoreBtn.classList.remove('hidden');
        }
    }

    // View toggle functionality
    const viewButtons = document.querySelectorAll('.view-btn');
    
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            
            // Update active button
            viewButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Update grid view
            if (view === 'list') {
                productsGrid.classList.add('list-view');
            } else {
                productsGrid.classList.remove('list-view');
            }
        });
    });
    
    // Add to cart animation
    const cartButtons = document.querySelectorAll('.btn-cart');
    cartButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const button = this;
            const originalText = button.innerHTML;
            
            button.innerHTML = '✓ Added to Cart';
            button.style.background = 'var(--gradient-accent)';
            button.style.borderColor = 'var(--accent)';
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.background = '';
                button.style.borderColor = '';
            }, 2000);
        });
    });
    
    // Wishlist toggle
    const wishlistButtons = document.querySelectorAll('.wishlist-btn');
    wishlistButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.toggle('active');
            const svg = this.querySelector('svg');
            
            if (this.classList.contains('active')) {
                svg.style.fill = 'var(--danger)';
                svg.style.stroke = 'var(--danger)';
            } else {
                svg.style.fill = 'none';
                svg.style.stroke = 'var(--gray-400)';
            }
        });
    });

    // Initialize filters
    applyFilters();
});

function resetAllFilters() {
    // Reset category
    document.querySelectorAll('.category-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector('.category-item[data-category="0"]').classList.add('active');
    
    // Reset price
    document.getElementById('priceRange').value = 10000;
    document.getElementById('minPriceInput').value = 0;
    document.getElementById('maxPriceInput').value = 10000;
    
    // Reset special offers
    document.getElementById('onSaleFilter').checked = false;
    document.getElementById('freeShippingFilter').checked = false;
    document.getElementById('newArrivalsFilter').checked = false;
    
    // Reset ratings
    document.querySelectorAll('input[name="rating"]').forEach(radio => {
        radio.checked = false;
    });
    document.querySelector('input[name="rating"][value="0"]').checked = true;
    
    // Reset sort
    document.getElementById('sortSelect').value = '';
    
    // Apply filters
    if (typeof applyFilters === 'function') {
        applyFilters();
    }
    
    // Update price display
    const event = new Event('input');
    document.getElementById('priceRange').dispatchEvent(event);
}
</script>

<?php require 'includes/footer.php'; ?>