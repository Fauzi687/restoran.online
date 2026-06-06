<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

$data = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id_transaksi DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transaksi — Resto App</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* ── BASE — identik dashboard ── */
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

/* ── Section header ── */
.section-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 1rem;
}
.section-title { font-size: 0.9rem; font-weight: 600; color: #fff; }

/* ── Table wrap ── */
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
  padding: 0.7rem 1.1rem;
  border-bottom: 1px solid rgba(255,255,255,0.07);
  white-space: nowrap;
}
.table-wrap tbody td {
  padding: 0.7rem 1.1rem;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.75); vertical-align: middle;
  white-space: nowrap;
}
.table-wrap tbody tr:last-child td { border-bottom: none; }
.table-wrap tbody tr:hover td { background: rgba(255,255,255,0.03); }

/* ── Badges ── */
.bx {
  font-size: 0.7rem; padding: 3px 10px; border-radius: 20px;
  font-weight: 500; display: inline-block; white-space: nowrap;
}
.bx-green  { background: rgba(34,197,94,0.15);  color: #4ade80; }
.bx-yellow { background: rgba(245,158,11,0.15); color: #fbbf24; }
.bx-red    { background: rgba(239,68,68,0.15);  color: #f87171; }
.bx-blue   { background: rgba(59,130,246,0.15); color: #60a5fa; }
.bx-gray   { background: rgba(255,255,255,0.08);color: rgba(255,255,255,0.5); }
.bx-purple { background: rgba(168,85,247,0.15); color: #c084fc; }
.bx-orange { background: rgba(251,146,60,0.15); color: #fb923c; }

/* ── Filter bar ── */
.filter-bar { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.input-dark {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px; color: #e2e2e2;
  padding: 6px 12px; font-size: 0.82rem;
  outline: none; transition: border 0.2s; font-family: inherit;
}
.input-dark:focus { border-color: rgba(168,85,247,0.5); }
.input-dark::placeholder { color: rgba(255,255,255,0.25); }

/* ── Aksi tombol ── */
.aksi-stack { display: flex; flex-direction: column; gap: 4px; min-width: 110px; }
.btn-aksi {
  display: inline-flex; align-items: center; justify-content: center; gap: 4px;
  border: none; border-radius: 7px; padding: 5px 10px;
  font-size: 0.72rem; font-weight: 600; cursor: pointer;
  transition: all .18s; text-decoration: none; white-space: nowrap; width: 100%;
  font-family: inherit;
}
.btn-aksi:hover { opacity: 0.85; transform: translateY(-1px); }
.btn-aksi.lunas   { background: #22c55e; color: #fff; }
.btn-aksi.gagal   { background: #ef4444; color: #fff; }
.btn-aksi.diantar { background: #3b82f6; color: #fff; }
.btn-aksi.selesai { background: #334155; color: #fff; }
.btn-aksi.print-struk { background: #7c3aed; color: #fff; }
.btn-aksi.disabled { opacity: .45; pointer-events: none; }
.aksi-done { font-size: 0.72rem; color: rgba(255,255,255,0.35); font-style: italic; }

/* ── Bukti thumb ── */
.bukti-thumb {
  width: 44px; height: 44px; object-fit: cover;
  border-radius: 8px; border: 1px solid rgba(255,255,255,0.12);
  cursor: pointer; transition: transform .18s; display: block;
}
.bukti-thumb:hover { transform: scale(1.08); }
.bukti-tag {
  display: inline-flex; align-items: center; gap: 3px;
  font-size: 0.69rem; font-weight: 500; padding: 3px 7px; border-radius: 6px;
}
.bukti-tag.hilang { background: rgba(245,158,11,0.12); color: #fbbf24; }
.bukti-tag.kosong { background: rgba(239,68,68,0.10);  color: #f87171; }

/* ── Modal bukti ── */
.modal-bukti-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.88);
  display: none; align-items: center; justify-content: center;
  z-index: 9000; cursor: pointer;
}
.modal-bukti-overlay.show { display: flex; }
.modal-bukti-overlay img {
  max-width: 90vw; max-height: 88vh; border-radius: 14px;
  box-shadow: 0 20px 60px rgba(0,0,0,.6); cursor: default;
}
.modal-close-btn {
  position: fixed; top: 16px; right: 18px;
  background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
  border-radius: 50%; width: 34px; height: 34px;
  font-size: 1rem; cursor: pointer; color: #fff;
  display: flex; align-items: center; justify-content: center;
}
.modal-close-btn:hover { background: rgba(255,255,255,0.2); }

/* ── Modal Struk ── */
.modal-struk-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.88);
  display: none; align-items: center; justify-content: center;
  z-index: 9100;
}
.modal-struk-overlay.show { display: flex; }
.struk-box {
  background: #fff; color: #111;
  border-radius: 12px; width: 320px; max-height: 90vh;
  overflow-y: auto; padding: 24px 20px;
  font-family: 'Courier New', monospace; font-size: 0.8rem;
  box-shadow: 0 20px 60px rgba(0,0,0,.6);
}
.struk-box .struk-header { text-align: center; margin-bottom: 12px; }
.struk-box .struk-header h2 { font-size: 1rem; font-weight: 700; margin: 0 0 2px; }
.struk-box .struk-header p  { font-size: 0.72rem; color: #555; margin: 0; }
.struk-divider {
  border: none; border-top: 1px dashed #aaa; margin: 10px 0;
}
.struk-row {
  display: flex; justify-content: space-between;
  margin: 3px 0; font-size: 0.78rem;
}
.struk-row.bold { font-weight: 700; font-size: 0.85rem; }
.struk-row.total {
  border-top: 1px dashed #aaa; padding-top: 6px;
  margin-top: 6px; font-weight: 700; font-size: 0.9rem;
}
.struk-items { margin: 6px 0; }
.struk-item { margin: 4px 0; }
.struk-item-name { font-weight: 600; }
.struk-item-sub  { display: flex; justify-content: space-between; color: #444; padding-left: 8px; }
.struk-footer { text-align: center; margin-top: 12px; font-size: 0.7rem; color: #666; }
.struk-actions {
  display: flex; gap: 8px; margin-top: 14px; justify-content: center;
}
.struk-btn {
  padding: 7px 18px; border-radius: 8px; border: none;
  font-size: 0.8rem; font-weight: 600; cursor: pointer;
  font-family: inherit; transition: opacity .2s;
}
.struk-btn:hover { opacity: .85; }
.struk-btn.print { background: #6d28d9; color: #fff; }
.struk-btn.close { background: #e5e7eb; color: #333; }

/* ── Toast ── */
.toast-wrap {
  position: fixed; bottom: 20px; right: 20px;
  z-index: 9999; display: flex; flex-direction: column; gap: 6px;
}
.toast-item {
  background: rgba(18,16,42,0.97);
  border: 1px solid rgba(255,255,255,0.1);
  color: #e2e2e2; padding: 10px 16px;
  border-radius: 10px; font-size: 0.8rem; font-weight: 500;
  box-shadow: 0 4px 20px rgba(0,0,0,.4); animation: slideUp .25s ease;
}
@keyframes slideUp { from{transform:translateY(14px);opacity:0;} to{transform:translateY(0);opacity:1;} }

/* ── Badge LIVE ── */
.badge-live {
  background: rgba(34,197,94,0.12); color: #4ade80;
  font-size: 0.68rem; font-weight: 600; padding: 3px 10px;
  border-radius: 20px; letter-spacing: 0.04em; animation: blink 1.8s infinite;
}
@keyframes blink { 0%,100%{opacity:1;} 50%{opacity:.3;} }

/* ── Catatan ── */
.catatan-box {
  background: rgba(245,158,11,0.07); border: 1px solid rgba(245,158,11,0.2);
  border-radius: 7px; padding: 4px 8px;
  font-size: 0.72rem; color: #fbbf24; max-width: 140px; line-height: 1.4;
}
.catatan-nil { color: rgba(255,255,255,0.25); font-size: 0.72rem; font-style: italic; }

/* ── Empty state ── */
.empty-state {
  text-align: center; padding: 2.5rem 1rem;
  color: rgba(255,255,255,0.3); font-size: 0.85rem;
}
.empty-state i { font-size: 2rem; display: block; margin-bottom: 0.5rem; }

/* ── Hamburger & overlay ── */
.hamburger { display: none; }
.sidebar-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.5); z-index: 150;
}
.sidebar-overlay.show { display: block; }

/* ── Responsive ── */
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
  .table-wrap { font-size: 0.78rem; }
}

/* ── Print — hanya tampilkan struk ── */
@media print {
  body > *:not(.modal-struk-overlay) { display: none !important; }
  .modal-struk-overlay {
    position: static !important; background: none !important;
    display: block !important;
  }
  .struk-box {
    box-shadow: none !important; border-radius: 0 !important;
    max-height: none !important; width: 100% !important;
  }
  .struk-actions { display: none !important; }
  .modal-close-btn { display: none !important; }
}
</style>
</head>
<body>

<!-- Sidebar overlay mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="topbar">
    <div class="d-flex align-items: center">
      <button class="hamburger" onclick="openSidebar()"><i class="bi bi-list"></i></button>
      <span class="topbar-title">
        <i class="bi bi-receipt me-2"></i>Transaksi
        <span class="badge-live ms-2">● LIVE</span>
      </span>
    </div>
    <div class="topbar-time" id="clock"></div>
  </div>

  <div class="content-area">

    <!-- Header tabel -->
    <div class="section-header">
      <div class="section-title"><i class="bi bi-table me-2"></i>Semua Transaksi</div>
      <div class="filter-bar">
        <input type="text" id="searchInput" class="input-dark"
               placeholder="Cari transaksi..." onkeyup="filterTable()" style="width:180px">
        <select id="filterStatus" class="input-dark" onchange="filterTable()">
          <option value="">Semua Status</option>
          <option value="dibayar">Lunas</option>
          <option value="pending">Pending</option>
          <option value="gagal">Gagal</option>
        </select>
      </div>
    </div>

    <div class="table-wrap">
      <?php if ($data && mysqli_num_rows($data) > 0): ?>
      <table id="trxTable">
        <thead>
          <tr>
            <th>No</th>
            <th>No Pesanan</th>
            <th>Pelanggan</th>
            <th>Meja</th>
            <th>Bayar Via</th>
            <th>Bukti</th>
            <th>Status Bayar</th>
            <th>Status Pesanan</th>
            <th>Total</th>
            <th>Catatan</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_array($data)):
          $tgl   = $row['tanggal'] ?? '';
          $tgl_f = $tgl ? date('d M Y', strtotime($tgl)) : '';
          $jam_f = $tgl ? date('H:i',   strtotime($tgl)) : '';

          $sp_bayar = $row['status_pembayaran'] ?? 'pending';
          $sp_pesan = $row['status_pesanan']    ?? 'menunggu';
          $sudah    = in_array($sp_bayar, ['dibayar','gagal']);

          // Badge bayar
          $badge_bayar = match($sp_bayar) {
            'dibayar' => '<span class="bx bx-green"><i class="bi bi-check-circle"></i> Lunas</span>',
            'gagal'   => '<span class="bx bx-red"><i class="bi bi-x-circle"></i> Gagal</span>',
            default   => '<span class="bx bx-yellow"><i class="bi bi-hourglass-split"></i> Pending</span>',
          };

          // Badge pesanan
          $pesan_map = [
            'menunggu' => ['bx-blue',   'bi-clock',         'Menunggu'],
            'diproses' => ['bx-yellow', 'bi-fire',          'Diproses'],
            'diantar'  => ['bx-purple', 'bi-bicycle',       'Diantar'],
            'selesai'  => ['bx-green',  'bi-check-circle',  'Selesai'],
            'gagal'    => ['bx-red',    'bi-x-circle',      'Gagal'],
          ];
          if (isset($pesan_map[$sp_pesan])) {
            [$pcls, $pico, $ptxt] = $pesan_map[$sp_pesan];
            $badge_pesan = "<span class=\"bx $pcls\"><i class=\"bi $pico\"></i> $ptxt</span>";
          } else {
            $badge_pesan = '<span class="bx bx-gray">'.htmlspecialchars($sp_pesan).'</span>';
          }

          // Tampilan meja — jika takeaway tampilkan label khusus
          $no_pesanan_val = $row['no_pesanan'] ?? '';
          $is_takeaway    = str_starts_with($no_pesanan_val, 'TKW-');
          $nomor_meja_val = $is_takeaway ? 'Takeaway' : ($row['nomor_meja'] ?? '-');
        ?>
        <tr id="row-<?= $row['id_transaksi'] ?>">

          <td style="color:rgba(255,255,255,0.3);font-size:0.74rem"><?= $no++ ?></td>

          <td style="color:#c084fc;font-weight:600;font-size:0.78rem">
            <?= htmlspecialchars($no_pesanan_val) ?>
          </td>

          <td style="font-weight:500"><?= htmlspecialchars($row['nama_pelanggan'] ?? '-') ?></td>

          <td style="text-align:center">
            <span class="bx <?= $is_takeaway ? 'bx-orange' : 'bx-purple' ?>">
              <?= $is_takeaway ? '<i class="bi bi-bag-check"></i> ' : '' ?>
              <?= htmlspecialchars($nomor_meja_val) ?>
            </span>
          </td>

          <td style="color:rgba(255,255,255,0.4);text-transform:capitalize">
            <?php
              $m = strtolower($row['metode_pembayaran'] ?? '');
              $mico = match($m) {
                'transfer','bank' => 'bi-bank2',
                'qris'            => 'bi-qr-code',
                'tunai','cash'    => 'bi-cash',
                default           => 'bi-credit-card',
              };
            ?>
            <i class="bi <?= $mico ?>"></i> <?= htmlspecialchars(ucfirst($m ?: '-')) ?>
          </td>

          <!-- Bukti -->
          <td>
            <?php
              $bukti     = trim($row['bukti_pembayaran'] ?? '');
              $file_path = "../uploads/bukti/" . $bukti;
            ?>
            <?php if ($bukti !== '' && file_exists($file_path)): ?>
              <img src="../uploads/bukti/<?= htmlspecialchars($bukti) ?>"
                   class="bukti-thumb" onclick="lihatBukti(this.src)"
                   alt="Bukti" title="Klik untuk perbesar">
            <?php elseif ($bukti !== ''): ?>
              <span class="bukti-tag hilang"><i class="bi bi-exclamation-triangle"></i> Hilang</span>
            <?php else: ?>
              <span class="bukti-tag kosong"><i class="bi bi-x"></i> Tidak Ada</span>
            <?php endif; ?>
          </td>

          <td id="sbayar-<?= $row['id_transaksi'] ?>"><?= $badge_bayar ?></td>

          <td id="spesan-<?= $row['id_transaksi'] ?>"><?= $badge_pesan ?></td>

          <td style="color:#4ade80;font-weight:600">
            Rp <?= number_format($row['total'] ?? 0, 0, ',', '.') ?>
          </td>

          <td>
            <?php $cat = trim($row['catatan'] ?? ''); ?>
            <?php if ($cat !== ''): ?>
              <div class="catatan-box"><i class="bi bi-chat-left-text me-1"></i><?= nl2br(htmlspecialchars($cat)) ?></div>
            <?php else: ?>
              <span class="catatan-nil">— kosong</span>
            <?php endif; ?>
          </td>

          <td style="color:rgba(255,255,255,0.4);font-size:0.77rem">
            <?php if ($tgl_f): ?>
              <div><?= $tgl_f ?></div>
              <div style="font-size:0.7rem;opacity:.6"><i class="bi bi-clock me-1"></i><?= $jam_f ?></div>
            <?php else: ?>—<?php endif; ?>
          </td>

          <!-- Aksi -->
          <td>
            <div class="aksi-stack" id="aksi-<?= $row['id_transaksi'] ?>">
              <?php if (!$sudah): ?>
                <button class="btn-aksi lunas"
                  onclick="updateBayar(<?= $row['id_transaksi'] ?>, 'dibayar', '<?= htmlspecialchars($row['no_pesanan'] ?? '') ?>')">
                  <i class="bi bi-check-circle"></i> Lunas
                </button>
                <button class="btn-aksi gagal"
                  onclick="updateBayar(<?= $row['id_transaksi'] ?>, 'gagal', '<?= htmlspecialchars($row['no_pesanan'] ?? '') ?>')">
                  <i class="bi bi-x-circle"></i> Gagal
                </button>
              <?php else: ?>
                <span class="aksi-done">
                  <?= $sp_bayar === 'dibayar'
                      ? '<i class="bi bi-check-circle text-success"></i> Dikonfirmasi'
                      : '<i class="bi bi-x-circle text-danger"></i> Ditolak' ?>
                </span>
                <?php if ($sp_bayar === 'dibayar'): ?>
                  <button class="btn-aksi print-struk"
                    onclick="bukaPrintStruk(<?= $row['id_transaksi'] ?>)">
                    <i class="bi bi-printer"></i> Print Struk
                  </button>
                <?php endif; ?>
              <?php endif; ?>
              <?php if ($sp_pesan === 'diproses'): ?>
                <a href="update_status.php?id=<?= $row['id_transaksi'] ?>&status=diantar" class="btn-aksi diantar">
                  <i class="bi bi-bicycle"></i> Diantar
                </a>
              <?php endif; ?>
              <?php if ($sp_pesan === 'diantar'): ?>
                <a href="update_status.php?id=<?= $row['id_transaksi'] ?>&status=selesai" class="btn-aksi selesai">
                  <i class="bi bi-check2-all"></i> Selesai
                </a>
              <?php endif; ?>
            </div>
          </td>

        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <div class="empty-state">
          <i class="bi bi-receipt-cutoff"></i>
          Belum ada transaksi
        </div>
      <?php endif; ?>
    </div>

  </div><!-- /content-area -->
</div><!-- /main-content -->

<!-- Toast -->
<div class="toast-wrap" id="toastWrap"></div>

<!-- Modal Bukti -->
<div class="modal-bukti-overlay" id="buktiModal" onclick="tutupBukti()">
  <button class="modal-close-btn" onclick="tutupBukti()"><i class="bi bi-x"></i></button>
  <img src="" id="buktiImg" alt="Bukti" onclick="event.stopPropagation()">
</div>

<!-- Modal Struk -->
<div class="modal-struk-overlay" id="strukModal" onclick="if(event.target===this)tutupStruk()">
  <div class="struk-box" id="strukKonten"></div>
</div>

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

/* ── Sidebar mobile ── */
function openSidebar() {
  document.querySelector('#sidebar, .sidebar')?.classList.add('open');
  document.getElementById('sidebarOverlay').classList.add('show');
}
function closeSidebar() {
  document.querySelector('#sidebar, .sidebar')?.classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('show');
}

/* ── Filter tabel ── */
function filterTable() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const status = document.getElementById('filterStatus').value.toLowerCase();
  document.querySelectorAll('#trxTable tbody tr').forEach(row => {
    const text       = row.textContent.toLowerCase();
    const statusCell = (row.cells[6]?.textContent ?? '').trim().toLowerCase();
    const matchSearch = text.includes(search);
    const matchStatus = !status || statusCell.includes(status);
    row.style.display = matchSearch && matchStatus ? '' : 'none';
  });
}

/* ── Update bayar ── */
const sudahDiubah = {};

function updateBayar(id, status, noPesanan) {
  const label = status === 'dibayar' ? 'Lunas' : 'Gagal';
  if (!confirm('Tandai pesanan ' + noPesanan + ' sebagai "' + label + '"?')) return;

  const aksiEl = document.getElementById('aksi-' + id);
  aksiEl.querySelectorAll('button').forEach(b => {
    b.classList.add('disabled');
    b.innerHTML = '<i class="bi bi-hourglass-split"></i> ...';
  });

  fetch('update_bayar.php?id=' + id + '&status=' + status)
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        const newBayar = res.status_pembayaran;
        const newPesan = res.status_pesanan;
        sudahDiubah[id] = { bayar: newBayar, pesan: newPesan };
        updateBadgeBayar(id, newBayar);
        updateBadgePesan(id, newPesan);

        let html = '<span class="aksi-done">'
          + (newBayar === 'dibayar'
              ? '<i class="bi bi-check-circle text-success"></i> Dikonfirmasi'
              : '<i class="bi bi-x-circle text-danger"></i> Ditolak')
          + '</span>';

        if (newBayar === 'dibayar') {
          html += '<button class="btn-aksi print-struk" onclick="bukaPrintStruk(' + id + ')">'
                + '<i class="bi bi-printer"></i> Print Struk</button>';
        }

        if (newPesan === 'diproses') {
          html += '<a href="update_status.php?id=' + id + '&status=diantar" class="btn-aksi diantar">'
                + '<i class="bi bi-bicycle"></i> Diantar</a>';
        }

        aksiEl.innerHTML = html;
        showToast((newBayar === 'dibayar' ? '✅' : '✕') + ' Pesanan ' + noPesanan + ' ditandai ' + label);
      } else {
        showToast('❌ Gagal: ' + (res.msg || 'Coba lagi'));
        location.reload();
      }
    })
    .catch(() => { showToast('❌ Koneksi bermasalah.'); location.reload(); });
}

function updateBadgeBayar(id, status) {
  const el = document.getElementById('sbayar-' + id);
  if (!el) return;
  const map = {
    dibayar: '<span class="bx bx-green"><i class="bi bi-check-circle"></i> Lunas</span>',
    gagal:   '<span class="bx bx-red"><i class="bi bi-x-circle"></i> Gagal</span>',
    pending: '<span class="bx bx-yellow"><i class="bi bi-hourglass-split"></i> Pending</span>',
  };
  el.innerHTML = map[status] || map['pending'];
}

function updateBadgePesan(id, status) {
  const el = document.getElementById('spesan-' + id);
  if (!el) return;
  const map = {
    menunggu: '<span class="bx bx-blue"><i class="bi bi-clock"></i> Menunggu</span>',
    diproses: '<span class="bx bx-yellow"><i class="bi bi-fire"></i> Diproses</span>',
    diantar:  '<span class="bx bx-purple"><i class="bi bi-bicycle"></i> Diantar</span>',
    selesai:  '<span class="bx bx-green"><i class="bi bi-check-circle"></i> Selesai</span>',
    gagal:    '<span class="bx bx-red"><i class="bi bi-x-circle"></i> Gagal</span>',
  };
  el.innerHTML = map[status] || '<span class="bx bx-gray">' + status + '</span>';
}

/* ── Auto refresh ── */
let refreshTimer = null;
function jadwalkanRefresh() {
  if (refreshTimer) clearTimeout(refreshTimer);
  refreshTimer = setTimeout(() => {
    if (Object.keys(sudahDiubah).length === 0) location.reload();
    else jadwalkanRefresh();
  }, 15000);
}
jadwalkanRefresh();

/* ── Polling auto selesai ── */
function pollAutoSelesai() {
  fetch('auto_selesai_api.php?t=' + Date.now())
    .then(r => r.json())
    .then(res => {
      if (!res.rows) return;
      res.rows.forEach(row => {
        const elPesan = document.getElementById('spesan-' + row.id);
        if (!elPesan) return;
        const badgeNow  = elPesan.querySelector('.bx');
        const statusNow = badgeNow?.textContent.trim().toLowerCase() ?? '';
        if (row.pesan === 'selesai' && !statusNow.includes('selesai')) {
          updateBadgePesan(row.id, 'selesai');
          const aksiEl = document.getElementById('aksi-' + row.id);
          aksiEl?.querySelectorAll('.diantar, .selesai').forEach(b => b.remove());
          showToast('✅ Pesanan #' + row.id + ' otomatis selesai');
        } else if (row.pesan !== 'selesai') {
          updateBadgePesan(row.id, row.pesan);
        }
      });
    })
    .catch(() => {});
}
setInterval(pollAutoSelesai, 5000);
pollAutoSelesai();

/* ── Bukti modal ── */
function lihatBukti(src) {
  document.getElementById('buktiImg').src = src;
  document.getElementById('buktiModal').classList.add('show');
}
function tutupBukti() {
  document.getElementById('buktiModal').classList.remove('show');
  document.getElementById('buktiImg').src = '';
}

/* ── Print Struk ── */
function bukaPrintStruk(id) {
  document.getElementById('strukKonten').innerHTML =
    '<div style="text-align:center;padding:30px;color:#999">'
    + '<i class="bi bi-hourglass-split" style="font-size:1.5rem"></i>'
    + '<div style="margin-top:8px;font-size:0.8rem">Memuat struk...</div></div>';
  document.getElementById('strukModal').classList.add('show');

  fetch('get_struk.php?id=' + id)
    .then(r => r.json())
    .then(res => {
      if (!res.success) {
        showToast('❌ Gagal ambil data struk');
        tutupStruk();
        return;
      }
      renderStruk(res.data);
    })
    .catch(() => {
      showToast('❌ Koneksi bermasalah');
      tutupStruk();
    });
}

function renderStruk(d) {
  const tgl = d.tanggal
    ? new Date(d.tanggal).toLocaleString('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
      })
    : '-';

  const isTakeaway = d.no_pesanan && d.no_pesanan.startsWith('TKW-');

  const itemsHtml = (d.items || []).map(it => `
    <div class="struk-item">
      <div class="struk-item-name">${escHtml(it.nama_menu)}</div>
      <div class="struk-item-sub">
        <span>${it.qty} x Rp ${fmtRp(it.harga)}</span>
        <span>Rp ${fmtRp(it.qty * it.harga)}</span>
      </div>
    </div>`).join('');

  const metodeIcon = {
    transfer: '🏦', bank: '🏦', qris: '📱', tunai: '💵', cash: '💵'
  };
  const mIcon = metodeIcon[String(d.metode_pembayaran).toLowerCase()] || '💳';

  document.getElementById('strukKonten').innerHTML = `
    <div class="struk-header">
      <h2>🍽️ RESTO APP</h2>
      <p>Struk Pembayaran</p>
    </div>
    <hr class="struk-divider">
    <div class="struk-row">
      <span>No Pesanan</span>
      <span style="font-weight:700;color:#6d28d9">${escHtml(d.no_pesanan)}</span>
    </div>
    <div class="struk-row">
      <span>Pelanggan</span>
      <span>${escHtml(d.nama_pelanggan)}</span>
    </div>
    <div class="struk-row">
      <span>No. Meja</span>
      <span>${isTakeaway ? 'Takeaway' : escHtml(d.nomor_meja)}</span>
    </div>
    <div class="struk-row">
      <span>Tanggal</span>
      <span>${tgl}</span>
    </div>
    <div class="struk-row">
      <span>Metode Bayar</span>
      <span>${mIcon} ${escHtml(String(d.metode_pembayaran).charAt(0).toUpperCase() + String(d.metode_pembayaran).slice(1))}</span>
    </div>
    <hr class="struk-divider">
    <div style="font-weight:700;font-size:0.75rem;margin-bottom:6px;letter-spacing:0.05em">DETAIL PESANAN :</div>
    <div class="struk-items">
      ${itemsHtml || '<div style="color:#999;font-style:italic;font-size:0.75rem">Detail item tidak tersedia</div>'}
    </div>
    <hr class="struk-divider">
    <div class="struk-row total">
      <span>TOTAL BAYAR</span>
      <span>Rp ${fmtRp(d.total)}</span>
    </div>
    <div class="struk-row" style="margin-top:4px">
      <span>Status</span>
      <span style="color:#16a34a;font-weight:700">✔ LUNAS</span>
    </div>
    ${d.catatan ? `
    <div style="margin-top:8px;background:#fffbeb;border:1px solid #fde68a;
      border-radius:6px;padding:5px 8px;font-size:0.72rem;color:#92400e">
      <b>Catatan:</b> ${escHtml(d.catatan)}
    </div>` : ''}
    <div class="struk-footer">
      <hr class="struk-divider">
      Terima kasih telah memesan!<br>
      Selamat menikmati hidangannya 😊
    </div>
    <div class="struk-actions">
      <button class="struk-btn print" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
      </button>
      <button class="struk-btn close" onclick="tutupStruk()">Tutup</button>
    </div>
  `;
}

function tutupStruk() {
  document.getElementById('strukModal').classList.remove('show');
}

/* ── Helpers ── */
function fmtRp(n) {
  return Number(n).toLocaleString('id-ID');
}
function escHtml(s) {
  if (!s) return '-';
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

/* ── Toast ── */
function showToast(msg) {
  const w = document.getElementById('toastWrap');
  const t = document.createElement('div');
  t.className = 'toast-item';
  t.textContent = msg;
  w.appendChild(t);
  setTimeout(() => t.remove(), 4500);
}

/* ── ESC close semua modal ── */
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { tutupBukti(); tutupStruk(); }
});
</script>
</body>
</html>