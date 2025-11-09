<?php
// Remove session_start() since it's already started in db.php
require_once 'includes/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// Fetch orders
$stmt = $mysqli->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY order_date DESC
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$orders = $stmt->get_result();

// Get order statistics
$stats_stmt = $mysqli->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_spent,
        SUM(CASE WHEN order_status = 'Pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN order_status = 'Delivered' THEN 1 ELSE 0 END) as completed_orders
    FROM orders 
    WHERE user_id = ?
");
$stats_stmt->bind_param("i", $uid);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | Your Account</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #eef2ff;
            --secondary: #8b5cf6;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --card-bg: #ffffff;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f8fafc;
            --border-light: #e2e8f0;
            --radius: 16px;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-hover: 0 10px 30px rgba(0,0,0,0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", sans-serif;
            background: linear-gradient(135deg, var(--bg-light) 0%, #f1f5f9 100%);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-dark);
            text-align: center;
        }

        .page-subtitle {
            text-align: center;
            color: var(--text-light);
            margin-bottom: 40px;
            font-size: 16px;
        }

        .order-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .order-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--border-light);
            position: relative;
            overflow: hidden;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .order-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .order-id {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .order-date {
            font-size: 14px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .order-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            color: white;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
        }

        /* Status Colors - FIXED to match database values */
        .status-pending { background: var(--warning); }
        .status-shipped { background: var(--accent); }
        .status-in-transit { background: var(--primary); }
        .status-delivered { background: var(--success); }
        .status-cancelled { background: var(--danger); } /* FIXED: status-cancelled to match database 'Cancelled' */

        .order-items {
            margin: 20px 0;
            border-top: 1px solid var(--border-light);
            padding-top: 15px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-name {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .item-qty {
            color: var(--text-light);
            background: var(--bg-light);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 13px;
        }

        .item-price {
            font-weight: 600;
            color: var(--primary-dark);
        }

        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-light);
        }

        .order-total {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .order-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-view {
            background: var(--primary);
            color: white;
        }

        .btn-view:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-invoice {
            background: var(--accent);
            color: white;
        }

        .btn-invoice:hover {
            background: #0891b2;
            transform: translateY(-2px);
        }

        .no-orders {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-top: 20px;
        }

        .no-orders i {
            font-size: 64px;
            color: var(--primary-light);
            margin-bottom: 20px;
        }

        .no-orders h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .no-orders p {
            color: var(--text-light);
            margin-bottom: 25px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-shop {
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-shop:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 20px;
        }

        .stat-orders { background: var(--primary-light); color: var(--primary); }
        .stat-pending { background: #fef3c7; color: var(--warning); }
        .stat-completed { background: #d1fae5; color: var(--success); }
        .stat-total { background: #fce7f3; color: #db2777; }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-light);
        }

        .payment-method {
            margin-top: 10px;
            font-size: 14px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .order-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .order-actions {
                width: 100%;
                justify-content: space-between;
            }

            .btn {
                flex: 1;
                justify-content: center;
            }

            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }

            .order-actions {
                flex-direction: column;
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .delivery-address {
            margin-top: 15px;
            padding: 12px;
            background: var(--primary-light);
            border-radius: 8px;
            font-size: 14px;
            color: var(--text-dark);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .delivery-address i {
            color: var(--primary);
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <?php require 'includes/header.php'; ?>

    <div class="container">
        <h1 class="page-title">Your Order History</h1>
        <p class="page-subtitle">Track and manage all your past orders in one place</p>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card fade-in">
                <div class="stat-icon stat-orders">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-number"><?= $stats['total_orders'] ?? 0 ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card fade-in">
                <div class="stat-icon stat-pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?= $stats['pending_orders'] ?? 0 ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card fade-in">
                <div class="stat-icon stat-completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?= $stats['completed_orders'] ?? 0 ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card fade-in">
                <div class="stat-icon stat-total">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-number">₹<?= number_format($stats['total_spent'] ?? 0, 2) ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
        </div>

        <div class="order-container">
            <?php if($orders->num_rows == 0): ?>
                <div class="no-orders">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>No Orders Yet</h3>
                    <p>You haven't placed any orders with us. Start shopping to see your order history here.</p>
                    <a href="products.php" class="btn-shop">
                        <i class="fas fa-shopping-cart"></i> Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <?php while($order = $orders->fetch_assoc()): ?>
                    <div class="order-card fade-in">
                        <div class="order-header">
                            <div>
                                <div class="order-id">Order #<?= $order['order_id'] ?></div>
                                <div class="order-date">
                                    <i class="far fa-calendar"></i> 
                                    <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?>
                                </div>
                                <?php if(!empty($order['payment_method'])): ?>
                                <div class="payment-method">
                                    <i class="fas fa-credit-card"></i>
                                    Payment: <?= $order['payment_method'] ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <span class="order-status status-<?= strtolower(str_replace(' ', '-', $order['order_status'])) ?>">
                                <?php 
                                $status_icons = [
                                    'Pending' => 'fas fa-clock',
                                    'Shipped' => 'fas fa-shipping-fast',
                                    'In Transit' => 'fas fa-truck',
                                    'Delivered' => 'fas fa-check-circle',
                                    'Cancelled' => 'fas fa-times-circle'  // FIXED: 'Cancelled' to match database
                                ];
                                $status_icon = $status_icons[$order['order_status']] ?? 'fas fa-question';
                                ?>
                                <i class="<?= $status_icon ?>"></i> 
                                <?= $order['order_status'] ?>
                            </span>
                        </div>

                        <?php if(!empty($order['delivery_address'])): ?>
                        <div class="delivery-address">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= htmlspecialchars($order['delivery_address']) ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="order-items">
                            <?php 
                            // Fetch order items with product names and descriptions from products table
                            $item_stmt = $mysqli->prepare("
                                SELECT oi.*, p.product_name, p.description, p.image_url 
                                FROM order_items oi 
                                LEFT JOIN products p ON oi.product_id = p.product_id 
                                WHERE oi.order_id = ?
                            ");
                            $item_stmt->bind_param("i", $order['order_id']);
                            $item_stmt->execute();
                            $items = $item_stmt->get_result();
                            ?>
                            
                            <?php while($it = $items->fetch_assoc()): ?>
                                <div class="item-row">
                                    <div style="flex: 1;">
                                        <div class="item-name">
                                            <i class="fas fa-box"></i>
                                            <?= htmlspecialchars($it['product_name']) ?>
                                            <span style="font-size: 12px; color: var(--text-light);">
                                                (₹<?= number_format($it['unit_price'], 2) ?> each)
                                            </span>
                                        </div>
                                        <?php if(!empty($it['description'])): ?>
                                        <div style="font-size: 13px; color: var(--text-light); margin-top: 5px; line-height: 1.4;">
                                            <?= htmlspecialchars($it['description']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; gap: 15px; align-items: center;">
                                        <div class="item-qty">Qty: <?= $it['quantity'] ?></div>
                                        <div class="item-price">₹<?= number_format($it['subtotal'], 2) ?></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <div class="order-footer">
                            <div class="order-total">Total: ₹<?= number_format($order['total_amount'], 2) ?></div>
                            <div class="order-actions">
                                <a href="view_order.php?id=<?= $order['order_id'] ?>" class="btn btn-view">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                
                                <?php if($order['order_status'] == 'Pending'): ?>
                                    <button class="btn cancel-btn" style="background: #f1f5f9; color: var(--text-light);" 
                                            onclick="cancelOrder(<?= $order['order_id'] ?>, this)">
                                        <i class="fas fa-times"></i> Cancel Order
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add fade-in animation to order cards as they appear
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.order-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
        
        function cancelOrder(orderId, button) {
            if(confirm('Are you sure you want to cancel order #' + orderId + '?')) {
                // Show loading state
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
                button.disabled = true;
                
                // Send AJAX request
                fetch('cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'order_id=' + orderId
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network error: ' + response.status);
                    }
                    return response.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            alert('Order cancelled successfully!');
                            // Reload the page to reflect changes
                            location.reload();
                        } else {
                            throw new Error(data.message || 'Failed to cancel order');
                        }
                    } catch (e) {
                        console.error('JSON Parse Error:', text);
                        throw new Error('Server error - please try again');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error: ' + error.message);
                    // Restore button
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }
    </script>
</body>
</html>