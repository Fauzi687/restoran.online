<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Grafik Penjualan — Resto App</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
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

.page-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 1.5rem; flex-wrap: wrap; gap: 12px;
}
.page-header-left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.page-header-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.badge-live {
  background: rgba(74,222,128,0.12); color: #4ade80;
  font-size: 0.68rem; font-weight: 600; padding: 3px 10px;
  border-radius: 20px; letter-spacing: .04em; animation: blink 1.8s infinite;
}
.badge-history {
  background: rgba(251,191,36,0.12); color: #fbbf24;
  font-size: 0.68rem; font-weight: 600; padding: 3px 10px;
  border-radius: 20px; letter-spacing: .04em;
}
@keyframes blink { 0%,100%{opacity:1;} 50%{opacity:.35;} }

/* ── Navigasi tanggal (hanya muncul di mode harian) ── */
.date-nav {
  display: flex; align-items: center; gap: 6px;
}
.date-nav-btn {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px; color: #e2e2e2;
  padding: 5px 10px; cursor: pointer; font-size: 0.82rem;
  transition: background 0.15s; line-height: 1;
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
  font-family: inherit; transition: background .15s;
}
.today-btn:hover { background: rgba(168,85,247,0.25); }

.mode-toggle {
  display: flex; background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 12px; padding: 4px; gap: 2px;
}
.mode-btn {
  padding: 6px 16px; border: none; border-radius: 9px;
  font-size: 0.78rem; font-weight: 600; cursor: pointer;
  transition: all .2s; background: transparent;
  color: rgba(255,255,255,0.4); font-family: inherit;
}
.mode-btn.active { background: rgba(168,85,247,0.25); color: #c084fc; box-shadow: 0 2px 8px rgba(168,85,247,0.2); }
.mode-btn:hover:not(.active) { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.7); }

.btn-export {
  display: flex; align-items: center; gap: 6px;
  padding: 7px 16px; border-radius: 10px; border: none;
  background: rgba(34,197,94,0.15); color: #4ade80;
  font-size: 0.78rem; font-weight: 600; cursor: pointer;
  font-family: inherit; transition: background .2s, transform .15s;
}
.btn-export:hover { background: rgba(34,197,94,0.25); transform: translateY(-1px); }
.btn-export:active { transform: translateY(0); }
.btn-export.loading { opacity: .7; pointer-events: none; }
.btn-export i { font-size: 0.9rem; }

.stat-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
  gap: 1rem; margin-bottom: 1.75rem;
}
.stat-card {
  background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
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
.sc-label {
  font-size: 0.7rem; font-weight: 500; text-transform: uppercase;
  letter-spacing: 0.8px; color: rgba(255,255,255,0.4); margin-bottom: 3px;
}
.sc-value { font-size: 1.55rem; font-weight: 700; color: #fff; line-height: 1.1; }
.sc-sub   { font-size: 0.72rem; color: rgba(255,255,255,0.3); margin-top: 4px; }

.chart-grid {
  display: grid; grid-template-columns: 1fr 320px;
  gap: 1rem; margin-bottom: 1rem;
}
@media(max-width:900px){ .chart-grid{ grid-template-columns:1fr; } }

.chart-card {
  background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px; padding: 1.25rem; margin-bottom: 1rem;
}
.chart-card-title {
  font-size: 0.7rem; font-weight: 500; text-transform: uppercase;
  letter-spacing: 0.8px; color: rgba(255,255,255,0.4); margin-bottom: 1rem;
  display: flex; align-items: center; gap: 6px;
}
.chart-card-title i { font-size: 0.85rem; }

.top-menu-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
.top-menu-item { display: flex; align-items: center; gap: 12px; }
.rank-num {
  width: 26px; height: 26px; border-radius: 8px; background: rgba(255,255,255,0.06);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.75rem; color: rgba(255,255,255,0.4); flex-shrink: 0;
}
.rank-num.gold   { background: rgba(251,191,36,0.15); color: #fbbf24; }
.rank-num.silver { background: rgba(148,163,184,0.15); color: #94a3b8; }
.rank-num.bronze { background: rgba(251,146,60,0.15); color: #fb923c; }
.top-menu-info { flex: 1; min-width: 0; }
.top-menu-info .name {
  font-size: 0.8rem; font-weight: 500; color: rgba(255,255,255,0.75);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.top-menu-info .bar-wrap { height: 4px; background: rgba(255,255,255,0.07); border-radius: 10px; margin-top: 5px; overflow: hidden; }
.top-menu-info .bar-fill { height: 100%; border-radius: 10px; background: linear-gradient(90deg,#c084fc,#818cf8); transition: width .8s ease; }
.top-menu-qty { font-size: 0.78rem; font-weight: 700; color: #c084fc; flex-shrink: 0; }

.loading-state {
  display: flex; align-items: center; justify-content: center;
  height: 200px; color: rgba(255,255,255,0.3); font-size: 0.84rem; gap: 8px;
}
.spinner {
  width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.1);
  border-top-color: #c084fc; border-radius: 50%; animation: spin .7s linear infinite; flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }
.error-state {
  display: flex; align-items: center; justify-content: center;
  height: 200px; color: #f87171; font-size: 0.84rem; gap: 8px;
}

.toast-wrap {
  position: fixed; bottom: 20px; right: 20px;
  z-index: 9999; display: flex; flex-direction: column; gap: 6px;
}
.toast-item {
  background: rgba(18,16,42,0.97); border: 1px solid rgba(255,255,255,0.1);
  border-left: 3px solid #4ade80; color: #e2e2e2;
  padding: 10px 16px; border-radius: 10px; font-size: 0.8rem; font-weight: 500;
  box-shadow: 0 4px 20px rgba(0,0,0,.4); animation: slideUp .25s ease;
}
@keyframes slideUp { from{transform:translateY(14px);opacity:0;} to{transform:translateY(0);opacity:1;} }

.hamburger { display: none; background: none; border: none; color: #fff; font-size: 1.25rem; cursor: pointer; padding: 2px 6px; }
.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 99; }
.sidebar-overlay.show { display: block; }

@media (max-width: 991px) {
  .main-content { margin-left: 0; }
  .topbar-time  { display: none; }
  .hamburger    { display: inline-block; }
  #sidebar { transform: translateX(-100%); transition: transform .25s ease; }
  #sidebar.open { transform: translateX(0); }
}
@media (max-width: 575px) {
  .content-area { padding: 1rem; }
  .sc-value { font-size: 1.2rem; }
  .chart-card { padding: 1rem; }
  .btn-export span { display: none; }
  .date-nav { flex-wrap: wrap; }
}
</style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:10px;">
      <button class="hamburger" onclick="openSidebar()"><i class="bi bi-list"></i></button>
      <div class="topbar-title"><i class="bi bi-bar-chart-line me-2"></i>Grafik Penjualan</div>
    </div>
    <div class="topbar-time" id="clock"></div>
  </div>

  <div class="content-area">

    <!-- Page Header -->
    <div class="page-header">
      <div class="page-header-left">
        <!-- Badge: LIVE kalau hari ini, RIWAYAT kalau tanggal lain -->
        <span id="badgeLive" class="badge-live">● LIVE</span>
        <span id="badgeHistory" class="badge-history" style="display:none">📅 Riwayat</span>

        <!-- Navigasi tanggal — hanya tampil di mode harian -->
        <div class="date-nav" id="dateNav">
          <button class="date-nav-btn" id="btnPrev" onclick="geserHari(-1)" title="Hari sebelumnya">
            <i class="bi bi-chevron-left"></i>
          </button>
          <input type="date" class="date-input" id="tglPicker"
                 value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>"
                 onchange="pilihTanggal(this.value)">
          <button class="date-nav-btn" id="btnNext" onclick="geserHari(1)" title="Hari berikutnya" disabled>
            <i class="bi bi-chevron-right"></i>
          </button>
          <button class="today-btn" id="btnToday" onclick="kembalikanHariIni()" style="display:none">
            Hari Ini
          </button>
        </div>
      </div>

      <div class="page-header-right">
        <div class="mode-toggle">
          <button class="mode-btn active" data-mode="harian">Harian</button>
          <button class="mode-btn" data-mode="mingguan">Mingguan</button>
          <button class="mode-btn" data-mode="bulanan">Bulanan</button>
        </div>
        <button class="btn-export" id="btnExport" onclick="exportExcel()">
          <i class="bi bi-file-earmark-excel-fill"></i>
          <span>Export Excel</span>
        </button>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="sc-icon sc-purple"><i class="bi bi-cash-stack"></i></div>
        <div class="sc-label">Total Pendapatan</div>
        <div class="sc-value" id="sumRev">—</div>
        <div class="sc-sub" id="sumRevSub">memuat...</div>
      </div>
      <div class="stat-card">
        <div class="sc-icon sc-green"><i class="bi bi-receipt-cutoff"></i></div>
        <div class="sc-label">Total Transaksi</div>
        <div class="sc-value" id="sumTrx">—</div>
        <div class="sc-sub" id="sumTrxSub">memuat...</div>
      </div>
      <div class="stat-card">
        <div class="sc-icon sc-blue"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="sc-label">Rata-rata per Transaksi</div>
        <div class="sc-value" id="sumAvg">—</div>
        <div class="sc-sub" id="sumAvgSub">per transaksi</div>
      </div>
    </div>

    <!-- Charts -->
    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-card-title">
          <i class="bi bi-bar-chart-fill" style="color:#c084fc"></i>
          <span id="titlePendapatan">Pendapatan (Rp)</span>
        </div>
        <div id="wrapPendapatan">
          <canvas id="chartPendapatan" height="220"></canvas>
        </div>
      </div>
      <div class="chart-card">
        <div class="chart-card-title">
          <i class="bi bi-trophy-fill" style="color:#fbbf24"></i> Menu Terlaris
        </div>
        <ul class="top-menu-list" id="topMenuList">
          <li class="loading-state"><div class="spinner"></div> Memuat...</li>
        </ul>
      </div>
    </div>

    <div class="chart-card">
      <div class="chart-card-title">
        <i class="bi bi-activity" style="color:#60a5fa"></i>
        <span id="titleTransaksi">Jumlah Transaksi</span>
      </div>
      <div id="wrapTransaksi">
        <canvas id="chartTransaksi" height="120"></canvas>
      </div>
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
function openSidebar()  { document.getElementById('sidebar')?.classList.add('open');    document.getElementById('sidebarOverlay').classList.add('show'); }
function closeSidebar() { document.getElementById('sidebar')?.classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('show'); }

/* ── Toast ── */
function toast(msg, color) {
  const w = document.getElementById('toastWrap');
  const t = document.createElement('div');
  t.className = 'toast-item';
  t.style.borderLeftColor = color || '#4ade80';
  t.textContent = msg;
  w.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

/* ── Helpers ── */
const fmt      = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');
const fmtShort = (n) => {
  n = parseFloat(n) || 0;
  if (n >= 1_000_000) return 'Rp ' + (n / 1_000_000).toFixed(1) + 'jt';
  if (n >= 1_000)     return 'Rp ' + (n / 1_000).toFixed(0) + 'rb';
  return 'Rp ' + n;
};
function formatTglLabel(tgl) {
  const d = new Date(tgl + 'T00:00:00');
  const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
  return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
}

/* ── State ── */
const TODAY   = '<?= date('Y-m-d') ?>';
let mode      = 'harian';
let tglAktif  = TODAY;
let chartPend = null;
let chartTrx  = null;
let lastData  = null;
let autoRefreshTimer = null;

const gridColor = 'rgba(255,255,255,0.05)';
const tickColor = 'rgba(255,255,255,0.3)';
const tickFont  = { size: 11, family: "'Segoe UI', system-ui, sans-serif" };

/* ── Update UI navigasi tanggal ── */
function updateDateNav() {
  const isHarian  = (mode === 'harian');
  const isToday   = (tglAktif === TODAY);

  // Sembunyikan/tampilkan navigasi tanggal
  document.getElementById('dateNav').style.display = isHarian ? 'flex' : 'none';

  if (isHarian) {
    document.getElementById('tglPicker').value = tglAktif;
    document.getElementById('btnNext').disabled = (tglAktif >= TODAY);
    document.getElementById('btnToday').style.display = isToday ? 'none' : 'inline-block';

    // Badge LIVE vs Riwayat
    document.getElementById('badgeLive').style.display    = isToday ? 'inline-block' : 'none';
    document.getElementById('badgeHistory').style.display = isToday ? 'none' : 'inline-block';

    // Judul chart
    const label = isToday ? 'Hari Ini' : formatTglLabel(tglAktif);
    document.getElementById('titlePendapatan').textContent = 'Pendapatan (Rp) — ' + label;
    document.getElementById('titleTransaksi').textContent  = 'Jumlah Transaksi — ' + label;
  } else {
    // Mode mingguan/bulanan: selalu LIVE, tidak ada navigasi tanggal
    document.getElementById('badgeLive').style.display    = 'inline-block';
    document.getElementById('badgeHistory').style.display = 'none';
    document.getElementById('titlePendapatan').textContent = 'Pendapatan (Rp)';
    document.getElementById('titleTransaksi').textContent  = 'Jumlah Transaksi';
  }
}

/* ── Summary labels ── */
function getModeLabel() {
  if (mode === 'harian') {
    return tglAktif === TODAY
      ? 'Hari Ini — ' + new Date().toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' })
      : formatTglLabel(tglAktif);
  }
  if (mode === 'mingguan') return '7 Hari Terakhir';
  return '12 Bulan Terakhir';
}

/* ── Load data ── */
function loadData() {
  let url = 'grafik_api.php?mode=' + mode + '&t=' + Date.now();
  if (mode === 'harian') url += '&tgl=' + tglAktif;

  fetch(url)
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(data => {
      if (data.db_error) console.error('DB Error:', data.db_error);
      lastData = data;
      renderSummary(data);
      renderChartPendapatan(data);
      renderChartTransaksi(data);
      renderTopMenu(data.top_menu || []);
    })
    .catch(err => {
      console.error('Gagal memuat grafik:', err);
      if (!chartPend) {
        document.getElementById('wrapPendapatan').innerHTML =
          '<div class="error-state"><i class="bi bi-exclamation-triangle"></i> Gagal memuat data.</div>';
        document.getElementById('wrapTransaksi').innerHTML =
          '<div class="error-state"><i class="bi bi-exclamation-triangle"></i> Gagal memuat data.</div>';
      }
    });
}

/* ── Summary ── */
function renderSummary(data) {
  const rev = parseFloat(data.summary_rev) || 0;
  const trx = parseInt(data.summary_trx)   || 0;
  const avg = trx > 0 ? rev / trx : 0;
  const lbl = getModeLabel();
  document.getElementById('sumRev').textContent    = fmtShort(rev);
  document.getElementById('sumRevSub').textContent = lbl;
  document.getElementById('sumTrx').textContent    = trx + ' pesanan';
  document.getElementById('sumTrxSub').textContent = lbl;
  document.getElementById('sumAvg').textContent    = fmtShort(avg);
}

/* ── Chart Pendapatan ── */
function renderChartPendapatan(data) {
  if (chartPend) {
    chartPend.data.labels = data.labels || [];
    chartPend.data.datasets[0].data = data.total || [];
    chartPend.options.scales.x.ticks.maxTicksLimit = mode === 'harian' ? 12 : 20;
    chartPend.update('none');
    return;
  }
  const ctx = document.getElementById('chartPendapatan').getContext('2d');
  chartPend = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.labels || [],
      datasets: [{
        label: 'Pendapatan',
        data: data.total || [],
        backgroundColor: function(context) {
          const chart = context.chart;
          const { ctx: c, chartArea } = chart;
          if (!chartArea) return 'rgba(192,132,252,0.6)';
          const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
          g.addColorStop(0, 'rgba(192,132,252,0.85)');
          g.addColorStop(1, 'rgba(192,132,252,0.05)');
          return g;
        },
        borderColor: '#c084fc', borderWidth: 2,
        borderRadius: 8, borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      animation: { duration: 500 },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(18,16,42,0.95)',
          borderColor: 'rgba(255,255,255,0.1)', borderWidth: 1,
          titleColor: 'rgba(255,255,255,0.5)', bodyColor: '#c084fc',
          callbacks: { label: (ctx) => ' ' + fmt(ctx.raw) }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: tickFont, color: tickColor, maxTicksLimit: 12 } },
        y: { grid: { color: gridColor }, ticks: { font: tickFont, color: tickColor, callback: (v) => fmtShort(v) }, beginAtZero: true }
      }
    }
  });
}

/* ── Chart Transaksi ── */
function renderChartTransaksi(data) {
  if (chartTrx) {
    chartTrx.data.labels = data.labels || [];
    chartTrx.data.datasets[0].data = data.jumlah || [];
    chartTrx.options.scales.x.ticks.maxTicksLimit = mode === 'harian' ? 12 : 20;
    chartTrx.update('none');
    return;
  }
  const ctx = document.getElementById('chartTransaksi').getContext('2d');
  chartTrx = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.labels || [],
      datasets: [{
        label: 'Transaksi',
        data: data.jumlah || [],
        borderColor: '#60a5fa', backgroundColor: 'rgba(96,165,250,0.07)',
        borderWidth: 2.5, pointBackgroundColor: '#60a5fa',
        pointRadius: 4, pointHoverRadius: 6,
        tension: 0.4, fill: true,
      }]
    },
    options: {
      responsive: true,
      animation: { duration: 500 },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(18,16,42,0.95)',
          borderColor: 'rgba(255,255,255,0.1)', borderWidth: 1,
          titleColor: 'rgba(255,255,255,0.5)', bodyColor: '#60a5fa',
          callbacks: { label: (ctx) => ' ' + ctx.raw + ' transaksi' }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: tickFont, color: tickColor, maxTicksLimit: 12 } },
        y: { grid: { color: gridColor }, ticks: { font: tickFont, color: tickColor, stepSize: 1, callback: (v) => Number.isInteger(v) ? v : '' }, beginAtZero: true }
      }
    }
  });
}

/* ── Top menu ── */
function renderTopMenu(list) {
  const el = document.getElementById('topMenuList');
  if (!list.length) {
    el.innerHTML = '<li style="color:rgba(255,255,255,0.3);font-size:.8rem;text-align:center;padding:20px 0;">Belum ada data</li>';
    return;
  }
  const maxQty  = list[0].qty || 1;
  const rankCls = ['gold','silver','bronze','',''];
  el.innerHTML  = list.map((item, i) => `
    <li class="top-menu-item">
      <div class="rank-num ${rankCls[i] || ''}">${i + 1}</div>
      <div class="top-menu-info">
        <div class="name">${String(item.nama || '').replace(/</g,'&lt;')}</div>
        <div class="bar-wrap"><div class="bar-fill" style="width:${Math.round(item.qty / maxQty * 100)}%"></div></div>
      </div>
      <div class="top-menu-qty">${item.qty}x</div>
    </li>`).join('');
}

/* ── Destroy chart (saat ganti mode) ── */
function destroyCharts() {
  if (chartPend) { chartPend.destroy(); chartPend = null; }
  if (chartTrx)  { chartTrx.destroy();  chartTrx  = null; }
}

/* ── Auto refresh: aktif hanya saat mode harian + hari ini ── */
function resetAutoRefresh() {
  clearInterval(autoRefreshTimer);
  autoRefreshTimer = null;

  const shouldRefresh = (mode !== 'harian') || (tglAktif === TODAY);
  if (shouldRefresh) {
    autoRefreshTimer = setInterval(loadData, 30000);
  }
  // Mode harian + tanggal lampau: tidak perlu auto-refresh
}

/* ── Navigasi tanggal ── */
function pilihTanggal(tgl) {
  if (tgl > TODAY) return;
  tglAktif = tgl;
  destroyCharts(); // destroy supaya animasi muncul saat ganti tanggal
  updateDateNav();
  resetAutoRefresh();
  loadData();
}

function geserHari(delta) {
  const d = new Date(tglAktif + 'T00:00:00');
  d.setDate(d.getDate() + delta);
  const tgl = d.getFullYear() + '-'
    + String(d.getMonth() + 1).padStart(2, '0') + '-'
    + String(d.getDate()).padStart(2, '0');
  if (tgl > TODAY) return;
  pilihTanggal(tgl);
}

function kembalikanHariIni() {
  pilihTanggal(TODAY);
}

/* ── Mode toggle ── */
document.querySelectorAll('.mode-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    if (this.dataset.mode === mode) return;
    document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    mode = this.dataset.mode;

    // Kembali ke hari ini saat pindah ke mode harian dari mode lain
    if (mode === 'harian') tglAktif = TODAY;

    destroyCharts();
    updateDateNav();
    resetAutoRefresh();
    loadData();
  });
});

/* ── Export Excel ── */
function exportExcel() {
  if (!lastData) { toast('⚠️ Data belum dimuat, tunggu sebentar.', '#fbbf24'); return; }

  const btn = document.getElementById('btnExport');
  btn.classList.add('loading');
  btn.innerHTML = '<i class="bi bi-hourglass-split"></i> <span>Menyiapkan...</span>';

  try {
    const data      = lastData;
    const rev       = parseFloat(data.summary_rev) || 0;
    const trx       = parseInt(data.summary_trx)   || 0;
    const avg       = trx > 0 ? Math.round(rev / trx) : 0;
    const labels    = data.labels   || [];
    const totals    = data.total    || [];
    const counts    = data.jumlah   || [];
    const topMenu   = data.top_menu || [];
    const colHead   = { harian:'Jam', mingguan:'Hari', bulanan:'Bulan' }[mode];
    const modeCaption = { harian:'Harian', mingguan:'Mingguan (7 Hari)', bulanan:'Bulanan (12 Bulan)' }[mode];
    const periodLabel = getModeLabel();
    const nowStr    = new Date().toLocaleString('id-ID', { day:'2-digit', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit' });

    const wb = XLSX.utils.book_new();

    // Sheet 1 — Ringkasan
    const wsR = XLSX.utils.aoa_to_sheet([
      ['REKAP PENJUALAN — RESTO APP'],
      ['Periode', modeCaption + (mode === 'harian' ? ' (' + periodLabel + ')' : '')],
      ['Tanggal Cetak', nowStr],
      [],
      ['RINGKASAN'],
      ['Total Pendapatan', rev],
      ['Total Transaksi', trx],
      ['Rata-rata per Transaksi', avg],
    ]);
    wsR['!cols'] = [{ wch: 26 }, { wch: 36 }];
    ['B6','B7','B8'].forEach(a => { if (wsR[a]) wsR[a].z = '"Rp "#,##0'; });
    XLSX.utils.book_append_sheet(wb, wsR, 'Ringkasan');

    // Sheet 2 — Detail
    const detailRows = labels.map((lbl, i) => [lbl, counts[i] || 0, totals[i] || 0]);
    const wsD = XLSX.utils.aoa_to_sheet([
      ['DETAIL PENJUALAN PER ' + colHead.toUpperCase()],
      ['Periode: ' + periodLabel],
      [],
      [colHead, 'Jumlah Transaksi', 'Pendapatan (Rp)'],
      ...detailRows,
      [],
      ['TOTAL', trx, rev],
    ]);
    wsD['!cols'] = [{ wch: 20 }, { wch: 20 }, { wch: 24 }];
    for (let i = 0; i < labels.length; i++) {
      const a = XLSX.utils.encode_cell({ r: 4 + i, c: 2 });
      if (wsD[a]) wsD[a].z = '"Rp "#,##0';
    }
    XLSX.utils.book_append_sheet(wb, wsD, 'Detail Per ' + colHead);

    // Sheet 3 — Menu Terlaris
    const wsM = XLSX.utils.aoa_to_sheet([
      ['MENU TERLARIS'],
      ['Periode: ' + periodLabel],
      [],
      ['Peringkat', 'Nama Menu', 'Qty Terjual'],
      ...topMenu.map((m, i) => [i + 1, m.nama, m.qty]),
    ]);
    wsM['!cols'] = [{ wch: 12 }, { wch: 28 }, { wch: 16 }];
    XLSX.utils.book_append_sheet(wb, wsM, 'Menu Terlaris');

    const tglFile = new Date().toISOString().slice(0, 10);
    const suffix  = mode === 'harian' ? tglAktif : tglFile;
    XLSX.writeFile(wb, `rekap-penjualan-${mode}-${suffix}.xlsx`);
    toast('✅ File Excel berhasil diunduh!');
  } catch (err) {
    console.error('Export error:', err);
    toast('❌ Gagal export Excel: ' + err.message, '#f87171');
  } finally {
    btn.classList.remove('loading');
    btn.innerHTML = '<i class="bi bi-file-earmark-excel-fill"></i> <span>Export Excel</span>';
  }
}

/* ── Init ── */
updateDateNav();
loadData();
resetAutoRefresh();
</script>
</body>
</html>