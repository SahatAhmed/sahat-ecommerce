<?php
require_once __DIR__ . '/../includes/db.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $oid = (int)$_POST['order_id'];
    $status = $_POST['order_status'];

    $u = $mysqli->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $u->bind_param('si', $status, $oid);
    
    if ($u->execute()) {
        $_SESSION['flash_message'] = "Order #" . str_pad($oid, 10, '0', STR_PAD_LEFT) . " status updated successfully";
        $_SESSION['flash_type'] = "success";
    } else {
        $_SESSION['flash_message'] = "Failed to update order status";
        $_SESSION['flash_type'] = "error";
    }

    header('Location: orders.php');
    exit;
}

$orders = $mysqli->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.user_id ORDER BY o.order_date DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f1f5f9;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stat-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .pending .stat-icon { background: #fef3c7; color: var(--warning); }
        .shipped .stat-icon { background: #dbeafe; color: var(--primary); }
        .transit .stat-icon { background: #e0e7ff; color: #6366f1; }
        .delivered .stat-icon { background: #d1fae5; color: var(--success); }
        .cancelled .stat-icon { background: #fee2e2; color: var(--error); }
        
        .stat-info h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stat-info p {
            color: var(--secondary);
            font-size: 0.875rem;
        }
        
        .orders-list {
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .order-card {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            transition: all 0.3s ease;
        }
        
        /* Alternate background colors for orders */
        .order-card:nth-child(odd) {
            background: #ffffff;
        }
        
        .order-card:nth-child(even) {
            background: #fafbfc;
        }
        
        .order-card:hover {
            background: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .order-card:last-child {
            border-bottom: none;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .order-id {
            font-weight: 600;
            color: var(--dark);
            font-size: 1.125rem;
        }
        
        .order-date {
            color: var(--secondary);
            font-size: 0.875rem;
        }
        
        .order-meta {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
        }
        
        .meta-label {
            font-size: 0.75rem;
            color: var(--secondary);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .meta-value {
            font-weight: 500;
        }
        
        .order-items {
            background: var(--light);
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
            border: 1px solid var(--border);
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }
        
        .status-form {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }
        
        select {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: 1px solid var(--border);
            background: white;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            min-width: 140px;
            transition: all 0.2s;
        }
        
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--secondary);
            border: 1px solid var(--border);
        }
        
        .btn-outline:hover {
            background: var(--light);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-shipped { background: #dbeafe; color: #1e40af; }
        .status-transit { background: #e0e7ff; color: #3730a3; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        
        .flash-message {
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }
        
        .flash-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid var(--success);
        }
        
        .flash-error {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid var(--error);
        }
        
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

        /* Status-based border accents */
        .order-card.status-pending-card {
            border-left: 4px solid var(--warning);
        }
        
        .order-card.status-shipped-card {
            border-left: 4px solid var(--primary);
        }
        
        .order-card.status-transit-card {
            border-left: 4px solid #6366f1;
        }
        
        .order-card.status-delivered-card {
            border-left: 4px solid var(--success);
        }
        
        .order-card.status-cancelled-card {
            border-left: 4px solid var(--error);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .order-header {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .order-meta {
                grid-template-columns: 1fr;
            }
            
            .order-actions {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .status-form {
                width: 100%;
            }
            
            select {
                flex: 1;
            }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <h1 class="page-title">Order Management</h1>
    </div>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="flash-message flash-<?= $_SESSION['flash_type'] ?>">
            <i class="fas fa-<?= $_SESSION['flash_type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= $_SESSION['flash_message'] ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>
    
    <div class="stats-cards">
        <?php
        $status_counts = [
            'Pending' => 0,
            'Shipped' => 0,
            'In Transit' => 0,
            'Delivered' => 0,
            'Cancelled' => 0  // Fixed from 'Cancel' to 'Cancelled'
        ];
        
        foreach ($orders as $order) {
            if (isset($status_counts[$order['order_status']])) {
                $status_counts[$order['order_status']]++;
            }
        }
        ?>
        
        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?= $status_counts['Pending'] ?></h3>
                <p>Pending Orders</p>
            </div>
        </div>
        
        <div class="stat-card shipped">
            <div class="stat-icon">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <div class="stat-info">
                <h3><?= $status_counts['Shipped'] ?></h3>
                <p>Shipped Orders</p>
            </div>
        </div>
        
        <div class="stat-card transit">
            <div class="stat-icon">
                <i class="fas fa-truck"></i>
            </div>
            <div class="stat-info">
                <h3><?= $status_counts['In Transit'] ?></h3>
                <p>In Transit</p>
            </div>
        </div>
        
        <div class="stat-card delivered">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?= $status_counts['Delivered'] ?></h3>
                <p>Delivered</p>
            </div>
        </div>

        <div class="stat-card cancelled">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?= $status_counts['Cancelled'] ?></h3>
                <p>Cancelled</p>
            </div>
        </div>
    </div>
    
    <div class="orders-list">
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h3>No orders found</h3>
                <p>There are no orders to display at the moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $index => $o): ?>
                <div class="order-card status-<?= strtolower(str_replace(' ', '-', $o['order_status'])) ?>-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?= str_pad($o['order_id'], 10, '0', STR_PAD_LEFT) ?></div>
                            <div class="order-date"><?= date('M j, Y g:i A', strtotime($o['order_date'])) ?></div>
                        </div>
                        <div class="status-badge status-<?= strtolower(str_replace(' ', '-', $o['order_status'])) ?>">
                            <?= $o['order_status'] ?>
                        </div>
                    </div>
                    
                    <div class="order-meta">
                        <div class="meta-item">
                            <span class="meta-label">Customer</span>
                            <span class="meta-value"><?= esc($o['username']) ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Total Amount</span>
                            <span class="meta-value">₹<?= number_format($o['total_amount'], 2) ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Delivery Address</span>
                            <span class="meta-value"><?= esc($o['delivery_address']) ?></span>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <?php
                            $si = $mysqli->prepare("SELECT oi.*, p.product_name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
                            $si->bind_param('i', $o['order_id']);
                            $si->execute();
                            $items = $si->get_result();
                        ?>
                        
                        <?php while ($it = $items->fetch_assoc()): ?>
                            <div class="order-item">
                                <span><?= esc($it['product_name']) ?> × <?= $it['quantity'] ?></span>
                                <span>₹<?= number_format($it['subtotal'], 2) ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="order-actions">
                        <form method="post" class="status-form">
                            <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                            <select name="order_status">
                                <?php foreach (["Pending", "Shipped", "In Transit", "Delivered", "Cancelled"] as $s): ?>
                                    <option value="<?= $s ?>" <?= $s === $o['order_status'] ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary">
                                <i class="fas fa-sync-alt"></i> Update Status
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
</body>
</html>