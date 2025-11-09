<?php
require_once __DIR__ . '/db.php';

// Fetch username from database if user is logged in
$username = '';
if(isset($_SESSION['user_id'])) {
    $stmt = $mysqli->prepare("SELECT username, first_name, last_name FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $username = $user['username'];
        // You can also use first_name and last_name if you prefer
        // $username = $user['first_name'] . ' ' . $user['last_name'];
    }
    $stmt->close();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sahat E-Commerce</title>
  <style>
    .nav-header-reset { margin: 0; padding: 0; box-sizing: border-box; }
    .nav-header-body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f8f9fa; }
    .nav-header-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
    .nav-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; background: linear-gradient(135deg, #9de8fdff 0%, #09d0fdff 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; }
    .nav-header-logo { height: 40px; width: auto; transition: transform 0.3s ease; }
    .nav-header-logo:hover { transform: scale(1.05); }
    .nav-header-center { display: flex; gap: 2rem; }
    .nav-header-link { font-size:18px; text-decoration: none; color: #333; font-weight: 500; padding: 0.5rem 1rem; border-radius: 4px; transition: all 0.3s ease; position: relative; }
    .nav-header-link:hover { color: #ff6b35; background-color: none; }
    .nav-header-link::after { content: ''; position: absolute; bottom: -2px; left: 50%; width: 0; height: 2px; background: #ff6b35; transition: all 0.3s ease; transform: translateX(-50%); }
    .nav-header-link:hover::after { width: 80%; }
    .nav-header-right { display: flex; align-items: center; gap: 1.5rem; }
    .nav-header-action { font-size:18px; text-decoration: none; color: #333; font-weight: 500; padding: 0.5rem 1rem; border-radius: 4px; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem; }
    .nav-header-action:hover { color: #ff6b35; background-color: none; }
    .nav-header-cart { background: #ff6b35; color: white; padding: 0.2rem 0.6rem; border-radius: 50%; font-size: 0.8rem; font-weight: bold; }
    .nav-header-username { 
    font-size: 15px; 
    font-weight: 600; 
    color: #ffffff; 
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    padding: 0.5rem 1.2rem; 
    border-radius: 20px; 
    border: 1px solid #4a6572;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.nav-header-username::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(9, 208, 253, 0.1) 0%, rgba(255, 107, 53, 0.1) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.nav-header-username:hover::after {
    opacity: 1;
}

.nav-header-username:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    border-color: #09d0fd;
}

.nav-header-username i {
    font-size: 16px;
    color: #09d0fd;
    position: relative;
    z-index: 2;
}

.nav-header-username span {
    position: relative;
    z-index: 2;
}
    
  </style>
</head>
<body class="nav-header-body">
<header class="nav-header">
  <div class="nav-header-left">
    <a href="<?= BASE_URL ?>">
      <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo" class="nav-header-logo">
    </a>
  </div>
  
  <nav class="nav-header-center">
    <a href="<?= BASE_URL ?>index.php" class="nav-header-link">Home</a>
    <a href="<?= BASE_URL ?>about.php" class="nav-header-link">About Us</a>
    <a href="<?= BASE_URL ?>orders.php" class="nav-header-link">My Orders</a>
    <a href="<?= BASE_URL ?>contact.php" class="nav-header-link">Contact Us</a>
  </nav>
  
  <div class="nav-header-right">
    <?php if(isset($_SESSION['user_id']) && !empty($username)): ?>
      <div class="nav-header-username">
        <i class="fas fa-user-secret"></i>
        Hello, <?= htmlspecialchars($username) ?>
      </div>
    <?php endif; ?>
    
    <a href="<?= BASE_URL ?>cart.php" class="nav-header-action">
      Cart <span class="nav-header-cart"><?php
        if(isset($_SESSION['user_id'])){
           $stmt = $mysqli->prepare("SELECT SUM(quantity) AS sumq FROM cart WHERE user_id = ?");
           $stmt->bind_param('i', $_SESSION['user_id']);
           $stmt->execute(); 
           $res = $stmt->get_result()->fetch_assoc();
           $stmt->close();
           echo (int)$res['sumq'];
        } else {
           echo 0;
        }
      ?></span>
    </a>
    
    <?php if(isset($_SESSION['user_id'])): ?>
      <a href="<?= BASE_URL ?>logout.php" class="nav-header-action">Logout</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>login.php" class="nav-header-action">Login</a>
    <?php endif; ?>
  </div>
</header>
<main class="nav-header-container">