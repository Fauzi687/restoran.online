<?php
session_start();
if(!isset($_SESSION['admin'])) header("Location: login.php");
include '../config/koneksi.php';
include "../qr/phpqrcode/qrlib.php";

$error = '';
$success = false;

if(isset($_POST['simpan'])){
    $nomor_meja = trim($_POST['nomor_meja']);

    // Cek duplikat
    $cek = mysqli_query($conn, "SELECT id_meja FROM meja WHERE nomor_meja='$nomor_meja'");
    if(mysqli_num_rows($cek) > 0){
        $error = 'Nomor meja <strong>' . htmlspecialchars($nomor_meja) . '</strong> sudah terdaftar. Gunakan nomor yang berbeda.';
    } else {
        $filename = "meja-".$nomor_meja.".png";
        $link = "https://sistemrestoonline.42web.io/customer/index.php?meja=".$nomor_meja;

        QRcode::png(
            $link,
            "../qr/meja/".$filename,
            QR_ECLEVEL_L,
            10
        );

        mysqli_query($conn,"
            INSERT INTO meja(nomor_meja, qr_code)
            VALUES('$nomor_meja','$filename')
        ");

        header("Location: meja.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Meja — Resto QR</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  :root {
    --bg-main: #0f0e1a;
    --bg-sidebar: #13121f;
    --bg-card: #1a1929;
    --bg-card2: #201f30;
    --accent: #7c5cbf;
    --accent2: #a07de0;
    --accent-glow: rgba(124,92,191,0.18);
    --text-main: #e8e6f0;
    --text-muted: #8884a0;
    --border: rgba(255,255,255,0.07);
    --sidebar-w: 240px;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: var(--bg-main);
    color: var(--text-main);
    font-family: 'Segoe UI', sans-serif;
    min-height: 100vh;
    display: flex;
  }

  /* SIDEBAR */
  .sidebar {
    width: var(--sidebar-w);
    background: var(--bg-sidebar);
    border-right: 1px solid var(--border);
    display: flex; flex-direction: column;
    position: fixed; top:0; left:0; bottom:0; z-index:100;
  }
  .sidebar-brand { padding:24px 20px 16px; border-bottom:1px solid var(--border); }
  .sidebar-brand .logo-text {
    font-size:1.15rem; font-weight:700; letter-spacing:.04em;
    background:linear-gradient(135deg,#fff 30%,var(--accent2));
    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
  }
  .sidebar-brand .logo-sub { font-size:.7rem; color:var(--text-muted); letter-spacing:.12em; text-transform:uppercase; margin-top:2px; }
  .sidebar-nav { padding:16px 12px; flex:1; overflow-y:auto; }
  .nav-label { font-size:.65rem; letter-spacing:.14em; text-transform:uppercase; color:var(--text-muted); padding:10px 8px 6px; }
  .sidebar-link {
    display:flex; align-items:center; gap:10px;
    padding:9px 12px; border-radius:8px;
    color:var(--text-muted); text-decoration:none;
    font-size:.875rem; transition:all .18s; margin-bottom:2px;
  }
  .sidebar-link:hover { background:var(--accent-glow); color:var(--text-main); }
  .sidebar-link.active {
    background:linear-gradient(135deg,rgba(124,92,191,.35),rgba(160,125,224,.15));
    color:#fff; border:1px solid rgba(124,92,191,.3);
  }
  .sidebar-link.active i { color:var(--accent2); }
  .sidebar-link i { font-size:1rem; width:18px; text-align:center; }
  .sidebar-footer { padding:14px 20px; border-top:1px solid var(--border); font-size:.75rem; color:var(--text-muted); }

  /* MAIN */
  .main-wrap { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; min-height:100vh; }
  .topbar {
    background:var(--bg-sidebar); border-bottom:1px solid var(--border);
    padding:14px 28px; display:flex; align-items:center; justify-content:space-between;
    position:sticky; top:0; z-index:90;
  }
  .topbar-title { font-size:1.1rem; font-weight:600; }
  .breadcrumb-bar { font-size:.75rem; color:var(--text-muted); margin-top:1px; }
  .breadcrumb-bar a { color:var(--accent2); text-decoration:none; }
  .topbar-right { display:flex; align-items:center; gap:14px; }
  .clock-badge {
    background:var(--bg-card2); border:1px solid var(--border);
    border-radius:8px; padding:5px 12px; font-size:.78rem;
    color:var(--text-muted); font-variant-numeric:tabular-nums;
  }
  .admin-avatar {
    width:34px; height:34px; border-radius:50%;
    background:linear-gradient(135deg,var(--accent),var(--accent2));
    display:flex; align-items:center; justify-content:center;
    font-size:.85rem; font-weight:700; color:#fff;
  }

  /* CONTENT */
  .content { padding:28px; flex:1; max-width:640px; }

  .form-card {
    background:var(--bg-card);
    border:1px solid var(--border);
    border-radius:16px;
    overflow:hidden;
    animation: fadeUp .3s ease;
  }
  @keyframes fadeUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:none; } }

  .form-card-header {
    padding:20px 24px;
    border-bottom:1px solid var(--border);
    display:flex; align-items:center; gap:12px;
  }
  .header-icon {
    width:40px; height:40px; border-radius:10px;
    background:var(--accent-glow); border:1px solid rgba(124,92,191,.25);
    display:flex; align-items:center; justify-content:center;
    color:var(--accent2); font-size:1.1rem;
  }
  .form-card-header h5 { font-size:1rem; font-weight:700; margin:0; }
  .form-card-header p { font-size:.76rem; color:var(--text-muted); margin:2px 0 0; }

  .form-card-body { padding:24px; }

  /* Info box */
  .info-box {
    background:rgba(96,165,250,.08);
    border:1px solid rgba(96,165,250,.2);
    border-radius:10px;
    padding:12px 16px;
    font-size:.8rem;
    color:#93c5fd;
    display:flex; align-items:flex-start; gap:10px;
    margin-bottom:22px;
  }
  .info-box i { font-size:1rem; margin-top:1px; flex-shrink:0; }

  /* Error box */
  .error-box {
    background:rgba(239,68,68,.08);
    border:1px solid rgba(239,68,68,.25);
    border-radius:10px;
    padding:12px 16px;
    font-size:.82rem;
    color:#fca5a5;
    display:flex; align-items:flex-start; gap:10px;
    margin-bottom:20px;
  }
  .error-box i { font-size:1rem; margin-top:1px; flex-shrink:0; }

  .form-label-dark { font-size:.8rem; font-weight:600; color:var(--text-muted); letter-spacing:.04em; text-transform:uppercase; margin-bottom:6px; display:block; }

  .input-dark {
    width:100%; background:var(--bg-card2);
    border:1px solid var(--border); border-radius:9px;
    color:var(--text-main); font-size:.9rem;
    padding:10px 14px; outline:none; transition:border-color .18s;
  }
  .input-dark:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(124,92,191,.15); }
  .input-dark::placeholder { color:var(--text-muted); }

  .input-hint { font-size:.72rem; color:var(--text-muted); margin-top:5px; }

  /* Preview link */
  .preview-link {
    background:var(--bg-card2);
    border:1px solid var(--border);
    border-radius:8px;
    padding:10px 14px;
    font-size:.75rem;
    color:var(--text-muted);
    margin-top:14px;
    word-break:break-all;
    display:none;
  }
  .preview-link span { color:var(--accent2); }

  .form-actions { display:flex; gap:10px; margin-top:24px; }

  .btn-save {
    background:linear-gradient(135deg,var(--accent),var(--accent2));
    color:#fff; border:none; border-radius:9px;
    padding:10px 24px; font-size:.875rem; font-weight:700;
    display:inline-flex; align-items:center; gap:7px;
    cursor:pointer; transition:opacity .18s;
  }
  .btn-save:hover { opacity:.88; }

  .btn-back {
    background:transparent;
    border:1px solid var(--border);
    color:var(--text-muted); border-radius:9px;
    padding:10px 18px; font-size:.875rem; font-weight:600;
    text-decoration:none; display:inline-flex; align-items:center; gap:7px;
    transition:border-color .18s;
  }
  .btn-back:hover { border-color:var(--accent2); color:var(--text-main); }

  /* QR Preview illustration */
  .qr-preview-box {
    margin-top:22px;
    background:var(--bg-card2);
    border:1px dashed rgba(124,92,191,.35);
    border-radius:12px;
    padding:20px;
    text-align:center;
    color:var(--text-muted);
    font-size:.8rem;
  }
  .qr-preview-box i { font-size:2.5rem; display:block; margin-bottom:8px; color:rgba(124,92,191,.4); }
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="logo-text">🍽 Resto QR</div>
    <div class="logo-sub">Admin Panel</div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-label">Utama</div>
    <a href="dashboard.php"     class="sidebar-link"><i class="bi bi-grid-1x2"></i> Dashboard</a>
    <div class="nav-label">Manajemen</div>
    <a href="kategori.php"      class="sidebar-link"><i class="bi bi-tags"></i> Kategori</a>
    <a href="menu.php"          class="sidebar-link"><i class="bi bi-journal-richtext"></i> Menu</a>
    <a href="meja.php"          class="sidebar-link active"><i class="bi bi-grid-3x3-gap"></i> Meja</a>
    <div class="nav-label">Operasional</div>
    <a href="pesanan_masuk.php" class="sidebar-link"><i class="bi bi-bag-check"></i> Pesanan Masuk</a>
    <a href="transaksi.php"     class="sidebar-link"><i class="bi bi-receipt"></i> Transaksi</a>
    <a href="bank.php"          class="sidebar-link"><i class="bi bi-bank2"></i> Bank</a>
    <a href="grafik.php"        class="sidebar-link"><i class="bi bi-bar-chart-line"></i> Grafik Penjualan</a>
    <div class="nav-label">Akun</div>
    <a href="logout.php"        class="sidebar-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
  </nav>
  <div class="sidebar-footer">v1.0 &middot; Resto QR System</div>
</aside>

<!-- MAIN -->
<div class="main-wrap">
  <div class="topbar">
    <div>
      <div class="topbar-title">Tambah Meja</div>
      <div class="breadcrumb-bar">
        <a href="dashboard.php">Dashboard</a> ›
        <a href="meja.php">Meja</a> ›
        Tambah Meja
      </div>
    </div>
    <div class="topbar-right">
      <div class="clock-badge"><i class="bi bi-clock me-1"></i><span id="clock">--:--:--</span></div>
      <div class="admin-avatar">A</div>
    </div>
  </div>

  <div class="content">
    <div class="form-card">
      <div class="form-card-header">
        <div class="header-icon"><i class="bi bi-plus-square-dotted"></i></div>
        <div>
          <h5>Tambah Meja Baru</h5>
          <p>QR Code akan digenerate otomatis setelah disimpan</p>
        </div>
      </div>
      <div class="form-card-body">

        <!-- Info box -->
        <div class="info-box">
          <i class="bi bi-info-circle-fill"></i>
          <div>
            Setiap meja akan mendapatkan QR Code unik. Customer bisa scan QR untuk langsung membuka menu dan memesan tanpa perlu aplikasi tambahan.
          </div>
        </div>

        <?php if($error): ?>
        <div class="error-box">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <div><?= $error ?></div>
        </div>
        <?php endif; ?>

        <form method="POST">

          <div class="mb-4">
            <label class="form-label-dark">Nomor Meja</label>
            <input type="text"
                   name="nomor_meja"
                   id="nomorMeja"
                   class="input-dark"
                   placeholder="Contoh: 1, 2, VIP-1, Teras-A"
                   value="<?= isset($_POST['nomor_meja']) ? htmlspecialchars($_POST['nomor_meja']) : '' ?>"
                   required
                   autocomplete="off">
            <div class="input-hint"><i class="bi bi-lightbulb me-1"></i>Bisa berupa angka atau teks singkat.</div>

            <!-- Live preview link -->
            <div class="preview-link" id="linkPreview">
              🔗 Link yang akan dibuat: <span id="linkText"></span>
            </div>
          </div>

          <!-- QR preview placeholder -->
          <div class="qr-preview-box" id="qrPlaceholder">
            <i class="bi bi-qr-code"></i>
            QR Code akan muncul di sini setelah meja disimpan
          </div>

          <div class="form-actions">
            <button type="submit" name="simpan" class="btn-save">
              <i class="bi bi-qr-code"></i> Generate & Simpan
            </button>
            <a href="meja.php" class="btn-back">
              <i class="bi bi-arrow-left"></i> Kembali
            </a>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Clock
function updateClock(){
  const now = new Date();
  document.getElementById('clock').textContent =
    now.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
}
updateClock(); setInterval(updateClock, 1000);

// Live link preview
const input = document.getElementById('nomorMeja');
const preview = document.getElementById('linkPreview');
const linkText = document.getElementById('linkText');

input.addEventListener('input', function(){
  const val = this.value.trim();
  if(val){
    linkText.textContent = 'https://sistemrestoonline.42web.io/customer/index.php?meja=' + val;
    preview.style.display = 'block';
  } else {
    preview.style.display = 'none';
  }
});

// Trigger on load if value exists (after error)
if(input.value) input.dispatchEvent(new Event('input'));
</script>
</body>
</html>