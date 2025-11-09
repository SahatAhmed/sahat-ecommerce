<?php
require_once 'includes/db.php';
if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user_id'];
$errors = [];
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $items = [];
    $total = 0.0;

    /* -------------------- BUY NOW CHECKOUT -------------------- */
    if(isset($_POST['buy_product_id'])){
        $pid = (int)$_POST['buy_product_id'];
        $qty = max(1, (int)($_POST['buy_quantity'] ?? 1));

        $stmt = $mysqli->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param('i',$pid);
        $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();

        if(!$p){
            $errors[] = "Product not found";
        } else {
            $items[] = [
                'product_id'=>$pid,
                'quantity'=>$qty,
                'unit_price'=>$p['price']
            ];
            $total += $p['price'] * $qty;
        }

    /* -------------------- CART CHECKOUT -------------------- */
    } elseif(isset($_POST['cart_checkout'])){
        $stmt = $mysqli->prepare("
            SELECT c.product_id, c.quantity, p.price 
            FROM cart c 
            JOIN products p ON c.product_id = p.product_id 
            WHERE c.user_id = ?
        ");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $res = $stmt->get_result();

        while($r = $res->fetch_assoc()){
            $items[] = [
                'product_id'=>$r['product_id'],
                'quantity'=>$r['quantity'],
                'unit_price'=>$r['price']
            ];
            $total += $r['price'] * $r['quantity'];
        }

        if(empty($items)){
            $errors[] = "Your cart is empty";
        }

    } else {
        $errors[] = "Invalid request";
    }

    /* -------------------- ADDRESS VALIDATION -------------------- */
    $address = trim($_POST['delivery_address'] ?? '');
    if($address === '') $errors[] = "Delivery address is required";

    /* -------------------- PLACE ORDER -------------------- */
    if(empty($errors)){
        $ins = $mysqli->prepare("
            INSERT INTO orders (user_id, total_amount, delivery_address, payment_method)
            VALUES (?, ?, ?, 'COD')
        ");
        $ins->bind_param('ids', $uid, $total, $address);

        if($ins->execute()){
            $order_id = $ins->insert_id;

            $oi = $mysqli->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach($items as $it){
                $sub = $it['quantity'] * $it['unit_price'];
                $oi->bind_param('iiidd', $order_id, $it['product_id'], $it['quantity'], $it['unit_price'], $sub);
                $oi->execute();
            }

            // clear cart if needed
            if(isset($_POST['cart_checkout'])){
                $del = $mysqli->prepare("DELETE FROM cart WHERE user_id = ?");
                $del->bind_param('i', $uid);
                $del->execute();
            }

            header("Location: orders.php?placed=$order_id");
            exit;

        } else {
            $errors[] = "Order creation failed";
        }
    }
}

require 'includes/header.php';
?>

<style>
.checkout-container {
    max-width: 500px;
    margin: 2rem auto;
    padding: 2rem;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.checkout-title {
    text-align: center;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #1f2937;
}

.address-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.95rem;
    resize: vertical;
    min-height: 100px;
    transition: all 0.2s;
    background: #f9fafb;
}

.address-input:focus {
    outline: none;
    border-color: #3b82f6;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.submit-btn {
    width: 100%;
    padding: 0.75rem;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.submit-btn:hover {
    background: #1d4ed8;
}

.errors {
    background: #fef2f2;
    color: #dc2626;
    padding: 0.75rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    border-left: 4px solid #dc2626;
}
</style>

<div class="checkout-container">
    <h2 class="checkout-title">Complete Your Order</h2>

    <?php if($errors): ?>
        <div class="errors">
            <?= implode('<br>', array_map('esc', $errors)) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <textarea 
            name="delivery_address" 
            class="address-input" 
            placeholder="Enter your complete delivery address..."
            required
        ><?= esc($_POST['delivery_address'] ?? '') ?></textarea>

        <?php if(isset($_POST['buy_product_id'])): ?>
            <input type="hidden" name="buy_product_id" value="<?= (int)$_POST['buy_product_id'] ?>">
            <input type="hidden" name="buy_quantity" value="<?= (int)$_POST['buy_quantity'] ?>">
        <?php else: ?>
            <input type="hidden" name="cart_checkout" value="1">
        <?php endif; ?>

        <button type="submit" class="submit-btn">Place Order</button>
    </form>
</div>

<?php require 'includes/footer.php'; ?>