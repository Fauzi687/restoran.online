<?php
include '../config/koneksi.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

// Otomatis selesaikan transaksi yang waktu diantarnya sudah habis (>= 3 menit)
mysqli_query($conn, "
    UPDATE transaksi
    SET status_pesanan = 'selesai', waktu_selesai = NOW()
    WHERE status_pesanan = 'diantar'
      AND waktu_diantar IS NOT NULL
      AND waktu_diantar != '0000-00-00 00:00:00'
      AND TIMESTAMPDIFF(SECOND, waktu_diantar, NOW()) >= 180
");

// Ambil semua status terkini untuk ditampilkan di admin
$res = mysqli_query($conn, "
    SELECT id_transaksi, status_pembayaran, status_pesanan
    FROM transaksi
    ORDER BY id_transaksi DESC
");

$rows = [];
while($row = mysqli_fetch_assoc($res)){
    $rows[] = [
        'id'     => (int)$row['id_transaksi'],
        'bayar'  => $row['status_pembayaran'],
        'pesan'  => $row['status_pesanan'],
    ];
}

echo json_encode(['rows' => $rows]);
exit;