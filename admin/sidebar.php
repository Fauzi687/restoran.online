<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}
$current = basename($_SERVER['PHP_SELF']);

$menus = [
    'Utama' => [
        ['file' => 'dashboard.php', 'label' => 'Dashboard',         'icon' => 'bi-speedometer2'],
    ],
    'Operasional' => [
        ['file' => 'kategori.php',  'label' => 'Kategori',          'icon' => 'bi-tags'],
        ['file' => 'menu.php',      'label' => 'Menu',              'icon' => 'bi-journal-text'],
        ['file' => 'meja.php',      'label' => 'Meja',              'icon' => 'bi-grid-3x3-gap'],
        ['file' => 'takeaway.php',  'label' => 'Takeaway',          'icon' => 'bi-bag-check'],
        ['file' => 'bank.php',      'label' => 'Bank',              'icon' => 'bi-bank'],
        ['file' => 'transaksi.php', 'label' => 'Transaksi',         'icon' => 'bi-receipt'],
    ],
    'Laporan' => [
        ['file' => 'grafik.php',    'label' => 'Grafik Penjualan',  'icon' => 'bi-bar-chart-line'],
    ],
];
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
.sidebar {
  width: 240px;
  min-height: 100vh;
  background: #12102a;
  border-right: 1px solid rgba(255,255,255,0.07);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0; left: 0;
  z-index: 100;
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

.sidebar-brand {
  padding: 1.4rem 1.25rem 1rem;
  border-bottom: 1px solid rgba(255,255,255,0.07);
  display: flex;
  align-items: center;
  gap: 10px;
}
.sidebar-brand .brand-icon {
  width: 36px; height: 36px;
  background: linear-gradient(135deg, #6c50c8, #a855f7);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1rem; flex-shrink: 0;
}
.sidebar-brand .brand-name {
  font-size: 1rem; font-weight: 700; color: #fff; letter-spacing: -0.3px;
}
.sidebar-brand .brand-sub {
  font-size: 0.7rem; color: rgba(255,255,255,0.35); margin-top: 1px;
}

.sidebar-nav { flex: 1; padding: 1rem 0.75rem; overflow-y: auto; }

.nav-section-label {
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: rgba(255,255,255,0.3);
  padding: 0.75rem 0.5rem 0.35rem;
}
.nav-section-label:first-child { padding-top: 0; }

.nav-link-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0.55rem 0.75rem;
  border-radius: 10px;
  color: rgba(255,255,255,0.6);
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 400;
  margin-bottom: 2px;
  transition: background 0.15s, color 0.15s;
}
.nav-link-item i { font-size: 1.05rem; flex-shrink: 0; }
.nav-link-item:hover {
  background: rgba(255,255,255,0.07);
  color: rgba(255,255,255,0.9);
}
.nav-link-item.active {
  background: rgba(168,85,247,0.18);
  color: #c084fc;
  font-weight: 500;
}
.nav-link-item.active i { color: #a855f7; }

.sidebar-footer {
  padding: 1rem 1.25rem;
  border-top: 1px solid rgba(255,255,255,0.07);
}
.user-info {
  display: flex; align-items: center; gap: 10px; margin-bottom: 10px;
}
.user-avatar {
  width: 34px; height: 34px;
  background: linear-gradient(135deg, #6c50c8, #a855f7);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.8rem; font-weight: 600; color: #fff; flex-shrink: 0;
}
.user-name { font-size: 0.85rem; font-weight: 500; color: #fff; }
.user-role { font-size: 0.7rem; color: rgba(255,255,255,0.35); }
.btn-logout {
  display: flex; align-items: center; gap: 8px;
  width: 100%; padding: 0.5rem 0.75rem;
  background: rgba(220,53,69,0.1);
  border: 1px solid rgba(220,53,69,0.2);
  border-radius: 10px;
  color: #f87171;
  font-size: 0.8rem; font-weight: 500;
  text-decoration: none; cursor: pointer;
  transition: background 0.15s;
}
.btn-logout:hover { background: rgba(220,53,69,0.2); color: #fca5a5; }
</style>

<div class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="bi bi-shop"></i></div>
    <div>
      <div class="brand-name">Resto App</div>
      <div class="brand-sub">Panel Admin</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($menus as $section => $items): ?>
      <div class="nav-section-label"><?= $section ?></div>
      <?php foreach ($items as $m):
        $active = ($current === $m['file']) ? 'active' : '';
      ?>
        <a href="<?= $m['file'] ?>" class="nav-link-item <?= $active ?>">
          <i class="bi <?= $m['icon'] ?>"></i>
          <?= $m['label'] ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?></div>
        <div class="user-role">Administrator</div>
      </div>
    </div>
    <a href="../logout.php" class="btn-logout">
      <i class="bi bi-box-arrow-left"></i> Keluar
    </a>
  </div>
</div>