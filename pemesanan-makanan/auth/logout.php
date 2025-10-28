<?php
session_start();

// Hapus semua variabel sesi
session_unset();

// Hapus sesi pengguna
session_destroy();

// Arahkan ke halaman login
header("Location: login.php");
exit;
?>
