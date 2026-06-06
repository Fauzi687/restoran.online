<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit;
}

if(isset($_POST['simpan'])){
    $nama   = $_POST['nama_kategori'];
    $status = $_POST['status'];

    mysqli_query($conn,"
        INSERT INTO kategori(nama_kategori, status)
        VALUES('$nama','$status')
    ");

    header("Location: kategori.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Kategori — Resto App</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  :root {
    --bg-base:      #0f0e1a;
    --bg-card:      #1a1826;
    --bg-sidebar:   #13111f;
    --bg-input:     #231f35;
    --bg-hover:     #2a2640;
    --border:       rgba(255,255,255,0.07);
    --accent:       #7c5cfc;
    --accent-dim:   rgba(124,92,252,0.15);
    --accent-hover: #9b82fd;
    --text-primary: #e8e4f8;
    --text-muted:   #7b7897;
    --success:      #22c55e;
    --danger:       #ef4444;
    --radius:       12px;
    --radius-sm:    8px;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: var(--bg-base);
    color: var(--text-primary);
    font-family: 'Segoe UI', system-ui, sans-serif;
    min-height: 100vh;
    display: flex;
  }

  /* SIDEBAR */
  .sidebar {
    width: 240px; min-height: 100vh;
    background: var(--bg-sidebar);
    border-right: 1px solid var(--border);
    display: flex; flex-direction: column;
    padding: 24px 16px;
    position: fixed; top: 0; left: 0; bottom: 0; z-index: 100;
  }
  .sidebar-brand {
    display: flex; align-items: center; gap: 10px;
    padding: 0 8px 20px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 20px;
  }
  .brand-icon {
    width: 36px; height: 36px; background: var(--accent);
    border-radius: 10px; display: grid; place-items: center; font-size: 18px;
  }
  .sidebar-brand h5 { font-size: 15px; font-weight: 700; color: var(--text-primary); margin: 0; }
  .sidebar-label {
    font-size: 10px; font-weight: 600; letter-spacing: 1.2px;
    text-transform: uppercase; color: var(--text-muted);
    padding: 0 8px; margin: 16px 0 6px;
  }
  .nav-link {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 12px; border-radius: var(--radius-sm);
    color: var(--text-muted); font-size: 13.5px; font-weight: 500;
    text-decoration: none; transition: all .18s; margin-bottom: 2px;
  }
  .nav-link:hover  { background: var(--bg-hover); color: var(--text-primary); }
  .nav-link.active { background: var(--accent-dim); color: var(--accent-hover); }
  .nav-link i { font-size: 15px; width: 18px; text-align: center; }
  .sidebar-footer { margin-top: auto; border-top: 1px solid var(--border); padding-top: 16px; }
  .user-info { display: flex; align-items: center; gap: 10px; padding: 8px 10px; }
  .user-avatar {
    width: 34px; height: 34px; background: var(--accent);
    border-radius: 50%; display: grid; place-items: center; font-size: 14px; font-weight: 700;
  }
  .user-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
  .user-role { font-size: 11px; color: var(--text-muted); }

  /* TOPBAR */
  .topbar {
    position: fixed; top: 0; left: 240px; right: 0; height: 60px;
    background: var(--bg-card); border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 28px; z-index: 99;
  }
  .breadcrumb-nav { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted); }
  .breadcrumb-nav a { color: var(--text-muted); text-decoration: none; transition: color .15s; }
  .breadcrumb-nav a:hover { color: var(--accent-hover); }
  .breadcrumb-nav .sep { opacity: .4; }
  .breadcrumb-nav .current { color: var(--text-primary); font-weight: 600; }

  /* MAIN */
  .main { margin-left: 240px; padding-top: 60px; flex: 1; }
  .page-content { padding: 28px 32px; max-width: 620px; }
  .page-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px;
  }
  .page-title {
    font-size: 20px; font-weight: 700; color: var(--text-primary);
    display: flex; align-items: center; gap: 10px;
  }
  .page-title .icon-wrap {
    width: 38px; height: 38px; background: var(--accent-dim);
    border-radius: var(--radius-sm); display: grid; place-items: center;
    color: var(--accent-hover); font-size: 17px;
  }

  /* FORM CARD */
  .form-card {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: var(--radius); overflow: hidden;
  }
  .form-card-header {
    padding: 18px 24px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 10px;
  }
  .form-card-header h6 {
    font-size: 13px; font-weight: 600; color: var(--text-muted);
    margin: 0; text-transform: uppercase; letter-spacing: .6px;
  }
  .form-card-body { padding: 28px 24px; }

  /* FORM CONTROLS */
  .form-label {
    font-size: 12px; font-weight: 600; letter-spacing: .5px;
    text-transform: uppercase; color: var(--text-muted);
    margin-bottom: 7px; display: block;
  }
  .form-control, .form-select {
    background: var(--bg-input) !important;
    border: 1px solid var(--border) !important;
    border-radius: var(--radius-sm) !important;
    color: var(--text-primary) !important;
    font-size: 14px; padding: 11px 14px;
    transition: border-color .18s, box-shadow .18s;
  }
  .form-control:focus, .form-select:focus {
    border-color: var(--accent) !important;
    box-shadow: 0 0 0 3px rgba(124,92,252,.18) !important;
    outline: none;
  }
  .form-control::placeholder { color: var(--text-muted); }
  .form-select option { background: var(--bg-card); color: var(--text-primary); }

  .char-counter { font-size: 11px; color: var(--text-muted); text-align: right; margin-top: 5px; }

  /* Status badge preview */
  .status-preview {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
    margin-top: 8px; transition: all .2s;
  }
  .status-preview.aktif    { background: rgba(34,197,94,.15); color: #22c55e; border: 1px solid rgba(34,197,94,.25); }
  .status-preview.nonaktif { background: rgba(107,114,128,.15); color: #9ca3af; border: 1px solid rgba(107,114,128,.25); }

  /* Tip box */
  .tip-box {
    background: rgba(34,197,94,.08); border: 1px solid rgba(34,197,94,.18);
    border-radius: var(--radius-sm); padding: 12px 16px;
    font-size: 13px; color: #4ade80;
    display: flex; align-items: flex-start; gap: 9px;
    margin-bottom: 24px;
  }
  .tip-box i { font-size: 15px; margin-top: 1px; flex-shrink: 0; }

  /* BUTTONS */
  .btn-accent {
    background: var(--accent); color: #fff; border: none;
    border-radius: var(--radius-sm); padding: 11px 24px;
    font-size: 14px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 7px;
    cursor: pointer; transition: background .18s, transform .1s;
  }
  .btn-accent:hover { background: var(--accent-hover); transform: translateY(-1px); }
  .btn-accent:active { transform: translateY(0); }
  .btn-ghost {
    background: transparent; color: var(--text-muted);
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    padding: 11px 20px; font-size: 14px; font-weight: 500;
    display: inline-flex; align-items: center; gap: 7px;
    text-decoration: none; transition: background .18s, color .18s;
  }
  .btn-ghost:hover { background: var(--bg-hover); color: var(--text-primary); }

  /* Animasi masuk */
  .form-card { animation: fadeUp .3s ease; }
  @keyframes fadeUp {
    from { opacity:0; transform:translateY(12px); }
    to   { opacity:1; transform:translateY(0); }
  }
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">🍽️</div>
    <h5>RESTO APP</h5>
  </div>

  <div class="sidebar-label">Utama</div>
  <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>

  <div class="sidebar-label">Operasional</div>
  <a href="kategori.php"      class="nav-link active"><i class="bi bi-tag"></i> Kategori</a>
  <a href="menu.php"          class="nav-link"><i class="bi bi-egg-fried"></i> Menu</a>
  <a href="meja.php"          class="nav-link"><i class="bi bi-grid-3x3-gap"></i> Meja</a>
  <a href="pesanan_masuk.php" class="nav-link"><i class="bi bi-bag-check"></i> Pesanan Masuk</a>
  <a href="bank.php"          class="nav-link"><i class="bi bi-bank"></i> Bank</a>

  <div class="sidebar-label">Laporan</div>
  <a href="transaksi.php" class="nav-link"><i class="bi bi-receipt"></i> Transaksi</a>
  <a href="grafik.php"    class="nav-link"><i class="bi bi-bar-chart-line"></i> Grafik Penjualan</a>

  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'] ?? $_SESSION['admin'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div class="user-name"><?= $_SESSION['nama'] ?? $_SESSION['admin'] ?? 'Admin' ?></div>
        <div class="user-role">Administrator</div>
      </div>
    </div>
    <a href="../logout.php" class="nav-link mt-2" style="color:var(--danger)">
      <i class="bi bi-box-arrow-left"></i> Logout
    </a>
  </div>
</aside>

<!-- TOPBAR -->
<div class="topbar">
  <nav class="breadcrumb-nav">
    <a href="dashboard.php">Dashboard</a>
    <span class="sep">/</span>
    <a href="kategori.php">Kategori</a>
    <span class="sep">/</span>
    <span class="current">Tambah Kategori</span>
  </nav>
  <div style="font-size:13px; color:var(--text-muted)" id="jam"></div>
</div>

<!-- MAIN -->
<div class="main">
  <div class="page-content">

    <div class="page-header">
      <div class="page-title">
        <div class="icon-wrap"><i class="bi bi-plus-lg"></i></div>
        Tambah Kategori
      </div>
      <a href="kategori.php" class="btn-ghost">
        <i class="bi bi-arrow-left"></i> Kembali
      </a>
    </div>

    <div class="tip-box">
      <i class="bi bi-lightbulb-fill"></i>
      <span>Gunakan nama kategori yang singkat dan jelas, seperti <strong>Minuman</strong>, <strong>Makanan Berat</strong>, atau <strong>Dessert</strong>.</span>
    </div>

    <form method="POST">
      <div class="form-card">

        <div class="form-card-header">
          <i class="bi bi-tag" style="color:var(--accent)"></i>
          <h6>Informasi Kategori Baru</h6>
        </div>

        <div class="form-card-body">
          <div class="row g-4">

            <!-- Nama -->
            <div class="col-12">
              <label class="form-label">Nama Kategori</label>
              <input type="text"
                     name="nama_kategori"
                     id="namaInput"
                     class="form-control"
                     placeholder="Contoh: Minuman Dingin, Makanan Berat..."
                     maxlength="100"
                     autocomplete="off"
                     required>
              <div class="char-counter">
                <span id="charCount">0</span>/100 karakter
              </div>
            </div>

            <!-- Status -->
            <div class="col-12">
              <label class="form-label">Status</label>
              <select name="status" id="statusSelect" class="form-select">
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
              </select>
              <div class="status-preview aktif" id="statusBadge">
                <i class="bi bi-check-circle-fill" id="statusIcon"></i>
                <span id="statusText">Aktif</span>
              </div>
            </div>

          </div>
        </div>

        <div style="padding:18px 24px; border-top:1px solid var(--border); display:flex; gap:10px;">
          <button type="submit" name="simpan" class="btn-accent">
            <i class="bi bi-plus-lg"></i> Simpan Kategori
          </button>
          <a href="kategori.php" class="btn-ghost">
            <i class="bi bi-x-lg"></i> Batal
          </a>
        </div>

      </div>
    </form>

  </div>
</div>

<script>
  // Jam
  function updateJam(){
    const now = new Date();
    document.getElementById('jam').textContent =
      now.toLocaleString('id-ID',{weekday:'short',day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
  }
  updateJam(); setInterval(updateJam, 1000);

  // Char counter
  const namaInput = document.getElementById('namaInput');
  const charCount = document.getElementById('charCount');
  namaInput.addEventListener('input', () => charCount.textContent = namaInput.value.length);

  // Live status badge
  const statusSelect = document.getElementById('statusSelect');
  const statusBadge  = document.getElementById('statusBadge');
  const statusIcon   = document.getElementById('statusIcon');
  const statusText   = document.getElementById('statusText');

  statusSelect.addEventListener('change', function(){
    const val = this.value;
    statusBadge.className = 'status-preview ' + val;
    statusText.textContent = val === 'aktif' ? 'Aktif' : 'Nonaktif';
    statusIcon.className   = val === 'aktif' ? 'bi bi-check-circle-fill' : 'bi bi-slash-circle-fill';
  });
</script>

</body>
</html>