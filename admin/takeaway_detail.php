<?php
/**
 * takeaway_detail.php
 * AJAX endpoint – kembalikan JSON detail transaksi takeaway untuk cetak struk.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

require_once '../config.php';

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['error' => 'ID tidak valid']);
    exit;
}

// Ambil header transaksi
$stmt = $conn->prepare("
    SELECT t.kode_transaksi, t.nama_customer, t.no_hp, t.catatan,
           t.total, t.bayar, t.created_at,
           b.nama_bank
    FROM transaksi t
    LEFT JOIN bank b ON t.id_bank = b.id_bank
    WHERE t.id_transaksi = ? AND t.tipe = 'takeaway'
    LIMIT 1
");
$stmt->bind_param('i', $id);
$stmt->execute();
$trx = $stmt->get_result()->fetch_assoc();

if (!$trx) {
    echo json_encode(['error' => 'Transaksi tidak ditemukan']);
    exit;
}

// Ambil detail item
$stmt2 = $conn->prepare("
    SELECT m.nama_menu AS nama, dt.qty, dt.harga_satuan AS harga, dt.subtotal
    FROM detail_transaksi dt
    JOIN menu m ON dt.id_menu = m.id_menu
    WHERE dt.id_transaksi = ?
");
$stmt2->bind_param('i', $id);
$stmt2->execute();
$result = $stmt2->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode([
    'kode_transaksi' => $trx['kode_transaksi'],
    'nama_customer'  => $trx['nama_customer'],
    'no_hp'          => $trx['no_hp'],
    'catatan'        => $trx['catatan'],
    'total'          => (int)$trx['total'],
    'bayar'          => (int)$trx['bayar'],
    'nama_bank'      => $trx['nama_bank'] ?? 'Tunai',
    'created_at'     => date('d M Y H:i', strtotime($trx['created_at'])),
    'items'          => $items,
]);