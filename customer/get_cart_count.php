<?php
session_start();

header('Content-Type: application/json');

$total_item = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $total_item += $qty;
    }
}

echo json_encode(['count' => $total_item]);
?>