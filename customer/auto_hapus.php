<?php
include '../config/koneksi.php';

// hapus transaksi selesai lebih dari 15 menit

mysqli_query($conn,"
DELETE FROM transaksi
WHERE status_pesanan='selesai'
AND tanggal < NOW() - INTERVAL 15 MINUTE
");
?>