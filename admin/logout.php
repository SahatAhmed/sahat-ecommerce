<?php
require_once __DIR__ . '/../includes/db.php';
unset($_SESSION['admin_id']);
header('Location: index.php'); exit;
