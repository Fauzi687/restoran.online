<?php
include '../config/koneksi.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

$mode = $_GET['mode'] ?? 'harian';
$tgl  = $_GET['tgl']  ?? date('Y-m-d');
$tgl  = preg_replace('/[^0-9\-]/', '', $tgl); // sanitasi
if (!$tgl) $tgl = date('Y-m-d');

$result = [];

if ($mode === 'harian') {
    // Per jam — gunakan $tgl (bisa hari lain, bukan hanya CURDATE)
    $res = mysqli_query($conn, "
        SELECT
            HOUR(tanggal) AS jam,
            COUNT(*) AS jumlah_transaksi,
            SUM(total) AS total_pendapatan
        FROM transaksi
        WHERE DATE(tanggal) = '$tgl'
          AND status_pesanan = 'selesai'
        GROUP BY HOUR(tanggal)
        ORDER BY jam ASC
    ");

    $data = [];
    for ($i = 0; $i < 24; $i++) {
        $data[$i] = ['label' => sprintf('%02d:00', $i), 'jumlah' => 0, 'total' => 0];
    }
    while ($row = mysqli_fetch_assoc($res)) {
        $jam = (int)$row['jam'];
        $data[$jam]['jumlah'] = (int)$row['jumlah_transaksi'];
        $data[$jam]['total']  = (float)$row['total_pendapatan'];
    }

    $result['labels'] = array_column($data, 'label');
    $result['jumlah'] = array_column($data, 'jumlah');
    $result['total']  = array_column($data, 'total');

    $sum = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) AS trx, IFNULL(SUM(total),0) AS rev
        FROM transaksi
        WHERE DATE(tanggal) = '$tgl' AND status_pesanan = 'selesai'
    "));
    $result['summary_trx'] = (int)$sum['trx'];
    $result['summary_rev'] = (float)$sum['rev'];
    $result['tgl']         = $tgl;

} elseif ($mode === 'mingguan') {
    $res = mysqli_query($conn, "
        SELECT
            DATE(tanggal) AS tgl,
            COUNT(*) AS jumlah_transaksi,
            SUM(total) AS total_pendapatan
        FROM transaksi
        WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
          AND status_pesanan = 'selesai'
        GROUP BY DATE(tanggal)
        ORDER BY tgl ASC
    ");

    $raw = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $raw[$row['tgl']] = ['jumlah' => (int)$row['jumlah_transaksi'], 'total' => (float)$row['total_pendapatan']];
    }

    $hari_id = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $labels = []; $jumlah = []; $total = [];
    for ($i = 6; $i >= 0; $i--) {
        $t   = date('Y-m-d', strtotime("-$i days"));
        $dow = (int)date('w', strtotime($t));
        $labels[] = $hari_id[$dow] . ' ' . date('d/m', strtotime($t));
        $jumlah[] = $raw[$t]['jumlah'] ?? 0;
        $total[]  = $raw[$t]['total']  ?? 0;
    }

    $result['labels'] = $labels;
    $result['jumlah'] = $jumlah;
    $result['total']  = $total;

    $sum = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) AS trx, IFNULL(SUM(total),0) AS rev
        FROM transaksi
        WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
          AND status_pesanan = 'selesai'
    "));
    $result['summary_trx'] = (int)$sum['trx'];
    $result['summary_rev'] = (float)$sum['rev'];

} elseif ($mode === 'bulanan') {
    $res = mysqli_query($conn, "
        SELECT
            DATE_FORMAT(tanggal,'%Y-%m') AS bulan,
            COUNT(*) AS jumlah_transaksi,
            SUM(total) AS total_pendapatan
        FROM transaksi
        WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
          AND status_pesanan = 'selesai'
        GROUP BY bulan
        ORDER BY bulan ASC
    ");

    $raw = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $raw[$row['bulan']] = ['jumlah' => (int)$row['jumlah_transaksi'], 'total' => (float)$row['total_pendapatan']];
    }

    $bulan_id = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
    $labels = []; $jumlah = []; $total = [];
    for ($i = 11; $i >= 0; $i--) {
        $key    = date('Y-m', strtotime("-$i months"));
        [$y,$m] = explode('-', $key);
        $labels[] = $bulan_id[(int)$m] . ' ' . $y;
        $jumlah[] = $raw[$key]['jumlah'] ?? 0;
        $total[]  = $raw[$key]['total']  ?? 0;
    }

    $result['labels'] = $labels;
    $result['jumlah'] = $jumlah;
    $result['total']  = $total;

    $sum = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) AS trx, IFNULL(SUM(total),0) AS rev
        FROM transaksi
        WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
          AND status_pesanan = 'selesai'
    "));
    $result['summary_trx'] = (int)$sum['trx'];
    $result['summary_rev'] = (float)$sum['rev'];
}

// Menu terlaris
$top = mysqli_query($conn, "
    SELECT m.nama_menu, SUM(dt.qty) AS total_qty
    FROM detail_transaksi dt
    JOIN menu m ON m.id_menu = dt.id_menu
    JOIN transaksi t ON t.id_transaksi = dt.id_transaksi
    WHERE t.status_pesanan = 'selesai'
    GROUP BY dt.id_menu, m.nama_menu
    ORDER BY total_qty DESC
    LIMIT 5
");
$result['top_menu'] = [];
if ($top) {
    while ($r = mysqli_fetch_assoc($top)) {
        $result['top_menu'][] = ['nama' => $r['nama_menu'], 'qty' => (int)$r['total_qty']];
    }
}

if (mysqli_error($conn)) $result['db_error'] = mysqli_error($conn);

echo json_encode($result);
exit;