<?php

$host = "sql102.byetcluster.com";
$user = "if0_42006054";
$pass = "ZGy1JwkNuJj";
$db   = "if0_42006054_resto_qr";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>