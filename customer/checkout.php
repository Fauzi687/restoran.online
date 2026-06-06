<?php
session_start();
include '../config/koneksi.php';

$meja = $_GET['meja'];
$total = 0;

if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

foreach($_SESSION['cart'] as $id => $qty){
    $data = mysqli_query($conn,"SELECT * FROM menu WHERE id_menu='$id'");
    $row = mysqli_fetch_array($data);
    $total += $row['harga'] * $qty;
}

if(isset($_POST['checkout'])){
    foreach($_SESSION['cart'] as $id => $qty){
        $cek = mysqli_query($conn,"SELECT * FROM menu WHERE id_menu='$id'");
        $c = mysqli_fetch_array($cek);
        if($qty > $c['stok']){
            echo "<script>alert('Menu ".$c['nama_menu']." tinggal ".$c['stok'].". Mohon pilih menu yang lain');window.location='cart.php?meja=$meja';</script>";
            exit;
        }
    }

    $nama       = trim($_POST['nama']);
    $pembayaran = $_POST['pembayaran'];
    $catatan    = $_POST['catatan'];
    $bukti      = '';

    if(empty($nama)){
        echo "<script>alert('Nama pelanggan wajib diisi');window.location='checkout.php?meja=$meja';</script>";
        exit;
    }

    if(empty($pembayaran) || !in_array($pembayaran, ['QRIS','Tunai'])){
        echo "<script>alert('Silakan pilih metode pembayaran');window.location='checkout.php?meja=$meja';</script>";
        exit;
    }

    if($pembayaran == 'QRIS'){
        if(empty($_FILES['bukti_pembayaran']['name'])){
            echo "<script>alert('Pembayaran QRIS wajib upload bukti pembayaran');window.location='checkout.php?meja=$meja';</script>";
            exit;
        }
        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION));
        if(!in_array($ext, $allowed)){
            echo "<script>alert('Format file tidak didukung. Gunakan JPG, PNG, atau JPEG');window.location='checkout.php?meja=$meja';</script>";
            exit;
        }
        if($_FILES['bukti_pembayaran']['size'] > 5 * 1024 * 1024){
            echo "<script>alert('Ukuran file terlalu besar. Maksimal 5MB');window.location='checkout.php?meja=$meja';</script>";
            exit;
        }
    }

    $status_pesanan = 'menunggu';

    if(!empty($_FILES['bukti_pembayaran']['name'])){
        $folder = __DIR__ . '/../uploads/bukti/';
        if(!is_dir($folder)) mkdir($folder, 0755, true);
        $ext   = strtolower(pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION));
        $bukti = time() . '_' . uniqid() . '.' . $ext;
        $upload_result = move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $folder . $bukti);
        if(!$upload_result){
            $err = error_get_last();
            echo "<script>alert('Gagal upload bukti: ".addslashes($err['message'] ?? 'Coba lagi')."');window.location='checkout.php?meja=$meja';</script>";
            exit;
        }
    }

    $no_pesanan = "ORD".rand(1000,9999);

    $insert = mysqli_query($conn,"
        INSERT INTO transaksi(no_pesanan,nama_pelanggan,nomor_meja,catatan,metode_pembayaran,bukti_pembayaran,status_pembayaran,status_pesanan,total)
        VALUES('$no_pesanan','$nama','$meja','$catatan','$pembayaran','$bukti','pending','$status_pesanan','$total')
    ");

    if(!$insert) die("Query Error: ".mysqli_error($conn));

    $id_transaksi = mysqli_insert_id($conn);

    foreach($_SESSION['cart'] as $id => $qty){
        $menu = mysqli_query($conn,"SELECT * FROM menu WHERE id_menu='$id'");
        $m    = mysqli_fetch_array($menu);
        $sub  = $m['harga'] * $qty;
        mysqli_query($conn,"INSERT INTO detail_transaksi(id_transaksi,id_menu,qty,subtotal) VALUES('$id_transaksi','$id','$qty','$sub')");
        $stok_baru = $m['stok'] - $qty;
        $st = $stok_baru <= 0 ? 'nonaktif' : 'aktif';
        mysqli_query($conn,"UPDATE menu SET stok='$stok_baru',status='$st' WHERE id_menu='$id'");
    }

    unset($_SESSION['cart']);
    header("Location: success.php?order=$no_pesanan&meja=$meja&metode=$pembayaran");
    exit;
}

$bank = mysqli_query($conn,"SELECT * FROM bank ORDER BY id_bank DESC LIMIT 1");
$b    = mysqli_fetch_array($bank);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout — Meja <?= htmlspecialchars($meja) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --brand:      #FF6B35;
  --brand-2:    #FF9F1C;
  --brand-dk:   #e05620;
  --brand-glow: rgba(255,107,53,.2);
  --bg:         #FFF8F5;
  --surface:    #FFFFFF;
  --surface-2:  #FFF3EE;
  --border:     rgba(255,107,53,.12);
  --border-2:   rgba(0,0,0,.07);
  --text-1:     #1A1108;
  --text-2:     #6B5B4E;
  --text-3:     #B8A89E;
  --success:    #16A34A;
  --warn:       #D97706;
  --danger:     #DC2626;
  --radius:     18px;
  --radius-sm:  12px;
  --shadow:     0 4px 24px rgba(255,107,53,.1), 0 1px 4px rgba(0,0,0,.06);
}

html, body { min-height: 100%; }
body {
  font-family: 'Plus Jakarta Sans', sans-serif;
  background: var(--bg);
  background-image:
    radial-gradient(ellipse at 10% 0%, rgba(255,107,53,.08) 0%, transparent 50%),
    radial-gradient(ellipse at 90% 100%, rgba(255,159,28,.07) 0%, transparent 50%);
  color: var(--text-1);
}

.page-wrap { max-width: 900px; margin: 0 auto; padding: 1.25rem 1rem 3rem; }

.back-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.1rem; gap: .5rem; }
.btn-back {
  display: inline-flex; align-items: center; gap: 6px;
  background: var(--surface); border: 1px solid var(--border-2);
  border-radius: var(--radius-sm); color: var(--text-2);
  font-size: .82rem; font-weight: 600;
  padding: .42rem .9rem; text-decoration: none;
  transition: background .15s, color .15s; white-space: nowrap;
}
.btn-back:hover { background: var(--surface-2); color: var(--brand); }
.meja-badge {
  display: inline-flex; align-items: center; gap: 5px;
  background: linear-gradient(135deg, var(--brand), var(--brand-2));
  color: #fff; font-size: .72rem; font-weight: 700;
  letter-spacing: .04em; border-radius: 20px;
  padding: .28rem .85rem; white-space: nowrap;
}

.page-title { font-size: clamp(1.2rem, 4vw, 1.6rem); font-weight: 800; margin-bottom: .2rem; }
.page-sub   { font-size: .82rem; color: var(--text-2); margin-bottom: 1.25rem; }

.checkout-grid { display: grid; grid-template-columns: 1fr 360px; gap: 1.1rem; align-items: start; }

.card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--radius); box-shadow: var(--shadow);
  overflow: hidden; margin-bottom: 1rem;
}
.card:last-child { margin-bottom: 0; }
.card-head { padding: .8rem 1.1rem; border-bottom: 1px solid rgba(0,0,0,.05); display: flex; align-items: center; gap: 9px; }
.card-head-icon {
  width: 30px; height: 30px; border-radius: 8px;
  background: var(--surface-2);
  display: flex; align-items: center; justify-content: center;
  color: var(--brand); font-size: .95rem; flex-shrink: 0;
}
.card-head-title { font-size: .86rem; font-weight: 700; }
.card-body { padding: 1rem 1.1rem; }

.order-item {
  display: flex; align-items: center; gap: .75rem;
  padding: .65rem 0; border-bottom: 1px solid rgba(0,0,0,.05);
}
.order-item:last-child { border-bottom: none; padding-bottom: 0; }
.order-item:first-child { padding-top: 0; }
.item-img { width: 54px; height: 54px; border-radius: 11px; object-fit: cover; flex-shrink: 0; border: 1px solid var(--border); }
.item-img-placeholder {
  width: 54px; height: 54px; border-radius: 11px; flex-shrink: 0;
  background: var(--surface-2); border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  color: var(--text-3); font-size: 1.2rem;
}
.item-info { flex: 1; min-width: 0; }
.item-name { font-size: .86rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-qty  { font-size: .74rem; color: var(--text-3); margin-top: 2px; }
.item-qty span { background: var(--surface-2); border-radius: 5px; padding: 1px 6px; font-weight: 600; color: var(--text-2); }
.item-price { font-size: .86rem; font-weight: 700; color: var(--brand); white-space: nowrap; }

.fgroup { margin-bottom: .9rem; }
.fgroup:last-child { margin-bottom: 0; }
.flabel { display: block; font-size: .73rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--text-2); margin-bottom: 5px; }
.finput, .ftextarea {
  width: 100%; background: var(--surface-2);
  border: 1.5px solid transparent; border-radius: var(--radius-sm);
  padding: .7rem .95rem;
  font-family: 'Plus Jakarta Sans', sans-serif; font-size: .875rem; color: var(--text-1);
  outline: none; transition: border-color .18s, box-shadow .18s;
}
.finput::placeholder, .ftextarea::placeholder { color: var(--text-3); }
.finput:focus, .ftextarea:focus { border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-glow); background: #fff; }
.ftextarea { resize: none; min-height: 75px; }
.finput.is-error { border-color: var(--danger) !important; box-shadow: 0 0 0 3px rgba(220,38,38,.15) !important; background: #fff5f5 !important; }

.field-error { font-size: .72rem; color: var(--danger); margin-top: 4px; display: none; align-items: center; gap: 4px; }
.field-error.show { display: flex; }

.pay-options { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin-bottom: .5rem; }
.pay-option { display: none; }
.pay-label {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 5px; padding: .8rem .5rem;
  background: var(--surface-2); border: 2px solid transparent;
  border-radius: var(--radius-sm); cursor: pointer;
  transition: border-color .18s, background .18s; text-align: center;
}
.pay-label i { font-size: 1.35rem; color: var(--text-3); transition: color .18s; }
.pay-label span { font-size: .76rem; font-weight: 700; color: var(--text-2); transition: color .18s; }
.pay-option:checked + .pay-label { border-color: var(--brand); background: rgba(255,107,53,.07); }
.pay-option:checked + .pay-label i,
.pay-option:checked + .pay-label span { color: var(--brand); }
.pay-options.is-error .pay-label { border-color: rgba(220,38,38,.4); background: #fff5f5; }

.pay-info { display: none; margin-top: .75rem; }
.pay-info.visible { display: block; animation: slideIn .22s ease; }
@keyframes slideIn { from { opacity:0; transform:translateY(-5px); } to { opacity:1; transform:translateY(0); } }

.qris-box {
  background: #F0FDF4; border: 1.5px solid rgba(22,163,74,.2);
  border-radius: var(--radius-sm); padding: 1rem;
}
.qris-title { font-size: .82rem; font-weight: 700; color: var(--success); margin-bottom: .35rem; display: flex; align-items: center; gap: 6px; }
.qris-sub   { font-size: .76rem; color: #4b7c5a; margin-bottom: .75rem; }
.qris-img-wrap { text-align: center; margin-bottom: .65rem; }
.qris-img-wrap img { max-width: 190px; width: 100%; border-radius: 11px; box-shadow: 0 4px 14px rgba(0,0,0,.1); }
.qris-owner { text-align: center; font-size: .78rem; color: #4b7c5a; margin-bottom: .75rem; }
.qris-owner strong { color: var(--success); }

.tunai-box {
  background: #FFFBEB; border: 1.5px solid rgba(217,119,6,.2);
  border-radius: var(--radius-sm); padding: .9rem 1rem;
}
.tunai-title { font-size: .82rem; font-weight: 700; color: var(--warn); margin-bottom: .25rem; display: flex; align-items: center; gap: 6px; }
.tunai-sub   { font-size: .76rem; color: #92692a; }

.file-upload-label { font-size: .73rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--success); display: block; margin: .75rem 0 5px; }
.file-drop-zone {
  border: 2px dashed rgba(22,163,74,.35); border-radius: var(--radius-sm);
  background: #fff; padding: 1rem; text-align: center; cursor: pointer;
  transition: border-color .2s, background .2s; position: relative;
}
.file-drop-zone:hover, .file-drop-zone.dragover { border-color: var(--success); background: #f0fdf4; }
.file-drop-zone.is-error { border-color: var(--danger); background: #fff5f5; }
.file-drop-zone.has-file  { border-color: var(--success); background: #f0fdf4; border-style: solid; }
.file-drop-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.drop-icon { font-size: 1.6rem; color: rgba(22,163,74,.5); margin-bottom: .35rem; display: block; }
.drop-text { font-size: .78rem; color: #4b7c5a; font-weight: 600; }
.drop-sub  { font-size: .7rem; color: #86a88e; margin-top: 3px; }

.file-preview {
  display: none; align-items: center; gap: 10px;
  background: #fff; border: 1px solid rgba(22,163,74,.25);
  border-radius: 10px; padding: .6rem .85rem; margin-top: .5rem;
}
.file-preview.show { display: flex; }
.fp-thumb { width: 40px; height: 40px; border-radius: 7px; object-fit: cover; border: 1px solid rgba(0,0,0,.08); flex-shrink: 0; }
.fp-info  { flex: 1; min-width: 0; }
.fp-name  { font-size: .78rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fp-size  { font-size: .68rem; color: var(--text-3); margin-top: 2px; }
.fp-remove {
  width: 24px; height: 24px; border-radius: 6px;
  background: rgba(220,38,38,.1); color: var(--danger);
  border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;
  font-size: .8rem; flex-shrink: 0; transition: background .15s;
}
.fp-remove:hover { background: rgba(220,38,38,.2); }
.file-hint { font-size: .71rem; color: var(--text-3); margin-top: 4px; }

.toast-wrap {
  position: fixed; top: 1rem; left: 50%; transform: translateX(-50%);
  z-index: 9999; display: flex; flex-direction: column; gap: .5rem;
  pointer-events: none; width: calc(100% - 2rem); max-width: 400px;
}
.toast {
  background: #1e1e28; color: #fff; border-radius: 12px; padding: .75rem 1rem;
  display: flex; align-items: center; gap: 10px;
  box-shadow: 0 8px 32px rgba(0,0,0,.25);
  animation: toastIn .3s ease both; pointer-events: auto;
}
.toast.error   { border-left: 3px solid var(--danger); }
.toast.success { border-left: 3px solid var(--success); }
@keyframes toastIn  { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
@keyframes toastOut { from { opacity:1; } to { opacity:0; transform:translateY(-8px); } }
.toast i { font-size: 1.1rem; flex-shrink: 0; }
.toast.error   i { color: #f87171; }
.toast.success i { color: #4ade80; }
.toast-msg { font-size: .82rem; font-weight: 500; flex: 1; }

.summary-card { position: sticky; top: 1rem; }
.summary-row { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .42rem 0; font-size: .82rem; color: var(--text-2); border-bottom: 1px dashed rgba(0,0,0,.07); }
.summary-row:last-of-type { border-bottom: none; }
.summary-row.total { padding-top: .7rem; margin-top: .2rem; border-top: 2px solid var(--border); font-size: 1rem; font-weight: 800; color: var(--text-1); }
.summary-row.total .s-val { color: var(--brand); }
.s-key { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.s-val { font-weight: 600; white-space: nowrap; }

.btn-order {
  width: 100%; padding: .88rem;
  background: linear-gradient(135deg, var(--brand), var(--brand-2));
  border: none; border-radius: var(--radius-sm);
  color: #fff; font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .95rem; font-weight: 800; cursor: pointer;
  box-shadow: 0 4px 18px var(--brand-glow);
  transition: opacity .2s, transform .15s;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  margin-top: .75rem; position: relative; overflow: hidden;
}
.btn-order::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(255,255,255,.14) 0%, transparent 60%); }
.btn-order:hover { opacity: .92; transform: translateY(-1px); }
.btn-order:active { transform: scale(.98); }
.btn-order:disabled { opacity: .55; cursor: not-allowed; transform: none; }

@media (max-width: 720px) {
  .checkout-grid { grid-template-columns: 1fr; }
  .summary-card { position: static; order: 2; }
  .left-col { order: 1; }
  .page-wrap { padding: 1rem .85rem 2.5rem; }
}
@media (max-width: 480px) {
  body { background: #fff; }
  .page-wrap { padding: 0 0 2rem; }
  .mobile-header { display: flex !important; background: linear-gradient(135deg, var(--brand), var(--brand-2)); padding: 1.1rem 1rem .9rem; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
  .mobile-header-title { color: #fff; font-weight: 800; font-size: 1rem; display: flex; align-items: center; gap: 7px; }
  .back-bar, .page-title, .page-sub { display: none; }
  .inner-pad { padding: 1rem; }
  .card { border-radius: var(--radius-sm); box-shadow: none; border: 1px solid rgba(0,0,0,.07); margin-bottom: .75rem; }
  .card-head { padding: .7rem .9rem; }
  .card-body { padding: .85rem .9rem; }
  .item-img, .item-img-placeholder { width: 46px; height: 46px; }
  .btn-order { font-size: .9rem; padding: .85rem; }
  .finput, .ftextarea { font-size: 16px; }
}
.mobile-header { display: none; }
.inner-pad { display: contents; }
</style>
</head>
<body>

<div class="toast-wrap" id="toastWrap"></div>

<div class="mobile-header">
  <div class="mobile-header-title"><i class="bi bi-bag-check"></i> Checkout</div>
  <div class="meja-badge"><i class="bi bi-grid-3x3-gap-fill"></i> Meja <?= htmlspecialchars($meja) ?></div>
</div>

<div class="page-wrap">

  <div class="back-bar">
    <a href="cart.php?meja=<?= htmlspecialchars($meja) ?>" class="btn-back">
      <i class="bi bi-arrow-left"></i> Kembali ke Keranjang
    </a>
    <div class="meja-badge"><i class="bi bi-grid-3x3-gap-fill"></i> Meja <?= htmlspecialchars($meja) ?></div>
  </div>

  <div class="page-title"><i class="bi bi-bag-check" style="color:var(--brand)"></i> Checkout Pesanan</div>
  <div class="page-sub">Periksa kembali pesanan sebelum dikirim ke dapur 🍽️</div>

  <form method="POST" enctype="multipart/form-data" id="checkoutForm" novalidate>
  <div class="checkout-grid">

    <div class="left-col">
    <div class="inner-pad">

      <!-- Detail Pesanan -->
      <div class="card">
        <div class="card-head">
          <div class="card-head-icon"><i class="bi bi-receipt"></i></div>
          <div class="card-head-title">Detail Pesanan</div>
        </div>
        <div class="card-body">
          <?php foreach($_SESSION['cart'] as $id => $qty):
            $data = mysqli_query($conn,"SELECT * FROM menu WHERE id_menu='$id'");
            $row  = mysqli_fetch_array($data);
            $sub  = $row['harga'] * $qty;
            $foto = '../uploads/menu/' . $row['foto'];
          ?>
          <div class="order-item">
            <?php if($row['foto'] && file_exists($foto)): ?>
              <img src="<?= $foto ?>" alt="" class="item-img">
            <?php else: ?>
              <div class="item-img-placeholder"><i class="bi bi-image"></i></div>
            <?php endif; ?>
            <div class="item-info">
              <div class="item-name"><?= htmlspecialchars($row['nama_menu']) ?></div>
              <div class="item-qty">Qty: <span><?= $qty ?>x</span></div>
            </div>
            <div class="item-price">Rp <?= number_format($sub, 0, ',', '.') ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Info Pelanggan -->
      <div class="card">
        <div class="card-head">
          <div class="card-head-icon"><i class="bi bi-person"></i></div>
          <div class="card-head-title">Informasi Pelanggan</div>
        </div>
        <div class="card-body">
          <div class="fgroup">
            <label class="flabel" for="nama">
              Nama Pelanggan <span style="color:var(--danger)">*</span>
            </label>
            <input type="text" id="nama" name="nama" class="finput"
                   placeholder="Masukkan nama Anda" autocomplete="name">
            <div class="field-error" id="err-nama">
              <i class="bi bi-exclamation-circle-fill"></i> Nama pelanggan wajib diisi
            </div>
          </div>
          <div class="fgroup">
            <label class="flabel" for="catatan">Catatan Pesanan</label>
            <textarea id="catatan" name="catatan" class="ftextarea"
                      placeholder="Contoh: tidak pedas, sambal dipisah, tanpa es…"></textarea>
          </div>
        </div>
      </div>

      <!-- Metode Pembayaran -->
      <div class="card">
        <div class="card-head">
          <div class="card-head-icon"><i class="bi bi-credit-card"></i></div>
          <div class="card-head-title">Metode Pembayaran <span style="color:var(--danger)">*</span></div>
        </div>
        <div class="card-body">
          <div class="pay-options" id="payOptions">
            <div>
              <input type="radio" name="pembayaran" id="pay-qris" value="QRIS" class="pay-option">
              <label for="pay-qris" class="pay-label">
                <i class="bi bi-qr-code-scan"></i><span>QRIS</span>
              </label>
            </div>
            <div>
              <input type="radio" name="pembayaran" id="pay-tunai" value="Tunai" class="pay-option">
              <label for="pay-tunai" class="pay-label">
                <i class="bi bi-cash-coin"></i><span>Tunai</span>
              </label>
            </div>
          </div>
          <div class="field-error" id="err-pay">
            <i class="bi bi-exclamation-circle-fill"></i> Silakan pilih metode pembayaran
          </div>

          <!-- QRIS -->
          <div class="pay-info" id="qris-box">
            <div class="qris-box">
              <div class="qris-title"><i class="bi bi-qr-code-scan"></i> Pembayaran QRIS</div>
              <div class="qris-sub">Scan kode QR berikut lalu upload bukti pembayaran.</div>
              <?php if($b): ?>
                <div class="qris-img-wrap">
                  <img src="../uploads/bank/<?= htmlspecialchars($b['qris']) ?>"
                       alt="QRIS <?= htmlspecialchars($b['nama_pemilik']) ?>">
                </div>
                <div class="qris-owner">Atas Nama: <strong><?= htmlspecialchars($b['nama_pemilik']) ?></strong></div>
              <?php else: ?>
                <p style="color:#b91c1c;font-size:.8rem">Data QRIS belum tersedia.</p>
              <?php endif; ?>
              <label class="file-upload-label">
                Upload Bukti Pembayaran <span style="color:var(--danger)">*</span>
              </label>
              <div class="file-drop-zone" id="dropZone">
                <input type="file" name="bukti_pembayaran" id="buktiInput"
                       accept="image/jpeg,image/png,image/webp">
                <span class="drop-icon"><i class="bi bi-cloud-upload"></i></span>
                <div class="drop-text">Klik atau drag foto bukti transfer</div>
                <div class="drop-sub">JPG, PNG, WEBP · Maks. 5 MB</div>
              </div>
              <div class="field-error" id="err-bukti">
                <i class="bi bi-exclamation-circle-fill"></i> Bukti pembayaran wajib diupload
              </div>
              <div class="file-preview" id="filePreview">
                <img src="" alt="" class="fp-thumb" id="fpThumb">
                <div class="fp-info">
                  <div class="fp-name" id="fpName">—</div>
                  <div class="fp-size" id="fpSize">—</div>
                </div>
                <button type="button" class="fp-remove" id="fpRemove" title="Hapus file">
                  <i class="bi bi-x"></i>
                </button>
              </div>
              <div class="file-hint"><i class="bi bi-info-circle"></i> Screenshot atau foto bukti transfer yang jelas</div>
            </div>
          </div>

          <!-- Tunai -->
          <div class="pay-info" id="tunai-box">
            <div class="tunai-box">
              <div class="tunai-title"><i class="bi bi-cash-coin"></i> Pembayaran Tunai</div>
              <div class="tunai-sub">Silakan menuju kasir dan lakukan pembayaran. Admin akan memvalidasi pesanan Anda.</div>
            </div>
          </div>
        </div>
      </div>

    </div>
    </div>

    <!-- RIGHT -->
    <div class="summary-card">
    <div class="inner-pad">
      <div class="card">
        <div class="card-head">
          <div class="card-head-icon"><i class="bi bi-bag"></i></div>
          <div class="card-head-title">Ringkasan Pesanan</div>
        </div>
        <div class="card-body">
          <?php
          $itemCount = 0;
          foreach($_SESSION['cart'] as $id => $qty):
            $d2  = mysqli_query($conn,"SELECT * FROM menu WHERE id_menu='$id'");
            $row = mysqli_fetch_array($d2);
            $itemCount += $qty;
          ?>
          <div class="summary-row">
            <span class="s-key"><?= htmlspecialchars($row['nama_menu']) ?> ×<?= $qty ?></span>
            <span class="s-val">Rp <?= number_format($row['harga'] * $qty, 0, ',', '.') ?></span>
          </div>
          <?php endforeach; ?>
          <div class="summary-row total">
            <span class="s-key">Total (<?= $itemCount ?> item)</span>
            <span class="s-val">Rp <?= number_format($total, 0, ',', '.') ?></span>
          </div>

          <!-- Tombol type="button" — tidak langsung submit -->
          <button type="button" class="btn-order" id="btnOrder" onclick="submitCheckout()">
            <i class="bi bi-send-fill"></i> Pesan Sekarang
          </button>

          <!-- Submit asli tersembunyi — hanya dipanggil setelah validasi lolos -->
          <button type="submit" name="checkout" id="realSubmit" style="display:none"></button>
        </div>
      </div>
    </div>
    </div>

  </div>
  </form>
</div>

<script>
const form       = document.getElementById('checkoutForm');
const namaInput  = document.getElementById('nama');
const payOptions = document.getElementById('payOptions');
const qrisRadio  = document.getElementById('pay-qris');
const tunaiRadio = document.getElementById('pay-tunai');
const qrisBox    = document.getElementById('qris-box');
const tunaiBox   = document.getElementById('tunai-box');
const dropZone   = document.getElementById('dropZone');
const buktiInput = document.getElementById('buktiInput');
const filePreview= document.getElementById('filePreview');
const fpThumb    = document.getElementById('fpThumb');
const fpName     = document.getElementById('fpName');
const fpSize     = document.getElementById('fpSize');
const fpRemove   = document.getElementById('fpRemove');
const btnOrder   = document.getElementById('btnOrder');
const toastWrap  = document.getElementById('toastWrap');

/* ── Toast ── */
function showToast(msg, type = 'error'){
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = `<i class="bi bi-${type === 'error' ? 'exclamation-circle-fill' : 'check-circle-fill'}"></i>
                 <span class="toast-msg">${msg}</span>`;
  toastWrap.appendChild(t);
  setTimeout(() => {
    t.style.animation = 'toastOut .3s ease forwards';
    setTimeout(() => t.remove(), 300);
  }, 3500);
}

/* ── Error helpers ── */
function setError(inputEl, errId, show){
  if(inputEl) inputEl.classList.toggle('is-error', show);
  const e = document.getElementById(errId);
  if(e) e.classList.toggle('show', show);
}
function setPayError(show){
  payOptions.classList.toggle('is-error', show);
  document.getElementById('err-pay').classList.toggle('show', show);
}
function setDropError(show){
  dropZone.classList.toggle('is-error', show);
  document.getElementById('err-bukti').classList.toggle('show', show);
}

/* ── Payment toggle ── */
function updatePayUI(){
  qrisBox.classList.toggle('visible', qrisRadio.checked);
  tunaiBox.classList.toggle('visible', tunaiRadio.checked);
  setPayError(false);
  setDropError(false);
}
qrisRadio.addEventListener('change', updatePayUI);
tunaiRadio.addEventListener('change', updatePayUI);

/* ── File helpers ── */
function formatBytes(b){
  if(b < 1024) return b + ' B';
  if(b < 1048576) return (b/1024).toFixed(1) + ' KB';
  return (b/1048576).toFixed(1) + ' MB';
}
function showPreview(file){
  const r = new FileReader();
  r.onload = e => { fpThumb.src = e.target.result; };
  r.readAsDataURL(file);
  fpName.textContent = file.name;
  fpSize.textContent = formatBytes(file.size);
  filePreview.classList.add('show');
  dropZone.classList.add('has-file');
  setDropError(false);
}
function clearFile(){
  buktiInput.value = '';
  fpThumb.src = '';
  fpName.textContent = '—';
  fpSize.textContent = '—';
  filePreview.classList.remove('show');
  dropZone.classList.remove('has-file');
}

buktiInput.addEventListener('change', function(){
  if(this.files && this.files[0]){
    const file = this.files[0];
    if(!['image/jpeg','image/png','image/webp'].includes(file.type)){
      showToast('Format tidak didukung. Gunakan JPG, PNG, atau WEBP');
      clearFile(); return;
    }
    if(file.size > 5 * 1024 * 1024){
      showToast('Ukuran file terlalu besar. Maksimal 5 MB');
      clearFile(); return;
    }
    showPreview(file);
  }
});
fpRemove.addEventListener('click', clearFile);

/* ── Drag & drop ── */
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
dropZone.addEventListener('drop', e => {
  e.preventDefault(); dropZone.classList.remove('dragover');
  const file = e.dataTransfer.files[0];
  if(!file) return;
  const dt = new DataTransfer();
  dt.items.add(file);
  buktiInput.files = dt.files;
  buktiInput.dispatchEvent(new Event('change'));
});

namaInput.addEventListener('input', function(){
  if(this.value.trim()) setError(this, 'err-nama', false);
});

/* ── VALIDASI — dipanggil saat tombol diklik ── */
function submitCheckout(){
  let valid = true;
  const errors = [];

  // 1. Nama
  if(!namaInput.value.trim()){
    setError(namaInput, 'err-nama', true);
    errors.push('Nama pelanggan wajib diisi');
    valid = false;
  } else {
    setError(namaInput, 'err-nama', false);
  }

  // 2. Metode pembayaran
  const paySelected = document.querySelector('input[name="pembayaran"]:checked');
  if(!paySelected){
    setPayError(true);
    errors.push('Silakan pilih metode pembayaran');
    valid = false;
  } else {
    setPayError(false);
  }

  // 3. Bukti QRIS
  if(paySelected && paySelected.value === 'QRIS'){
    if(!buktiInput.files || buktiInput.files.length === 0){
      setDropError(true);
      errors.push('Bukti pembayaran QRIS wajib diupload');
      valid = false;
    } else {
      setDropError(false);
    }
  }

  // Ada error → tampilkan toast, scroll ke error, STOP
  if(!valid){
    errors.forEach((msg, i) => setTimeout(() => showToast(msg), i * 200));
    const firstErr = form.querySelector('.is-error, .pay-options.is-error, .file-drop-zone.is-error');
    if(firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }

  // Semua valid → loading state → submit ke PHP
  btnOrder.disabled = true;
  btnOrder.innerHTML = '<i class="bi bi-hourglass-split"></i> Mengirim Pesanan…';
  document.getElementById('realSubmit').click();
}
</script>
</body>
</html>