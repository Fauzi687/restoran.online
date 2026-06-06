<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

// ── AJAX polling stat cards ──
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $hari_ini = date('Y-m-d');

    // PERBAIKAN: pakai status_pesanan='selesai' bukan status_pembayaran='dibayar'
    $r = mysqli_query($conn,
        "SELECT COUNT(*) as jml, COALESCE(SUM(total),0) as uang
         FROM transaksi WHERE DATE(tanggal)='$hari_ini' AND status_pesanan='selesai'");
    $total_trx = 0; $pendapatan = 0;
    if ($r && $tmp = mysqli_fetch_assoc($r)) {
        $total_trx  = (int)$tmp['jml'];
        $pendapatan = (int)$tmp['uang'];
    }

    $r = mysqli_query($conn,
        "SELECT COUNT(*) as c FROM transaksi WHERE status_pembayaran='pending' AND DATE(tanggal)='$hari_ini'");
    $trx_pending = ($r && $row = mysqli_fetch_assoc($r)) ? (int)$row['c'] : 0;

    $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM menu");
    $total_menu = ($r && $row = mysqli_fetch_assoc($r)) ? (int)$row['c'] : 0;

    $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM kategori");
    $total_kategori = ($r && $row = mysqli_fetch_assoc($r)) ? (int)$row['c'] : 0;

    $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM meja");
    $total_meja = ($r && $row = mysqli_fetch_assoc($r)) ? (int)$row['c'] : 0;

    echo json_encode([
        'total_trx'      => $total_trx,
        'pendapatan'     => $pendapatan,
        'pendapatan_fmt' => 'Rp ' . number_format($pendapatan, 0, ',', '.'),
        'trx_pending'    => $trx_pending,
        'total_menu'     => $total_menu,
        'total_kategori' => $total_kategori,
        'total_meja'     => $total_meja,
    ]);
    exit;
}

// ── AJAX untuk tabel transaksi per tanggal ──
if (isset($_GET['ajax_trx'])) {
    header('Content-Type: application/json');
    $tgl = $_GET['tgl'] ?? date('Y-m-d');
    $tgl = preg_replace('/[^0-9\-]/', '', $tgl);

    $rows = mysqli_query($conn,
        "SELECT * FROM transaksi WHERE DATE(tanggal)='$tgl' ORDER BY id_transaksi DESC"
    );
    $data = [];
    if ($rows) {
        while ($r = mysqli_fetch_assoc($rows)) $data[] = $r;
    }

    echo json_encode(['rows' => $data]);
    exit;
}

// ── Load awal halaman ──
$hari_ini = date('Y-m-d');

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM menu");
$total_menu = ($r && $row = mysqli_fetch_assoc($r)) ? (int)$row['c'] : 0;

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM kategori");
$total_kategori = ($r && $row = mysqli_fetch_assoc($r)) ? (int)$row['c'] : 0;

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM meja");
$total_meja = ($r && $row = mysqli_fetch_assoc($r)) ? (int)$row['c'] : 0;

// PERBAIKAN: pakai status_pesanan='selesai'
$r = mysqli_query($conn,
    "SELECT COUNT(*) as jml, COALESCE(SUM(total),0) as uang
     FROM transaksi WHERE DATE(tanggal)='$hari_ini' AND status_pesanan='selesai'");
$total_trx = 0; $pendapatan = 0;
if ($r) {
    $tmp = mysqli_fetch_assoc($r);
    $total_trx  = (int)($tmp['jml']  ?? 0);
    $pendapatan = (int)($tmp['uang'] ?? 0);
}

$r = mysqli_query($conn,
    "SELECT COUNT(*) as c FROM transaksi WHERE status_pembayaran='pending' AND DATE(tanggal)='$hari_ini'");
$trx_pending = ($r && $row = mysqli_fetch_assoc($r)) ? (int)$row['c'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Resto App</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
* { box-sizing: border-box; }
body {
  background: #0f0e1a;
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  color: #e2e2e2; min-height: 100vh; margin: 0;
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
  gap: 1rem; margin-bottom: 1.75rem;
}
.stat-card {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px; padding: 1.25rem; transition: border-color 0.2s;
}
.stat-card:hover { border-color: rgba(255,255,255,0.15); }
.sc-icon {
  width: 40px; height: 40px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.15rem; margin-bottom: 0.9rem;
}
.sc-purple { background: rgba(168,85,247,0.18); color: #c084fc; }
.sc-green  { background: rgba(34,197,94,0.15);  color: #4ade80; }
.sc-blue   { background: rgba(59,130,246,0.15); color: #60a5fa; }
.sc-amber  { background: rgba(245,158,11,0.15); color: #fbbf24; }
.sc-label {
  font-size: 0.7rem; font-weight: 500; text-transform: uppercase;
  letter-spacing: 0.8px; color: rgba(255,255,255,0.4); margin-bottom: 3px;
}
.sc-value { font-size: 1.55rem; font-weight: 700; color: #fff; line-height: 1.1; }
.sc-sub   { font-size: 0.72rem; color: rgba(255,255,255,0.3); margin-top: 4px; }

@keyframes flashUpdate {
  0%,100% { opacity:1; }
  40%     { opacity:0.2; }
}
.sc-value.flash { animation: flashUpdate 0.55s ease; }
.stat-card.pending-on { border-color: rgba(245,158,11,0.35) !important; }

.section-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 1rem; flex-wrap: wrap; gap: 8px;
}
.section-title { font-size: 0.9rem; font-weight: 600; color: #fff; }
.section-link {
  font-size: 0.72rem; background: rgba(168,85,247,0.15); color: #c084fc;
  padding: 4px 12px; border-radius: 20px; text-decoration: none;
}
.section-link:hover { background: rgba(168,85,247,0.25); color: #d8b4fe; }

.date-nav { display: flex; align-items: center; gap: 6px; }
.date-nav-btn {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px; color: #e2e2e2;
  padding: 5px 10px; cursor: pointer; font-size: 0.82rem;
  transition: background 0.15s;
}
.date-nav-btn:hover { background: rgba(168,85,247,0.2); border-color: rgba(168,85,247,0.4); }
.date-nav-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.date-input {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px; color: #e2e2e2;
  padding: 5px 10px; font-size: 0.82rem; font-family: inherit;
  outline: none; cursor: pointer;
}
.date-input:focus { border-color: rgba(168,85,247,0.5); }
.today-btn {
  font-size: 0.72rem; background: rgba(168,85,247,0.15); color: #c084fc;
  padding: 4px 10px; border-radius: 20px; border: none; cursor: pointer;
  font-family: inherit;
}
.today-btn:hover { background: rgba(168,85,247,0.25); }

.table-wrap {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px; overflow: hidden; margin-bottom: 1.75rem;
  overflow-x: auto;
}
.table-wrap table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
.table-wrap thead th {
  background: rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.4); font-weight: 500;
  font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.6px;
  padding: 0.7rem 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.07);
  white-space: nowrap;
}
.table-wrap tbody td {
  padding: 0.7rem 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.75); vertical-align: middle; white-space: nowrap;
}
.table-wrap tbody tr:last-child td { border-bottom: none; }
.table-wrap tbody tr:hover td { background: rgba(255,255,255,0.03); }
.bx {
  font-size: 0.7rem; padding: 3px 10px; border-radius: 20px;
  font-weight: 500; display: inline-block;
}
.bx-green  { background: rgba(34,197,94,0.15);  color: #4ade80; }
.bx-yellow { background: rgba(245,158,11,0.15); color: #fbbf24; }
.bx-red    { background: rgba(239,68,68,0.15);  color: #f87171; }
.bx-blue   { background: rgba(59,130,246,0.15); color: #60a5fa; }
.bx-gray   { background: rgba(255,255,255,0.08);color: rgba(255,255,255,0.5); }
.bx-purple { background: rgba(168,85,247,0.15); color: #c084fc; }
.bx-orange { background: rgba(251,146,60,0.15); color: #fb923c; }

.tbl-loading {
  text-align: center; padding: 2rem;
  color: rgba(255,255,255,0.3); font-size: 0.85rem;
}

.shortcut-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.85rem;
}
.shortcut-card {
  background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px; padding: 1.1rem 1rem; text-decoration: none;
  display: flex; flex-direction: column; align-items: flex-start; gap: 8px;
  transition: background 0.15s, border-color 0.15s, transform 0.15s;
}
.shortcut-card:hover {
  background: rgba(255,255,255,0.07); border-color: rgba(168,85,247,0.3);
  transform: translateY(-2px);
}
.shortcut-card i { font-size: 1.25rem; }
.shortcut-card span { font-size: 0.82rem; color: rgba(255,255,255,0.7); font-weight: 500; }

.empty-state {
  text-align: center; padding: 2.5rem 1rem;
  color: rgba(255,255,255,0.3); font-size: 0.85rem;
}
.empty-state i { font-size: 2rem; display: block; margin-bottom: 0.5rem; }

.badge-live {
  background: rgba(34,197,94,0.12); color: #4ade80;
  font-size: 0.68rem; font-weight: 600; padding: 3px 10px;
  border-radius: 20px; letter-spacing:.04em; animation: blink 1.8s infinite;
}
@keyframes blink { 0%,100%{opacity:1;} 50%{opacity:.35;} }

.toast-wrap {
  position: fixed; bottom: 20px; right: 20px;
  z-index: 9999; display: flex; flex-direction: column; gap: 6px;
}
.toast-item {
  background: rgba(18,16,42,0.97); border: 1px solid rgba(255,255,255,0.1);
  border-left: 3px solid #c084fc;
  color: #e2e2e2; padding: 10px 16px; border-radius: 10px;
  font-size: 0.8rem; font-weight: 500;
  box-shadow: 0 4px 20px rgba(0,0,0,.4); animation: slideUp .25s ease;
}
@keyframes slideUp { from{transform:translateY(14px);opacity:0;} to{transform:translateY(0);opacity:1;} }

.hamburger { display: none; }
.sidebar-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.5); z-index: 150;
}
.sidebar-overlay.show { display: block; }

@media(max-width:991px) {
  .main-content { margin-left: 0; }
  .topbar { padding: 0.85rem 1rem; }
  .content-area { padding: 1rem; }
  .hamburger {
    display: inline-flex; align-items: center; justify-content: center;
    background: none; border: none; color: #fff;
    font-size: 1.3rem; cursor: pointer; margin-right: 0.5rem; padding: 0;
  }
  .topbar-time { display: none; }
}
@media(max-width:575px) {
  .content-area { padding: 0.75rem; }
  .sc-value { font-size: 1.2rem; }
}
</style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="topbar">
    <div class="d-flex align-items-center">
      <button class="hamburger" onclick="openSidebar()"><i class="bi bi-list"></i></button>
      <div class="topbar-title"><i class="bi bi-speedometer2 me-2"></i>Dashboard</div>
      <span class="badge-live ms-2">● LIVE</span>
    </div>
    <div class="topbar-time" id="clock"></div>
  </div>

  <div class="content-area">

    <!-- Stat Cards -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="sc-icon sc-purple"><i class="bi bi-receipt-cutoff"></i></div>
        <div class="sc-label">Pesanan Selesai Hari Ini</div>
        <div class="sc-value" id="statTrx"><?= $total_trx ?></div>
        <div class="sc-sub"><?= date('d M Y') ?></div>
      </div>
      <div class="stat-card">
        <div class="sc-icon sc-green"><i class="bi bi-cash-coin"></i></div>
        <div class="sc-label">Pendapatan Hari Ini</div>
        <div class="sc-value" id="statPendapatan">Rp <?= number_format($pendapatan, 0, ',', '.') ?></div>
        <div class="sc-sub">Dari pesanan selesai</div>
      </div>
      <div class="stat-card" id="cardPending">
        <div class="sc-icon sc-amber"><i class="bi bi-hourglass-split"></i></div>
        <div class="sc-label">Pending Hari Ini</div>
        <div class="sc-value" id="statPending"><?= $trx_pending ?></div>
        <div class="sc-sub">Belum dibayar</div>
      </div>
      <div class="stat-card">
        <div class="sc-icon sc-blue"><i class="bi bi-journal-text"></i></div>
        <div class="sc-label">Total Menu</div>
        <div class="sc-value" id="statMenu"><?= $total_menu ?></div>
        <div class="sc-sub" id="statKategori"><?= $total_kategori ?> kategori</div>
      </div>
      <div class="stat-card">
        <div class="sc-icon sc-amber"><i class="bi bi-grid-3x3-gap"></i></div>
        <div class="sc-label">Total Meja</div>
        <div class="sc-value" id="statMeja"><?= $total_meja ?></div>
        <div class="sc-sub">Terdaftar</div>
      </div>
    </div>

    <!-- Tabel Transaksi -->
    <div class="section-header">
      <div class="section-title" id="sectionTitle">
        <i class="bi bi-calendar-day me-2"></i>Transaksi Hari Ini
      </div>
      <div class="date-nav">
        <button class="date-nav-btn" id="btnPrev" onclick="geserHari(-1)">
          <i class="bi bi-chevron-left"></i>
        </button>
        <input type="date" class="date-input" id="tglPicker"
               value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>"
               onchange="loadTrx(this.value)">
        <button class="date-nav-btn" id="btnNext" onclick="geserHari(1)" disabled>
          <i class="bi bi-chevron-right"></i>
        </button>
        <button class="today-btn" onclick="kembalikanHariIni()">Hari Ini</button>
        <a href="transaksi.php" class="section-link">Semua &rsaquo;</a>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th><th>No. Pesanan</th><th>Pelanggan</th><th>Meja</th>
            <th>Total</th><th>Pembayaran</th><th>Pesanan</th><th>Waktu</th>
          </tr>
        </thead>
        <tbody id="trxTbody">
          <tr><td colspan="8" class="tbl-loading"><i class="bi bi-hourglass-split"></i> Memuat...</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Akses Cepat -->
    <div class="section-header">
      <div class="section-title"><i class="bi bi-lightning-charge me-2"></i>Akses Cepat</div>
    </div>
    <div class="shortcut-grid">
      <a href="transaksi.php" class="shortcut-card">
        <i class="bi bi-plus-circle" style="color:#c084fc"></i><span>Transaksi Baru</span>
      </a>
      <a href="menu.php" class="shortcut-card">
        <i class="bi bi-journal-plus" style="color:#60a5fa"></i><span>Tambah Menu</span>
      </a>
      <a href="meja.php" class="shortcut-card">
        <i class="bi bi-grid-3x3-gap" style="color:#fbbf24"></i><span>Kelola Meja</span>
      </a>
      <a href="kategori.php" class="shortcut-card">
        <i class="bi bi-tags" style="color:#4ade80"></i><span>Kategori</span>
      </a>
      <a href="grafik.php" class="shortcut-card">
        <i class="bi bi-bar-chart-line" style="color:#f87171"></i><span>Grafik Penjualan</span>
      </a>
      <a href="bank.php" class="shortcut-card">
        <i class="bi bi-bank" style="color:#fb923c"></i><span>Data Bank</span>
      </a>
    </div>

  </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
/* ── Clock ── */
function tick() {
  const d = new Date();
  const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
  document.getElementById('clock').textContent =
    days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear()
    + ' \u2014 ' + d.toLocaleTimeString('id-ID');
}
tick(); setInterval(tick, 1000);

/* ── Sidebar ── */
function openSidebar()  { document.querySelector('#sidebar,.sidebar')?.classList.add('open');    document.getElementById('sidebarOverlay').classList.add('show'); }
function closeSidebar() { document.querySelector('#sidebar,.sidebar')?.classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('show'); }

/* ── Helpers ── */
function fmt(n) { return Number(n).toLocaleString('id-ID'); }
function esc(s) {
  if (!s) return '-';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function formatTglLabel(tgl) {
  const d = new Date(tgl + 'T00:00:00');
  const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
  return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
}
function flash(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('flash'); void el.offsetWidth; el.classList.add('flash');
  setTimeout(() => el.classList.remove('flash'), 600);
}
function toast(msg) {
  const w = document.getElementById('toastWrap');
  const t = document.createElement('div');
  t.className = 'toast-item'; t.textContent = msg; w.appendChild(t);
  setTimeout(() => t.remove(), 4000);
}

// PERBAIKAN jam: format waktu dari string MySQL tanpa salah timezone
// "2024-01-15 14:30:00" → ambil langsung bagian jamnya tanpa new Date()
function jamDariMySQL(str) {
  if (!str) return '-';
  // Format MySQL: "YYYY-MM-DD HH:MM:SS"
  const parts = str.split(' ');
  if (parts.length < 2) return '-';
  const timeParts = parts[1].split(':');
  if (timeParts.length < 2) return '-';
  return timeParts[0] + ':' + timeParts[1]; // HH:MM
}

/* ── State ── */
const TODAY = '<?= date('Y-m-d') ?>';
let tglAktif = TODAY;
let fetchingTrx = false;

// Nilai awal stat cards — HANYA pollStats yang boleh update ini
let prev = {
  trx       : <?= $total_trx ?>,
  pendapatan: <?= $pendapatan ?>,
  pending   : <?= $trx_pending ?>,
  menu      : <?= $total_menu ?>,
  kategori  : <?= $total_kategori ?>,
  meja      : <?= $total_meja ?>,
};

/* ─────────────────────────────────────────────
   pollStats — SATU-SATUNYA yang update stat cards
   Tidak ada fungsi lain yang boleh nulis ke statTrx,
   statPendapatan, statPending, statMenu, dll.
───────────────────────────────────────────── */
function pollStats() {
  fetch('dashboard.php?ajax=1&t=' + Date.now())
    .then(r => { if (!r.ok) throw new Error(); return r.json(); })
    .then(d => {
      if (d.total_trx !== prev.trx) {
        document.getElementById('statTrx').textContent = d.total_trx;
        flash('statTrx');
        if (d.total_trx > prev.trx) toast('✅ Pesanan selesai: ' + d.total_trx);
        prev.trx = d.total_trx;
      }
      if (d.pendapatan !== prev.pendapatan) {
        document.getElementById('statPendapatan').textContent = d.pendapatan_fmt;
        flash('statPendapatan');
        if (d.pendapatan > prev.pendapatan) toast('💰 Pendapatan: ' + d.pendapatan_fmt);
        prev.pendapatan = d.pendapatan;
      }
      if (d.trx_pending !== prev.pending) {
        document.getElementById('statPending').textContent = d.trx_pending;
        flash('statPending');
        const card = document.getElementById('cardPending');
        if (card) card.classList.toggle('pending-on', d.trx_pending > 0);
        if (d.trx_pending > prev.pending) toast('⏳ Pesanan pending: ' + d.trx_pending);
        prev.pending = d.trx_pending;
      }
      if (d.total_menu !== prev.menu) {
        document.getElementById('statMenu').textContent = d.total_menu;
        flash('statMenu');
        toast('🍜 Total menu: ' + d.total_menu);
        prev.menu = d.total_menu;
      }
      if (d.total_kategori !== prev.kategori) {
        document.getElementById('statKategori').textContent = d.total_kategori + ' kategori';
        prev.kategori = d.total_kategori;
      }
      if (d.total_meja !== prev.meja) {
        document.getElementById('statMeja').textContent = d.total_meja;
        flash('statMeja');
        toast('🪑 Total meja: ' + d.total_meja);
        prev.meja = d.total_meja;
      }

      // Refresh tabel hanya jika sedang lihat hari ini
      if (tglAktif === TODAY && !fetchingTrx) {
        refreshTabelSaja();
      }
    })
    .catch(() => {});
}

/* ── refreshTabelSaja — update baris tabel tanpa sentuh stat cards ── */
function refreshTabelSaja() {
  if (fetchingTrx) return;
  fetchingTrx = true;
  fetch('dashboard.php?ajax_trx=1&tgl=' + tglAktif + '&t=' + Date.now())
    .then(r => r.json())
    .then(res => { renderTrx(res.rows || []); })
    .catch(() => {})
    .finally(() => { fetchingTrx = false; });
}

/* ── loadTrx — dipanggil saat user ganti tanggal, hanya update tabel ── */
function loadTrx(tgl) {
  tglAktif = tgl;
  document.getElementById('tglPicker').value = tgl;
  document.getElementById('btnNext').disabled = (tgl >= TODAY);

  const isToday = (tgl === TODAY);
  document.getElementById('sectionTitle').innerHTML =
    '<i class="bi bi-calendar-day me-2"></i>' +
    (isToday ? 'Transaksi Hari Ini' : 'Transaksi ' + formatTglLabel(tgl));

  // Tampilkan loading
  document.getElementById('trxTbody').innerHTML =
    '<tr><td colspan="8" class="tbl-loading"><i class="bi bi-hourglass-split"></i> Memuat...</td></tr>';

  fetchingTrx = true;
  fetch('dashboard.php?ajax_trx=1&tgl=' + tgl + '&t=' + Date.now())
    .then(r => r.json())
    .then(res => { renderTrx(res.rows || []); })
    .catch(() => {
      document.getElementById('trxTbody').innerHTML =
        '<tr><td colspan="8" class="tbl-loading">Gagal memuat data.</td></tr>';
    })
    .finally(() => { fetchingTrx = false; });
}

function renderTrx(rows) {
  const tbody = document.getElementById('trxTbody');
  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state">
      <i class="bi bi-calendar-x"></i>Belum ada transaksi</div></td></tr>`;
    return;
  }

  const spMap = {
    dibayar: ['bx-green',  'Lunas'],
    gagal:   ['bx-red',    'Gagal'],
    pending: ['bx-yellow', 'Pending'],
  };
  const soMap = {
    selesai:  ['bx-green',  'Selesai'],
    diantar:  ['bx-blue',   'Diantar'],
    diproses: ['bx-yellow', 'Diproses'],
    gagal:    ['bx-red',    'Gagal'],
    menunggu: ['bx-gray',   'Menunggu'],
  };

  let html = '';
  rows.forEach((row, i) => {
    const [spCls, spLbl] = spMap[row.status_pembayaran] ?? ['bx-gray', row.status_pembayaran ?? '-'];
    const [soCls, soLbl] = soMap[row.status_pesanan]    ?? ['bx-gray', row.status_pesanan    ?? '-'];

    // PERBAIKAN: ambil jam langsung dari string MySQL, bukan pakai new Date()
    const jamTampil = jamDariMySQL(row.tanggal);

    const isTakeaway = row.no_pesanan?.startsWith('TKW-');
    const mejaTxt    = isTakeaway ? 'Takeaway' : (row.nomor_meja || '-');
    const mejaCls    = isTakeaway ? 'bx-orange' : 'bx-purple';
    const mejaIcon   = isTakeaway ? '<i class="bi bi-bag-check me-1"></i>' : '';

    html += `
      <tr>
        <td style="color:rgba(255,255,255,0.3)">${i + 1}</td>
        <td style="color:#c084fc;font-weight:600">${esc(row.no_pesanan)}</td>
        <td>${esc(row.nama_pelanggan)}</td>
        <td><span class="bx ${mejaCls}">${mejaIcon}${esc(mejaTxt)}</span></td>
        <td style="color:#4ade80;font-weight:600">Rp ${fmt(row.total)}</td>
        <td><span class="bx ${spCls}">${spLbl}</span></td>
        <td><span class="bx ${soCls}">${soLbl}</span></td>
        <td style="color:rgba(255,255,255,0.4);font-size:0.77rem">${jamTampil}</td>
      </tr>`;
  });
  tbody.innerHTML = html;
}

function geserHari(delta) {
  const d = new Date(tglAktif + 'T00:00:00');
  d.setDate(d.getDate() + delta);
  const tgl = d.getFullYear() + '-'
    + String(d.getMonth() + 1).padStart(2, '0') + '-'
    + String(d.getDate()).padStart(2, '0');
  if (tgl > TODAY) return;
  loadTrx(tgl);
}
function kembalikanHariIni() { loadTrx(TODAY); }

/* ── Init ── */
if (prev.pending > 0) document.getElementById('cardPending')?.classList.add('pending-on');

loadTrx(TODAY);      // load tabel pertama kali
pollStats();         // load stat cards + set timer
setInterval(pollStats, 5000); // polling tiap 5 detik — satu-satunya timer
</script>
</body>
</html>