<?php
session_start();

$id = $_GET['id'];
$meja = $_GET['meja'];

if(isset($_SESSION['cart'][$id])){

    // kurangi qty 1
    $_SESSION['cart'][$id]--;

    // jika qty sudah 0 maka hapus item
    if($_SESSION['cart'][$id] <= 0){

        unset($_SESSION['cart'][$id]);

    }

}

header("Location: cart.php?meja=".$meja);
exit;
?>