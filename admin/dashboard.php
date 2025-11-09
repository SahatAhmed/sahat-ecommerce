<?php
require_once __DIR__ . '/../includes/db.php';
if(!isset($_SESSION['admin_id'])){ header('Location: index.php'); exit; }

$total_products = $mysqli->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$total_orders   = $mysqli->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$total_revenue  = $mysqli->query("SELECT COALESCE(SUM(total_amount), 0) AS revenue FROM orders WHERE order_status = 'Delivered'")->fetch_assoc()['revenue'];
$pending_orders = $mysqli->query("SELECT COUNT(*) AS c FROM orders WHERE order_status = 'Pending'")->fetch_assoc()['c'];

$category_stats = $mysqli->query("
    SELECT c.category_name, COUNT(p.product_id) AS cnt 
    FROM categories c 
    LEFT JOIN products p ON c.category_id = p.category_id 
    GROUP BY c.category_id
    ORDER BY cnt DESC
")->fetch_all(MYSQLI_ASSOC);

$recent_orders = $mysqli->query("
    SELECT o.order_id, o.order_date, o.total_amount, o.order_status, u.username 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.order_date DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Analytics Overview</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
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
            margin-bottom: 2.5rem;
        }

        .page-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            color: var(--secondary);
            font-size: 1.125rem;
            font-weight: 500;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--info));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            background: rgba(255, 255, 255, 0.95);
        }

        .stat-card.products::before { background: linear-gradient(90deg, var(--primary), #6366f1); }
        .stat-card.orders::before { background: linear-gradient(90deg, var(--success), #22c55e); }
        .stat-card.revenue::before { background: linear-gradient(90deg, var(--warning), #eab308); }
        .stat-card.pending::before { background: linear-gradient(90deg, var(--danger), #f97316); }

        .stat-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-card.products .stat-icon { background: linear-gradient(135deg, var(--primary), #6366f1); }
        .stat-card.orders .stat-icon { background: linear-gradient(135deg, var(--success), #22c55e); }
        .stat-card.revenue .stat-icon { background: linear-gradient(135deg, var(--warning), #eab308); }
        .stat-card.pending .stat-icon { background: linear-gradient(135deg, var(--danger), #f97316); }

        .stat-info {
            flex: 1;
        }

        .stat-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--dark);
            line-height: 1;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .trend-up { color: var(--success); }
        .trend-down { color: var(--danger); }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Dashboard Cards */
        .dashboard-card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
            background: var(--light);
        }

        .card-header h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-header h3 i {
            color: var(--primary);
        }

        .card-body {
            padding: 1.5rem 2rem;
        }

        /* Category Stats */
        .category-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
            transition: all 0.2s ease;
        }

        .category-item:hover {
            background: var(--light);
            padding-left: 1rem;
            padding-right: 1rem;
            margin: 0 -1rem;
            border-radius: 0.5rem;
        }

        .category-item:last-child {
            border-bottom: none;
        }

        .category-name {
            font-weight: 500;
            color: var(--dark);
        }

        .category-count {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        /* Recent Orders */
        .order-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
            transition: all 0.2s ease;
        }

        .order-item:hover {
            background: var(--light);
            padding-left: 1rem;
            padding-right: 1rem;
            margin: 0 -1rem;
            border-radius: 0.5rem;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-info h4 {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .order-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--secondary);
        }

        .order-amount {
            font-weight: 700;
            color: var(--dark);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-shipped { background: #dbeafe; color: #1e40af; }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            background: white;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .action-btn i {
            font-size: 1.25rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <h1 class="page-title">Dashboard Overview</h1>
        <p class="page-subtitle">Welcome back! Here's what's happening with your store today.</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card products">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-title">Total Products</div>
                    <div class="stat-value"><?= number_format($total_products) ?></div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>Active inventory</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-card orders">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-title">Total Orders</div>
                    <div class="stat-value"><?= number_format($total_orders) ?></div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-chart-line"></i>
                        <span>All-time orders</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-card revenue">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-title">Total Revenue</div>
                    <div class="stat-value">₹<?= number_format($total_revenue, 2) ?></div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-wallet"></i>
                        <span>From delivered orders</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-title">Pending Orders</div>
                    <div class="stat-value"><?= number_format($pending_orders) ?></div>
                    <div class="stat-trend trend-down">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Require attention</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Category Statistics -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-tags"></i> Category Statistics</h3>
            </div>
            <div class="card-body">
                <ul class="category-list">
                    <?php foreach($category_stats as $cs): ?>
                        <li class="category-item">
                            <span class="category-name"><?= esc($cs['category_name']) ?></span>
                            <span class="category-count"><?= $cs['cnt'] ?> products</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Recent Orders</h3>
            </div>
            <div class="card-body">
                <ul class="order-list">
                    <?php foreach($recent_orders as $order): ?>
                        <li class="order-item">
                            <div class="order-info">
                                <h4>Order #<?= str_pad($order['order_id'], 8, '0', STR_PAD_LEFT) ?></h4>
                                <div class="order-meta">
                                    <span><?= esc($order['username']) ?></span>
                                    <span><?= date('M j, Y', strtotime($order['order_date'])) ?></span>
                                </div>
                            </div>
                            <div class="order-right">
                                <div class="order-amount">₹<?= number_format($order['total_amount'], 2) ?></div>
                                <div class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['order_status'])) ?>">
                                    <?= $order['order_status'] ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="products.php" class="action-btn">
            <i class="fas fa-plus"></i>
            <span>Add New Product</span>
        </a>
        <a href="orders.php" class="action-btn">
            <i class="fas fa-eye"></i>
            <span>View All Orders</span>
        </a>
        <a href="products.php" class="action-btn">
            <i class="fas fa-edit"></i>
            <span>Manage Products</span>
        </a>
        <a href="reports.php" class="action-btn">
            <i class="fas fa-chart-bar"></i>
            <span>View Reports</span>
        </a>
    </div>
</main>

</body>
</html>