<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'sql102.byetcluster.com';
$user = 'if0_42006054';
$pass = 'ZGy1JwkNuJj';
$db   = 'if0_42006054_resto_qr';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die('<div style="font-family:sans-serif;padding:20px;background:#fee2e2;color:#b91c1c;border-radius:8px;margin:20px">
        <strong>Koneksi database gagal!</strong><br>' . mysqli_connect_error() . '
    </div>');
}

mysqli_set_charset($conn, 'utf8mb4');
?>