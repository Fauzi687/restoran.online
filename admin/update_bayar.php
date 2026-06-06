<?php
session_start();
include '../config/koneksi.php';

header('Content-Type: application/json');

if(!isset($_GET['id']) || !isset($_GET['status'])){
    echo json_encode(['success'=>false,'msg'=>'Parameter tidak lengkap']);
    exit;
}

$id     = intval($_GET['id']);
$status = $_GET['status'];

if(!in_array($status, ['dibayar','gagal'])){
    echo json_encode(['success'=>false,'msg'=>'Status tidak valid']);
    exit;
}

$q1 = mysqli_query($conn, "UPDATE transaksi SET status_pembayaran='$status' WHERE id_transaksi=$id");

if(!$q1){
    echo json_encode(['success'=>false,'msg'=>'Gagal: '.mysqli_error($conn)]);
    exit;
}

if($status === 'dibayar'){
    mysqli_query($conn, "UPDATE transaksi SET status_pesanan='diproses' WHERE id_transaksi=$id");
    $status_pesanan_baru = 'diproses';
} else {
    mysqli_query($conn, "UPDATE transaksi SET status_pesanan='gagal' WHERE id_transaksi=$id");
    $status_pesanan_baru = 'gagal';
}

echo json_encode([
    'success'           => true,
    'status_pembayaran' => $status,
    'status_pesanan'    => $status_pesanan_baru,
]);
exit;