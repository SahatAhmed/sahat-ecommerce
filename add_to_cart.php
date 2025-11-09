<?php
require_once 'includes/db.php';
if(!isset($_SESSION['user_id'])){
    header('Location: login.php'); exit;
}
$uid = $_SESSION['user_id'];
$pid = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['quantity'] ?? 1));

// check if exists -> update, else insert
$stmt = $mysqli->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param('ii', $uid, $pid);
$stmt->execute(); $res = $stmt->get_result();
if($res->num_rows){
    $r = $res->fetch_assoc();
    $newq = $r['quantity'] + $qty;
    $up = $mysqli->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
    $up->bind_param('ii', $newq, $r['cart_id']); $up->execute();
} else {
    $ins = $mysqli->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,?)");
    $ins->bind_param('iii', $uid, $pid, $qty); $ins->execute();
}
header('Location: cart.php');
exit;
