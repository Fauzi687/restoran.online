<?php
session_start();
include '../config/koneksi.php';
include 'auto_hapus.php';

$meja = $_GET['meja'];

// ambil kategori
$kategori = mysqli_query($conn,"
SELECT * FROM kategori
WHERE status='aktif'
");

// hitung jumlah cart
$jumlah_cart = 0;

if(isset($_SESSION['cart'])){
    foreach($_SESSION['cart'] as $qty){
        $jumlah_cart += $qty;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Menu Restoran</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

*{
    font-family:'Poppins', sans-serif;
}

body{
    background:#f4f7fb;
}

/* HEADER */
.header-box{
    background:linear-gradient(135deg,#ff7b00,#ffb347);
    padding:30px;
    border-radius:20px;
    color:white;
    margin-bottom:30px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

.header-box h2{
    font-weight:700;
}

.header-box p{
    margin:0;
    opacity:0.9;
}

/* BUTTON KERANJANG */
.btn-cart{
    background:white;
    color:#ff7b00;
    border:none;
    border-radius:15px;
    padding:10px 20px;
    font-weight:600;
    transition:0.3s;
    text-decoration:none;
}

.btn-cart:hover{
    background:#fff3e6;
    transform:translateY(-3px);
}

/* KATEGORI */
.kategori-btn{
    border-radius:12px;
    padding:10px 18px;
    font-weight:500;
    transition:0.3s;
    text-decoration:none;
    display:inline-block;
}

.kategori-btn:hover{
    transform:translateY(-2px);
}

/* CARD MENU */
.card-menu{
    border:none;
    border-radius:20px;
    overflow:hidden;
    transition:0.3s;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

.card-menu:hover{
    transform:translateY(-8px);
    box-shadow:0 15px 35px rgba(0,0,0,0.12);
}

.card-menu img{
    height:220px;
    object-fit:cover;
}

.card-body{
    padding:20px;
}

.nama-menu{
    font-size:20px;
    font-weight:600;
    margin-bottom:10px;
}

.stok{
    background:#eef5ff;
    color:#0d6efd;
    display:inline-block;
    padding:5px 12px;
    border-radius:10px;
    font-size:14px;
    margin-bottom:15px;
}

.harga{
    font-size:24px;
    font-weight:700;
    color:#ff7b00;
}

/* BUTTON PESAN */
.btn-pesan{
    background:linear-gradient(135deg,#ff7b00,#ff9900);
    border:none;
    border-radius:12px;
    padding:12px;
    font-weight:600;
    transition:0.3s;
    color:white;
    text-decoration:none;
    display:block;
    text-align:center;
    cursor:pointer;
}

.btn-pesan:hover{
    transform:scale(1.03);
    background:linear-gradient(135deg,#ff6600,#ff8800);
    color:white;
}

.btn-pesan:disabled {
    opacity: 0.6;
    transform: none;
    cursor: not-allowed;
}

/* TOAST NOTIFIKASI */
.toast-notif {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #22c55e;
    color: white;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 500;
    z-index: 9999;
    animation: slideIn 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.badge{
    font-size:12px;
}

/* RESPONSIVE */
@media(max-width:768px){
    .header-box{
        text-align:center;
    }
}

/* Sticky kategori */
.kategori-sticky {
    position: sticky;
    top: 0;
    background: #f4f7fb;
    z-index: 100;
    padding: 10px 0;
    margin-bottom: 20px;
}
</style>
</head>
<body>

<div class="container py-4">

<div class="header-box d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h2>🍽️ Menu Restoran</h2>
        <p>Meja : <?= $meja ?></p>
    </div>
    <a href="cart.php?meja=<?= $meja ?>" class="btn-cart position-relative">
        🛒 Keranjang
        <?php if($jumlah_cart > 0){ ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartCount">
                <?= $jumlah_cart ?>
            </span>
        <?php } ?>
    </a>
</div>

<!-- PILIHAN KATEGORI - STICKY -->
<div class="kategori-sticky">
    <ul class="nav nav-pills">
        <li class="nav-item me-2">
            <a href="?meja=<?= $meja ?>" class="btn btn-dark kategori-btn">Semua</a>
        </li>
        <?php 
        // Reset pointer kategori
        mysqli_data_seek($kategori, 0);
        while($k = mysqli_fetch_array($kategori)){ 
            $active = (isset($_GET['kategori']) && $_GET['kategori'] == $k['id_kategori']) ? 'btn-primary' : 'btn-outline-primary';
        ?>
            <li class="nav-item me-2">
                <a href="?meja=<?= $meja ?>&kategori=<?= $k['id_kategori'] ?>" class="btn <?= $active ?> kategori-btn">
                    <?= $k['nama_kategori'] ?>
                </a>
            </li>
        <?php } ?>
    </ul>
</div>

<div class="row" id="menuContainer">
    <?php
    $where = "";
    if(isset($_GET['kategori'])){
        $id_kategori = $_GET['kategori'];
        $where = "AND id_kategori='$id_kategori'";
    }

    $menu = mysqli_query($conn,"
        SELECT * FROM menu
        WHERE status='aktif'
        $where
        ORDER BY id_menu DESC
    ");

    while($row = mysqli_fetch_array($menu)){
    ?>
    <div class="col-md-4 mb-4" data-menu-id="<?= $row['id_menu'] ?>">
        <div class="card card-menu h-100">
            <img src="../uploads/menu/<?= $row['foto'] ?>" class="card-img-top" onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
            <div class="card-body d-flex flex-column">
                <div class="nama-menu"><?= $row['nama_menu'] ?></div>
                <div class="stok">Stok : <?= $row['stok'] ?></div>
                <div class="harga mb-3">Rp <?= number_format($row['harga']) ?></div>
                <button class="btn btn-pesan w-100 mt-auto" data-id="<?= $row['id_menu'] ?>" data-nama="<?= $row['nama_menu'] ?>">
                    ➕ Tambah Pesanan
                </button>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

</div>

<script>
// Simpan posisi scroll sebelum AJAX
let scrollPosition = 0;

// Fungsi untuk menyimpan posisi scroll
function saveScrollPosition() {
    scrollPosition = window.scrollY;
}

// Fungsi untuk mengembalikan posisi scroll
function restoreScrollPosition() {
    window.scrollTo(0, scrollPosition);
}

// Tampilkan notifikasi
function showNotification(message, isError = false) {
    const notif = document.createElement('div');
    notif.className = 'toast-notif';
    notif.style.backgroundColor = isError ? '#ef4444' : '#22c55e';
    notif.innerHTML = message;
    document.body.appendChild(notif);
    
    setTimeout(() => {
        notif.remove();
    }, 2000);
}

// Update jumlah keranjang di badge
function updateCartCount() {
    fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            const cartCount = document.getElementById('cartCount');
            if (data.count > 0) {
                if (cartCount) {
                    cartCount.textContent = data.count;
                } else {
                    // Buat badge baru jika belum ada
                    const btnCart = document.querySelector('.btn-cart');
                    const badge = document.createElement('span');
                    badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                    badge.id = 'cartCount';
                    badge.textContent = data.count;
                    btnCart.style.position = 'relative';
                    btnCart.appendChild(badge);
                }
            } else {
                if (cartCount) cartCount.remove();
            }
        })
        .catch(err => console.log('Error:', err));
}

// Event listener untuk semua tombol pesan
document.querySelectorAll('.btn-pesan').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        
        const id_menu = this.getAttribute('data-id');
        const nama_menu = this.getAttribute('data-nama');
        const meja = '<?= $meja ?>';
        
        // Simpan posisi scroll saat ini
        saveScrollPosition();
        
        // Disable tombol sementara
        const originalText = this.innerHTML;
        this.innerHTML = '⏳ Menambahkan...';
        this.disabled = true;
        
        // Kirim request AJAX
        fetch('tambah_cart_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id_menu + '&meja=' + meja
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('✅ ' + nama_menu + ' ditambahkan ke keranjang!');
                updateCartCount();
            } else {
                showNotification('❌ Gagal: ' + data.message, true);
            }
        })
        .catch(error => {
            showNotification('❌ Gagal menambahkan pesanan', true);
        })
        .finally(() => {
            // Kembalikan tombol ke keadaan semula
            this.innerHTML = originalText;
            this.disabled = false;
            // Kembalikan posisi scroll
            restoreScrollPosition();
        });
    });
});

// Simpan posisi scroll saat link kategori diklik
document.querySelectorAll('.kategori-btn').forEach(link => {
    link.addEventListener('click', function(e) {
        saveScrollPosition();
    });
});

// Kembalikan posisi scroll setelah halaman selesai dimuat
window.addEventListener('load', function() {
    if (sessionStorage.getItem('scrollPos')) {
        window.scrollTo(0, parseInt(sessionStorage.getItem('scrollPos')));
        sessionStorage.removeItem('scrollPos');
    }
});

// Simpan posisi scroll sebelum page unload (untuk navigasi biasa)
window.addEventListener('beforeunload', function() {
    sessionStorage.setItem('scrollPos', window.scrollY);
});
</script>

</body>
</html>