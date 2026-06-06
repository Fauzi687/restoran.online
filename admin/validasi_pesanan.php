<?php
include '../config/koneksi.php';

$id = $_GET['id'];

mysqli_query($conn,"
UPDATE transaksi SET
status_pesanan='diproses'
WHERE id_transaksi='$id'
");

header("Location: pesanan_masuk.php");
?>