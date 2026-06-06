<?php
include '../config/koneksi.php';

$id = $_GET['id'];

$data = mysqli_query($conn,"
SELECT * FROM meja
WHERE id_meja='$id'
");

$row = mysqli_fetch_array($data);

unlink("../qr/meja/".$row['qr_code']);

mysqli_query($conn,"
DELETE FROM meja
WHERE id_meja='$id'
");

header("Location: meja.php");
?>