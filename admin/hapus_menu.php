<?php
include '../config/koneksi.php';

$id = $_GET['id'];

$data = mysqli_query($conn,"
SELECT * FROM menu
WHERE id_menu='$id'
");

$row = mysqli_fetch_array($data);

unlink("../uploads/menu/".$row['foto']);

mysqli_query($conn,"
DELETE FROM menu
WHERE id_menu='$id'
");

header("Location: menu.php");
?>