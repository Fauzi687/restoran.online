<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit;
}

$kategori = mysqli_query($conn,"SELECT * FROM kategori WHERE status='aktif'");

if(isset($_POST['simpan'])){
    $id_kategori = $_POST['id_kategori'];
    $nama_menu   = $_POST['nama_menu'];
    $harga       = $_POST['harga'];
    $stok        = $_POST['stok'];
    $status      = $_POST['status'];

    if($stok <= 0){ $status = 'nonaktif'; }

    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];
    move_uploaded_file($tmp, "../uploads/menu/".$foto);

    mysqli_query($conn,"
        INSERT INTO menu(id_kategori, nama_menu, harga, stok, foto, status)
        VALUES('$id_kategori','$nama_menu','$harga','$stok','$foto','$status')
    ");

    header("Location: menu.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Menu — Resto App</title>
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
    --warning:      #f59e0b;
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
  .page-content { padding: 28px 32px; max-width: 860px; }
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
    animation: fadeUp .3s ease;
  }
  @keyframes fadeUp {
    from { opacity:0; transform:translateY(12px); }
    to   { opacity:1; transform:translateY(0); }
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

  .input-group-text {
    background: var(--bg-hover) !important;
    border: 1px solid var(--border) !important;
    border-right: none !important;
    color: var(--text-muted) !important;
    font-size: 13px;
  }
  .input-group .form-control { border-left: none !important; }

  /* FILE INPUT */
  .file-drop-zone {
    position: relative;
    background: var(--bg-input);
    border: 2px dashed rgba(124,92,252,.35);
    border-radius: var(--radius-sm);
    padding: 28px 20px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
  }
  .file-drop-zone:hover,
  .file-drop-zone.drag-over {
    border-color: var(--accent);
    background: var(--accent-dim);
  }
  .file-drop-zone input[type="file"] {
    position: absolute; inset: 0;
    opacity: 0; cursor: pointer; width: 100%; height: 100%;
  }
  .file-drop-icon { font-size: 28px; color: var(--accent); margin-bottom: 8px; }
  .file-drop-text { font-size: 13px; color: var(--text-muted); }
  .file-drop-text strong { color: var(--accent-hover); }
  .file-drop-hint { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

  /* Preview foto */
  .preview-wrap {
    display: none;
    align-items: center; gap: 16px;
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 14px;
    margin-top: 10px;
  }
  .preview-wrap.show { display: flex; }
  .preview-thumb {
    width: 72px; height: 72px;
    object-fit: cover;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    flex-shrink: 0;
  }
  .preview-info { flex: 1; }
  .preview-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
  .preview-size { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
  .preview-remove {
    background: rgba(239,68,68,.15); color: var(--danger);
    border: 1px solid rgba(239,68,68,.25);
    border-radius: var(--radius-sm);
    padding: 5px 10px; font-size: 12px; cursor: pointer;
    transition: background .15s;
  }
  .preview-remove:hover { background: rgba(239,68,68,.3); }

  /* Stok warning */
  .stok-hint {
    font-size: 11px; color: var(--warning);
    margin-top: 5px; display: none;
  }

  /* Status badge preview */
  .status-preview {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
    margin-top: 8px; transition: all .2s;
  }
  .status-preview.aktif    { background: rgba(34,197,94,.15); color: #22c55e; border: 1px solid rgba(34,197,94,.25); }
  .status-preview.nonaktif { background: rgba(107,114,128,.15); color: #9ca3af; border: 1px solid rgba(107,114,128,.25); }

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
  <a href="kategori.php"      class="nav-link"><i class="bi bi-tag"></i> Kategori</a>
  <a href="menu.php"          class="nav-link active"><i class="bi bi-egg-fried"></i> Menu</a>
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
    <a href="menu.php">Menu</a>
    <span class="sep">/</span>
    <span class="current">Tambah Menu</span>
  </nav>
  <div style="font-size:13px; color:var(--text-muted)" id="jam"></div>
</div>

<!-- MAIN -->
<div class="main">
  <div class="page-content">

    <div class="page-header">
      <div class="page-title">
        <div class="icon-wrap"><i class="bi bi-plus-lg"></i></div>
        Tambah Menu
      </div>
      <a href="menu.php" class="btn-ghost">
        <i class="bi bi-arrow-left"></i> Kembali
      </a>
    </div>

    <form method="POST" enctype="multipart/form-data" id="formMenu">
      <div class="form-card">

        <div class="form-card-header">
          <i class="bi bi-egg-fried" style="color:var(--accent)"></i>
          <h6>Informasi Menu Baru</h6>
        </div>

        <div class="form-card-body">
          <div class="row g-4">

            <!-- Nama Menu -->
            <div class="col-md-6">
              <label class="form-label">Nama Menu</label>
              <input type="text" name="nama_menu" class="form-control"
                     placeholder="Contoh: Nasi Goreng Spesial" required>
            </div>

            <!-- Kategori -->
            <div class="col-md-6">
              <label class="form-label">Kategori</label>
              <select name="id_kategori" class="form-select">
                <?php while($k = mysqli_fetch_array($kategori)): ?>
                <option value="<?= $k['id_kategori'] ?>">
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
                       placeholder="0" min="0" required>
              </div>
            </div>

            <!-- Stok -->
            <div class="col-md-4">
              <label class="form-label">Stok</label>
              <input type="number" name="stok" id="stokInput" class="form-control"
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
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
              </select>
              <div class="status-preview aktif" id="statusBadge">
                <i class="bi bi-check-circle-fill" id="statusIcon"></i>
                <span id="statusText">Aktif</span>
              </div>
            </div>

            <!-- Foto -->
            <div class="col-12">
              <label class="form-label">Foto Menu <span style="color:var(--danger)">*</span></label>
              <div class="file-drop-zone" id="dropZone">
                <input type="file" name="foto" id="fotoInput" accept="image/*" required>
                <div class="file-drop-icon"><i class="bi bi-cloud-upload"></i></div>
                <div class="file-drop-text">
                  <strong>Klik untuk upload</strong> atau seret foto ke sini
                </div>
                <div class="file-drop-hint">PNG, JPG, JPEG — maks. 2 MB</div>
              </div>

              <!-- Preview -->
              <div class="preview-wrap" id="previewWrap">
                <img id="previewThumb" class="preview-thumb" src="" alt="preview">
                <div class="preview-info">
                  <div class="preview-name" id="previewName">—</div>
                  <div class="preview-size" id="previewSize">—</div>
                </div>
                <button type="button" class="preview-remove" id="removeBtn">
                  <i class="bi bi-trash"></i> Hapus
                </button>
              </div>
            </div>

          </div>
        </div>

        <!-- Footer -->
        <div style="padding:18px 24px; border-top:1px solid var(--border); display:flex; gap:10px;">
          <button type="submit" name="simpan" class="btn-accent">
            <i class="bi bi-plus-lg"></i> Simpan Menu
          </button>
          <a href="menu.php" class="btn-ghost">
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

  // Stok → status otomatis
  const stokInput    = document.getElementById('stokInput');
  const stokHint     = document.getElementById('stokHint');
  const statusSelect = document.getElementById('statusSelect');
  const statusBadge  = document.getElementById('statusBadge');
  const statusIcon   = document.getElementById('statusIcon');
  const statusText   = document.getElementById('statusText');

  function updateStatusBadge(val){
    statusBadge.className = 'status-preview ' + val;
    statusText.textContent = val === 'aktif' ? 'Aktif' : 'Nonaktif';
    statusIcon.className   = val === 'aktif' ? 'bi bi-check-circle-fill' : 'bi bi-slash-circle-fill';
  }

  stokInput.addEventListener('input', function(){
    if(parseInt(this.value) <= 0 && this.value !== ''){
      stokHint.style.display = 'block';
      statusSelect.value = 'nonaktif';
      updateStatusBadge('nonaktif');
    } else {
      stokHint.style.display = 'none';
    }
  });

  statusSelect.addEventListener('change', function(){
    updateStatusBadge(this.value);
  });

  // File preview
  const fotoInput   = document.getElementById('fotoInput');
  const dropZone    = document.getElementById('dropZone');
  const previewWrap = document.getElementById('previewWrap');
  const previewThumb= document.getElementById('previewThumb');
  const previewName = document.getElementById('previewName');
  const previewSize = document.getElementById('previewSize');
  const removeBtn   = document.getElementById('removeBtn');

  function formatSize(bytes){
    if(bytes < 1024) return bytes + ' B';
    if(bytes < 1048576) return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/1048576).toFixed(1) + ' MB';
  }

  function showPreview(file){
    const reader = new FileReader();
    reader.onload = e => { previewThumb.src = e.target.result; };
    reader.readAsDataURL(file);
    previewName.textContent = file.name;
    previewSize.textContent = formatSize(file.size);
    previewWrap.classList.add('show');
    dropZone.style.display = 'none';
  }

  fotoInput.addEventListener('change', function(){
    if(this.files && this.files[0]) showPreview(this.files[0]);
  });

  removeBtn.addEventListener('click', function(){
    fotoInput.value = '';
    previewWrap.classList.remove('show');
    dropZone.style.display = 'block';
  });

  // Drag & drop visual
  dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
  dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
  dropZone.addEventListener('drop',      e => { e.preventDefault(); dropZone.classList.remove('drag-over'); });
</script>

</body>
</html>