<?php
session_start();
include '../config/koneksi.php';
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

if(!isset($_GET['order'])){
    echo json_encode(['error' => 'No pesanan tidak ada']);
    exit;
}

$order = mysqli_real_escape_string($conn, $_GET['order']);
$res   = mysqli_query($conn,
    "SELECT status_pembayaran, status_pesanan, waktu_diantar
     FROM transaksi WHERE no_pesanan = '$order' LIMIT 1"
);

if(!$res || mysqli_num_rows($res) === 0){
    echo json_encode(['error' => 'Pesanan tidak ditemukan']);
    exit;
}

$row = mysqli_fetch_assoc($res);

// Jika status diantar dan waktu_diantar belum diisi, isi sekarang
if($row['status_pesanan'] === 'diantar' && empty($row['waktu_diantar'])){
    mysqli_query($conn,
        "UPDATE transaksi SET waktu_diantar = NOW() WHERE no_pesanan = '$order'"
    );
    $row['waktu_diantar'] = date('Y-m-d H:i:s');
}

// Hitung sisa waktu (2 menit = 120 detik)
$sisa_waktu = null;
if($row['status_pesanan'] === 'diantar' && !empty($row['waktu_diantar'])){
    $sisa = strtotime($row['waktu_diantar']) + 120 - time();
    $sisa_waktu = max(0, $sisa);
}

echo json_encode([
    'status_pembayaran' => $row['status_pembayaran'],
    'status_pesanan'    => $row['status_pesanan'],
    'waktu_diantar'     => $row['waktu_diantar'],
    'sisa_waktu'        => $sisa_waktu,
]);
exit;
?>