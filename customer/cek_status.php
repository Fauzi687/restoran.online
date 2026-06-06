<?php
session_start();
include '../config/koneksi.php';

if(!isset($_GET['order'])) die("No pesanan tidak ditemukan");

$order = mysqli_real_escape_string($conn, $_GET['order']);
$res   = mysqli_query($conn, "SELECT * FROM transaksi WHERE no_pesanan='$order' LIMIT 1");
$d     = mysqli_fetch_array($res);

if(!$d) die("Data pesanan tidak ditemukan");

if(isset($_SESSION['active_order'])
   && $_SESSION['active_order']['no_pesanan'] === $order
   && in_array($d['status_pesanan'], ['selesai','gagal']))
{
    unset($_SESSION['active_order']);
}

$sisa = 120;
if($d['status_pesanan'] === 'diantar' && !empty($d['waktu_diantar'])){
    $sisa = strtotime($d['waktu_diantar']) + 120 - time();
    $sisa = max(0, min(120, $sisa));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status Pesanan — <?= htmlspecialchars($order) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg:      #0D0D12;
  --surface: #16161E;
  --surface2:#1E1E28;
  --border:  rgba(255,255,255,.07);
  --border2: rgba(255,255,255,.12);
  --text-1:  #F0EFF8;
  --text-2:  rgba(240,239,248,.52);
  --text-3:  rgba(240,239,248,.25);
  --orange: #F97316; --orange-g: #EA580C;
  --amber:  #F59E0B; --amber-g:  #D97706;
  --purple: #8B5CF6; --purple-g: #7C3AED;
  --green:  #22C55E; --green-g:  #16A34A;
  --red:    #EF4444; --red-g:    #DC2626;
  --radius: 20px;
  --radius-sm: 13px;
}

html { scroll-behavior: smooth; }
body {
  font-family: 'Plus Jakarta Sans', sans-serif;
  background: var(--bg);
  color: var(--text-1);
  min-height: 100vh;
  display: flex; flex-direction: column;
  align-items: center;
  padding: 1.25rem 1rem 3rem;
  background-image:
    radial-gradient(ellipse 60% 50% at 50% 0%, rgba(139,92,246,.12) 0%, transparent 70%),
    radial-gradient(ellipse 40% 30% at 80% 80%, rgba(249,115,22,.08) 0%, transparent 60%);
}

.page-wrap {
  width: 100%; max-width: 460px;
  animation: fadeUp .4s ease both;
}
@keyframes fadeUp {
  from { opacity:0; transform:translateY(16px); }
  to   { opacity:1; transform:translateY(0); }
}

.hero {
  border-radius: var(--radius) var(--radius) 0 0;
  padding: 2rem 1.5rem 1.6rem;
  text-align: center; position: relative; overflow: hidden;
}
.hero::before {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(180deg, rgba(255,255,255,.07) 0%, transparent 60%);
  pointer-events: none;
}
.hero::after {
  content: ''; position: absolute; inset: 0;
  background-image: radial-gradient(rgba(255,255,255,.07) 1px, transparent 1px);
  background-size: 22px 22px; pointer-events: none;
}
.hero.orange { background: linear-gradient(135deg, var(--orange), var(--orange-g)); }
.hero.amber  { background: linear-gradient(135deg, var(--amber),  var(--amber-g)); }
.hero.purple { background: linear-gradient(135deg, var(--purple), var(--purple-g)); }
.hero.green  { background: linear-gradient(135deg, var(--green),  var(--green-g)); }
.hero.red    { background: linear-gradient(135deg, var(--red),    var(--red-g)); }

.hero-icon {
  font-size: 3.2rem; line-height: 1;
  display: block; margin-bottom: .75rem;
  position: relative; z-index: 1;
  animation: bounce .6s ease both .15s;
}
@keyframes bounce {
  0%  { transform:scale(.7); opacity:0; }
  60% { transform:scale(1.12); }
  100%{ transform:scale(1); opacity:1; }
}
.hero-title {
  font-size: 1.15rem; font-weight: 800; color: #fff;
  margin-bottom: .25rem; position: relative; z-index: 1;
}
.hero-sub {
  font-size: .8rem; color: rgba(255,255,255,.82);
  position: relative; z-index: 1;
}

.card-body {
  background: var(--surface);
  border: 1px solid var(--border2);
  border-top: none;
  border-radius: 0 0 var(--radius) var(--radius);
  padding: 1.25rem 1.25rem 1.1rem;
}

.info-strip {
  display: grid; grid-template-columns: repeat(3, 1fr);
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  overflow: hidden; margin-bottom: 1.1rem;
}
.info-col { padding: .75rem .5rem; text-align: center; }
.info-col + .info-col { border-left: 1px solid var(--border); }
.info-lbl { font-size: .6rem; font-weight: 600; text-transform: uppercase; letter-spacing: .07em; color: var(--text-3); margin-bottom: 4px; }
.info-val { font-size: .84rem; font-weight: 700; color: var(--text-1); word-break: break-all; }

.steps { display: flex; align-items: center; margin-bottom: 1.1rem; }
.step-item {
  display: flex; flex-direction: column; align-items: center; flex: 1;
  position: relative;
}
.step-item + .step-item::before {
  content: ''; position: absolute; left: -50%; top: 14px;
  width: 100%; height: 2px; background: var(--border2); z-index: 0;
}
.step-item.done + .step-item::before  { background: var(--green); }
.step-dot {
  width: 28px; height: 28px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .72rem; position: relative; z-index: 1;
  border: 2px solid var(--border2);
  background: var(--surface2); color: var(--text-3);
  transition: all .3s;
}
.step-item.done   .step-dot { background: var(--green); border-color: var(--green); color: #fff; }
.step-item.active .step-dot { box-shadow: 0 0 0 4px rgba(255,255,255,.06); }
.step-item.active.orange .step-dot { color: var(--orange); border-color: var(--orange); background: transparent; }
.step-item.active.amber  .step-dot { color: var(--amber);  border-color: var(--amber);  background: transparent; }
.step-item.active.purple .step-dot { color: var(--purple); border-color: var(--purple); background: transparent; }
.step-item.active.green  .step-dot { color: var(--green);  border-color: var(--green);  background: transparent; }
.step-lbl { font-size: .58rem; font-weight: 600; margin-top: 4px; color: var(--text-3); text-align: center; line-height: 1.2; }
.step-item.done   .step-lbl { color: var(--green); }
.step-item.active .step-lbl { color: var(--text-1); }

.alert-block {
  border-radius: var(--radius-sm);
  padding: .85rem 1rem; margin-bottom: .75rem;
  display: flex; align-items: flex-start; gap: 9px;
}
.ab-icon  { font-size: 1.15rem; flex-shrink: 0; line-height: 1.35; }
.ab-title { font-size: .82rem; font-weight: 700; margin-bottom: 2px; }
.ab-sub   { font-size: .75rem; opacity: .82; }
.ab-pending { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08); color: var(--text-2); }
.ab-lunas   { background: rgba(249,115,22,.12);  border: 1px solid rgba(249,115,22,.25); color: #fb923c; }
.ab-gagal   { background: rgba(239,68,68,.12);   border: 1px solid rgba(239,68,68,.2);   color: #f87171; }
.ab-menunggu{ background: rgba(59,130,246,.12);  border: 1px solid rgba(59,130,246,.2);  color: #60a5fa; }
.ab-diproses{ background: rgba(245,158,11,.12);  border: 1px solid rgba(245,158,11,.2);  color: #fbbf24; }
.ab-diantar { background: rgba(139,92,246,.12);  border: 1px solid rgba(139,92,246,.2);  color: #c084fc; }
.ab-selesai { background: rgba(34,197,94,.12);   border: 1px solid rgba(34,197,94,.2);   color: #4ade80; }

/* Timer */
.timer-wrap {
  display: none;
  background: var(--surface2);
  border: 1px solid rgba(139,92,246,.25);
  border-radius: var(--radius-sm);
  padding: 1rem 1.1rem; margin-bottom: .75rem;
  text-align: center;
}
.timer-lbl { font-size: .66rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: var(--purple); margin-bottom: .35rem; }
.timer-digits { font-size: 2rem; font-weight: 800; color: #fff; font-variant-numeric: tabular-nums; }
.timer-bar-wrap { background: var(--border); border-radius: 4px; height: 4px; margin-top: .55rem; overflow: hidden; }
.timer-bar { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--purple), #c084fc); transition: width 1s linear; }

.refresh-bar {
  background: var(--surface2); border: 1px solid var(--border);
  border-radius: 10px; padding: .55rem .9rem;
  font-size: .72rem; color: var(--text-3);
  text-align: center; margin-top: .2rem;
  display: flex; align-items: center; justify-content: center; gap: 6px;
}
.refresh-dot {
  width: 6px; height: 6px; border-radius: 50%; background: var(--green);
  animation: blink 1.4s ease-in-out infinite; flex-shrink: 0;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }

@media (max-width: 480px) {
  body { padding: 0; align-items: stretch; background-image: none; }
  .page-wrap { max-width: 100%; display: flex; flex-direction: column; min-height: 100vh; }
  .hero { border-radius: 0; padding: 1.75rem 1.25rem 1.4rem; }
  .card-body { border-radius: 0; border-left: none; border-right: none; flex: 1; padding: 1.1rem 1rem 1.5rem; }
  .hero-icon { font-size: 2.8rem; }
  .hero-title { font-size: 1.05rem; }
  .timer-digits { font-size: 1.75rem; }
  .info-val { font-size: .78rem; }
  .step-lbl { font-size: .55rem; }
}
</style>
</head>
<body>
<div class="page-wrap">

  <?php
  $colorMap = ['menunggu'=>'orange','diproses'=>'amber','diantar'=>'purple','selesai'=>'green','gagal'=>'red'];
  $iconMap  = ['menunggu'=>'⏳','diproses'=>'🍳','diantar'=>'🛵','selesai'=>'✅','gagal'=>'❌'];
  $titleMap = ['menunggu'=>'Menunggu Konfirmasi','diproses'=>'Sedang Dimasak','diantar'=>'Sedang Diantar','selesai'=>'Pesanan Selesai! 🎉','gagal'=>'Pesanan Ditolak'];
  $subMap   = ['menunggu'=>'Pesanan sedang menunggu konfirmasi admin','diproses'=>'Harap tunggu sekitar 10 menit ya!','diantar'=>'Pesanan dalam perjalanan ke meja Anda','selesai'=>'Terima kasih telah makan di resto kami','gagal'=>'Silakan hubungi kasir untuk informasi'];
  $s = $d['status_pesanan'];
  ?>

  <div class="hero <?= $colorMap[$s] ?? 'orange' ?>" id="cardTop">
    <span class="hero-icon" id="statusIcon"><?= $iconMap[$s] ?? '❓' ?></span>
    <div class="hero-title" id="headerTitle"><?= $titleMap[$s] ?? '' ?></div>
    <div class="hero-sub"   id="headerSub"><?= $subMap[$s] ?? '' ?></div>
  </div>

  <div class="card-body">

    <div class="info-strip">
      <div class="info-col">
        <div class="info-lbl">No. Pesanan</div>
        <div class="info-val"><?= htmlspecialchars($d['no_pesanan']) ?></div>
      </div>
      <div class="info-col">
        <div class="info-lbl">Nama</div>
        <div class="info-val"><?= htmlspecialchars($d['nama_pelanggan']) ?></div>
      </div>
      <div class="info-col">
        <div class="info-lbl">Meja</div>
        <div class="info-val"><?= htmlspecialchars($d['nomor_meja']) ?></div>
      </div>
    </div>

    <?php if($s !== 'gagal'):
      $steps      = ['menunggu','diproses','diantar','selesai'];
      $stepIcons  = ['bi-clock','bi-fire','bi-bicycle','bi-check-lg'];
      $stepLabels = ['Menunggu','Dimasak','Diantar','Selesai'];
      $stepColors = ['orange','amber','purple','green'];
      $stepIdx    = array_search($s, $steps);
      $stepIdx    = $stepIdx === false ? -1 : $stepIdx;
    ?>
    <div class="steps" id="stepTracker">
      <?php for($i = 0; $i < 4; $i++):
        if($i < $stepIdx)       $cls = 'done';
        elseif($i === $stepIdx) $cls = 'active ' . $stepColors[$i];
        else                    $cls = '';
      ?>
        <div class="step-item <?= $cls ?>">
          <div class="step-dot">
            <?= $i < $stepIdx
              ? '<i class="bi bi-check-lg"></i>'
              : '<i class="bi ' . $stepIcons[$i] . '"></i>' ?>
          </div>
          <div class="step-lbl"><?= $stepLabels[$i] ?></div>
        </div>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

    <div id="blockBayar">
      <?php if(in_array($s, ['diantar','selesai'])): ?>
        <?php if($d['status_pembayaran'] == 'dibayar'): ?>
          <div class="alert-block ab-lunas">
            <span class="ab-icon">📝</span>
            <div><div class="ab-title">Note</div><div class="ab-sub">Jika anda belum menerima makanan silahkan hubungi kasir.</div></div>
          </div>
        <?php elseif($d['status_pembayaran'] == 'gagal'): ?>
          <div class="alert-block ab-gagal">
            <span class="ab-icon">❌</span>
            <div><div class="ab-title">Pembayaran Ditolak</div><div class="ab-sub">Bukti tidak valid. Silakan hubungi kasir.</div></div>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <?php
    $alertCls  = ['menunggu'=>'ab-menunggu','diproses'=>'ab-diproses','diantar'=>'ab-diantar','selesai'=>'ab-selesai','gagal'=>'ab-gagal'];
    $alertTitle= ['menunggu'=>'Menunggu Konfirmasi','diproses'=>'Sedang Dimasak','diantar'=>'Sedang Diantar','selesai'=>'Pesanan Selesai','gagal'=>'Pesanan Tidak Diproses'];
    $alertSub  = ['menunggu'=>'Silahkan Menuju Kasir Untuk Melanjutkan Pembayaran.','diproses'=>'Harap tunggu sekitar 10 menit ya!','diantar'=>'Jika belum menerima makanan silahkan hubungi kasir.','selesai'=>'Selamat menikmati makanannya!','gagal'=>'Mohon maaf. Silakan hubungi kasir.'];
    ?>
    <div id="blockPesanan">
      <div class="alert-block <?= $alertCls[$s] ?? 'ab-pending' ?>">
        <span class="ab-icon"><?= $iconMap[$s] ?? '❓' ?></span>
        <div>
          <div class="ab-title"><?= $alertTitle[$s] ?? '' ?></div>
          <div class="ab-sub"><?= $alertSub[$s] ?? '' ?></div>
        </div>
      </div>
    </div>

    <!-- Timer countdown (tampil saat status = diantar) -->
    <div class="timer-wrap" id="timerBox">
      <div class="timer-lbl"><i class="bi bi-alarm"></i> Estimasi tiba di meja</div>
      <div class="timer-digits" id="timerCountdown">02:00</div>
      <div class="timer-bar-wrap">
        <div class="timer-bar" id="timerBarEl" style="width:100%"></div>
      </div>
    </div>

    <div class="refresh-bar" id="refreshBar">
      <span class="refresh-dot"></span>
      <span id="refreshText">Memperbarui otomatis</span>
    </div>

  </div>
</div>

<script>
const ORDER       = "<?= htmlspecialchars($d['no_pesanan'], ENT_QUOTES) ?>";
const MEJA        = "<?= htmlspecialchars($d['nomor_meja'], ENT_QUOTES) ?>";
const TIMER_KEY   = 'timer_diantar_' + ORDER;
const TIMER_TOTAL = 120;

let currentStatus = "<?= $s ?>";
let currentBayar  = "<?= $d['status_pembayaran'] ?>";
let timerInterval = null;

const steps      = ['menunggu','diproses','diantar','selesai'];
const stepColors = ['orange','amber','purple','green'];
const stepIcons  = ['bi-clock','bi-fire','bi-bicycle','bi-check-lg'];
const stepLabels = ['Menunggu','Dimasak','Diantar','Selesai'];

/* Step tracker update */
function updateSteps(status){
  const tracker = document.getElementById('stepTracker');
  if(!tracker) return;
  if(status === 'gagal'){ tracker.style.display = 'none'; return; }
  tracker.style.display = 'flex';
  const idx   = steps.indexOf(status);
  const items = tracker.querySelectorAll('.step-item');
  items.forEach((el, i) => {
    el.className = 'step-item';
    const dot = el.querySelector('.step-dot');
    if(i < idx){
      el.classList.add('done');
      dot.innerHTML = '<i class="bi bi-check-lg"></i>';
    } else if(i === idx){
      el.classList.add('active', stepColors[i]);
      dot.innerHTML = `<i class="bi ${stepIcons[i]}"></i>`;
    } else {
      dot.innerHTML = `<i class="bi ${stepIcons[i]}"></i>`;
    }
  });
}

/* Timer */
function startTimerUntil(endMs){
  stopTimer();
  const box     = document.getElementById('timerBox');
  const display = document.getElementById('timerCountdown');
  const bar     = document.getElementById('timerBarEl');
  box.style.display = 'block';

  function tick(){
    const rem = Math.floor((endMs - Date.now()) / 1000);
    if(rem <= 0){
      stopTimer();
      display.textContent = '00:00';
      bar.style.width = '0%';
      localStorage.removeItem(TIMER_KEY);
      fetch('cek_status_api.php?order=' + encodeURIComponent(ORDER) + '&t=' + Date.now())
        .then(r => r.json())
        .then(data => { if(!data.error) updateUI(data); showSelesai(); })
        .catch(() => showSelesai());
      return;
    }
    const m = Math.floor(rem / 60), s = rem % 60;
    display.textContent = m.toString().padStart(2,'0') + ':' + s.toString().padStart(2,'0');
    const pct = Math.min(100, (rem / TIMER_TOTAL) * 100);
    bar.style.width = pct + '%';
    bar.style.background = rem <= 30
      ? 'linear-gradient(90deg,#ef4444,#f87171)'
      : 'linear-gradient(90deg,var(--purple),#c084fc)';
  }
  tick();
  timerInterval = setInterval(tick, 1000);
}

function stopTimer(){
  if(timerInterval){ clearInterval(timerInterval); timerInterval = null; }
}

/* Init timer — tahan refresh via localStorage */
function initTimer(sisaServer){
  // Bersihkan localStorage jika status bukan diantar (cegah timer sisa lama)
  if(currentStatus !== 'diantar'){
    localStorage.removeItem(TIMER_KEY);
    return;
  }

  let endMs = null;
  const stored = localStorage.getItem(TIMER_KEY);
  if(stored){
    const p = parseInt(stored, 10);
    // Masih valid dan belum lewat
    if(p > Date.now()) endMs = p;
  }
  if(!endMs){
    const sisa = (sisaServer && sisaServer > 0) ? sisaServer : 120;
    endMs = Date.now() + sisa * 1000;
    localStorage.setItem(TIMER_KEY, endMs.toString());
  }
  startTimerUntil(endMs);
}

/* Alert HTML helper */
function alertHTML(cls, icon, title, sub){
  return `<div class="alert-block ${cls}">
    <span class="ab-icon">${icon}</span>
    <div><div class="ab-title">${title}</div><div class="ab-sub">${sub}</div></div>
  </div>`;
}

/* Hero update */
function setHero(color, icon, title, sub){
  document.getElementById('cardTop').className      = 'hero ' + color;
  document.getElementById('statusIcon').innerHTML   = icon;
  document.getElementById('headerTitle').textContent = title;
  document.getElementById('headerSub').textContent   = sub;
}

/* Tampilkan pesanan selesai */
function showSelesai(){
  if(currentStatus === 'selesai') return;
  currentStatus = 'selesai';
  document.getElementById('blockPesanan').innerHTML =
    alertHTML('ab-selesai','✅','Pesanan Selesai','Selamat menikmati makanannya!');
  setHero('green','✅','Pesanan Selesai! 🎉','Terima kasih telah makan di resto kami');
  document.getElementById('timerBox').style.display = 'none';
  stopTimer();
  localStorage.removeItem(TIMER_KEY);
  updateSteps('selesai');

  let sisa = 5;
  const bar = document.getElementById('refreshText');
  bar.textContent = `Kembali ke menu dalam ${sisa} detik…`;
  const cd = setInterval(() => {
    sisa--;
    if(sisa <= 0){ clearInterval(cd); window.location.href = 'index.php?meja=' + MEJA; }
    else bar.textContent = `Kembali ke menu dalam ${sisa} detik…`;
  }, 1000);
}

const heroMap = {
  menunggu: { color:'orange', icon:'⏳', title:'Menunggu Konfirmasi',  sub:'Pesanan sedang menunggu konfirmasi admin' },
  diproses: { color:'amber',  icon:'🍳', title:'Sedang Dimasak',       sub:'Harap tunggu sekitar 10 menit ya!' },
  diantar:  { color:'purple', icon:'🛵', title:'Sedang Diantar',       sub:'Pesanan dalam perjalanan ke meja Anda' },
  selesai:  { color:'green',  icon:'✅', title:'Pesanan Selesai! 🎉', sub:'Terima kasih telah makan di resto kami' },
  gagal:    { color:'red',    icon:'❌', title:'Pesanan Ditolak',      sub:'Silakan hubungi kasir untuk informasi' },
};
const pesanMap = {
  menunggu: ['ab-menunggu','⏳','Menunggu Konfirmasi','Silahkan Menuju Kasir Untuk Melanjutkan Pembayaran.'],
  diproses: ['ab-diproses','🍳','Sedang Dimasak','Harap tunggu sekitar 10 menit ya!'],
  diantar:  ['ab-diantar', '🛵','Sedang Diantar','Mohon Ditunggu Yaa.'],
  selesai:  ['ab-selesai', '✅','Pesanan Selesai','Selamat menikmati makanannya!'],
  gagal:    ['ab-gagal',   '❌','Pesanan Tidak Diproses','Mohon maaf. Silakan hubungi kasir.'],
};

/* Update UI dari data polling */
function updateUI(data){
  if(data.status_pembayaran !== currentBayar || data.status_pesanan !== currentStatus){
    let html = '';
    if(['diantar','selesai'].includes(data.status_pesanan)){
      if(data.status_pembayaran === 'dibayar')
        html = alertHTML('ab-lunas','📝','Note!','Jika anda belum menerima makanan silahkan hubungi kasir.');
      else if(data.status_pembayaran === 'gagal')
        html = alertHTML('ab-gagal','❌','Pembayaran Ditolak','Bukti tidak valid. Silakan hubungi kasir.');
    }
    document.getElementById('blockBayar').innerHTML = html;
    currentBayar = data.status_pembayaran;
  }

  if(data.status_pesanan !== currentStatus){
    if(data.status_pesanan === 'selesai'){ showSelesai(); return; }
    const h = heroMap[data.status_pesanan];
    if(h) setHero(h.color, h.icon, h.title, h.sub);
    const a = pesanMap[data.status_pesanan];
    if(a) document.getElementById('blockPesanan').innerHTML = alertHTML(...a);
    updateSteps(data.status_pesanan);

    if(data.status_pesanan === 'diantar'){
      currentStatus = 'diantar';
      initTimer(data.sisa_waktu);
    } else {
      stopTimer();
      localStorage.removeItem(TIMER_KEY);
      document.getElementById('timerBox').style.display = 'none';
      currentStatus = data.status_pesanan;
    }
  }
}

/* Polling setiap 3 detik */
function pollStatus(){
  fetch('cek_status_api.php?order=' + encodeURIComponent(ORDER) + '&t=' + Date.now())
    .then(r => r.json())
    .then(data => { if(!data.error) updateUI(data); })
    .catch(err => console.log('Poll error:', err));
}

/* Init */
(function init(){
  <?php if($d['status_pesanan'] === 'diantar'): ?>
  initTimer(<?= (int)$sisa ?>);
  <?php endif; ?>
  <?php if($d['status_pesanan'] === 'selesai'): ?>
  showSelesai();
  <?php endif; ?>
  updateSteps("<?= $s ?>");
  setInterval(pollStatus, 3000);
  pollStatus();
})();
</script>
</body>
</html>