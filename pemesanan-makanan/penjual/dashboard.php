<?php
session_start();
include '../config/database.php';

// Pastikan hanya penjual yang bisa mengakses
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../auth/login.php");
    exit;
}

$id_penjual = $_SESSION['id_user'];

// Ambil informasi penjual dari tabel users
$query_penjual = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_penjual'");
$penjual = mysqli_fetch_assoc($query_penjual);

// Ambil statistik dashboard
$total_pesanan = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM pesanan p 
    JOIN menu m ON p.id_menu = m.id_menu 
    WHERE m.id_penjual = '$id_penjual'
")->fetch_assoc()['total'];

$pesanan_baru = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM pesanan p 
    JOIN menu m ON p.id_menu = m.id_menu 
    WHERE m.id_penjual = '$id_penjual' AND p.status_pesanan = 'Menunggu'
")->fetch_assoc()['total'];

$total_pendapatan = mysqli_query($conn, "
    SELECT COALESCE(SUM(p.total_harga), 0) as total 
    FROM pesanan p 
    JOIN menu m ON p.id_menu = m.id_menu 
    WHERE m.id_penjual = '$id_penjual' AND p.status_pesanan = 'Selesai'
")->fetch_assoc()['total'];

// Ambil daftar pesanan yang berkaitan dengan menu milik penjual
$query_pesanan = mysqli_query($conn, "
    SELECT 
        p.id_pesanan, 
        u.nama AS nama_pembeli, 
        m.nama_menu, 
        p.jumlah, 
        p.total_harga, 
        p.status_pesanan, 
        p.tanggal_pesan
    FROM pesanan p
    JOIN users u ON p.id_pembeli = u.id_user
    JOIN menu m ON p.id_menu = m.id_menu
    WHERE m.id_penjual = '$id_penjual'
    ORDER BY p.tanggal_pesan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual - FoodOrder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-r from-gray-700 to-gray-900 rounded-lg flex items-center justify-center">
                        <i class="fas fa-store text-white"></i>
                    </div>
                    <h1 class="text-xl font-bold">Food<span class="text-gray-300">Order</span></h1>
                </div>
                
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center space-x-3 bg-gray-700 px-4 py-3 rounded-lg text-white">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="menu.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                        <i class="fas fa-utensils"></i>
                        <span>Kelola Menu</span>
                    </a>
                    <a href="pesanan.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Pesanan</span>
                    </a>
                    <a href="laporan.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                        <i class="fas fa-chart-bar"></i>
                        <span>Laporan</span>
                    </a>
                    <a href="pengaturan.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                        <i class="fas fa-cog"></i>
                        <span>Pengaturan</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex justify-between items-center px-8 py-4">
                    <h1 class="text-2xl font-bold text-gray-800">Dashboard Penjual</h1>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Halo,</p>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($penjual['nama']); ?></p>
                        </div>
                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-gray-600"></i>
                        </div>
                        <a href="../auth/logout.php" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition flex items-center space-x-2">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Keluar</span>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Stats Cards -->
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Pesanan</p>
                                <p class="text-3xl font-bold text-gray-800 mt-2"><?= $total_pesanan; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Pesanan Baru</p>
                                <p class="text-3xl font-bold text-gray-800 mt-2"><?= $pesanan_baru; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-orange-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Pendapatan</p>
                                <p class="text-3xl font-bold text-gray-800 mt-2">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-wallet text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Pesanan Terbaru</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700">Pesanan</th>
                                    <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700">Pembeli</th>
                                    <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700">Jumlah</th>
                                    <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700">Total</th>
                                    <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700">Status</th>
                                    <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (mysqli_num_rows($query_pesanan) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($query_pesanan)): ?>
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="py-4 px-6">
                                                <div>
                                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($row['nama_menu']); ?></p>
                                                    <p class="text-sm text-gray-500">#<?= $row['id_pesanan']; ?></p>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <p class="text-gray-800"><?= htmlspecialchars($row['nama_pembeli']); ?></p>
                                            </td>
                                            <td class="py-4 px-6">
                                                <p class="text-gray-800"><?= $row['jumlah']; ?> item</p>
                                            </td>
                                            <td class="py-4 px-6">
                                                <p class="font-semibold text-gray-800">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></p>
                                            </td>
                                            <td class="py-4 px-6">
                                                <?php
                                                $statusColors = [
                                                    'Menunggu' => 'bg-yellow-100 text-yellow-800',
                                                    'Diproses' => 'bg-blue-100 text-blue-800',
                                                    'Dikirim' => 'bg-orange-100 text-orange-800',
                                                    'Selesai' => 'bg-green-100 text-green-800',
                                                    'Dibatalkan' => 'bg-red-100 text-red-800'
                                                ];
                                                ?>
                                                <span class="px-3 py-1 rounded-full text-xs font-medium <?= $statusColors[$row['status_pesanan']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                                    <?= $row['status_pesanan']; ?>
                                                </span>
                                            </td>
                                            <td class="py-4 px-6">
                                                <p class="text-sm text-gray-600"><?= date('d M Y, H:i', strtotime($row['tanggal_pesan'])); ?></p>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="py-8 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <i class="fas fa-shopping-cart text-4xl mb-4 opacity-50"></i>
                                                <p class="text-lg">Belum ada pesanan</p>
                                                <p class="text-sm mt-2">Pesanan yang diterima akan muncul di sini</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>