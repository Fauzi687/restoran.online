<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}
require '../config/koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM bank ORDER BY id_bank DESC");

$pesan = ''; $tipe = '';
if (isset($_SESSION['pesan'])) {
    [$tipe, $pesan] = explode('|', $_SESSION['pesan'], 2);
    unset($_SESSION['pesan']);
}

if (isset($_GET['hapus'])) {
    $id  = (int)$_GET['hapus'];
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT logo, qris FROM bank WHERE id_bank=$id"));
    if ($row) {
        $d = '../uploads/bank/';
        if ($row['logo'] && file_exists($d.$row['logo'])) unlink($d.$row['logo']);
        if ($row['qris'] && file_exists($d.$row['qris'])) unlink($d.$row['qris']);
    }
    mysqli_query($conn, "DELETE FROM bank WHERE id_bank=$id");
    $_SESSION['pesan'] = 'sukses|Rekening berhasil dihapus.';
    header('Location: bank.php'); exit;
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id        = (int)$_GET['edit'];
    $edit_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM bank WHERE id_bank=$id"));
}

$total_rek  = mysqli_num_rows(mysqli_query($conn,"SELECT id_bank FROM bank"));
$total_qris = mysqli_num_rows(mysqli_query($conn,"SELECT id_bank FROM bank WHERE qris IS NOT NULL AND qris != ''"));
$total_logo = mysqli_num_rows(mysqli_query($conn,"SELECT id_bank FROM bank WHERE logo IS NOT NULL AND logo != ''"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bank — Resto App</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* ── BASE sama dashboard ── */
* { box-sizing: border-box; }
body {
  background: #0f0e1a;
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  color: #e2e2e2;
  min-height: 100vh;
  margin: 0;
}
.main-content { margin-left: 240px; min-height: 100vh; }

/* TOPBAR */
.topbar {
  background: rgba(18,16,42,0.97);
  border-bottom: 1px solid rgba(255,255,255,0.07);
  padding: 0.85rem 1.75rem;
  display: flex; align-items: center; justify-content: space-between;
  position: sticky; top: 0; z-index: 50;
}
.topbar-title { font-size: 1.05rem; font-weight: 600; color: #fff; }
.topbar-time  { font-size: 0.8rem; color: rgba(255,255,255,0.4); }

/* CONTENT */
.content-area { padding: 1.75rem; }

/* ALERT */
.alert-dark {
  padding: 12px 16px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-size: 0.82rem;
  font-weight: 500;
  display: flex; align-items: center; gap: 9px;
  animation: slideDown .25s ease;
}
@keyframes slideDown { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:none} }
.alert-sukses { background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.25); color: #4ade80; }
.alert-error  { background: rgba(239,68,68,.12);  border: 1px solid rgba(239,68,68,.25);  color: #f87171; }

/* STAT CARDS */
.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
  gap: 1rem;
  margin-bottom: 1.75rem;
}
.stat-card {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px; padding: 1.25rem;
  transition: border-color .2s;
}
.stat-card:hover { border-color: rgba(255,255,255,0.15); }
.sc-icon {
  width: 40px; height: 40px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; margin-bottom: 0.9rem;
}
.sc-purple { background: rgba(168,85,247,0.18); color: #c084fc; }
.sc-green  { background: rgba(34,197,94,0.15);  color: #4ade80; }
.sc-blue   { background: rgba(59,130,246,0.15); color: #60a5fa; }
.sc-label {
  font-size: 0.7rem; font-weight: 500; text-transform: uppercase;
  letter-spacing: 0.8px; color: rgba(255,255,255,0.4); margin-bottom: 3px;
}
.sc-value { font-size: 1.55rem; font-weight: 700; color: #fff; line-height: 1.1; }

/* LAYOUT GRID */
.layout-grid {
  display: grid;
  grid-template-columns: 340px 1fr;
  gap: 1.5rem;
  align-items: start;
}

/* CARD (form & table wrapper) */
.dark-card {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  overflow: hidden;
}
.dark-card-header {
  padding: 0.9rem 1.25rem;
  border-bottom: 1px solid rgba(255,255,255,0.07);
  background: rgba(255,255,255,0.03);
  display: flex; align-items: center; gap: 10px;
}
.dark-card-header .ch-icon {
  width: 30px; height: 30px; border-radius: 8px;
  background: rgba(168,85,247,0.2);
  display: flex; align-items: center; justify-content: center;
  color: #c084fc; font-size: 0.85rem;
}
.dark-card-header h2 { font-size: 0.88rem; font-weight: 600; color: #fff; margin: 0; }
.dark-card-body { padding: 1.25rem; }

/* FORM */
.form-lbl {
  display: block;
  font-size: 0.68rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.7px;
  color: rgba(255,255,255,0.4);
  margin-bottom: 5px;
}
.form-inp {
  width: 100%;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px;
  color: #e2e2e2;
  font-size: 0.84rem;
  padding: 9px 12px;
  outline: none;
  transition: border-color .18s;
  font-family: inherit;
}
.form-inp:focus {
  border-color: rgba(168,85,247,0.5);
  background: rgba(168,85,247,0.06);
  box-shadow: 0 0 0 3px rgba(168,85,247,0.1);
}
.form-inp option { background: #1a1729; color: #e2e2e2; }
.form-inp[type="file"] { padding: 7px 10px; cursor: pointer; }

.input-hint { font-size: 0.68rem; color: rgba(255,255,255,0.3); margin-top: 4px; }

/* File preview */
.file-prev {
  margin-top: 8px;
  display: flex; align-items: center; gap: 10px;
  padding: 8px 10px;
  background: rgba(255,255,255,0.03);
  border: 1px dashed rgba(255,255,255,0.1);
  border-radius: 8px;
}
.file-prev img {
  width: 44px; height: 44px;
  object-fit: contain; border-radius: 7px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.1);
  padding: 2px;
}
.file-prev span { font-size: 0.7rem; color: rgba(255,255,255,0.35); }

/* Live preview card */
.rek-preview {
  background: linear-gradient(135deg, rgba(168,85,247,0.2), rgba(59,130,246,0.15), rgba(168,85,247,0.1));
  border: 1px solid rgba(168,85,247,0.25);
  border-radius: 14px;
  padding: 18px;
  margin-top: 16px;
  position: relative; overflow: hidden;
}
.rek-preview::before {
  content:'';
  position:absolute; top:-30px; right:-30px;
  width:100px; height:100px;
  background: radial-gradient(circle, rgba(168,85,247,.3), transparent 70%);
  border-radius:50%;
}
.rp-chip {
  display: inline-flex; align-items: center; gap: 5px;
  background: rgba(255,255,255,0.08);
  padding: 2px 9px; border-radius: 20px;
  font-size: 0.62rem; font-weight: 700; letter-spacing: 1px;
  color: rgba(255,255,255,0.45); text-transform: uppercase;
  margin-bottom: 12px;
}
.rp-bank {
  font-size: 1.15rem; font-weight: 800;
  background: linear-gradient(135deg, #c084fc, #818cf8);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text; margin-bottom: 8px;
}
.rp-no {
  font-size: 1rem; font-weight: 700; letter-spacing: 3px;
  color: #fff; margin-bottom: 5px;
  font-variant-numeric: tabular-nums;
}
.rp-nama { font-size: 0.75rem; color: rgba(255,255,255,0.45); }
.rp-nama strong { color: rgba(255,255,255,0.8); }

/* BUTTONS */
.btn-dark {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 16px; border: none; border-radius: 8px;
  font-size: 0.8rem; font-weight: 600; cursor: pointer;
  text-decoration: none; font-family: inherit; transition: opacity .15s;
}
.btn-dark:hover { opacity: .82; }
.btn-purple { background: rgba(168,85,247,0.2); color: #c084fc; border: 1px solid rgba(168,85,247,.3); }
.btn-gray   { background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,.1); }
.btn-blue   { background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(96,165,250,.25); }
.btn-red    { background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(248,113,113,.2); }
.btn-sm     { padding: 5px 11px; font-size: 0.72rem; border-radius: 7px; }

/* TABLE */
.table-wrap { overflow-x: auto; }
.dark-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.dark-table thead th {
  background: rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.4); font-weight: 500;
  font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.6px;
  padding: 0.65rem 1rem;
  border-bottom: 1px solid rgba(255,255,255,0.07);
  white-space: nowrap;
}
.dark-table tbody td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.75);
  vertical-align: middle;
}
.dark-table tbody tr:last-child td { border-bottom: none; }
.dark-table tbody tr:hover td { background: rgba(255,255,255,0.03); }

.bank-badge {
  display: inline-flex; align-items: center; gap: 5px;
  background: rgba(168,85,247,0.12);
  border: 1px solid rgba(168,85,247,.2);
  color: #c084fc; padding: 3px 10px;
  border-radius: 20px; font-size: 0.72rem; font-weight: 600;
}

.logo-thumb {
  width: 40px; height: 40px;
  object-fit: contain; border-radius: 8px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,.1); padding: 3px;
}
.qr-thumb {
  width: 44px; height: 44px;
  object-fit: contain; border-radius: 7px;
  background: rgba(255,255,255,0.9);
  border: 1px solid rgba(255,255,255,.15);
  padding: 2px; cursor: pointer;
  transition: transform .15s;
}
.qr-thumb:hover { transform: scale(1.12); }
.no-img {
  width: 40px; height: 40px;
  background: rgba(255,255,255,0.04);
  border: 1px dashed rgba(255,255,255,0.1);
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  color: rgba(255,255,255,0.2); font-size: 0.9rem;
}

.empty-state {
  text-align: center; padding: 3rem 1rem;
  color: rgba(255,255,255,0.3); font-size: 0.85rem;
}
.empty-state i { font-size: 2rem; display: block; margin-bottom: 0.5rem; }

/* RESPONSIVE */
@media (max-width: 991.98px) {
  .main-content { margin-left: 0; }
  .topbar { padding: .75rem 1rem; }
  .sidebar-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.55); z-index: 199;
  }
  .sidebar-overlay.show { display: block; }
  #sidebar {
    position: fixed; top: 0; left: -260px; bottom: 0;
    width: 240px; z-index: 200; transition: left .25s ease;
  }
  #sidebar.open { left: 0; }
  .content-area { padding: 1rem; }
  .layout-grid { grid-template-columns: 1fr; }
  .stat-grid { grid-template-columns: repeat(3,1fr); }
}
@media (max-width: 575.98px) {
  .stat-grid { grid-template-columns: 1fr; }
  .topbar-time { display: none; }
}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="main-content">

  <!-- TOPBAR -->
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:10px;">
      <button class="d-lg-none" onclick="openSidebar()"
              style="background:none;border:none;color:#fff;font-size:1.25rem;padding:0;line-height:1;cursor:pointer;">
        <i class="bi bi-list"></i>
      </button>
      <div class="topbar-title"><i class="bi bi-bank2 me-2"></i>Data Rekening Bank</div>
    </div>
    <div class="topbar-time" id="clock"></div>
  </div>

  <div class="content-area">

    <!-- ALERT -->
    <?php if ($pesan): ?>
    <div class="alert-dark alert-<?= $tipe ?>">
      <i class="bi bi-<?= $tipe==='sukses' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
      <?= htmlspecialchars($pesan) ?>
    </div>
    <?php endif; ?>

    <!-- STAT -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="sc-icon sc-purple"><i class="bi bi-bank2"></i></div>
        <div class="sc-label">Total Rekening</div>
        <div class="sc-value"><?= $total_rek ?></div>
      </div>
      <div class="stat-card">
        <div class="sc-icon sc-green"><i class="bi bi-qr-code"></i></div>
        <div class="sc-label">Punya QRIS</div>
        <div class="sc-value"><?= $total_qris ?></div>
      </div>
      <div class="stat-card">
        <div class="sc-icon sc-blue"><i class="bi bi-image"></i></div>
        <div class="sc-label">Punya Logo</div>
        <div class="sc-value"><?= $total_logo ?></div>
      </div>
    </div>

    <!-- LAYOUT -->
    <div class="layout-grid">

      <!-- ── FORM ── -->
      <div class="dark-card">
        <div class="dark-card-header">
          <div class="ch-icon"><i class="bi bi-<?= $edit_data ? 'pencil' : 'plus-lg' ?>"></i></div>
          <h2><?= $edit_data ? 'Edit Rekening' : 'Tambah Rekening Baru' ?></h2>
        </div>
        <div class="dark-card-body">
          <form action="simpan_bank.php" method="POST" enctype="multipart/form-data">
            <?php if ($edit_data): ?>
              <input type="hidden" name="id"        value="<?= $edit_data['id_bank'] ?>">
              <input type="hidden" name="aksi"      value="edit">
              <input type="hidden" name="logo_lama" value="<?= htmlspecialchars($edit_data['logo']??'') ?>">
              <input type="hidden" name="qris_lama" value="<?= htmlspecialchars($edit_data['qris']??'') ?>">
            <?php else: ?>
              <input type="hidden" name="aksi" value="tambah">
            <?php endif; ?>

            <div class="mb-3">
              <label class="form-lbl"><i class="bi bi-bank2 me-1"></i>Nama Bank</label>
              <select name="nama_bank" id="selBank" class="form-inp" required>
                <option value="">-- Pilih Bank --</option>
                <?php
                $banks = ['BCA','BNI','BRI','Mandiri','BSI','CIMB Niaga','Danamon','Permata','BTN','Bank Jateng','Bank Jatim','OVO','GoPay','Dana','ShopeePay','LinkAja','Lainnya'];
                foreach ($banks as $b):
                  $sel = ($edit_data && $edit_data['nama_bank']===$b) ? 'selected' : '';
                ?>
                  <option value="<?= $b ?>" <?= $sel ?>><?= $b ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-lbl"><i class="bi bi-person me-1"></i>Nama Pemilik Rekening</label>
              <input type="text" name="nama_pemilik" id="inpNama" class="form-inp"
                     placeholder="Nama sesuai rekening"
                     value="<?= htmlspecialchars($edit_data['nama_pemilik']??'') ?>"
                     required maxlength="100">
            </div>

            <div class="mb-3">
              <label class="form-lbl"><i class="bi bi-hash me-1"></i>Nomor Rekening</label>
              <input type="text" name="nomor_rekening" id="inpNo" class="form-inp"
                     placeholder="Contoh: 1234567890"
                     value="<?= htmlspecialchars($edit_data['nomor_rekening']??'') ?>"
                     required maxlength="50">
            </div>

            <div class="mb-3">
              <label class="form-lbl"><i class="bi bi-image me-1"></i>Logo Bank
                <span style="font-weight:400;text-transform:none;color:rgba(255,255,255,.25)">(opsional)</span>
              </label>
              <input type="file" name="logo" class="form-inp" accept="image/*">
              <div class="input-hint">JPG, PNG, WEBP — maks 2MB</div>
              <?php if (!empty($edit_data['logo'])): ?>
              <div class="file-prev">
                <img src="../uploads/bank/<?= htmlspecialchars($edit_data['logo']) ?>" alt="logo">
                <span><?= htmlspecialchars($edit_data['logo']) ?></span>
              </div>
              <?php endif; ?>
            </div>

            <div class="mb-4">
              <label class="form-lbl"><i class="bi bi-qr-code me-1"></i>Gambar QRIS
                <span style="font-weight:400;text-transform:none;color:rgba(255,255,255,.25)">(opsional)</span>
              </label>
              <input type="file" name="qris" class="form-inp" accept="image/*">
              <div class="input-hint">Upload gambar QRIS untuk halaman pembayaran</div>
              <?php if (!empty($edit_data['qris'])): ?>
              <div class="file-prev">
                <img src="../uploads/bank/<?= htmlspecialchars($edit_data['qris']) ?>" alt="qris">
                <span><?= htmlspecialchars($edit_data['qris']) ?></span>
              </div>
              <?php endif; ?>
            </div>

            <div style="display:flex;gap:8px;margin-bottom:0;">
              <button type="submit" class="btn-dark btn-purple">
                <i class="bi bi-<?= $edit_data ? 'floppy' : 'plus-lg' ?>"></i>
                <?= $edit_data ? 'Simpan Perubahan' : 'Tambah Rekening' ?>
              </button>
              <?php if ($edit_data): ?>
              <a href="bank.php" class="btn-dark btn-gray"><i class="bi bi-x-lg"></i> Batal</a>
              <?php endif; ?>
            </div>

            <!-- Live preview -->
            <div class="rek-preview">
              <div class="rp-chip"><i class="bi bi-bank2 me-1"></i>Preview Rekening</div>
              <div class="rp-bank" id="prevBank"><?= htmlspecialchars($edit_data['nama_bank']??'Nama Bank') ?></div>
              <div class="rp-no"  id="prevNo"  ><?= htmlspecialchars($edit_data['nomor_rekening']??'•••• •••• ••••') ?></div>
              <div class="rp-nama">a.n. <strong id="prevNama"><?= htmlspecialchars($edit_data['nama_pemilik']??'Nama Pemilik') ?></strong></div>
            </div>
          </form>
        </div>
      </div>

      <!-- ── TABLE ── -->
      <div class="dark-card">
        <div class="dark-card-header">
          <div class="ch-icon"><i class="bi bi-list-ul"></i></div>
          <h2>Daftar Rekening</h2>
        </div>
        <div class="table-wrap">
          <?php if (mysqli_num_rows($query) > 0): ?>
          <table class="dark-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Logo</th>
                <th>Bank</th>
                <th>No. Rekening</th>
                <th>Nama Pemilik</th>
                <th>QRIS</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            mysqli_data_seek($query, 0);
            while ($row = mysqli_fetch_assoc($query)):
            ?>
              <tr>
                <td style="color:rgba(255,255,255,.3)"><?= $no++ ?></td>

                <td>
                  <?php if (!empty($row['logo'])): ?>
                  <img src="../uploads/bank/<?= htmlspecialchars($row['logo']) ?>"
                       class="logo-thumb" alt="logo">
                  <?php else: ?>
                  <div class="no-img"><i class="bi bi-image"></i></div>
                  <?php endif; ?>
                </td>

                <td>
                  <span class="bank-badge">
                    <i class="bi bi-bank2"></i>
                    <?= htmlspecialchars($row['nama_bank']) ?>
                  </span>
                </td>

                <td>
                  <span style="font-weight:600;color:#fff;letter-spacing:1px;">
                    <?= htmlspecialchars($row['nomor_rekening']) ?>
                  </span>
                </td>

                <td><?= htmlspecialchars($row['nama_pemilik']) ?></td>

                <td>
                  <?php if (!empty($row['qris'])): ?>
                  <img src="../uploads/bank/<?= htmlspecialchars($row['qris']) ?>"
                       class="qr-thumb" alt="qris"
                       onclick="window.open('../uploads/bank/<?= htmlspecialchars($row['qris']) ?>')"
                       title="Klik untuk lihat QRIS">
                  <?php else: ?>
                  <span style="color:rgba(255,255,255,.2);font-size:.8rem">—</span>
                  <?php endif; ?>
                </td>

                <td>
                  <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <a href="bank.php?edit=<?= $row['id_bank'] ?>"
                       class="btn-dark btn-blue btn-sm">
                      <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="bank.php?hapus=<?= $row['id_bank'] ?>"
                       class="btn-dark btn-red btn-sm"
                       onclick="return confirm('Yakin hapus rekening <?= htmlspecialchars(addslashes($row['nama_bank'])) ?>?')">
                      <i class="bi bi-trash3"></i> Hapus
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
          <?php else: ?>
            <div class="empty-state">
              <i class="bi bi-bank2"></i>
              Belum ada rekening.<br>
              <span style="font-size:.78rem;">Tambahkan melalui form di sebelah kiri.</span>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div><!-- /layout-grid -->
  </div><!-- /content-area -->
</div><!-- /main-content -->

<script>
/* Clock */
function tick() {
  const d = new Date();
  const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
  const el = document.getElementById('clock');
  if (el) el.textContent =
    days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear()
    + ' \u2014 ' + d.toLocaleTimeString('id-ID');
}
tick(); setInterval(tick, 1000);

/* Sidebar mobile */
function openSidebar()  { document.getElementById('sidebar').classList.add('open');    document.getElementById('sidebarOverlay').classList.add('show'); }
function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('show'); }

/* Live preview */
const selBank = document.getElementById('selBank');
const inpNo   = document.getElementById('inpNo');
const inpNama = document.getElementById('inpNama');

function updatePreview() {
  document.getElementById('prevBank').textContent = selBank.value || 'Nama Bank';
  document.getElementById('prevNo').textContent   = inpNo.value   || '•••• •••• ••••';
  document.getElementById('prevNama').textContent = inpNama.value  || 'Nama Pemilik';
}
selBank.addEventListener('change', updatePreview);
inpNo.addEventListener('input',    updatePreview);
inpNama.addEventListener('input',  updatePreview);
</script>
</body>
</html>