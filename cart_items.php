<?php
session_start();

header('Content-Type: application/json');

$cartItems = isset($_SESSION['selectedExtras']) ? $_SESSION['selectedExtras'] : [];
$totalAmount = array_reduce($cartItems, function($sum, $item) {
    return $sum + $item['Amount'];
}, 0);

echo json_encode(['items' => $cartItems, 'totalAmount' => $totalAmount]);
?>
