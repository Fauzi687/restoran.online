<?php
session_start();
include '../config/koneksi.php';

if(!isset($_GET['order'])){
    die("No pesanan tidak ditemukan");
}

$order = htmlspecialchars($_GET['order']);
$meja  = isset($_GET['meja']) ? htmlspecialchars($_GET['meja']) : '';

// Ambil data transaksi dari database
$res      = mysqli_query($conn, "SELECT metode_pembayaran, nama_pelanggan, total FROM transaksi WHERE no_pesanan='$order' LIMIT 1");
$transaksi = mysqli_fetch_array($res);

$metode_pembayaran = isset($transaksi['metode_pembayaran']) ? $transaksi['metode_pembayaran'] : '';
$nama_pelanggan    = isset($transaksi['nama_pelanggan'])    ? htmlspecialchars($transaksi['nama_pelanggan']) : '';
$total             = isset($transaksi['total'])             ? $transaksi['total'] : 0;

// Fallback dari GET jika belum ada di database
if(empty($metode_pembayaran) && isset($_GET['metode'])){
    $metode_pembayaran = htmlspecialchars($_GET['metode']);
}

$is_tunai = ($metode_pembayaran === 'Tunai');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pesanan Berhasil — <?= $order ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --brand:      #FF6B35;
  --brand-2:    #FF9F1C;
  --brand-glow: rgba(255,107,53,.22);
  --bg:         #FFF8F5;
  --surface:    #FFFFFF;
  --surface-2:  #FFF3EE;
  --border:     rgba(255,107,53,.13);
  --border-2:   rgba(0,0,0,.07);
  --text-1:     #1A1108;
  --text-2:     #6B5B4E;
  --text-3:     #B8A89E;
  --green:      #16A34A;
  --green-l:    #22C55E;
  --blue:       #2563EB;
  --blue-l:     #3B82F6;
  --radius:     22px;
  --radius-sm:  14px;
  --shadow:     0 8px 40px rgba(255,107,53,.13), 0 2px 8px rgba(0,0,0,.06);
}

html, body {
  min-height: 100%;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

body {
  background: var(--bg);
  background-image:
    radial-gradient(ellipse 70% 50% at 50% -10%, rgba(255,107,53,.1) 0%, transparent 60%),
    radial-gradient(ellipse 50% 40% at 90% 110%, rgba(255,159,28,.08) 0%, transparent 55%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 1.5rem 1rem 3rem;
  color: var(--text-1);
}

/* ── Page wrapper ── */
.page-wrap {
  width: 100%;
  max-width: 460px;
  animation: fadeUp .45s cubic-bezier(.22,1,.36,1) both;
}
@keyframes fadeUp {
  from { opacity:0; transform:translateY(22px); }
  to   { opacity:1; transform:translateY(0); }
}

/* ── Card ── */
.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
}

/* ── Hero top ── */
.card-hero {
  padding: 2.25rem 1.75rem 1.75rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.card-hero.green { background: linear-gradient(145deg, #22C55E, #16A34A); }
.card-hero.blue  { background: linear-gradient(145deg, #3B82F6, #1D4ED8); }

/* Decorative circles */
.card-hero::before,
.card-hero::after {
  content: '';
  position: absolute;
  border-radius: 50%;
  background: rgba(255,255,255,.09);
  pointer-events: none;
}
.card-hero::before { width: 130px; height: 130px; bottom: -40px; left: -40px; }
.card-hero::after  { width: 90px;  height: 90px;  top: -25px; right: -20px; }

/* Dot pattern overlay */
.card-hero .dots {
  position: absolute; inset: 0;
  background-image: radial-gradient(rgba(255,255,255,.1) 1px, transparent 1px);
  background-size: 20px 20px;
  pointer-events: none;
}

.hero-icon {
  font-size: 3.6rem;
  line-height: 1;
  display: block;
  margin-bottom: .85rem;
  position: relative; z-index: 1;
  animation: popIn .55s cubic-bezier(.34,1.56,.64,1) both .1s;
}
@keyframes popIn {
  from { transform:scale(.5); opacity:0; }
  to   { transform:scale(1);  opacity:1; }
}
.hero-title {
  font-size: clamp(1.1rem, 4vw, 1.3rem);
  font-weight: 800; color: #fff;
  margin-bottom: .3rem;
  position: relative; z-index: 1;
}
.hero-sub {
  font-size: .82rem;
  color: rgba(255,255,255,.85);
  position: relative; z-index: 1;
  line-height: 1.45;
}

/* ── Card body ── */
.card-body {
  padding: 1.4rem 1.5rem 1.5rem;
}

/* ── Alert info box ── */
.info-box {
  border-radius: var(--radius-sm);
  padding: .95rem 1.1rem;
  margin-bottom: 1.1rem;
  display: flex;
  align-items: flex-start;
  gap: 11px;
}
.info-box.amber {
  background: #FFFBEB;
  border: 1.5px solid rgba(245,158,11,.22);
}
.info-box.sky {
  background: #EFF6FF;
  border: 1.5px solid rgba(59,130,246,.2);
}
.ib-icon { font-size: 1.25rem; line-height: 1.3; flex-shrink: 0; }
.ib-title {
  font-size: .84rem; font-weight: 700;
  color: var(--text-1); margin-bottom: 2px;
}
.ib-sub { font-size: .76rem; color: var(--text-2); line-height: 1.45; }

/* ── Order number box ── */
.order-box {
  background: var(--surface-2);
  border: 1.5px dashed var(--border);
  border-radius: var(--radius-sm);
  padding: 1.1rem 1.25rem;
  text-align: center;
  margin-bottom: 1.1rem;
}
.order-lbl {
  font-size: .63rem;
  font-weight: 700;
  letter-spacing: .09em;
  text-transform: uppercase;
  color: var(--text-3);
  margin-bottom: 6px;
}
.order-num {
  font-size: 1.75rem;
  font-weight: 800;
  color: var(--brand);
  letter-spacing: .04em;
  line-height: 1;
}

/* ── Detail row (nama, meja, total) ── */
.detail-strip {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .6rem;
  margin-bottom: 1.1rem;
}
.detail-item {
  background: var(--surface-2);
  border: 1px solid var(--border-2);
  border-radius: var(--radius-sm);
  padding: .7rem .9rem;
}
.detail-lbl {
  font-size: .62rem;
  font-weight: 700;
  letter-spacing: .07em;
  text-transform: uppercase;
  color: var(--text-3);
  margin-bottom: 3px;
}
.detail-val {
  font-size: .84rem;
  font-weight: 700;
  color: var(--text-1);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.detail-val.brand { color: var(--brand); }

/* ── Buttons ── */
.btn-pantau {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: .9rem 1.25rem;
  background: linear-gradient(135deg, var(--brand), var(--brand-2));
  color: #fff;
  border: none;
  border-radius: var(--radius-sm);
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .9rem;
  font-weight: 800;
  text-decoration: none;
  cursor: pointer;
  box-shadow: 0 4px 18px var(--brand-glow);
  transition: opacity .2s, transform .15s;
  position: relative;
  overflow: hidden;
  margin-bottom: .65rem;
}
.btn-pantau::before {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(180deg, rgba(255,255,255,.14) 0%, transparent 55%);
}
.btn-pantau:hover { opacity: .9; transform: translateY(-2px); color: #fff; }
.btn-pantau:active { transform: scale(.98); }

.btn-menu {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  width: 100%;
  padding: .75rem 1.25rem;
  background: transparent;
  color: var(--text-2);
  border: 1.5px solid var(--border-2);
  border-radius: var(--radius-sm);
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .84rem;
  font-weight: 600;
  text-decoration: none;
  transition: background .15s, color .15s, border-color .15s;
}
.btn-menu:hover {
  background: var(--surface-2);
  color: var(--brand);
  border-color: var(--border);
}

/* ── Confetti (CSS only) ── */
.confetti-wrap {
  position: fixed; inset: 0;
  pointer-events: none; overflow: hidden; z-index: 0;
}
.dot {
  position: absolute;
  top: -12px;
  width: 8px; height: 8px;
  border-radius: 2px;
  animation: fall linear forwards;
}
@keyframes fall {
  to { transform: translateY(110vh) rotate(540deg); opacity: 0; }
}

/* ── Responsive ── */
@media (max-width: 480px) {
  body {
    background: #fff;
    background-image: none;
    justify-content: flex-start;
    padding: 0;
    align-items: stretch;
  }
  .page-wrap {
    max-width: 100%;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }
  .card {
    border-radius: 0;
    box-shadow: none;
    border: none;
    flex: 1;
    display: flex;
    flex-direction: column;
  }
  .card-hero {
    padding: 2rem 1.25rem 1.6rem;
    border-radius: 0 0 24px 24px;
  }
  .card-body {
    padding: 1.25rem 1.1rem 1.75rem;
    flex: 1;
  }
  .hero-icon { font-size: 3rem; }
  .order-num { font-size: 1.5rem; }
  .detail-strip { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 360px) {
  .detail-strip { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- Confetti container -->
<div class="confetti-wrap" id="confettiWrap"></div>

<div class="page-wrap">
  <div class="card">

    <!-- Hero -->
    <div class="card-hero <?= $is_tunai ? 'blue' : 'green' ?>">
      <div class="dots"></div>
      <span class="hero-icon"><?= $is_tunai ? '💰' : '✅' ?></span>
      <div class="hero-title">
        <?= $is_tunai ? 'Pesanan Berhasil Dibuat!' : 'Pesanan Berhasil Dikirim!' ?>
      </div>
      <div class="hero-sub">
        <?= $is_tunai
          ? 'Silakan menuju kasir untuk menyelesaikan pembayaran'
          : 'Pesanan Anda sudah masuk dan sedang diproses' ?>
      </div>
    </div>

    <!-- Body -->
    <div class="card-body">

      <!-- Info box -->
      <?php if($is_tunai): ?>
        <div class="info-box sky">
          <span class="ib-icon">🏧</span>
          <div>
            <div class="ib-title">Pembayaran Tunai di Kasir</div>
            <div class="ib-sub">Segera temui kasir kami untuk menyelesaikan pembayaran agar pesanan segera diproses oleh dapur.</div>
          </div>
        </div>
      <?php else: ?>
        <div class="info-box amber">
          <span class="ib-icon">🍳</span>
          <div>
            <div class="ib-title">Bukti Sedang Diverifikasi</div>
            <div class="ib-sub">Admin kami akan memverifikasi bukti pembayaran Anda. Pesanan akan segera diproses setelah dikonfirmasi.</div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Nomor pesanan -->
      <div class="order-box">
        <div class="order-lbl"><i class="bi bi-receipt"></i> Nomor Pesanan Anda</div>
        <div class="order-num"><?= $order ?></div>
      </div>

      <!-- Detail strip -->
      <div class="detail-strip">
        <?php if($nama_pelanggan): ?>
        <div class="detail-item">
          <div class="detail-lbl"><i class="bi bi-person"></i> Nama</div>
          <div class="detail-val"><?= $nama_pelanggan ?></div>
        </div>
        <?php endif; ?>
        <div class="detail-item">
          <div class="detail-lbl"><i class="bi bi-grid-3x3-gap"></i> Meja</div>
          <div class="detail-val"><?= $meja ?: '—' ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-lbl"><i class="bi bi-credit-card"></i> Metode</div>
          <div class="detail-val"><?= $metode_pembayaran ?: '—' ?></div>
        </div>
        <?php if($total > 0): ?>
        <div class="detail-item">
          <div class="detail-lbl"><i class="bi bi-cash-coin"></i> Total</div>
          <div class="detail-val brand">Rp <?= number_format($total, 0, ',', '.') ?></div>
        </div>
        <?php endif; ?>
      </div>

      <!-- CTA buttons -->
      <a href="cek_status.php?order=<?= urlencode($order) ?>&meja=<?= urlencode($meja) ?>" class="btn-pantau">
        <i class="bi bi-radar"></i> Pantau Status Pesanan
      </a>

    </div>
  </div>
</div>

<script>
// Confetti burst on load
(function(){
  const colors = ['#FF6B35','#FF9F1C','#22C55E','#3B82F6','#F59E0B','#EC4899','#8B5CF6'];
  const wrap   = document.getElementById('confettiWrap');
  const count  = 55;
  for(let i = 0; i < count; i++){
    const d = document.createElement('div');
    d.className = 'dot';
    d.style.cssText = [
      `left:${Math.random()*100}%`,
      `width:${6+Math.random()*7}px`,
      `height:${6+Math.random()*7}px`,
      `background:${colors[Math.floor(Math.random()*colors.length)]}`,
      `border-radius:${Math.random()>.5?'50%':'3px'}`,
      `animation-duration:${1.8+Math.random()*2.2}s`,
      `animation-delay:${Math.random()*1}s`,
      `opacity:${0.7+Math.random()*.3}`,
    ].join(';');
    wrap.appendChild(d);
  }
  // Remove after animation
  setTimeout(() => wrap.remove(), 4000);
})();
</script>
</body>
</html>