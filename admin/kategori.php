<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

$data = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id_kategori DESC");
$total = $data ? mysqli_num_rows($data) : 0;

// Hitung aktif & nonaktif
$r_aktif = mysqli_query($conn, "SELECT COUNT(*) as c FROM kategori WHERE status='aktif'");
$total_aktif = ($r_aktif && $row = mysqli_fetch_assoc($r_aktif)) ? (int)$row['c'] : 0;
$total_nonaktif = $total - $total_aktif;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kategori — Resto App</title>
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

/* Topbar */
.topbar {
  background: rgba(18,16,42,0.97);
  border-bottom: 1px solid rgba(255,255,255,0.07);
  padding: 0.85rem 1.75rem;
  display: flex; align-items: center; justify-content: space-between;
  position: sticky; top: 0; z-index: 50;
}
.topbar-title { font-size: 1.05rem; font-weight: 600; color: #fff; }

/* Content */
.content-area { padding: 1.75rem; }

/* Stat cards mini */
.mini-stats {
  display: flex; gap: 1rem; margin-bottom: 1.5rem;
  flex-wrap: wrap;
}
.mini-card {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px;
  padding: 1rem 1.4rem;
  display: flex; align-items: center; gap: 14px;
  min-width: 160px;
}
.mini-icon {
  width: 38px; height: 38px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; flex-shrink: 0;
}
.sc-purple { background: rgba(168,85,247,0.18); color: #c084fc; }
.sc-green  { background: rgba(34,197,94,0.15);  color: #4ade80; }
.sc-amber  { background: rgba(245,158,11,0.15); color: #fbbf24; }
.mini-label { font-size: 0.68rem; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.7px; }
.mini-value { font-size: 1.3rem; font-weight: 700; color: #fff; line-height: 1.1; }

/* Panel */
.panel {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  overflow: hidden;
}
.panel-header {
  padding: 1rem 1.4rem;
  border-bottom: 1px solid rgba(255,255,255,0.07);
  display: flex; align-items: center; justify-content: space-between;
}
.panel-title { font-size: 0.9rem; font-weight: 600; color: #fff; }

/* Search */
.search-wrap { position: relative; }
.search-wrap input {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px;
  color: #fff; font-size: 0.83rem;
  padding: 0.45rem 0.85rem 0.45rem 2.2rem;
  width: 200px; outline: none;
  transition: border-color 0.2s;
}
.search-wrap input:focus { border-color: rgba(168,85,247,0.5); }
.search-wrap input::placeholder { color: rgba(255,255,255,0.25); }
.search-wrap i {
  position: absolute; left: 0.65rem; top: 50%;
  transform: translateY(-50%);
  color: rgba(255,255,255,0.3); font-size: 0.85rem;
  pointer-events: none;
}

/* Btn tambah */
.btn-tambah {
  display: inline-flex; align-items: center; gap: 7px;
  background: linear-gradient(135deg, #6c50c8, #a855f7);
  border: none; border-radius: 10px;
  color: #fff; font-size: 0.83rem; font-weight: 500;
  padding: 0.5rem 1rem; text-decoration: none;
  transition: opacity 0.2s, transform 0.15s;
}
.btn-tambah:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; }

/* Table */
table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
thead th {
  background: rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.4); font-weight: 500;
  font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.6px;
  padding: 0.7rem 1.2rem;
  border-bottom: 1px solid rgba(255,255,255,0.07);
}
tbody td {
  padding: 0.8rem 1.2rem;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.78); vertical-align: middle;
}
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: rgba(255,255,255,0.03); }

/* Badge status */
.bx {
  font-size: 0.7rem; padding: 3px 11px; border-radius: 20px;
  font-weight: 500; display: inline-block;
}
.bx-green  { background: rgba(34,197,94,0.15);  color: #4ade80; }
.bx-gray   { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.45); }

/* Toggle switch status */
.toggle-status {
  display: inline-flex; align-items: center; gap: 7px;
  cursor: pointer; text-decoration: none;
}
.toggle-track {
  width: 36px; height: 20px; border-radius: 20px;
  position: relative; transition: background 0.2s; flex-shrink: 0;
}
.toggle-track.on  { background: rgba(34,197,94,0.35); }
.toggle-track.off { background: rgba(255,255,255,0.12); }
.toggle-knob {
  position: absolute; top: 3px;
  width: 14px; height: 14px; border-radius: 50%;
  transition: left 0.2s, background 0.2s;
}
.toggle-track.on  .toggle-knob { left: 19px; background: #4ade80; }
.toggle-track.off .toggle-knob { left: 3px;  background: rgba(255,255,255,0.4); }

/* Action buttons */
.btn-edit, .btn-hapus {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 0.78rem; font-weight: 500;
  padding: 0.38rem 0.85rem; border-radius: 8px;
  text-decoration: none; border: 1px solid transparent;
  transition: background 0.15s, border-color 0.15s;
}
.btn-edit  { background: rgba(59,130,246,0.12); color: #60a5fa; border-color: rgba(59,130,246,0.2); }
.btn-edit:hover  { background: rgba(59,130,246,0.22); color: #93c5fd; }
.btn-hapus { background: rgba(239,68,68,0.1);   color: #f87171; border-color: rgba(239,68,68,0.2); }
.btn-hapus:hover { background: rgba(239,68,68,0.2);  color: #fca5a5; }

/* No data */
.empty-state {
  text-align: center; padding: 3rem 1rem;
  color: rgba(255,255,255,0.3); font-size: 0.85rem;
}
.empty-state i { font-size: 2.2rem; display: block; margin-bottom: 0.6rem; }

/* Nomor urut */
.no-col { color: rgba(255,255,255,0.25); font-size: 0.78rem; }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

  <!-- Topbar -->
  <div class="topbar">
    <div class="topbar-title"><i class="bi bi-tags me-2"></i>Kategori Menu</div>
    <a href="tambah_kategori.php" class="btn-tambah">
      <i class="bi bi-plus-lg"></i> Tambah Kategori
    </a>
  </div>

  <div class="content-area">

    <!-- Mini stats -->
    <div class="mini-stats">
      <div class="mini-card">
        <div class="mini-icon sc-purple"><i class="bi bi-tags"></i></div>
        <div>
          <div class="mini-label">Total Kategori</div>
          <div class="mini-value"><?= $total ?></div>
        </div>
      </div>
      <div class="mini-card">
        <div class="mini-icon sc-green"><i class="bi bi-check-circle"></i></div>
        <div>
          <div class="mini-label">Aktif</div>
          <div class="mini-value"><?= $total_aktif ?></div>
        </div>
      </div>
      <div class="mini-card">
        <div class="mini-icon sc-amber"><i class="bi bi-pause-circle"></i></div>
        <div>
          <div class="mini-label">Nonaktif</div>
          <div class="mini-value"><?= $total_nonaktif ?></div>
        </div>
      </div>
    </div>

    <!-- Tabel -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title"><i class="bi bi-list-ul me-2"></i>Daftar Kategori</div>
        <div class="search-wrap">
          <i class="bi bi-search"></i>
          <input type="text" id="searchInput" placeholder="Cari kategori...">
        </div>
      </div>

      <?php if ($data && mysqli_num_rows($data) > 0): ?>
      <table id="tabelKategori">
        <thead>
          <tr>
            <th style="width:50px">#</th>
            <th>Nama Kategori</th>
            <th style="width:130px">Status</th>
            <th style="width:160px">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($data)):
          $aktif = strtolower($row['status'] ?? '') === 'aktif';
        ?>
          <tr>
            <td class="no-col"><?= $no++ ?></td>
            <td style="font-weight:500; color:#fff">
              <?= htmlspecialchars($row['nama_kategori']) ?>
            </td>
            <td>
              <span class="bx <?= $aktif ? 'bx-green' : 'bx-gray' ?>">
                <?= $aktif ? 'Aktif' : 'Nonaktif' ?>
              </span>
            </td>
            <td>
              <a href="edit_kategori.php?id=<?= $row['id_kategori'] ?>" class="btn-edit">
                <i class="bi bi-pencil"></i> Edit
              </a>
              <a href="hapus_kategori.php?id=<?= $row['id_kategori'] ?>"
                 class="btn-hapus"
                 onclick="return confirm('Hapus kategori \'<?= htmlspecialchars(addslashes($row['nama_kategori'])) ?>\'?')">
                <i class="bi bi-trash"></i> Hapus
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <div class="empty-state">
          <i class="bi bi-tags"></i>
          Belum ada kategori. <a href="tambah_kategori.php" style="color:#c084fc">Tambah sekarang</a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
// Search filter
document.getElementById('searchInput').addEventListener('input', function () {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#tabelKategori tbody tr').forEach(tr => {
    const nama = tr.cells[1]?.textContent.toLowerCase() ?? '';
    tr.style.display = nama.includes(q) ? '' : 'none';
  });
});
</script>
</body>
</html>