<?php
include '../config/database.php';
session_start();

// Pastikan pengguna sudah login dan berperan sebagai pembeli
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: ../auth/login.php");
    exit;
}

// Pastikan parameter id pesanan dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: pesanan_saya.php?error=invalid_request");
    exit;
}

$id_pesanan = intval($_GET['id']);
$id_pembeli = $_SESSION['id_user'];

// Cek apakah pesanan benar milik pembeli dan belum diproses
$query = mysqli_query($conn, "SELECT * FROM pesanan WHERE id_pesanan='$id_pesanan' AND id_pembeli='$id_pembeli'");
if (mysqli_num_rows($query) == 0) {
    header("Location: pesanan_saya.php?error=not_found");
    exit;
}

$pesanan = mysqli_fetch_assoc($query);

// Hanya bisa dibatalkan jika status pesanan masih “Menunggu” atau “Diproses”
if (in_array($pesanan['status_pesanan'], ['Menunggu', 'Diproses'])) {

    // Ubah status pesanan menjadi "Dibatalkan"
    $update = mysqli_query($conn, "UPDATE pesanan SET status_pesanan='Dibatalkan' WHERE id_pesanan='$id_pesanan'");

    if ($update) {
        header("Location: pesanan_saya.php?success=Pesanan berhasil dibatalkan");
        exit;
    } else {
        header("Location: pesanan_saya.php?error=gagal_batal");
        exit;
    }

} else {
    // Jika status sudah selesai atau dikirim, tidak bisa dibatalkan
    header("Location: pesanan_saya.php?error=tidak_dapat_dibatalkan");
    exit;
}
?>
