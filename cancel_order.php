<?php
// Set content type to JSON immediately
header('Content-Type: application/json');

// Turn off error reporting to prevent HTML in JSON
error_reporting(0);
ini_set('display_errors', 0);

// Start session
session_start();

// Include database
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to cancel orders']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the order ID
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    // Update order status to Cancelled
    $stmt = $mysqli->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE order_id = ? AND user_id = ? AND order_status = 'Pending'");
    
    if ($stmt === false) {
        throw new Exception('Database prepare failed');
    }
    
    $stmt->bind_param("ii", $order_id, $user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Order cancelled successfully',
                'order_id' => $order_id
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Order not found, already processed, or cannot be cancelled'
            ]);
        }
    } else {
        throw new Exception('Database execution failed');
    }
    
    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

// Close connection
if (isset($mysqli)) {
    $mysqli->close();
}
?>