<?php
session_start();
include '../config/koneksi.php';

header('Content-Type: application/json');

if (!isset($_POST['id']) || !isset($_POST['meja'])) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
    exit;
}

$id_menu = $_POST['id'];
$meja = $_POST['meja'];

// Cek stok menu
$cekStok = mysqli_query($conn, "SELECT stok, nama_menu FROM menu WHERE id_menu='$id_menu'");
$menu = mysqli_fetch_array($cekStok);

if (!$menu) {
    echo json_encode(['success' => false, 'message' => 'Menu tidak ditemukan']);
    exit;
}

if ($menu['stok'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'Stok habis!']);
    exit;
}

// Tambah ke session cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$id_menu])) {
    $_SESSION['cart'][$id_menu]++;
} else {
    $_SESSION['cart'][$id_menu] = 1;
}

// Hitung total item di cart
$total_item = 0;
foreach ($_SESSION['cart'] as $qty) {
    $total_item += $qty;
}

echo json_encode([
    'success' => true, 
    'message' => $menu['nama_menu'] . ' ditambahkan',
    'cart_count' => $total_item
]);
?>