<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'];

$data = mysqli_query($conn,"SELECT * FROM menu WHERE id_menu='$id'");
$row = mysqli_fetch_array($data);

$kategori = mysqli_query($conn,"SELECT * FROM kategori WHERE status='aktif'");

if(isset($_POST['update'])){
    $id_kategori = $_POST['id_kategori'];
    $nama_menu   = $_POST['nama_menu'];
    $harga       = $_POST['harga'];
    $stok        = $_POST['stok'];
    $status      = $_POST['status'];

    if($stok <= 0){ $status = 'nonaktif'; }

    if($_FILES['foto']['name'] != ''){
        $foto = $_FILES['foto']['name'];
        $tmp  = $_FILES['foto']['tmp_name'];
        move_uploaded_file($tmp, "../uploads/menu/".$foto);

        mysqli_query($conn,"
            UPDATE menu SET
            id_kategori='$id_kategori',
            nama_menu='$nama_menu',
            harga='$harga',
            stok='$stok',
            foto='$foto',
            status='$status'
            WHERE id_menu='$id'
        ");
    } else {
        mysqli_query($conn,"
            UPDATE menu SET
            id_kategori='$id_kategori',
            nama_menu='$nama_menu',
            harga='$harga',
            stok='$stok',
            status='$status'
            WHERE id_menu='$id'
        ");
    }

    header("Location: menu.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Menu — Resto App</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  :root {
    --bg-base:       #0f0e1a;
    --bg-card:       #1a1826;
    --bg-sidebar:    #13111f;
    --bg-input:      #231f35;
    --bg-hover:      #2a2640;
    --border:        rgba(255,255,255,0.07);
    --accent:        #7c5cfc;
    --accent-dim:    rgba(124,92,252,0.15);
    --accent-hover:  #9b82fd;
    --text-primary:  #e8e4f8;
    --text-muted:    #7b7897;
    --success:       #22c55e;
    --warning:       #f59e0b;
    --danger:        #ef4444;
    --radius:        12px;
    --radius-sm:     8px;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: var(--bg-base);
    color: var(--text-primary);
    font-family: 'Segoe UI', system-ui, sans-serif;
    min-height: 100vh;
    display: flex;
  }

  /* ── SIDEBAR ── */
  .sidebar {
    width: 240px;
    min-height: 100vh;
    background: var(--bg-sidebar);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    padding: 24px 16px;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 100;
  }

  .sidebar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 8px 20px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 20px;
  }

  .sidebar-brand .brand-icon {
    width: 36px; height: 36px;
    background: var(--accent);
    border-radius: 10px;
    display: grid; place-items: center;
    font-size: 18px;
  }

  .sidebar-brand h5 {
    font-size: 15px;
    font-weight: 700;
    letter-spacing: .5px;
    color: var(--text-primary);
    margin: 0;
  }

  .sidebar-label {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--text-muted);
    padding: 0 8px;
    margin: 16px 0 6px;
  }

  .nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 12px;
    border-radius: var(--radius-sm);
    color: var(--text-muted);
    font-size: 13.5px;
    font-weight: 500;
    text-decoration: none;
    transition: all .18s;
    margin-bottom: 2px;
  }

  .nav-link:hover { background: var(--bg-hover); color: var(--text-primary); }
  .nav-link.active { background: var(--accent-dim); color: var(--accent-hover); }
  .nav-link i { font-size: 15px; width: 18px; text-align: center; }

  .sidebar-footer {
    margin-top: auto;
    border-top: 1px solid var(--border);
    padding-top: 16px;
  }

  .user-info {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px; border-radius: var(--radius-sm);
  }

  .user-avatar {
    width: 34px; height: 34px;
    background: var(--accent);
    border-radius: 50%;
    display: grid; place-items: center;
    font-size: 14px; font-weight: 700;
  }

  .user-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
  .user-role { font-size: 11px; color: var(--text-muted); }

  /* ── TOPBAR ── */
  .topbar {
    position: fixed;
    top: 0; left: 240px; right: 0;
    height: 60px;
    background: var(--bg-card);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 28px;
    z-index: 99;
  }

  .topbar-left {
    display: flex; align-items: center; gap: 12px;
  }

  .breadcrumb-nav {
    display: flex; align-items: center; gap: 6px;
    font-size: 13px; color: var(--text-muted);
  }

  .breadcrumb-nav a { color: var(--text-muted); text-decoration: none; transition: color .15s; }
  .breadcrumb-nav a:hover { color: var(--accent-hover); }
  .breadcrumb-nav .sep { opacity: .4; }
  .breadcrumb-nav .current { color: var(--text-primary); font-weight: 600; }

  /* ── MAIN ── */
  .main {
    margin-left: 240px;
    padding-top: 60px;
    flex: 1;
  }

  .page-content {
    padding: 28px 32px;
    max-width: 860px;
  }

  .page-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px;
  }

  .page-title {
    font-size: 20px; font-weight: 700;
    color: var(--text-primary);
    display: flex; align-items: center; gap: 10px;
  }

  .page-title .icon-wrap {
    width: 38px; height: 38px;
    background: var(--accent-dim);
    border-radius: var(--radius-sm);
    display: grid; place-items: center;
    color: var(--accent-hover);
    font-size: 17px;
  }

  /* ── FORM CARD ── */
  .form-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
  }

  .form-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 10px;
  }

  .form-card-header h6 {
    font-size: 13.5px; font-weight: 600;
    color: var(--text-muted);
    margin: 0; text-transform: uppercase; letter-spacing: .6px;
  }

  .form-card-body { padding: 24px; }

  /* ── PREVIEW FOTO ── */
  .foto-preview-wrap {
    position: relative;
    display: inline-block;
  }

  .foto-preview {
    width: 120px; height: 120px;
    object-fit: cover;
    border-radius: var(--radius);
    border: 2px solid var(--border);
    background: var(--bg-input);
    display: block;
  }

  .foto-placeholder {
    width: 120px; height: 120px;
    background: var(--bg-input);
    border: 2px dashed var(--border);
    border-radius: var(--radius);
    display: grid; place-items: center;
    color: var(--text-muted);
    font-size: 28px;
  }

  .foto-badge {
    position: absolute; bottom: -6px; right: -6px;
    background: var(--accent);
    color: #fff;
    font-size: 11px; font-weight: 600;
    padding: 2px 8px;
    border-radius: 20px;
  }

  /* ── FORM CONTROLS ── */
  .form-label {
    font-size: 12px; font-weight: 600;
    letter-spacing: .5px; text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 6px;
    display: block;
  }

  .form-control, .form-select {
    background: var(--bg-input) !important;
    border: 1px solid var(--border) !important;
    border-radius: var(--radius-sm) !important;
    color: var(--text-primary) !important;
    font-size: 14px;
    padding: 10px 14px;
    transition: border-color .18s, box-shadow .18s;
  }

  .form-control:focus, .form-select:focus {
    border-color: var(--accent) !important;
    box-shadow: 0 0 0 3px rgba(124,92,252,.18) !important;
    outline: none;
  }

  .form-control::placeholder { color: var(--text-muted); }

  .form-select option {
    background: var(--bg-card);
    color: var(--text-primary);
  }

  /* file input */
  .file-input-wrap {
    position: relative;
    background: var(--bg-input);
    border: 1px dashed rgba(124,92,252,.4);
    border-radius: var(--radius-sm);
    padding: 16px;
    text-align: center;
    cursor: pointer;
    transition: border-color .18s, background .18s;
  }

  .file-input-wrap:hover {
    border-color: var(--accent);
    background: var(--accent-dim);
  }

  .file-input-wrap input[type="file"] {
    position: absolute; inset: 0;
    opacity: 0; cursor: pointer; width: 100%; height: 100%;
  }

  .file-input-text { font-size: 13px; color: var(--text-muted); }
  .file-input-text strong { color: var(--accent-hover); }
  .file-input-icon { font-size: 22px; color: var(--accent); margin-bottom: 6px; }

  .input-group-text {
    background: var(--bg-hover) !important;
    border: 1px solid var(--border) !important;
    color: var(--text-muted) !important;
    border-right: none !important;
    font-size: 13px;
  }

  .input-group .form-control { border-left: none !important; }

  /* ── BADGES STATUS ── */
  .badge-aktif    { background: rgba(34,197,94,.15); color: #22c55e; border: 1px solid rgba(34,197,94,.25); }
  .badge-nonaktif { background: rgba(107,114,128,.15); color: #9ca3af; border: 1px solid rgba(107,114,128,.25); }

  /* ── BUTTONS ── */
  .btn-accent {
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    padding: 10px 22px;
    font-size: 14px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 7px;
    cursor: pointer;
    transition: background .18s, transform .1s;
  }

  .btn-accent:hover { background: var(--accent-hover); transform: translateY(-1px); }
  .btn-accent:active { transform: translateY(0); }

  .btn-ghost {
    background: transparent;
    color: var(--text-muted);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 10px 20px;
    font-size: 14px; font-weight: 500;
    display: inline-flex; align-items: center; gap: 7px;
    text-decoration: none;
    transition: background .18s, color .18s;
  }

  .btn-ghost:hover { background: var(--bg-hover); color: var(--text-primary); }

  /* ── DIVIDER ── */
  .section-divider {
    border: none;
    border-top: 1px solid var(--border);
    margin: 4px 0 20px;
  }

  /* ── STOK HINT ── */
  .stok-hint {
    font-size: 11px; color: var(--warning);
    margin-top: 4px; display: none;
  }

  /* ── TOAST ── */
  .toast-wrap {
    position: fixed; bottom: 24px; right: 24px; z-index: 9999;
  }

  .toast-msg {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-left: 3px solid var(--accent);
    border-radius: var(--radius-sm);
    padding: 12px 18px;
    font-size: 13.5px;
    color: var(--text-primary);
    box-shadow: 0 8px 24px rgba(0,0,0,.4);
    animation: slideIn .3s ease;
  }

  @keyframes slideIn {
    from { opacity:0; transform:translateY(12px); }
    to   { opacity:1; transform:translateY(0); }
  }
</style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">🍽️</div>
    <div>
      <h5>RESTO APP</h5>
    </div>
  </div>

  <div class="sidebar-label">Utama</div>
  <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>

  <div class="sidebar-label">Operasional</div>
  <a href="kategori.php"     class="nav-link"><i class="bi bi-tag"></i> Kategori</a>
  <a href="menu.php"         class="nav-link active"><i class="bi bi-egg-fried"></i> Menu</a>
  <a href="meja.php"         class="nav-link"><i class="bi bi-grid-3x3-gap"></i> Meja</a>
  <a href="pesanan_masuk.php"class="nav-link"><i class="bi bi-bag-check"></i> Pesanan Masuk</a>
  <a href="bank.php"         class="nav-link"><i class="bi bi-bank"></i> Bank</a>

  <div class="sidebar-label">Laporan</div>
  <a href="transaksi.php"    class="nav-link"><i class="bi bi-receipt"></i> Transaksi</a>
  <a href="grafik.php"       class="nav-link"><i class="bi bi-bar-chart-line"></i> Grafik Penjualan</a>

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

<!-- ── TOPBAR ── -->
<div class="topbar">
  <div class="topbar-left">
    <nav class="breadcrumb-nav">
      <a href="dashboard.php">Dashboard</a>
      <span class="sep">/</span>
      <a href="menu.php">Menu</a>
      <span class="sep">/</span>
      <span class="current">Edit Menu</span>
    </nav>
  </div>
  <div style="font-size:13px; color:var(--text-muted)" id="jam"></div>
</div>

<!-- ── MAIN ── -->
<div class="main">
  <div class="page-content">

    <!-- Header -->
    <div class="page-header">
      <div class="page-title">
        <div class="icon-wrap"><i class="bi bi-pencil-square"></i></div>
        Edit Menu
      </div>
      <a href="menu.php" class="btn-ghost">
        <i class="bi bi-arrow-left"></i> Kembali
      </a>
    </div>

    <!-- Form Card -->
    <form method="POST" enctype="multipart/form-data">
      <div class="form-card">

        <!-- Section: Info Dasar -->
        <div class="form-card-header">
          <i class="bi bi-info-circle" style="color:var(--accent)"></i>
          <h6>Informasi Menu</h6>
        </div>
        <div class="form-card-body">

          <div class="row g-4">

            <!-- Foto preview -->
            <div class="col-12 d-flex align-items-start gap-4 mb-2">
              <div class="foto-preview-wrap">
                <?php if(!empty($row['foto']) && file_exists("../uploads/menu/".$row['foto'])): ?>
                  <img id="previewImg"
                       src="../uploads/menu/<?= htmlspecialchars($row['foto']) ?>"
                       class="foto-preview" alt="Foto Menu">
                <?php else: ?>
                  <div class="foto-placeholder" id="previewPlaceholder">
                    <i class="bi bi-image"></i>
                  </div>
                  <img id="previewImg" src="" class="foto-preview" style="display:none" alt="Foto Menu">
                <?php endif; ?>
                <span class="foto-badge">Foto</span>
              </div>
              <div style="flex:1">
                <label class="form-label">Ganti Foto <span style="color:var(--text-muted);text-transform:none;font-weight:400">(Opsional)</span></label>
                <div class="file-input-wrap" id="fileWrap">
                  <input type="file" name="foto" id="fotoInput" accept="image/*">
                  <div class="file-input-icon"><i class="bi bi-cloud-upload"></i></div>
                  <div class="file-input-text">
                    <strong>Klik untuk upload</strong> atau seret foto ke sini
                  </div>
                  <div class="file-input-text" style="font-size:11px;margin-top:3px">PNG, JPG, JPEG — maks. 2 MB</div>
                </div>
                <div id="fileNameLabel" style="font-size:12px;color:var(--accent);margin-top:6px;display:none">
                  <i class="bi bi-check-circle"></i> <span id="fileNameText"></span>
                </div>
              </div>
            </div>

            <!-- Nama Menu -->
            <div class="col-md-6">
              <label class="form-label">Nama Menu</label>
              <input type="text" name="nama_menu" class="form-control"
                     value="<?= htmlspecialchars($row['nama_menu']) ?>"
                     placeholder="Contoh: Nasi Goreng Spesial" required>
            </div>

            <!-- Kategori -->
            <div class="col-md-6">
              <label class="form-label">Kategori</label>
              <select name="id_kategori" class="form-select">
                <?php while($k = mysqli_fetch_array($kategori)): ?>
                <option value="<?= $k['id_kategori'] ?>"
                  <?= $row['id_kategori'] == $k['id_kategori'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($k['nama_kategori']) ?>
                </option>
                <?php endwhile; ?>
              </select>
            </div>

            <!-- Harga -->
            <div class="col-md-4">
              <label class="form-label">Harga</label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="harga" class="form-control"
                       value="<?= $row['harga'] ?>"
                       placeholder="0" min="0" required>
              </div>
            </div>

            <!-- Stok -->
            <div class="col-md-4">
              <label class="form-label">Stok</label>
              <input type="number" name="stok" id="stokInput" class="form-control"
                     value="<?= $row['stok'] ?>"
                     placeholder="0" min="0" required>
              <div class="stok-hint" id="stokHint">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Stok 0 — status otomatis jadi Nonaktif
              </div>
            </div>

            <!-- Status -->
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select name="status" id="statusSelect" class="form-select">
                <option value="aktif"    <?= $row['status']=='aktif'    ? 'selected' : '' ?>>Aktif</option>
                <option value="nonaktif" <?= $row['status']=='nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
              </select>
            </div>

          </div><!-- /row -->
        </div><!-- /card-body -->

        <!-- Footer Aksi -->
        <div style="padding:18px 24px; border-top:1px solid var(--border); display:flex; gap:10px;">
          <button type="submit" name="update" class="btn-accent">
            <i class="bi bi-check-lg"></i> Simpan Perubahan
          </button>
          <a href="menu.php" class="btn-ghost">
            <i class="bi bi-x-lg"></i> Batal
          </a>
        </div>

      </div><!-- /form-card -->
    </form>

  </div><!-- /page-content -->
</div><!-- /main -->

<!-- Toast -->
<div class="toast-wrap" id="toastWrap" style="display:none">
  <div class="toast-msg" id="toastMsg"></div>
</div>

<script>
  // Jam
  function updateJam(){
    const now = new Date();
    document.getElementById('jam').textContent =
      now.toLocaleString('id-ID',{weekday:'short',day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
  }
  updateJam(); setInterval(updateJam, 1000);

  // Preview foto saat dipilih
  const fotoInput = document.getElementById('fotoInput');
  const previewImg = document.getElementById('previewImg');
  const previewPlaceholder = document.getElementById('previewPlaceholder');
  const fileNameLabel = document.getElementById('fileNameLabel');
  const fileNameText  = document.getElementById('fileNameText');

  fotoInput.addEventListener('change', function(){
    if(this.files && this.files[0]){
      const reader = new FileReader();
      reader.onload = e => {
        previewImg.src = e.target.result;
        previewImg.style.display = 'block';
        if(previewPlaceholder) previewPlaceholder.style.display = 'none';
      };
      reader.readAsDataURL(this.files[0]);
      fileNameText.textContent = this.files[0].name;
      fileNameLabel.style.display = 'block';
    }
  });

  // Stok → warning + ubah status
  const stokInput    = document.getElementById('stokInput');
  const stokHint     = document.getElementById('stokHint');
  const statusSelect = document.getElementById('statusSelect');

  stokInput.addEventListener('input', function(){
    if(parseInt(this.value) <= 0){
      stokHint.style.display = 'block';
      statusSelect.value = 'nonaktif';
    } else {
      stokHint.style.display = 'none';
    }
  });

  // Inisialisasi
  if(parseInt(stokInput.value) <= 0) stokHint.style.display = 'block';
</script>

</body>
</html>