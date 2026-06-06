<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}
include '../config/koneksi.php';

$data       = mysqli_query($conn, "SELECT * FROM meja ORDER BY nomor_meja ASC");
$total_meja = mysqli_num_rows($data);
$data       = mysqli_query($conn, "SELECT * FROM meja ORDER BY nomor_meja ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Meja — Resto App</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
* { box-sizing: border-box; }
body {
  background: #0f0e1a;
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  color: #e2e2e2;
  min-height: 100vh;
  margin: 0;
}
.main-content { margin-left: 240px; min-height: 100vh; }

.topbar {
  background: rgba(18,16,42,0.97);
  border-bottom: 1px solid rgba(255,255,255,0.07);
  padding: 0.85rem 1.75rem;
  display: flex; align-items: center; justify-content: space-between;
  position: sticky; top: 0; z-index: 50;
}
.topbar-title { font-size: 1.05rem; font-weight: 600; color: #fff; }
.topbar-time  { font-size: 0.8rem; color: rgba(255,255,255,0.4); }

.content-area { padding: 1.75rem; }

.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
  gap: 1rem;
  margin-bottom: 1.75rem;
}
.stat-card {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px; padding: 1.25rem;
  transition: border-color 0.2s;
}
.stat-card:hover { border-color: rgba(255,255,255,0.15); }
.sc-icon {
  width: 40px; height: 40px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.15rem; margin-bottom: 0.9rem;
}
.sc-amber { background: rgba(245,158,11,0.15); color: #fbbf24; }
.sc-label {
  font-size: 0.7rem; font-weight: 500; text-transform: uppercase;
  letter-spacing: 0.8px; color: rgba(255,255,255,0.4); margin-bottom: 3px;
}
.sc-value { font-size: 1.55rem; font-weight: 700; color: #fff; line-height: 1.1; }
.sc-sub   { font-size: 0.72rem; color: rgba(255,255,255,0.3); margin-top: 4px; }

.section-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 1rem;
}
.section-title { font-size: 0.9rem; font-weight: 600; color: #fff; }

.btn-tambah {
  font-size: 0.72rem;
  background: rgba(168,85,247,0.15);
  color: #c084fc;
  padding: 5px 14px; border-radius: 20px;
  text-decoration: none;
  display: inline-flex; align-items: center; gap: 5px;
  transition: background 0.15s;
}
.btn-tambah:hover { background: rgba(168,85,247,0.25); color: #d8b4fe; }

.table-wrap {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px; overflow: hidden; margin-bottom: 1.75rem;
}
.table-wrap table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
.table-wrap thead th {
  background: rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.4); font-weight: 500;
  font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.6px;
  padding: 0.7rem 1.1rem;
  border-bottom: 1px solid rgba(255,255,255,0.07);
}
.table-wrap tbody td {
  padding: 0.75rem 1.1rem;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.75);
  vertical-align: middle;
}
.table-wrap tbody tr:last-child td { border-bottom: none; }
.table-wrap tbody tr:hover td { background: rgba(255,255,255,0.03); }

.qr-thumb {
  width: 56px; height: 56px; border-radius: 6px;
  background: #fff; padding: 2px;
  border: 1px solid rgba(255,255,255,0.1);
  object-fit: cover; vertical-align: middle;
}
.link-wrap { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; }
.link-wrap input {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 6px; color: rgba(255,255,255,0.45);
  font-size: 0.7rem; padding: 4px 8px; width: 190px; outline: none;
}

.act-btn {
  font-size: 0.7rem; padding: 4px 10px; border-radius: 6px;
  font-weight: 500; display: inline-flex; align-items: center; gap: 4px;
  text-decoration: none; border: none; cursor: pointer; transition: opacity .15s;
}
.act-btn:hover { opacity: .75; }
.act-green { background: rgba(34,197,94,0.15);  color: #4ade80; border: 1px solid rgba(74,222,128,.2); }
.act-blue  { background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(96,165,250,.2); }
.act-red   { background: rgba(239,68,68,0.12);  color: #f87171; border: 1px solid rgba(248,113,113,.2); }

.row-no { color: rgba(255,255,255,0.3); font-size: 0.82rem; }

.empty-state {
  text-align: center; padding: 2.5rem 1rem;
  color: rgba(255,255,255,0.3); font-size: 0.85rem;
}
.empty-state i { font-size: 2rem; display: block; margin-bottom: 0.5rem; }

.toast-copy {
  position: fixed; bottom: 24px; right: 24px;
  background: #162416; border: 1px solid rgba(74,222,128,.3);
  color: #4ade80; border-radius: 10px;
  padding: 9px 16px; font-size: .8rem;
  display: none; align-items: center; gap: 8px; z-index: 9999;
}
@keyframes fadeup { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }

/* ── RESPONSIVE ── */
@media (max-width: 991.98px) {
  .main-content { margin-left: 0; }
  .topbar { padding: 0.75rem 1rem; }
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
  .stat-grid { grid-template-columns: 1fr 1fr; }
  .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .link-wrap input { width: 130px; }
}
@media (max-width: 575.98px) {
  .stat-grid { grid-template-columns: 1fr; }
  .topbar-time { display: none; }
  .link-wrap input { width: 100px; }
  .qr-thumb { width: 44px; height: 44px; }
}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="main-content">

  <div class="topbar">
    <div style="display:flex;align-items:center;gap:10px;">
      <button class="d-lg-none"
              onclick="openSidebar()"
              style="background:none;border:none;color:#fff;font-size:1.25rem;padding:0;line-height:1;cursor:pointer;">
        <i class="bi bi-list"></i>
      </button>
      <div class="topbar-title"><i class="bi bi-grid-3x3-gap me-2"></i>Data Meja</div>
    </div>
    <div class="topbar-time" id="clock"></div>
  </div>

  <div class="content-area">

    <!-- Stat -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="sc-icon sc-amber"><i class="bi bi-grid-3x3-gap-fill"></i></div>
        <div class="sc-label">Total Meja Terdaftar</div>
        <div class="sc-value"><?= $total_meja ?></div>
        <div class="sc-sub"><?= date('d M Y') ?></div>
      </div>
    </div>

    <!-- Header -->
    <div class="section-header">
      <div class="section-title"><i class="bi bi-grid-3x3-gap me-2"></i>Daftar Meja &amp; QR Code</div>
      <a href="tambah_meja.php" class="btn-tambah">
        <i class="bi bi-plus-lg"></i> Tambah Meja
      </a>
    </div>

    <!-- Table -->
    <div class="table-scroll">
      <div class="table-wrap">
        <?php if ($total_meja > 0): ?>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Nomor Meja</th>
              <th>QR Code</th>
              <th>Link Customer</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $no = 1;
          while ($row = mysqli_fetch_assoc($data)):
           $link = "https://sistemrestoonline.42web.io/customer/index.php?meja=" . $row['nomor_meja'];
          ?>
            <tr>
              <td class="row-no"><?= $no++ ?></td>

              <td>
                <div style="font-weight:600;color:#fff;">Meja <?= htmlspecialchars($row['nomor_meja']) ?></div>
                <div style="font-size:.7rem;color:rgba(255,255,255,.3);margin-top:2px;">ID #<?= $row['id_meja'] ?></div>
              </td>

              <td>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                  <img src="../qr/meja/<?= htmlspecialchars($row['qr_code']) ?>"
                       class="qr-thumb"
                       onerror="this.style.display='none'"
                       alt="QR Meja <?= htmlspecialchars($row['nomor_meja']) ?>">
                  <a href="../qr/meja/<?= htmlspecialchars($row['qr_code']) ?>"
                     download
                     class="act-btn act-green">
                    <i class="bi bi-download"></i> Download
                  </a>
                </div>
              </td>

              <td>
                <div class="link-wrap">
                  <input type="text"
                         id="link-<?= $row['id_meja'] ?>"
                         value="<?= htmlspecialchars($link) ?>"
                         readonly>
                  <button class="act-btn act-blue"
                          onclick="copyLink('<?= $row['id_meja'] ?>')"
                          title="Salin link">
                    <i class="bi bi-clipboard"></i>
                  </button>
                  <a href="<?= htmlspecialchars($link) ?>"
                     target="_blank"
                     class="act-btn act-blue"
                     title="Buka link">
                    <i class="bi bi-box-arrow-up-right"></i>
                  </a>
                </div>
              </td>

              <td>
                <a href="hapus_meja.php?id=<?= $row['id_meja'] ?>"
                   class="act-btn act-red"
                   onclick="return confirm('Hapus Meja <?= htmlspecialchars($row['nomor_meja']) ?>?\nQR Code juga akan dihapus.')">
                  <i class="bi bi-trash3"></i> Hapus
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
        <?php else: ?>
          <div class="empty-state">
            <i class="bi bi-grid-3x3-gap"></i>
            Belum ada meja terdaftar.<br>
            <a href="tambah_meja.php" style="color:#c084fc;font-size:.82rem;">+ Tambah meja sekarang</a>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<div class="toast-copy" id="toastCopy" style="animation:fadeup .25s ease;">
  <i class="bi bi-check-circle-fill"></i> Link disalin!
</div>

<script>
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

function openSidebar() {
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('sidebarOverlay').classList.add('show');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('show');
}

function copyLink(id) {
  const val = document.getElementById('link-' + id).value;
  navigator.clipboard.writeText(val).then(() => {
    const t = document.getElementById('toastCopy');
    t.style.display = 'flex';
    setTimeout(() => { t.style.display = 'none'; }, 2200);
  });
}
</script>
</body>
</html>