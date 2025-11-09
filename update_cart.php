<?php
require_once 'includes/db.php';
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
$uid = $_SESSION['user_id'];

$qtys = $_POST['qty'] ?? [];
$remove = $_POST['remove'] ?? [];

foreach($qtys as $cart_id => $q){
    $cart_id = (int)$cart_id; $q = (int)$q;
    if(in_array($cart_id, $remove)){
        $d = $mysqli->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $d->bind_param('ii', $cart_id, $uid); $d->execute();
    } else {
        if($q <= 0){
            $d = $mysqli->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
            $d->bind_param('ii', $cart_id, $uid); $d->execute();
        } else {
            $u = $mysqli->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
            $u->bind_param('iii', $q, $cart_id, $uid); $u->execute();
        }
    }
}

header('Location: cart.php'); exit;
