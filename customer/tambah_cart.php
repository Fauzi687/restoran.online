<?php
session_start();

$id = $_GET['id'];
$meja = $_GET['meja'];

if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

if(isset($_SESSION['cart'][$id])){

    $_SESSION['cart'][$id] += 1;

}else{

    $_SESSION['cart'][$id] = 1;

}

header("Location: index.php?meja=".$meja);
?>