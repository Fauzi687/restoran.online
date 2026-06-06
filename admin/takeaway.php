<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

// ── Ambil daftar menu aktif ───────────────────────────────────────────────────
$menus_db = mysqli_query($conn, "
    SELECT m.id_menu, m.nama_menu, m.harga, m.stok, k.nama_kategori
    FROM menu m
    JOIN kategori k ON m.id_kategori = k.id_kategori
    WHERE m.status = 'aktif'
    ORDER BY k.nama_kategori, m.nama_menu
");
$menu_list = [];
while ($row = mysqli_fetch_assoc($menus_db)) $menu_list[] = $row;

// ── Simpan pesanan takeaway ───────────────────────────────────────────────────
$success_msg = $error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_pesanan'])) {
    $nama    = trim($_POST['nama_pelanggan'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');
    $items   = json_decode($_POST['items_json'] ?? '[]', true);
    $total   = (int)($_POST['total'] ?? 0);

    if (empty($nama) || empty($items)) {
        $error_msg = 'Nama pelanggan dan item pesanan wajib diisi.';
    } else {
        $no_pesanan = 'TKW-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        $metode     = 'tunai';
        $no_hp      = '';
        $bukti      = '';

        $stmt = mysqli_prepare($conn,
            "INSERT INTO transaksi
                (no_pesanan, nama_pelanggan, metode_pembayaran,
                 status_pembayaran, status_pesanan, total, catatan)
             VALUES (?, ?, 'tunai', 'dibayar', 'selesai', ?, ?)"
        );
        if (!$stmt) {
            $error_msg = 'Query error: ' . mysqli_error($conn);
        } else {
        mysqli_stmt_bind_param($stmt, 'ssis',
            $no_pesanan, $nama, $total, $catatan
        );

        if (mysqli_stmt_execute($stmt)) {
            $id_transaksi = mysqli_insert_id($conn);
            foreach ($items as $item) {
                $subtotal = (int)$item['qty'] * (int)$item['harga'];
                $st2 = mysqli_prepare($conn,
                    "INSERT INTO detail_transaksi (id_transaksi, id_menu, qty, subtotal)
                     VALUES (?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param($st2, 'iiii',
                    $id_transaksi, $item['id_menu'], $item['qty'], $subtotal);
                mysqli_stmt_execute($st2);
            }
            $success_msg = "Pesanan <strong>$no_pesanan</strong> berhasil disimpan!";
        } else {
            $error_msg = 'Gagal menyimpan: ' . mysqli_error($conn);
        }
        } // tutup else cek stmt
    }
}

// ── Riwayat 30 takeaway terakhir ─────────────────────────────────────────────
$riwayat = mysqli_query($conn, "
    SELECT t.id_transaksi, t.no_pesanan, t.nama_pelanggan,
           t.total, t.status_pembayaran, t.tanggal,
           COUNT(dt.id_detail) AS jml_item
    FROM transaksi t
    LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    WHERE t.no_pesanan LIKE 'TKW-%'
    GROUP BY t.id_transaksi
    ORDER BY t.tanggal DESC
    LIMIT 30
");
if (!$riwayat) die("Query riwayat error: " . mysqli_error($conn));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Takeaway – Resto App</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg:        #0e0c22;
      --surface:   #18163a;
      --surface2:  #1f1c42;
      --border:    rgba(255,255,255,0.07);
      --accent:    #a855f7;
      --accent-lo: rgba(168,85,247,0.15);
      --accent-hi: #c084fc;
      --green:     #34d399;
      --green-lo:  rgba(52,211,153,0.12);
      --red:       #f87171;
      --red-lo:    rgba(248,113,113,0.12);
      --amber:     #fbbf24;
      --txt:       #e2e0f0;
      --txt-sub:   rgba(226,224,240,0.45);
      --radius:    14px;
      --font:      'DM Sans', sans-serif;
      --mono:      'DM Mono', monospace;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: var(--bg);
      color: var(--txt);
      font-family: var(--font);
      min-height: 100vh;
      display: flex;
    }

    /* ── Layout ── */
    .page-wrap {
      margin-left: 240px;
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* ── Topbar ── */
    .topbar {
      padding: 1.25rem 2rem;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: var(--bg);
      position: sticky; top: 0; z-index: 50;
    }
    .topbar-title {
      display: flex; align-items: center; gap: 12px;
    }
    .topbar-icon {
      width: 40px; height: 40px;
      background: var(--accent-lo);
      border: 1px solid rgba(168,85,247,0.3);
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.15rem; color: var(--accent);
    }
    .topbar-title h1 { font-size: 1.1rem; font-weight: 600; }
    .topbar-title p  { font-size: 0.75rem; color: var(--txt-sub); margin-top: 1px; }

    /* ── Main content grid ── */
    .main-content {
      display: grid;
      grid-template-columns: 1fr 360px;
      gap: 1.5rem;
      padding: 1.75rem 2rem;
      flex: 1;
    }

    /* ── Section titles ── */
    .section-title {
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      color: var(--txt-sub);
      margin-bottom: 0.9rem;
    }

    /* ── Search & filter ── */
    .search-bar {
      display: flex; gap: 8px; margin-bottom: 1rem;
    }
    .search-input {
      flex: 1;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 0.55rem 0.9rem 0.55rem 2.4rem;
      color: var(--txt);
      font-family: var(--font);
      font-size: 0.875rem;
      outline: none;
      transition: border-color 0.15s;
      position: relative;
    }
    .search-wrap { position: relative; flex: 1; }
    .search-wrap i {
      position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);
      color: var(--txt-sub); font-size: 0.9rem; pointer-events: none;
    }
    .search-input:focus { border-color: rgba(168,85,247,0.4); }

    .filter-tabs {
      display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 1.25rem;
    }
    .filter-tab {
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      border: 1px solid var(--border);
      background: var(--surface);
      color: var(--txt-sub);
      font-size: 0.775rem; font-weight: 500;
      cursor: pointer; transition: all 0.15s;
    }
    .filter-tab:hover, .filter-tab.active {
      background: var(--accent-lo);
      border-color: rgba(168,85,247,0.35);
      color: var(--accent-hi);
    }

    /* ── Menu grid ── */
    .menu-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
      gap: 10px;
    }
    .menu-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1rem;
      cursor: pointer;
      transition: border-color 0.15s, transform 0.12s, background 0.15s;
      position: relative;
      overflow: hidden;
    }
    .menu-card:hover {
      border-color: rgba(168,85,247,0.35);
      background: var(--surface2);
      transform: translateY(-1px);
    }
    .menu-card.in-cart {
      border-color: rgba(168,85,247,0.5);
      background: rgba(168,85,247,0.06);
    }
    .menu-card .cat-badge {
      font-size: 0.62rem; font-weight: 600; text-transform: uppercase;
      letter-spacing: 0.8px;
      color: var(--accent-hi); background: var(--accent-lo);
      padding: 2px 7px; border-radius: 20px;
      display: inline-block; margin-bottom: 0.5rem;
    }
    .menu-card .menu-name {
      font-size: 0.875rem; font-weight: 600; line-height: 1.3;
      margin-bottom: 0.3rem;
    }
    .menu-card .menu-price {
      font-size: 0.8rem; font-weight: 500;
      color: var(--green); font-family: var(--mono);
    }
    .menu-card .add-btn {
      position: absolute; bottom: 10px; right: 10px;
      width: 26px; height: 26px;
      background: var(--accent);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 0.85rem;
      opacity: 0; transition: opacity 0.15s;
    }
    .menu-card:hover .add-btn { opacity: 1; }
    .menu-card.in-cart .add-btn { opacity: 1; background: var(--green); }

    /* ── Right Panel (Keranjang + Form) ── */
    .right-panel {
      display: flex; flex-direction: column; gap: 1rem;
    }

    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      overflow: hidden;
    }
    .card-header {
      padding: 0.85rem 1.1rem;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .card-header span { font-size: 0.875rem; font-weight: 600; }
    .card-body { padding: 1rem 1.1rem; }

    /* Cart */
    .cart-empty {
      text-align: center; padding: 2rem 1rem;
      color: var(--txt-sub); font-size: 0.85rem;
    }
    .cart-empty i { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; opacity: 0.3; }

    .cart-item {
      display: flex; align-items: center; gap: 10px;
      padding: 0.55rem 0;
      border-bottom: 1px solid var(--border);
    }
    .cart-item:last-child { border-bottom: none; }
    .cart-item-name { flex: 1; font-size: 0.825rem; font-weight: 500; }
    .cart-item-price { font-size: 0.775rem; color: var(--txt-sub); font-family: var(--mono); }
    .qty-ctrl {
      display: flex; align-items: center; gap: 6px;
    }
    .qty-btn {
      width: 24px; height: 24px;
      background: var(--surface2); border: 1px solid var(--border);
      border-radius: 7px; color: var(--txt);
      cursor: pointer; font-size: 0.85rem;
      display: flex; align-items: center; justify-content: center;
      transition: background 0.12s;
    }
    .qty-btn:hover { background: var(--accent-lo); border-color: rgba(168,85,247,0.3); color: var(--accent-hi); }
    .qty-num { font-size: 0.85rem; font-weight: 600; width: 20px; text-align: center; font-family: var(--mono); }

    .cart-total {
      display: flex; justify-content: space-between; align-items: center;
      padding: 0.75rem 0 0;
      font-weight: 700; font-size: 0.9rem;
    }
    .cart-total .amount { color: var(--green); font-family: var(--mono); font-size: 1rem; }

    /* Form */
    .form-group { margin-bottom: 0.85rem; }
    .form-label {
      font-size: 0.72rem; font-weight: 600; text-transform: uppercase;
      letter-spacing: 0.8px; color: var(--txt-sub);
      display: block; margin-bottom: 5px;
    }
    .form-control {
      width: 100%;
      background: var(--surface2);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 0.55rem 0.85rem;
      color: var(--txt);
      font-family: var(--font);
      font-size: 0.875rem;
      outline: none;
      transition: border-color 0.15s;
    }
    .form-control:focus { border-color: rgba(168,85,247,0.45); }
    .form-control::placeholder { color: var(--txt-sub); }
    textarea.form-control { resize: vertical; min-height: 60px; }
    select.form-control option { background: #1f1c42; }

    .kembalian-row {
      display: flex; justify-content: space-between; align-items: center;
      background: var(--green-lo); border: 1px solid rgba(52,211,153,0.2);
      border-radius: 10px; padding: 0.55rem 0.85rem;
      margin-top: 0.5rem; margin-bottom: 0.85rem;
      font-size: 0.825rem;
    }
    .kembalian-row .val { font-family: var(--mono); font-weight: 700; color: var(--green); }

    /* Buttons */
    .btn {
      display: inline-flex; align-items: center; gap: 7px;
      padding: 0.6rem 1.1rem;
      border-radius: 10px;
      font-family: var(--font); font-size: 0.85rem; font-weight: 600;
      cursor: pointer; border: none; transition: all 0.15s;
      text-decoration: none;
    }
    .btn-primary {
      background: var(--accent); color: #fff; width: 100%; justify-content: center;
    }
    .btn-primary:hover { background: #9333ea; }
    .btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }
    .btn-outline {
      background: transparent;
      border: 1px solid var(--border);
      color: var(--txt-sub); font-size: 0.775rem;
    }
    .btn-outline:hover { border-color: var(--red); color: var(--red); }

    /* Badge */
    .badge {
      display: inline-flex; align-items: center; justify-content: center;
      min-width: 20px; height: 20px; padding: 0 5px;
      background: var(--accent); color: #fff;
      border-radius: 20px; font-size: 0.7rem; font-weight: 700;
    }

    /* Alert */
    .alert {
      border-radius: var(--radius); padding: 0.85rem 1.1rem;
      font-size: 0.85rem; display: flex; align-items: flex-start; gap: 10px;
      margin: 0 2rem 1rem;
    }
    .alert-success { background: var(--green-lo); border: 1px solid rgba(52,211,153,0.25); color: var(--green); }
    .alert-danger  { background: var(--red-lo);   border: 1px solid rgba(248,113,113,0.25); color: var(--red); }

    /* ── Riwayat section ── */
    .riwayat-section { padding: 0 2rem 2rem; }
    .riwayat-table-wrap { overflow-x: auto; }
    table.riwayat {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.825rem;
    }
    table.riwayat th {
      padding: 0.6rem 1rem;
      text-align: left;
      font-size: 0.65rem; font-weight: 600;
      text-transform: uppercase; letter-spacing: 0.9px;
      color: var(--txt-sub);
      border-bottom: 1px solid var(--border);
    }
    table.riwayat td {
      padding: 0.7rem 1rem;
      border-bottom: 1px solid var(--border);
      vertical-align: middle;
    }
    table.riwayat tr:last-child td { border-bottom: none; }
    table.riwayat tr:hover td { background: rgba(255,255,255,0.02); }
    .status-pill {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 10px; border-radius: 20px;
      font-size: 0.7rem; font-weight: 600;
    }
    .pill-selesai { background: var(--green-lo); color: var(--green); }
    .pill-pending { background: rgba(251,191,36,0.12); color: var(--amber); }
    .kode-tag {
      font-family: var(--mono); font-size: 0.78rem;
      color: var(--accent-hi); background: var(--accent-lo);
      padding: 2px 8px; border-radius: 6px;
    }

    /* ── Print modal ── */
    .modal-overlay {
      position: fixed; inset: 0;
      background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
      z-index: 200;
      display: none; align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 18px;
      padding: 2rem;
      width: 360px; max-width: 95vw;
      position: relative;
    }
    .modal-close {
      position: absolute; top: 1rem; right: 1rem;
      background: none; border: none; color: var(--txt-sub);
      font-size: 1.2rem; cursor: pointer;
    }
    .modal-close:hover { color: var(--red); }
    .receipt {
      font-family: var(--mono);
      font-size: 0.8rem;
      line-height: 1.7;
      color: var(--txt);
    }
    .receipt hr { border: none; border-top: 1px dashed var(--border); margin: 0.5rem 0; }
    .receipt .r-title { text-align: center; font-weight: 700; font-size: 1rem; margin-bottom: 4px; }
    .receipt .r-sub   { text-align: center; color: var(--txt-sub); font-size: 0.72rem; }
    .receipt .r-row   { display: flex; justify-content: space-between; }
    .receipt .r-item  { padding-left: 0.5rem; }

    @media (max-width: 900px) {
      .main-content { grid-template-columns: 1fr; }
      .right-panel { order: -1; }
      .page-wrap { margin-left: 0; }
    }

    @media print {
      body * { visibility: hidden; }
      #print-area, #print-area * { visibility: visible; }
      #print-area { position: fixed; top: 0; left: 0; }
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="page-wrap">

  <!-- Topbar -->
  <div class="topbar">
    <div class="topbar-title">
      <div class="topbar-icon"><i class="bi bi-bag-check"></i></div>
      <div>
        <h1>Takeaway</h1>
        <p>Pesanan bungkus / dibawa pulang</p>
      </div>
    </div>
    <div style="font-size:0.8rem;color:var(--txt-sub);">
      <?= date('l, d M Y') ?>
    </div>
  </div>

  <!-- Alert -->
  <?php if ($success_msg): ?>
    <div class="alert alert-success">
      <i class="bi bi-check-circle-fill" style="flex-shrink:0;margin-top:1px;"></i>
      <span><?= $success_msg ?></span>
    </div>
  <?php endif; ?>
  <?php if ($error_msg): ?>
    <div class="alert alert-danger">
      <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:1px;"></i>
      <span><?= $error_msg ?></span>
    </div>
  <?php endif; ?>

  <!-- Main grid -->
  <div class="main-content">

    <!-- LEFT: Pilih menu -->
    <div>
      <div class="section-title">Pilih Item</div>

      <div class="search-bar">
        <div class="search-wrap" style="flex:1;">
          <i class="bi bi-search"></i>
          <input type="text" class="search-input" id="searchInput" placeholder="Cari nama menu…">
        </div>
      </div>

      <div class="filter-tabs">
        <button class="filter-tab active" data-cat="semua">Semua</button>
        <?php foreach ($menu_list as $m): ?>
          <?php
            static $shown_cats = [];
            if (!in_array($m['nama_kategori'], $shown_cats)) {
              $shown_cats[] = $m['nama_kategori'];
          ?>
          <button class="filter-tab" data-cat="<?= htmlspecialchars($m['nama_kategori']) ?>">
            <?= htmlspecialchars($m['nama_kategori']) ?>
          </button>
          <?php } ?>
        <?php endforeach; ?>
      </div>

      <div class="menu-grid" id="menuGrid">
        <?php foreach ($menu_list as $m): ?>
        <div class="menu-card"
             data-id="<?= $m['id_menu'] ?>"
             data-nama="<?= htmlspecialchars($m['nama_menu']) ?>"
             data-harga="<?= $m['harga'] ?>"
             data-cat="<?= htmlspecialchars($m['nama_kategori']) ?>"
             onclick="addToCart(this)">
          <span class="cat-badge"><?= htmlspecialchars($m['nama_kategori']) ?></span>
          <div class="menu-name"><?= htmlspecialchars($m['nama_menu']) ?></div>
          <div class="menu-price">Rp <?= number_format($m['harga'], 0, ',', '.') ?></div>
          <div class="add-btn"><i class="bi bi-plus"></i></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- RIGHT: Keranjang + Form -->
    <div class="right-panel">

      <!-- Keranjang -->
      <div class="card">
        <div class="card-header">
          <span><i class="bi bi-bag" style="color:var(--accent);margin-right:6px;"></i>Keranjang</span>
          <span class="badge" id="cartCount">0</span>
        </div>
        <div class="card-body">
          <div id="cartEmpty" class="cart-empty">
            <i class="bi bi-bag-x"></i>
            Belum ada item dipilih
          </div>
          <div id="cartItems"></div>
          <div id="cartTotalRow" class="cart-total" style="display:none;">
            <span>Total</span>
            <span class="amount" id="cartTotalAmt">Rp 0</span>
          </div>
        </div>
      </div>

      <!-- Form Pesanan -->
      <div class="card">
        <div class="card-header">
          <span><i class="bi bi-person-lines-fill" style="color:var(--accent);margin-right:6px;"></i>Data Pesanan</span>
        </div>
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="simpan_pesanan" value="1">
            <input type="hidden" name="items_json" id="itemsJson">
            <input type="hidden" name="total" id="totalHidden">

            <div class="form-group">
              <label class="form-label">Nama Pelanggan *</label>
              <input type="text" name="nama_pelanggan" class="form-control" placeholder="Contoh: Budi Santoso" required>
            </div>

            <div class="form-group">
              <label class="form-label">Catatan</label>
              <textarea name="catatan" class="form-control" placeholder="Misal: tidak pedas, tanpa bawang…"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" id="btnSimpan" disabled>
              <i class="bi bi-bag-check-fill"></i> Simpan Pesanan
            </button>
          </form>
        </div>
      </div>

    </div>
  </div><!-- /main-content -->

  <!-- Riwayat Takeaway -->
  <div class="riwayat-section">
    <div class="section-title">Riwayat Takeaway (30 terakhir)</div>
    <div class="card">
      <div class="riwayat-table-wrap">
        <table class="riwayat">
          <thead>
            <tr>
              <th>No Pesanan</th>
              <th>Pelanggan</th>
              <th>Item</th>
              <th>Total</th>
              <th>Waktu</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($riwayat && mysqli_num_rows($riwayat) > 0): ?>
              <?php while ($r = mysqli_fetch_assoc($riwayat)):
                $tgl     = $r['tanggal'] ?? '';
                $tgl_f   = $tgl ? date('d M Y', strtotime($tgl)) : '-';
                $jam_f   = $tgl ? date('H:i',   strtotime($tgl)) : '';
                $sp      = $r['status_pembayaran'] ?? 'pending';
                $badge   = match($sp) {
                  'dibayar' => '<span class="status-pill pill-selesai"><i class="bi bi-check-circle-fill"></i> Lunas</span>',
                  'gagal'   => '<span class="status-pill" style="background:var(--red-lo);color:var(--red);"><i class="bi bi-x-circle-fill"></i> Gagal</span>',
                  default   => '<span class="status-pill" style="background:rgba(251,191,36,0.12);color:var(--amber);"><i class="bi bi-hourglass-split"></i> Pending</span>',
                };
              ?>
              <tr>
                <td><span class="kode-tag"><?= htmlspecialchars($r['no_pesanan'] ?? '-') ?></span></td>
                <td style="font-weight:500;"><?= htmlspecialchars($r['nama_pelanggan'] ?? '-') ?></td>
                <td style="color:var(--txt-sub);"><?= $r['jml_item'] ?> item</td>
                <td style="font-family:var(--mono);color:var(--green);">
                  Rp <?= number_format($r['total'], 0, ',', '.') ?>
                </td>
                <td style="color:var(--txt-sub);font-size:0.775rem;">
                  <div><?= $tgl_f ?></div>
                  <?php if ($jam_f): ?><div style="font-size:0.7rem;opacity:.6;"><i class="bi bi-clock me-1"></i><?= $jam_f ?></div><?php endif; ?>
                </td>
                <td><?= $badge ?></td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" style="text-align:center;padding:2rem;color:var(--txt-sub);">
                  <i class="bi bi-bag-x" style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
                  Belum ada transaksi takeaway
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div><!-- /page-wrap -->

<!-- Modal Cetak Struk -->
<div class="modal-overlay" id="modalStruk">
  <div class="modal-box">
    <button class="modal-close" onclick="document.getElementById('modalStruk').classList.remove('open')">
      <i class="bi bi-x-lg"></i>
    </button>
    <div id="print-area">
      <div class="receipt" id="receiptContent"></div>
    </div>
    <div style="display:flex;gap:8px;margin-top:1.25rem;">
      <button class="btn btn-primary" onclick="window.print()" style="flex:1;">
        <i class="bi bi-printer-fill"></i> Cetak
      </button>
      <button class="btn btn-outline" onclick="document.getElementById('modalStruk').classList.remove('open')">
        Tutup
      </button>
    </div>
  </div>
</div>

<script>
// ── Cart state ────────────────────────────────────────────────────────────────
let cart = {}; // { id_menu: { nama, harga, qty } }

function fmt(n) {
  return 'Rp ' + parseInt(n).toLocaleString('id-ID');
}

function addToCart(el) {
  const id    = el.dataset.id;
  const nama  = el.dataset.nama;
  const harga = parseInt(el.dataset.harga);

  if (cart[id]) {
    cart[id].qty++;
  } else {
    cart[id] = { nama, harga, qty: 1 };
  }
  el.classList.add('in-cart');
  renderCart();
}

function changeQty(id, delta) {
  if (!cart[id]) return;
  cart[id].qty += delta;
  if (cart[id].qty <= 0) {
    delete cart[id];
    // Remove in-cart class from card
    const card = document.querySelector(`.menu-card[data-id="${id}"]`);
    if (card) card.classList.remove('in-cart');
  }
  renderCart();
}

function renderCart() {
  const keys   = Object.keys(cart);
  const count  = keys.reduce((s, k) => s + cart[k].qty, 0);
  const total  = keys.reduce((s, k) => s + cart[k].qty * cart[k].harga, 0);

  document.getElementById('cartCount').textContent = count;
  document.getElementById('cartEmpty').style.display   = keys.length ? 'none'  : 'block';
  document.getElementById('cartTotalRow').style.display = keys.length ? 'flex' : 'none';
  document.getElementById('cartTotalAmt').textContent  = fmt(total);
  document.getElementById('totalHidden').value = total;
  document.getElementById('itemsJson').value   = JSON.stringify(
    keys.map(k => ({ id_menu: k, nama: cart[k].nama, harga: cart[k].harga, qty: cart[k].qty }))
  );
  document.getElementById('btnSimpan').disabled = keys.length === 0;

  const container = document.getElementById('cartItems');
  container.innerHTML = keys.map(k => `
    <div class="cart-item">
      <div>
        <div class="cart-item-name">${cart[k].nama}</div>
        <div class="cart-item-price">${fmt(cart[k].harga)} × ${cart[k].qty} = ${fmt(cart[k].harga * cart[k].qty)}</div>
      </div>
      <div class="qty-ctrl">
        <button class="qty-btn" onclick="changeQty('${k}', -1)"><i class="bi bi-dash"></i></button>
        <span class="qty-num">${cart[k].qty}</span>
        <button class="qty-btn" onclick="changeQty('${k}', 1)"><i class="bi bi-plus"></i></button>
      </div>
    </div>
  `).join('');

  hitungKembalian();
}


function lihatBukti(src) {
  const m = document.getElementById('buktiModal');
  document.getElementById('buktiImg').src = src;
  m.style.display = 'flex';
}
function tutupBukti() {
  document.getElementById('buktiModal').style.display = 'none';
  document.getElementById('buktiImg').src = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupBukti(); });

// ── Filter kategori ───────────────────────────────────────────────────────────
document.querySelectorAll('.filter-tab').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filterMenu();
  });
});

document.getElementById('searchInput').addEventListener('input', filterMenu);

function filterMenu() {
  const cat    = document.querySelector('.filter-tab.active').dataset.cat;
  const q      = document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('.menu-card').forEach(card => {
    const matchCat  = (cat === 'semua') || (card.dataset.cat === cat);
    const matchQ    = card.dataset.nama.toLowerCase().includes(q);
    card.style.display = (matchCat && matchQ) ? '' : 'none';
  });
}

// ── Cetak Struk via AJAX ──────────────────────────────────────────────────────
function cetakStruk(id) {
  fetch(`takeaway_detail.php?id=${id}`)
    .then(r => r.json())
    .then(data => {
      const lines = data.items.map(i =>
        `<div class="r-row r-item"><span>${i.nama} x${i.qty}</span><span>${fmt(i.subtotal)}</span></div>`
      ).join('');

      document.getElementById('receiptContent').innerHTML = `
        <div class="r-title">RESTO APP</div>
        <div class="r-sub">TAKEAWAY ORDER</div>
        <hr>
        <div class="r-row"><span>Kode</span><span>${data.kode_transaksi}</span></div>
        <div class="r-row"><span>Customer</span><span>${data.nama_customer}</span></div>
        <div class="r-row"><span>Tanggal</span><span>${data.created_at}</span></div>
        <hr>
        ${lines}
        <hr>
        <div class="r-row"><strong>TOTAL</strong><strong>${fmt(data.total)}</strong></div>
        ${data.bayar > 0 ? `<div class="r-row"><span>Bayar</span><span>${fmt(data.bayar)}</span></div>` : ''}
        ${data.bayar > 0 ? `<div class="r-row"><span>Kembalian</span><span>${fmt(data.bayar - data.total)}</span></div>` : ''}
        <hr>
        ${data.catatan ? `<div style="color:var(--txt-sub);font-size:0.75rem;">Catatan: ${data.catatan}</div>` : ''}
        <div style="text-align:center;margin-top:8px;color:var(--txt-sub);font-size:0.72rem;">Terima kasih!</div>
      `;
      document.getElementById('modalStruk').classList.add('open');
    })
    .catch(() => alert('Gagal memuat data struk.'));
}
</script>

<!-- Modal Lihat Bukti -->
<div id="buktiModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.88);
            align-items:center;justify-content:center;z-index:9000;cursor:pointer;"
     onclick="tutupBukti()">
  <button onclick="tutupBukti()"
          style="position:fixed;top:16px;right:18px;background:rgba(255,255,255,0.1);
                 border:1px solid rgba(255,255,255,0.15);border-radius:50%;width:34px;
                 height:34px;font-size:1rem;cursor:pointer;color:#fff;display:flex;
                 align-items:center;justify-content:center;">
    <i class="bi bi-x"></i>
  </button>
  <img id="buktiImg" src="" alt="Bukti"
       style="max-width:90vw;max-height:88vh;border-radius:14px;
              box-shadow:0 20px 60px rgba(0,0,0,.6);cursor:default;"
       onclick="event.stopPropagation()">
</div>

</body>
</html>