<?php
$conn = mysqli_connect("localhost", "root", "", "pemesanan_makanan");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
