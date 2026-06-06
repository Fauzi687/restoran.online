<?php
/**
 * get_struk.php
 * Dipanggil via fetch() dari transaksi.php
 * GET ?id=ID_TRANSAKSI
 * Response: JSON
 */

session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'msg' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'msg' => 'ID tidak valid']);
    exit;
}

// ── Data transaksi utama ──────────────────────────────────────────
$q = mysqli_query($conn,
    "SELECT * FROM transaksi WHERE id_transaksi = $id LIMIT 1"
);

if (!$q || mysqli_num_rows($q) === 0) {
    echo json_encode(['success' => false, 'msg' => 'Transaksi tidak ditemukan']);
    exit;
}

$trx = mysqli_fetch_assoc($q);

// ── Detail item pesanan ───────────────────────────────────────────
$items = [];

$qi = mysqli_query($conn,
    "SELECT dt.qty, dt.subtotal, m.nama_menu
     FROM detail_transaksi dt
     JOIN menu m ON m.id_menu = dt.id_menu
     WHERE dt.id_transaksi = $id"
);

if ($qi) {
    while ($r = mysqli_fetch_assoc($qi)) {
        $items[] = [
            'nama_menu' => $r['nama_menu'],
            'qty'       => (int) $r['qty'],
            'harga'     => (float) ($r['subtotal'] / max((int)$r['qty'], 1)),
        ];
    }
}

// ── Response ─────────────────────────────────────────────────────
echo json_encode([
    'success' => true,
    'data'    => [
        'no_pesanan'        => $trx['no_pesanan']        ?? '-',
        'nama_pelanggan'    => $trx['nama_pelanggan']    ?? '-',
        'nomor_meja'        => $trx['nomor_meja']        ?? '-',
        'metode_pembayaran' => $trx['metode_pembayaran'] ?? '-',
        'total'             => $trx['total']             ?? 0,
        'tanggal'           => $trx['tanggal']           ?? null,
        'catatan'           => $trx['catatan']           ?? '',
        'items'             => $items,
    ],
]);