<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../auth/login.php");
    exit;
}

$id_penjual = $_SESSION['id_user'];

$query_penjual = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_penjual'");
$penjual = mysqli_fetch_assoc($query_penjual);

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$sort_order = isset($_GET['sort']) && $_GET['sort'] === 'asc' ? 'ASC' : 'DESC';

if ($start_date > $end_date) {
    $start_date = $end_date;
}

$stats_query = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_pesanan,
        SUM(CASE WHEN p.status_pesanan = 'Selesai' THEN p.total_harga ELSE 0 END) as total_pendapatan,
        SUM(CASE WHEN p.status_pesanan = 'Selesai' THEN 1 ELSE 0 END) as pesanan_selesai,
        AVG(CASE WHEN p.status_pesanan = 'Selesai' THEN p.total_harga ELSE NULL END) as rata_rata_transaksi,
        MAX(CASE WHEN p.status_pesanan = 'Selesai' THEN p.total_harga ELSE 0 END) as transaksi_tertinggi,
        MIN(CASE WHEN p.status_pesanan = 'Selesai' THEN p.total_harga ELSE NULL END) as transaksi_terendah
    FROM pesanan p
    JOIN menu m ON p.id_menu = m.id_menu
    WHERE m.id_penjual = '$id_penjual'
    AND DATE(p.tanggal_pesan) BETWEEN '$start_date' AND '$end_date'
");

$stats = mysqli_fetch_assoc($stats_query);

$status_stats_query = mysqli_query($conn, "
    SELECT 
        status_pesanan,
        COUNT(*) as total,
        SUM(total_harga) as total_harga
    FROM pesanan p
    JOIN menu m ON p.id_menu = m.id_menu
    WHERE m.id_penjual = '$id_penjual'
    AND DATE(p.tanggal_pesan) BETWEEN '$start_date' AND '$end_date'
    GROUP BY status_pesanan
    ORDER BY 
        CASE 
            WHEN status_pesanan = 'Menunggu' THEN 1
            WHEN status_pesanan = 'Diproses' THEN 2
            WHEN status_pesanan = 'Dikirim' THEN 3
            WHEN status_pesanan = 'Selesai' THEN 4
            ELSE 5
        END
");

$status_stats = [];
while ($row = mysqli_fetch_assoc($status_stats_query)) {
    $status_stats[$row['status_pesanan']] = $row;
}

$popular_menu_query = mysqli_query($conn, "
    SELECT 
        m.nama_menu,
        m.harga,
        COUNT(p.id_pesanan) as total_terjual,
        SUM(p.jumlah) as total_item,
        SUM(p.total_harga) as total_pendapatan,
        AVG(p.total_harga) as rata_rata_per_transaksi,
        MAX(p.total_harga) as transaksi_tertinggi,
        MIN(p.total_harga) as transaksi_terendah
    FROM pesanan p
    JOIN menu m ON p.id_menu = m.id_menu
    WHERE m.id_penjual = '$id_penjual'
    AND DATE(p.tanggal_pesan) BETWEEN '$start_date' AND '$end_date'
    AND p.status_pesanan = 'Selesai'
    GROUP BY m.id_menu, m.nama_menu, m.harga
    ORDER BY total_terjual DESC, total_pendapatan DESC
    LIMIT 10
");

$daily_revenue_query = mysqli_query($conn, "
    SELECT 
        DATE(tanggal_pesan) as tanggal,
        SUM(total_harga) as pendapatan,
        COUNT(*) as total_pesanan,
        AVG(total_harga) as rata_rata_transaksi,
        MAX(total_harga) as transaksi_tertinggi,
        MIN(total_harga) as transaksi_terendah
    FROM pesanan p
    JOIN menu m ON p.id_menu = m.id_menu
    WHERE m.id_penjual = '$id_penjual'
    AND p.status_pesanan = 'Selesai'
    AND DATE(p.tanggal_pesan) BETWEEN DATE_SUB('$end_date', INTERVAL 29 DAY) AND '$end_date'
    GROUP BY DATE(tanggal_pesan)
    ORDER BY tanggal ASC
");

$daily_revenue = [];
while ($row = mysqli_fetch_assoc($daily_revenue_query)) {
    $daily_revenue[$row['tanggal']] = $row;
}

$recent_orders_query = mysqli_query($conn, "
    SELECT 
        p.id_pesanan,
        u.nama as nama_pembeli,
        m.nama_menu,
        p.jumlah,
        p.total_harga,
        p.status_pesanan,
        p.tanggal_pesan
    FROM pesanan p
    JOIN users u ON p.id_pembeli = u.id_user
    JOIN menu m ON p.id_menu = m.id_menu
    WHERE m.id_penjual = '$id_penjual'
    AND DATE(p.tanggal_pesan) BETWEEN '$start_date' AND '$end_date'
    ORDER BY p.tanggal_pesan DESC
    LIMIT 10
");

$best_customer_query = mysqli_query($conn, "
    SELECT 
        u.nama as nama_pembeli,
        COUNT(p.id_pesanan) as total_pesanan,
        SUM(p.total_harga) as total_pengeluaran,
        AVG(p.total_harga) as rata_rata_transaksi,
        MAX(p.total_harga) as transaksi_tertinggi
    FROM pesanan p
    JOIN users u ON p.id_pembeli = u.id_user
    JOIN menu m ON p.id_menu = m.id_menu
    WHERE m.id_penjual = '$id_penjual'
    AND p.status_pesanan = 'Selesai'
    AND DATE(p.tanggal_pesan) BETWEEN '$start_date' AND '$end_date'
    GROUP BY u.id_user, u.nama
    ORDER BY total_pengeluaran $sort_order
    LIMIT 5
");

// Query untuk performa waktu (jam sibuk)
$peak_hours_query = mysqli_query($conn, "
    SELECT 
        HOUR(tanggal_pesan) as jam,
        COUNT(*) as total_pesanan,
        SUM(total_harga) as total_pendapatan
    FROM pesanan p
    JOIN menu m ON p.id_menu = m.id_menu
    WHERE m.id_penjual = '$id_penjual'
    AND p.status_pesanan = 'Selesai'
    AND DATE(p.tanggal_pesan) BETWEEN '$start_date' AND '$end_date'
    GROUP BY HOUR(tanggal_pesan)
    ORDER BY total_pesanan DESC
    LIMIT 6
");

$chart_labels = [];
$chart_revenue = [];
$chart_orders = [];

for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days", strtotime($end_date)));
    $chart_labels[] = date('d M', strtotime($date));
    $chart_revenue[] = isset($daily_revenue[$date]) ? (float)$daily_revenue[$date]['pendapatan'] : 0;
    $chart_orders[] = isset($daily_revenue[$date]) ? (int)$daily_revenue[$date]['total_pesanan'] : 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Analitik - FoodOrder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <div class="w-64 bg-gray-800 text-white">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-r from-gray-700 to-gray-900 rounded-lg flex items-center justify-center">
                        <i class="fas fa-store text-white"></i>
                    </div>
                    <h1 class="text-xl font-bold">Food<span class="text-gray-300">Order</span></h1>
                </div>
                
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
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
                    <a href="laporan.php" class="flex items-center space-x-3 bg-gray-700 px-4 py-3 rounded-lg text-white">
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

        <div class="flex-1 overflow-auto">
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex justify-between items-center px-8 py-4">
                    <h1 class="text-2xl font-bold text-gray-800">Laporan & Analitik</h1>
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

            <div class="p-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Filter Laporan</h2>
                        <form method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                                <input type="date" name="start_date" value="<?= $start_date ?>" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-800 focus:border-gray-800">
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                                <input type="date" name="end_date" value="<?= $end_date ?>" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-800 focus:border-gray-800">
                            </div>
                            <div class="flex gap-2">
                                <input type="hidden" name="sort" value="<?= $sort_order === 'ASC' ? 'asc' : 'desc'; ?>">
                                <button type="submit" name="sort" value="asc" 
                                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition flex items-center space-x-2 <?= $sort_order === 'ASC' ? 'bg-gray-800 text-white' : ''; ?>">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>ASC</span>
                                </button>
                                <button type="submit" name="sort" value="desc" 
                                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition flex items-center space-x-2 <?= $sort_order === 'DESC' ? 'bg-gray-800 text-white' : ''; ?>">
                                    <i class="fas fa-arrow-down"></i>
                                    <span>DESC</span>
                                </button>
                            </div>
                            <div>
                                <button type="submit" 
                                        class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition flex items-center space-x-2">
                                    <i class="fas fa-filter"></i>
                                    <span>Filter</span>
                                </button>
                            </div>
                            <div>
                                <a href="laporan.php" 
                                   class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition flex items-center space-x-2">
                                    <i class="fas fa-refresh"></i>
                                    <span>Reset</span>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Pesanan</p>
                                <p class="text-2xl font-bold text-gray-800 mt-2"><?= $stats['total_pesanan'] ?? 0; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Pendapatan</p>
                                <p class="text-2xl font-bold text-green-600 mt-2">Rp <?= number_format($stats['total_pendapatan'] ?? 0, 0, ',', '.'); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-wallet text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Transaksi Tertinggi</p>
                                <p class="text-2xl font-bold text-purple-600 mt-2">Rp <?= number_format($stats['transaksi_tertinggi'] ?? 0, 0, ',', '.'); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Rata-rata Transaksi</p>
                                <p class="text-2xl font-bold text-orange-600 mt-2">Rp <?= number_format($stats['rata_rata_transaksi'] ?? 0, 0, ',', '.'); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calculator text-orange-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Pendapatan 30 Hari Terakhir</h3>
                        <div class="h-80">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Status Pesanan</h3>
                        <div class="space-y-4">
                            <?php foreach ($status_stats as $status => $data): ?>
                                <?php
                                $statusColors = [
                                    'Menunggu' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'Diproses' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'Dikirim' => 'bg-orange-100 text-orange-800 border-orange-200',
                                    'Selesai' => 'bg-green-100 text-green-800 border-green-200',
                                    'Dibatalkan' => 'bg-red-100 text-red-800 border-red-200'
                                ];
                                $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                ?>
                                <div class="flex justify-between items-center p-3 border rounded-lg <?= $colorClass; ?>">
                                    <div class="flex items-center space-x-3">
                                        <span class="font-medium"><?= $status; ?></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold"><?= $data['total']; ?> pesanan</span>
                                        <p class="text-sm">Rp <?= number_format($data['total_harga'], 0, ',', '.'); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($status_stats)): ?>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-chart-pie text-4xl mb-2 opacity-50"></i>
                                    <p>Tidak ada data pesanan</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Pelanggan Terbaik</h3>
                        </div>
                        <div class="p-6">
                            <?php if (mysqli_num_rows($best_customer_query) > 0): ?>
                                <div class="space-y-4">
                                    <?php while ($customer = mysqli_fetch_assoc($best_customer_query)): ?>
                                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-10 h-10 bg-gray-800 text-white rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($customer['nama_pembeli']); ?></p>
                                                    <p class="text-sm text-gray-500"><?= $customer['total_pesanan']; ?> pesanan</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-green-600">Rp <?= number_format($customer['total_pengeluaran'], 0, ',', '.'); ?></p>
                                                <p class="text-sm text-gray-500">Rata-rata: Rp <?= number_format($customer['rata_rata_transaksi'], 0, ',', '.'); ?></p>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-users text-4xl mb-2 opacity-50"></i>
                                    <p>Belum ada data pelanggan</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Jam Sibuk</h3>
                        </div>
                        <div class="p-6">
                            <?php if (mysqli_num_rows($peak_hours_query) > 0): ?>
                                <div class="space-y-4">
                                    <?php while ($hour = mysqli_fetch_assoc($peak_hours_query)): ?>
                                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-800"><?= $hour['jam']; ?>:00 - <?= $hour['jam'] + 1; ?>:00</p>
                                                    <p class="text-sm text-gray-500"><?= $hour['total_pesanan']; ?> pesanan</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-green-600">Rp <?= number_format($hour['total_pendapatan'], 0, ',', '.'); ?></p>
                                                <p class="text-sm text-gray-500">Pendapatan</p>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-clock text-4xl mb-2 opacity-50"></i>
                                    <p>Belum ada data jam sibuk</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Menu Terpopuler</h3>
                        </div>
                        <div class="p-6">
                            <?php if (mysqli_num_rows($popular_menu_query) > 0): ?>
                                <div class="space-y-4">
                                    <?php $rank = 1; ?>
                                    <?php while ($menu = mysqli_fetch_assoc($popular_menu_query)): ?>
                                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-8 h-8 bg-gray-800 text-white rounded-full flex items-center justify-center text-sm font-bold">
                                                    <?= $rank++; ?>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($menu['nama_menu']); ?></p>
                                                    <p class="text-sm text-gray-500">Rp <?= number_format($menu['harga'], 0, ',', '.'); ?></p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-gray-800"><?= $menu['total_terjual']; ?>x terjual</p>
                                                <p class="text-sm text-green-600">Rp <?= number_format($menu['total_pendapatan'], 0, ',', '.'); ?></p>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-utensils text-4xl mb-2 opacity-50"></i>
                                    <p>Belum ada data penjualan menu</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Pesanan Terbaru</h3>
                        </div>
                        <div class="p-6">
                            <?php if (mysqli_num_rows($recent_orders_query) > 0): ?>
                                <div class="space-y-3">
                                    <?php while ($order = mysqli_fetch_assoc($recent_orders_query)): ?>
                                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                            <div>
                                                <p class="font-medium text-gray-800"><?= htmlspecialchars($order['nama_menu']); ?></p>
                                                <p class="text-sm text-gray-500">Oleh: <?= htmlspecialchars($order['nama_pembeli']); ?></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-gray-800">Rp <?= number_format($order['total_harga'], 0, ',', '.'); ?></p>
                                                <span class="text-xs px-2 py-1 rounded-full 
                                                    <?= $order['status_pesanan'] === 'Selesai' ? 'bg-green-100 text-green-800' : 
                                                       ($order['status_pesanan'] === 'Diproses' ? 'bg-blue-100 text-blue-800' : 
                                                       ($order['status_pesanan'] === 'Dikirim' ? 'bg-orange-100 text-orange-800' : 
                                                       'bg-yellow-100 text-yellow-800')); ?>">
                                                    <?= $order['status_pesanan']; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-shopping-cart text-4xl mb-2 opacity-50"></i>
                                    <p>Belum ada pesanan</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels); ?>,
                datasets: [
                    {
                        label: 'Pendapatan (Rp)',
                        data: <?= json_encode($chart_revenue); ?>,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Jumlah Pesanan',
                        data: <?= json_encode($chart_orders); ?>,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Pendapatan (Rp)'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Jumlah Pesanan'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });
    </script>
</body>
</html>