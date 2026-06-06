<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config/koneksi.php';

$error = '';

if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $query = mysqli_query($conn,"SELECT * FROM admin 
    WHERE username='$username' 
    AND password='$password'");

    if(mysqli_num_rows($query) > 0){

        $_SESSION['admin'] = true;
        header("Location: admin/dashboard.php");

    } else {

        $error = "Username atau password salah.";

    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Login — Resto App</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --brand:        #FF4D1C;
    --brand-dark:   #CC3A12;
    --brand-glow:   rgba(255,77,28,.22);
    --bg:           #0C0C0E;
    --surface:      #141416;
    --surface-2:    #1C1C1F;
    --border:       rgba(255,255,255,0.07);
    --border-mid:   rgba(255,255,255,0.12);
    --text-1:       #F5F4F0;
    --text-2:       rgba(245,244,240,0.42);
    --text-3:       rgba(245,244,240,0.22);
    --radius:       14px;
    --radius-sm:    10px;
}

html, body { height: 100%; }

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text-1);
    min-height: 100vh;
    display: flex;
    align-items: stretch;
}

/* ── Ambient blobs ── */
.bg-blob { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
.b1 {
    position: absolute; width: 560px; height: 560px;
    background: radial-gradient(circle, #FF4D1C 0%, transparent 70%);
    opacity: .13; border-radius: 50%;
    top: -240px; left: -180px;
    animation: pulse 8s ease-in-out infinite alternate;
}
.b2 {
    position: absolute; width: 420px; height: 420px;
    background: radial-gradient(circle, #7B1F00 0%, transparent 70%);
    opacity: .18; border-radius: 50%;
    bottom: -200px; right: -120px;
    animation: pulse 10s ease-in-out infinite alternate-reverse;
}
.b3 {
    position: absolute; width: 300px; height: 300px;
    background: radial-gradient(circle, #FF8A1C 0%, transparent 70%);
    opacity: .08; border-radius: 50%;
    top: 55%; left: 40%;
    animation: pulse 12s ease-in-out infinite alternate;
}
@keyframes pulse { from { transform: scale(1); } to { transform: scale(1.12); } }

.bg-grid {
    position: fixed; inset: 0; z-index: 0;
    background-image:
        linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
    background-size: 52px 52px;
}

/* ── Layout shell ── */
.shell {
    position: relative; z-index: 10;
    width: 100%; display: flex; align-items: stretch;
}

/* ── Left panel ── */
.left-panel {
    flex: 1;
    padding: 56px 64px;
    display: flex; flex-direction: column; justify-content: space-between;
    border-right: 1px solid var(--border);
}

.brand-row {
    display: flex; align-items: center; gap: 10px;
}
.brand-logo {
    width: 38px; height: 38px;
    background: var(--brand);
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 0 18px var(--brand-glow);
}
.brand-logo svg { width: 18px; height: 18px; stroke: #fff; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
.brand-name {
    font-family: 'Syne', sans-serif; font-size: 14px;
    font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #fff;
}

.hero-block { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 48px 0; }
.hero-eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 11px; font-weight: 600; letter-spacing: .14em; text-transform: uppercase;
    color: var(--brand); margin-bottom: 20px;
}
.hero-eyebrow::before {
    content: ''; display: block; width: 20px; height: 1.5px; background: var(--brand);
}
.hero {
    font-family: 'Syne', sans-serif;
    font-size: clamp(38px, 4.5vw, 56px);
    font-weight: 800; line-height: 1.07;
    letter-spacing: -.03em; color: #fff;
    margin-bottom: 20px;
}
.hero em { color: var(--brand); font-style: normal; }
.hero-sub {
    font-size: 14.5px; line-height: 1.8;
    color: var(--text-2); max-width: 340px; margin-bottom: 44px;
}

.feat-list { display: flex; flex-direction: column; gap: 14px; }
.feat {
    display: flex; align-items: center; gap: 12px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 12px 16px;
    transition: background .2s, border-color .2s;
}
.feat:hover { background: rgba(255,77,28,.06); border-color: rgba(255,77,28,.2); }
.feat-icon {
    width: 30px; height: 30px; min-width: 30px;
    background: rgba(255,77,28,.12); border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
}
.feat-icon svg { width: 14px; height: 14px; stroke: var(--brand); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
.feat span { font-size: 13px; color: var(--text-2); line-height: 1.45; }

.left-footer {
    font-size: 11px; color: var(--text-3);
    border-top: 1px solid var(--border); padding-top: 24px;
    display: flex; align-items: center; gap: 16px;
}
.left-footer a { color: var(--text-3); text-decoration: none; transition: color .15s; }
.left-footer a:hover { color: var(--text-2); }

/* ── Right panel ── */
.right-panel {
    width: 480px; min-width: 480px;
    display: flex; align-items: center; justify-content: center;
    padding: 48px 44px;
}

/* ── Login card ── */
.login-card {
    width: 100%; max-width: 390px;
    background: var(--surface);
    border: 1px solid var(--border-mid);
    border-radius: 24px;
    padding: 44px 40px 36px;
    position: relative; overflow: hidden;
}

/* Animated top bar */
.login-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, transparent, var(--brand), #FF9A5C, var(--brand), transparent);
    background-size: 300% 100%;
    animation: shimmer 4s linear infinite;
}
@keyframes shimmer { 0%{background-position:100%} 100%{background-position:-100%} }

/* Corner glow */
.login-card::after {
    content: ''; position: absolute; top: -80px; right: -80px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(255,77,28,.12) 0%, transparent 70%);
    border-radius: 50%; pointer-events: none;
}

/* Avatar icon */
.card-avatar {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, rgba(255,77,28,.2), rgba(255,77,28,.08));
    border: 1px solid rgba(255,77,28,.25);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 22px;
}
.card-avatar svg { width: 22px; height: 22px; stroke: var(--brand); fill: none; stroke-width: 1.8; stroke-linecap: round; }

.card-eyebrow {
    font-size: 10.5px; font-weight: 600; letter-spacing: .13em;
    text-transform: uppercase; color: var(--brand); margin-bottom: 8px;
}
.card-title {
    font-family: 'Syne', sans-serif; font-size: 26px;
    font-weight: 700; color: var(--text-1); margin-bottom: 4px; letter-spacing: -.025em;
}
.card-sub { font-size: 13.5px; color: var(--text-2); margin-bottom: 30px; line-height: 1.6; }

/* Error box */
.error-box {
    background: rgba(214,41,59,.1); border: 1px solid rgba(214,41,59,.22);
    border-radius: var(--radius-sm); padding: 12px 15px;
    font-size: 13px; color: #f59598; margin-bottom: 20px;
    display: flex; align-items: flex-start; gap: 10px; line-height: 1.5;
}
.error-box svg { width: 15px; height: 15px; min-width: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; margin-top: 1px; }

/* Form groups */
.fgroup { margin-bottom: 16px; }
.flabel {
    display: block; font-size: 11px; font-weight: 600;
    letter-spacing: .08em; text-transform: uppercase;
    color: var(--text-2); margin-bottom: 7px;
}
.input-wrap { position: relative; }
.input-icon {
    position: absolute; left: 13px; top: 50%;
    transform: translateY(-50%); pointer-events: none;
}
.input-icon svg { width: 15px; height: 15px; stroke: var(--text-3); fill: none; stroke-width: 1.8; stroke-linecap: round; display: block; }
.finput {
    width: 100%;
    background: rgba(255,255,255,.04);
    border: 1.5px solid var(--border-mid);
    border-radius: var(--radius-sm);
    padding: 13px 44px 13px 42px;
    font-family: 'DM Sans', sans-serif; font-size: 14px; color: var(--text-1);
    transition: border-color .18s, box-shadow .18s, background .18s;
    outline: none; appearance: none; -webkit-appearance: none;
}
.finput::placeholder { color: var(--text-3); }
.finput:hover { border-color: var(--border-mid); background: rgba(255,255,255,.05); }
.finput:focus {
    border-color: var(--brand);
    box-shadow: 0 0 0 3.5px rgba(255,77,28,.14);
    background: rgba(255,255,255,.06);
}
.finput:focus + .input-icon svg,
.input-wrap:focus-within .input-icon svg { stroke: var(--brand); opacity: .7; }

/* Toggle pw button */
.toggle-pw {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    color: var(--text-3); padding: 4px; display: flex; align-items: center;
    border-radius: 6px; transition: color .15s, background .15s;
}
.toggle-pw:hover { color: var(--text-2); background: rgba(255,255,255,.06); }
.toggle-pw svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 1.8; stroke-linecap: round; }

/* Submit button */
.btn-login {
    width: 100%; padding: 14px;
    background: var(--brand); color: #fff;
    font-family: 'DM Sans', sans-serif; font-size: 14.5px; font-weight: 600;
    border: none; border-radius: var(--radius-sm); cursor: pointer;
    box-shadow: 0 4px 24px rgba(255,77,28,.3), 0 1px 3px rgba(0,0,0,.4);
    transition: background .2s, box-shadow .2s, transform .15s;
    display: flex; align-items: center; justify-content: center; gap: 9px;
    margin-top: 8px; position: relative; overflow: hidden;
    letter-spacing: .01em;
}
.btn-login::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(255,255,255,.12) 0%, transparent 60%);
}
.btn-login:hover { background: var(--brand-dark); box-shadow: 0 6px 32px rgba(255,77,28,.45), 0 1px 3px rgba(0,0,0,.4); transform: translateY(-1px); }
.btn-login:active { transform: scale(.98) translateY(0); }
.btn-login svg { width: 16px; height: 16px; stroke: #fff; fill: none; stroke-width: 2; stroke-linecap: round; position: relative; }

/* Divider */
.divider { display: flex; align-items: center; gap: 12px; margin: 22px 0 0; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
.divider span { font-size: 11px; color: var(--text-3); white-space: nowrap; }

/* Card footer */
.card-footer {
    text-align: center; font-size: 11.5px; color: var(--text-3);
    margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--border);
}

/* =========================================
   RESPONSIVE — Mobile First
   ========================================= */

/* Tablet: hide left panel */
@media (max-width: 960px) {
    .left-panel { display: none; }
    .right-panel {
        width: 100%; min-width: 0;
        padding: 24px 20px 40px;
        align-items: flex-start;
        padding-top: 40px;
    }
    .login-card { max-width: 420px; margin: 0 auto; }
}

/* Mobile: full-width, lighter padding */
@media (max-width: 520px) {
    body { align-items: flex-start; background: var(--bg); }
    .right-panel {
        padding: 0;
        min-height: 100vh;
        align-items: stretch;
    }
    .login-card {
        border-radius: 0;
        border-left: none; border-right: none; border-top: none;
        padding: 0;
        background: transparent;
        max-width: 100%;
        display: flex; flex-direction: column; min-height: 100vh;
    }

    /* Top brand bar for mobile */
    .mobile-topbar {
        display: flex !important;
        align-items: center; justify-content: space-between;
        padding: 20px 24px 18px;
        border-bottom: 1px solid var(--border);
        background: rgba(20,20,22,.8);
        backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
    }
    .mobile-brand { display: flex; align-items: center; gap: 9px; }
    .mobile-logo {
        width: 32px; height: 32px; background: var(--brand);
        border-radius: 9px; display: flex; align-items: center; justify-content: center;
    }
    .mobile-logo svg { width: 14px; height: 14px; stroke: #fff; fill: none; stroke-width: 2; stroke-linecap: round; }
    .mobile-brand-name { font-family: 'Syne', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #fff; }
    .mobile-badge {
        font-size: 10px; font-weight: 600; letter-spacing: .08em;
        text-transform: uppercase; color: rgba(255,77,28,.8);
        border: 1px solid rgba(255,77,28,.25); border-radius: 6px;
        padding: 3px 9px; background: rgba(255,77,28,.08);
    }

    /* Form container */
    .mobile-form-wrap {
        padding: 32px 24px 32px;
        flex: 1; display: flex; flex-direction: column;
    }

    /* Card decorative elements are hidden on mobile borderless variant */
    .login-card::before { border-radius: 0; }
    .login-card::after { display: none; }

    .card-avatar { width: 46px; height: 46px; border-radius: 14px; margin-bottom: 18px; }
    .card-avatar svg { width: 20px; height: 20px; }
    .card-title { font-size: 22px; }
    .card-sub { font-size: 13px; margin-bottom: 26px; }
    .fgroup { margin-bottom: 14px; }
    .finput { padding: 14px 44px 14px 42px; font-size: 16px; /* prevent zoom */ }
    .btn-login { padding: 15px; font-size: 15px; }
}

/* Very small phones */
@media (max-width: 360px) {
    .mobile-form-wrap { padding: 26px 18px 28px; }
}

/* Desktop-only: show/hide helpers */
.mobile-topbar { display: none; }
.mobile-form-wrap { display: contents; }
</style>
</head>
<body>

<div class="bg-blob"><div class="b1"></div><div class="b2"></div><div class="b3"></div></div>
<div class="bg-grid"></div>

<div class="shell">

    <!-- ── Left info panel (hidden on mobile) ── -->
    <div class="left-panel">
        <div class="brand-row">
            <div class="brand-logo">
                <svg viewBox="0 0 24 24"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
            </div>
            <span class="brand-name">Resto App</span>
        </div>

        <div class="hero-block">
            <div class="hero-eyebrow">Platform Manajemen Restoran</div>
            <h1 class="hero">Kelola Restoran<br>Lebih <em>Cerdas</em></h1>
            <p class="hero-sub">Satu dasbor untuk semua kebutuhan restoran Anda — dari pesanan, menu, hingga laporan penjualan secara real-time.</p>

            <div class="feat-list">
                <div class="feat">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/><path d="M9 12h6m-6 4h4"/></svg>
                    </div>
                    <span>Manajemen menu &amp; kategori real-time</span>
                </div>
                <div class="feat">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    </div>
                    <span>Sistem pesanan meja berbasis QR Code</span>
                </div>
                <div class="feat">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <span>Laporan grafik penjualan otomatis</span>
                </div>
                <div class="feat">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <span>Konfirmasi pembayaran instan</span>
                </div>
            </div>
        </div>

        <div class="left-footer">
            <span>&copy; <?= date('Y') ?> Resto App</span>
            <span>·</span>
            <a href="#">Kebijakan Privasi</a>
            <span>·</span>
            <a href="#">Bantuan</a>
        </div>
    </div>

    <!-- ── Right panel (login form) ── -->
    <div class="right-panel">
        <div class="login-card">

            <!-- Mobile top bar — only visible on phones -->
            <div class="mobile-topbar">
                <div class="mobile-brand">
                    <div class="mobile-logo">
                        <svg viewBox="0 0 24 24"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
                    </div>
                    <span class="mobile-brand-name">Resto App</span>
                </div>
                <span class="mobile-badge">Admin</span>
            </div>

            <!-- Actual form content -->
            <div class="mobile-form-wrap">

                <div class="card-avatar">
                    <svg viewBox="0 0 24 24"><path d="M12 2a5 5 0 1 0 5 5A5 5 0 0 0 12 2zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1"/></svg>
                </div>

                <p class="card-eyebrow">Admin Panel</p>
                <h2 class="card-title">Selamat Datang</h2>
                <p class="card-sub">Masuk untuk mengelola restoran Anda.</p>

                <?php if($error): ?>
                <div class="error-box" role="alert">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>

                    <div class="fgroup">
                        <label class="flabel" for="username">Username</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </span>
                            <input type="text" id="username" name="username" class="finput"
                                   placeholder="Masukkan username" required
                                   autocomplete="username" spellcheck="false"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="fgroup">
                        <label class="flabel" for="password">Password</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input type="password" id="password" name="password" class="finput"
                                   placeholder="Masukkan password" required
                                   autocomplete="current-password">
                            <button type="button" class="toggle-pw" onclick="togglePw(this)" aria-label="Tampilkan password">
                                <svg id="eye-icon" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn-login">
                        <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        Masuk ke Dashboard
                    </button>

                </form>

                <div class="divider"><span>informasi sistem</span></div>

                <p class="card-footer">&copy; <?= date('Y') ?> Resto App &mdash; Hak akses hanya untuk administrator.</p>

            </div><!-- /.mobile-form-wrap -->

        </div>
    </div>

</div><!-- /.shell -->

<script>
function togglePw(btn) {
    const inp = document.getElementById('password');
    const ico = document.getElementById('eye-icon');
    const isHidden = inp.type === 'password';
    inp.type = isHidden ? 'text' : 'password';
    btn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
    ico.innerHTML = isHidden
        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
}
</script>

</body>
</html>