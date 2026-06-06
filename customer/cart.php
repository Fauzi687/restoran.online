<?php
session_start();

include '../config/koneksi.php';

$meja = $_GET['meja'];

$total = 0;

// jika cart belum ada
if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Keranjang</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

*{
    font-family:'Poppins', sans-serif;
    box-sizing:border-box;
}

body{
    background:#f4f7fb;
    overflow-x:hidden;
}

/* CONTAINER */
.container{
    width:100%;
    max-width:100%;
}

/* HEADER */
.header-box{
    background:linear-gradient(135deg,#ff7b00,#ffb347);
    padding:25px;
    border-radius:20px;
    color:white;
    margin-bottom:25px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

.header-box h2{
    font-weight:700;
    margin:0;
}

.header-box p{
    margin:5px 0 0;
    opacity:0.9;
}

/* CARD */
.card-custom{
    border:none;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

/* TABLE WRAPPER */
.table-responsive{
    width:100%;
    overflow-x:auto;
    -webkit-overflow-scrolling:touch;
}

/* TABLE */
.table{
    margin:0;
    min-width:700px;
}

.table thead{
    background:#ff7b00;
    color:white;
}

.table th,
.table td{
    vertical-align:middle;
    padding:15px;
    white-space:nowrap;
}

/* BUTTON */
.btn-custom{
    background:linear-gradient(135deg,#ff7b00,#ff9900);
    border:none;
    border-radius:12px;
    padding:12px 20px;
    font-weight:600;
    color:white;
    transition:0.3s;
}

.btn-custom:hover{
    transform:translateY(-2px);
    background:linear-gradient(135deg,#ff6600,#ff8800);
    color:white;
}

.btn-secondary-custom{
    border-radius:12px;
    padding:12px 20px;
    font-weight:600;
}

/* ALERT */
.alert{
    border:none;
    border-radius:15px;
    padding:18px;
}

/* TOTAL */
.total-box{
    background:#fff3e6;
    color:#ff7b00;
    padding:15px;
    border-radius:15px;
    font-size:22px;
    font-weight:700;
}

/* ACTION BUTTON */
.action-buttons{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

/* RESPONSIVE */
@media(max-width:768px){

    body{
        font-size:14px;
    }

    .container{
        padding-left:12px;
        padding-right:12px;
    }

    .header-box{
        text-align:center;
        padding:20px;
        border-radius:18px;
    }

    .header-box h2{
        font-size:24px;
    }

    .header-box p{
        font-size:14px;
    }

    .table{
        font-size:14px;
    }

    .table th,
    .table td{
        padding:12px;
    }

    .total-box{
        font-size:20px;
        text-align:center;
    }

    .action-buttons{
        flex-direction:column;
    }

    .action-buttons .btn{
        width:100%;
    }

}

/* HP KECIL */
@media(max-width:480px){

    .header-box{
        padding:18px;
    }

    .header-box h2{
        font-size:21px;
    }

    .table{
        font-size:13px;
        min-width:600px;
    }

    .table th,
    .table td{
        padding:10px;
    }

    .btn-custom,
    .btn-secondary-custom{
        width:100%;
        padding:12px;
        font-size:14px;
    }

    .total-box{
        font-size:18px;
        padding:14px;
    }

}

</style>

</head>
<body>

<div class="container py-4">

<!-- HEADER -->
<div class="header-box">

<h2>🛒 Keranjang Pesanan</h2>

<p>
Meja : <?= $meja ?>
</p>

</div>

<?php if(count($_SESSION['cart']) == 0){ ?>

<div class="alert alert-warning">

Keranjang masih kosong

</div>

<a href="index.php?meja=<?= $meja ?>"
class="btn btn-custom">

Pilih Menu

</a>

<?php } else { ?>

<div class="card card-custom">

<div class="card-body p-0">

<div class="table-responsive">

<table class="table table-bordered bg-white">

<thead>

<tr>
<th>Menu</th>
<th>Harga</th>
<th>Qty</th>
<th>Subtotal</th>
<th>Aksi</th>
</tr>

</thead>

<tbody>

<?php
foreach($_SESSION['cart'] as $id => $qty){

$data = mysqli_query($conn,"
SELECT * FROM menu
WHERE id_menu='$id'
");

$row = mysqli_fetch_array($data);

if(!$row){
    continue;
}

$subtotal = $row['harga'] * $qty;

$total += $subtotal;
?>

<tr>

<td>

<b><?= $row['nama_menu'] ?></b>

</td>

<td>
Rp <?= number_format($row['harga']) ?>
</td>

<td>

<span class="badge bg-primary p-2">
<?= $qty ?>
</span>

</td>

<td>
Rp <?= number_format($subtotal) ?>
</td>

<td>

<a href="hapus_cart.php?id=<?= $id ?>&meja=<?= $meja ?>"
class="btn btn-danger btn-sm">

Hapus 1

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<div class="total-box mt-4 mb-4">

Total :
Rp <?= number_format($total) ?>

</div>

<div class="action-buttons">

<a href="index.php?meja=<?= $meja ?>"
class="btn btn-secondary btn-secondary-custom">

Tambah Menu

</a>

<a href="checkout.php?meja=<?= $meja ?>"
class="btn btn-custom">

Checkout

</a>

</div>

<?php } ?>

</div>

</body>
</html>