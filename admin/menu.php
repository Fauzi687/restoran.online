<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

$query = mysqli_query($conn, "
    SELECT menu.*, kategori.nama_kategori
    FROM menu
    LEFT JOIN kategori ON menu.id_kategori = kategori.id_kategori
    ORDER BY menu.id_menu DESC
");

$total_menu = $query ? mysqli_num_rows($query) : 0;

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM menu WHERE status='aktif'");
$total_aktif = ($r && $row = mysqli_fetch_assoc($r)) ? (int)$row['c'] : 0;

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM menu WHERE stok = 0");
$total_habis = ($r && $row = mysqli_fetch_assoc($r)) ? (int)$row['c'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menu — Resto App</title>
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

.content-area { padding: 1.75rem; }

/* Mini stats */
.mini-stats { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.mini-card {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px; padding: 1rem 1.4rem;
  display: flex; align-items: center; gap: 14px; min-width: 160px;
}
.mini-icon {
  width: 38px; height: 38px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; flex-shrink: 0;
}
.sc-purple { background: rgba(168,85,247,0.18); color: #c084fc; }
.sc-green  { background: rgba(34,197,94,0.15);  color: #4ade80; }
.sc-red    { background: rgba(239,68,68,0.15);  color: #f87171; }
.sc-blue   { background: rgba(59,130,246,0.15); color: #60a5fa; }
.mini-label { font-size: 0.68rem; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.7px; }
.mini-value { font-size: 1.3rem; font-weight: 700; color: #fff; line-height: 1.1; }

/* Panel */
.panel {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px; overflow: hidden;
}
.panel-header {
  padding: 1rem 1.4rem;
  border-bottom: 1px solid rgba(255,255,255,0.07);
  display: flex; align-items: center; justify-content: space-between;
  gap: 1rem; flex-wrap: wrap;
}
.panel-title { font-size: 0.9rem; font-weight: 600; color: #fff; }

.panel-tools { display: flex; align-items: center; gap: 0.65rem; }

/* Search */
.search-wrap { position: relative; }
.search-wrap input {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; color: #fff; font-size: 0.83rem;
  padding: 0.45rem 0.85rem 0.45rem 2.2rem;
  width: 200px; outline: none; transition: border-color 0.2s;
}
.search-wrap input:focus { border-color: rgba(168,85,247,0.5); }
.search-wrap input::placeholder { color: rgba(255,255,255,0.25); }
.search-wrap i {
  position: absolute; left: 0.65rem; top: 50%;
  transform: translateY(-50%);
  color: rgba(255,255,255,0.3); font-size: 0.85rem; pointer-events: none;
}

/* Filter select */
.filter-select {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; color: rgba(255,255,255,0.7);
  font-size: 0.83rem; padding: 0.45rem 0.85rem;
  outline: none; cursor: pointer;
}
.filter-select option { background: #1e1b4b; color: #fff; }

/* Btn tambah */
.btn-tambah {
  display: inline-flex; align-items: center; gap: 7px;
  background: linear-gradient(135deg, #6c50c8, #a855f7);
  border: none; border-radius: 10px;
  color: #fff; font-size: 0.83rem; font-weight: 500;
  padding: 0.5rem 1rem; text-decoration: none;
  transition: opacity 0.2s, transform 0.15s; white-space: nowrap;
}
.btn-tambah:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; }

/* Table */
table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
thead th {
  background: rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.4); font-weight: 500;
  font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.6px;
  padding: 0.7rem 1.1rem;
  border-bottom: 1px solid rgba(255,255,255,0.07);
}
tbody td {
  padding: 0.75rem 1.1rem;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.75); vertical-align: middle;
}
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: rgba(255,255,255,0.03); }
.no-col { color: rgba(255,255,255,0.25); font-size: 0.78rem; }

/* Foto menu */
.menu-foto {
  width: 52px; height: 52px; border-radius: 10px;
  object-fit: cover;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
}
.menu-foto-placeholder {
  width: 52px; height: 52px; border-radius: 10px;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  display: flex; align-items: center; justify-content: center;
  color: rgba(255,255,255,0.2); font-size: 1.2rem;
}

/* Nama menu */
.menu-name { font-weight: 500; color: #fff; }
.menu-name small { color: rgba(255,255,255,0.35); font-size: 0.72rem; font-weight: 400; }

/* Badge */
.bx {
  font-size: 0.7rem; padding: 3px 11px; border-radius: 20px;
  font-weight: 500; display: inline-block;
}
.bx-green  { background: rgba(34,197,94,0.15);  color: #4ade80; }
.bx-gray   { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.45); }
.bx-red    { background: rgba(239,68,68,0.15);   color: #f87171; }
.bx-amber  { background: rgba(245,158,11,0.15);  color: #fbbf24; }
.bx-blue   { background: rgba(59,130,246,0.15);  color: #60a5fa; }

/* Stok indicator */
.stok-wrap { display: flex; align-items: center; gap: 8px; }
.stok-bar-bg {
  width: 50px; height: 4px; border-radius: 4px;
  background: rgba(255,255,255,0.08); overflow: hidden;
}
.stok-bar { height: 100%; border-radius: 4px; }

/* Price */
.price-col { font-weight: 600; color: #4ade80; }

/* Aksi */
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

/* Empty */
.empty-state {
  text-align: center; padding: 3rem 1rem;
  color: rgba(255,255,255,0.3); font-size: 0.85rem;
}
.empty-state i { font-size: 2.2rem; display: block; margin-bottom: 0.6rem; }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

  <!-- Topbar -->
  <div class="topbar">
    <div class="topbar-title"><i class="bi bi-journal-text me-2"></i>Menu Makanan</div>
    <a href="tambah_menu.php" class="btn-tambah">
      <i class="bi bi-plus-lg"></i> Tambah Menu
    </a>
  </div>

  <div class="content-area">

    <!-- Mini Stats -->
    <div class="mini-stats">
      <div class="mini-card">
        <div class="mini-icon sc-purple"><i class="bi bi-journal-text"></i></div>
        <div>
          <div class="mini-label">Total Menu</div>
          <div class="mini-value"><?= $total_menu ?></div>
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
        <div class="mini-icon sc-red"><i class="bi bi-exclamation-circle"></i></div>
        <div>
          <div class="mini-label">Stok Habis</div>
          <div class="mini-value"><?= $total_habis ?></div>
        </div>
      </div>
    </div>

    <!-- Tabel -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title"><i class="bi bi-list-ul me-2"></i>Daftar Menu</div>
        <div class="panel-tools">
          <select class="filter-select" id="filterStatus">
            <option value="">Semua Status</option>
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
          </select>
          <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" placeholder="Cari menu...">
          </div>
        </div>
      </div>

      <?php if ($query && mysqli_num_rows($query) > 0): ?>
      <table id="tabelMenu">
        <thead>
          <tr>
            <th style="width:45px">#</th>
            <th style="width:70px">Foto</th>
            <th>Nama Menu</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Stok</th>
            <th style="width:110px">Status</th>
            <th style="width:160px">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($query)):
          $aktif    = strtolower($row['status'] ?? '') === 'aktif';
          $stok     = (int)($row['stok'] ?? 0);
          $stok_max = 50; // asumsi stok maks untuk bar
          $stok_pct = min(100, ($stok > 0 ? ($stok / $stok_max * 100) : 0));
          $stok_cls = $stok === 0 ? 'bx-red' : ($stok <= 5 ? 'bx-amber' : 'bx-green');
          $bar_color= $stok === 0 ? '#f87171' : ($stok <= 5 ? '#fbbf24' : '#4ade80');
          $foto     = $row['foto'] ?? '';
          $foto_url = '../uploads/menu/' . htmlspecialchars($foto);
        ?>
          <tr data-status="<?= strtolower($row['status'] ?? '') ?>">
            <td class="no-col"><?= $no++ ?></td>
            <td>
              <?php if ($foto && file_exists('../uploads/menu/' . $foto)): ?>
                <img src="<?= $foto_url ?>" alt="<?= htmlspecialchars($row['nama_menu']) ?>" class="menu-foto">
              <?php else: ?>
                <div class="menu-foto-placeholder"><i class="bi bi-image"></i></div>
              <?php endif; ?>
            </td>
            <td>
              <div class="menu-name">
                <?= htmlspecialchars($row['nama_menu']) ?>
              </div>
            </td>
            <td>
              <span class="bx bx-blue"><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></span>
            </td>
            <td class="price-col">
              Rp <?= number_format($row['harga'] ?? 0, 0, ',', '.') ?>
            </td>
            <td>
              <div class="stok-wrap">
                <span class="bx <?= $stok_cls ?>"><?= $stok ?></span>
                <div class="stok-bar-bg">
                  <div class="stok-bar" style="width:<?= $stok_pct ?>%;background:<?= $bar_color ?>"></div>
                </div>
              </div>
            </td>
            <td>
              <span class="bx <?= $aktif ? 'bx-green' : 'bx-gray' ?>">
                <?= $aktif ? 'Aktif' : 'Nonaktif' ?>
              </span>
            </td>
            <td>
              <a href="edit_menu.php?id=<?= $row['id_menu'] ?>" class="btn-edit">
                <i class="bi bi-pencil"></i> Edit
              </a>
              <a href="hapus_menu.php?id=<?= $row['id_menu'] ?>"
                 class="btn-hapus"
                 onclick="return confirm('Hapus menu \'<?= htmlspecialchars(addslashes($row['nama_menu'])) ?>\'?')">
                <i class="bi bi-trash"></i> Hapus
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <div class="empty-state">
          <i class="bi bi-journal-x"></i>
          Belum ada menu. <a href="tambah_menu.php" style="color:#c084fc">Tambah sekarang</a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
const searchInput  = document.getElementById('searchInput');
const filterStatus = document.getElementById('filterStatus');

function filterTable() {
  const q  = searchInput.value.toLowerCase();
  const st = filterStatus.value.toLowerCase();
  document.querySelectorAll('#tabelMenu tbody tr').forEach(tr => {
    const nama   = tr.cells[2]?.textContent.toLowerCase() ?? '';
    const status = (tr.dataset.status ?? '').toLowerCase();
    const matchQ  = nama.includes(q);
    const matchSt = !st || status === st;
    tr.style.display = (matchQ && matchSt) ? '' : 'none';
  });
}

searchInput.addEventListener('input', filterTable);
filterStatus.addEventListener('change', filterTable);
</script>
</body>
</html>