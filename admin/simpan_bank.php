<?php
session_start();
require '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: bank.php');
    exit;
}

$aksi          = $_POST['aksi']          ?? '';
$nama_bank     = trim($_POST['nama_bank']     ?? '');
$nama_pemilik  = trim($_POST['nama_pemilik']  ?? '');
$nomor_rekening= trim($_POST['nomor_rekening'] ?? '');
$logo_lama     = $_POST['logo_lama']     ?? '';
$qris_lama     = $_POST['qris_lama']     ?? '';

// Validasi
if (empty($nama_bank) || empty($nama_pemilik) || empty($nomor_rekening)) {
    $_SESSION['pesan'] = 'error|Nama bank, nama pemilik, dan nomor rekening wajib diisi.';
    header('Location: bank.php');
    exit;
}

// Sanitasi
$nama_bank      = mysqli_real_escape_string($conn, $nama_bank);
$nama_pemilik   = mysqli_real_escape_string($conn, $nama_pemilik);
$nomor_rekening = mysqli_real_escape_string($conn, $nomor_rekening);

// Folder upload
$upload_dir = '../uploads/bank/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// ── Upload Logo ──
function upload_file($key, $upload_dir, $lama = '') {
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] === UPLOAD_ERR_NO_FILE) {
        return $lama; // tidak ada file baru, pakai yang lama
    }
    if ($_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    $ext     = strtolower(pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];
    if (!in_array($ext, $allowed)) return false;
    if ($_FILES[$key]['size'] > 2 * 1024 * 1024) return false; // maks 2MB

    $nama_file = $key . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
    if (move_uploaded_file($_FILES[$key]['tmp_name'], $upload_dir . $nama_file)) {
        // Hapus file lama
        if ($lama && file_exists($upload_dir . $lama)) unlink($upload_dir . $lama);
        return $nama_file;
    }
    return false;
}

$logo = upload_file('logo', $upload_dir, $logo_lama);
$qris = upload_file('qris', $upload_dir, $qris_lama);

if ($logo === false) {
    $_SESSION['pesan'] = 'error|File logo tidak valid (maks 2MB, format JPG/PNG/WEBP).';
    header('Location: bank.php');
    exit;
}
if ($qris === false) {
    $_SESSION['pesan'] = 'error|File QRIS tidak valid (maks 2MB, format JPG/PNG/WEBP).';
    header('Location: bank.php');
    exit;
}

$logo_db = mysqli_real_escape_string($conn, $logo ?: '');
$qris_db = mysqli_real_escape_string($conn, $qris ?: '');

// ── TAMBAH ──
if ($aksi === 'tambah') {

    $sql = "INSERT INTO bank (nama_bank, nama_pemilik, nomor_rekening, logo, qris)
            VALUES ('$nama_bank', '$nama_pemilik', '$nomor_rekening', '$logo_db', '$qris_db')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['pesan'] = 'sukses|Rekening berhasil ditambahkan.';
    } else {
        $_SESSION['pesan'] = 'error|Gagal menyimpan: ' . mysqli_error($conn);
    }

// ── EDIT ──
} elseif ($aksi === 'edit') {

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        $_SESSION['pesan'] = 'error|ID tidak valid.';
        header('Location: bank.php');
        exit;
    }

    $sql = "UPDATE bank SET
                nama_bank      = '$nama_bank',
                nama_pemilik   = '$nama_pemilik',
                nomor_rekening = '$nomor_rekening',
                logo           = '$logo_db',
                qris           = '$qris_db'
            WHERE id_bank = $id";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['pesan'] = 'sukses|Rekening berhasil diperbarui.';
    } else {
        $_SESSION['pesan'] = 'error|Gagal memperbarui: ' . mysqli_error($conn);
    }

} else {
    $_SESSION['pesan'] = 'error|Aksi tidak dikenali.';
}

header('Location: bank.php');
exit;