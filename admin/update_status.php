<?php
include '../config/koneksi.php';

if(!isset($_GET['id']) || !isset($_GET['status'])){
    die("Parameter tidak lengkap");
}

$id     = (int)$_GET['id'];
$status = mysqli_real_escape_string($conn, $_GET['status']);

if($status === 'diantar'){
    $sql = "UPDATE transaksi 
            SET status_pesanan='diantar', waktu_diantar=NOW() 
            WHERE id_transaksi=$id";
} elseif($status === 'selesai'){
    $sql = "UPDATE transaksi 
            SET status_pesanan='selesai', waktu_selesai=NOW() 
            WHERE id_transaksi=$id";
} else {
    $sql = "UPDATE transaksi 
            SET status_pesanan='$status' 
            WHERE id_transaksi=$id";
}

$result = mysqli_query($conn, $sql);

// Debug — hapus setelah berhasil
if(!$result){
    die("Query Error: " . mysqli_error($conn));
}

header("Location: transaksi.php");
exit;
?>