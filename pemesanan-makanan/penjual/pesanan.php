<?php
session_start();
include '../config/database.php';

// Pastikan hanya penjual yang bisa mengakses
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../auth/login.php");
    exit;
}

$id_penjual = $_SESSION['id_user'];

// Ambil informasi penjual
$query_penjual = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_penjual'");
$penjual = mysqli_fetch_assoc($query_penjual);

// Proses update status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id_pesanan = mysqli_real_escape_string($conn, $_POST['id_pesanan']);
    $status_baru = mysqli_real_escape_string($conn, $_POST['status_pesanan']);
    
    // Validasi bahwa pesanan ini milik menu penjual
    $check_query = mysqli_query($conn, "
        SELECT p.* FROM pesanan p 
        JOIN menu m ON p.id_menu = m.id_menu 
        WHERE p.id_pesanan = '$id_pesanan' AND m.id_penjual = '$id_penjual'
    ");
    
    if (mysqli_num_rows($check_query) > 0) {
        $query = "UPDATE pesanan SET status_pesanan = '$status_baru' WHERE id_pesanan = '$id_pesanan'";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Status pesanan berhasil diupdate!";
        } else {
            $_SESSION['error'] = "Gagal mengupdate status pesanan: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Pesanan tidak ditemukan!";
    }
    
    header("Location: pesanan.php");
    exit;
}

// Filter berdasarkan status
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$where_clause = "m.id_penjual = '$id_penjual'";
if ($filter_status && $filter_status !== 'semua') {
    $where_clause .= " AND p.status_pesanan = '$filter_status'";
}

// Ambil data pesanan
$query_pesanan = mysqli_query($conn, "
    SELECT 
        p.id_pesanan, 
        u.nama AS nama_pembeli,
        u.no_telp AS telp_pembeli,
        m.nama_menu, 
        m.foto AS foto_menu,
        p.jumlah, 
        p.total_harga, 
        p.status_pesanan, 
        p.alamat_pengiriman,
        p.tanggal_pesan
    FROM pesanan p
    JOIN users u ON p.id_pembeli = u.id_user
    JOIN menu m ON p.id_menu = m.id_menu
    WHERE $where_clause
    ORDER BY 
        CASE 
            WHEN p.status_pesanan = 'Menunggu' THEN 1
            WHEN p.status_pesanan = 'Diproses' THEN 2
            WHEN p.status_pesanan = 'Dikirim' THEN 3
            WHEN p.status_pesanan = 'Selesai' THEN 4
            ELSE 5
        END,
        p.tanggal_pesan DESC
");

// Hitung statistik pesanan
$stats_query = mysqli_query($conn, "
    SELECT 
        status_pesanan,
        COUNT(*) as total,
        SUM(total_harga) as total_pendapatan
    FROM pesanan p
    JOIN menu m ON p.id_menu = m.id_menu
    WHERE m.id_penjual = '$id_penjual'
    GROUP BY status_pesanan
");

$stats = [];
$total_pendapatan = 0;
while ($row = mysqli_fetch_assoc($stats_query)) {
    $stats[$row['status_pesanan']] = $row['total'];
    if ($row['status_pesanan'] === 'Selesai') {
        $total_pendapatan = $row['total_pendapatan'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - FoodOrder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
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
                    <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="menu.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                        <i class="fas fa-utensils"></i>
                        <span>Kelola Menu</span>
                    </a>
                    <a href="pesanan.php" class="flex items-center space-x-3 bg-gray-700 px-4 py-3 rounded-lg text-white">
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
                    <h1 class="text-2xl font-bold text-gray-800">Kelola Pesanan</h1>
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

            <!-- Notifikasi -->
            <div class="p-8">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center space-x-2">
                        <i class="fas fa-check-circle"></i>
                        <span><?= $_SESSION['success']; ?></span>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center space-x-2">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= $_SESSION['error']; ?></span>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Pesanan</p>
                                <p class="text-2xl font-bold text-gray-800 mt-2"><?= mysqli_num_rows($query_pesanan); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Menunggu</p>
                                <p class="text-2xl font-bold text-yellow-600 mt-2"><?= $stats['Menunggu'] ?? 0; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Diproses</p>
                                <p class="text-2xl font-bold text-blue-600 mt-2"><?= $stats['Diproses'] ?? 0; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cog text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Selesai</p>
                                <p class="text-2xl font-bold text-green-600 mt-2"><?= $stats['Selesai'] ?? 0; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter dan Pencarian -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <h2 class="text-xl font-semibold text-gray-800">Daftar Pesanan</h2>
                            
                            <div class="flex flex-col sm:flex-row gap-4">
                                <select id="filterStatus" onchange="filterOrders()" 
                                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-800 focus:border-gray-800">
                                    <option value="semua" <?= $filter_status === '' || $filter_status === 'semua' ? 'selected' : ''; ?>>Semua Status</option>
                                    <option value="Menunggu" <?= $filter_status === 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                    <option value="Diproses" <?= $filter_status === 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                                    <option value="Dikirim" <?= $filter_status === 'Dikirim' ? 'selected' : ''; ?>>Dikirim</option>
                                    <option value="Selesai" <?= $filter_status === 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                                    <option value="Dibatalkan" <?= $filter_status === 'Dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Pesanan -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
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
                                    <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (mysqli_num_rows($query_pesanan) > 0): ?>
                                    <?php while ($pesanan = mysqli_fetch_assoc($query_pesanan)): ?>
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="py-4 px-6">
                                                <div class="flex items-center space-x-3">
                                                    <?php if ($pesanan['foto_menu']): ?>
                                                        <img src="../uploads/<?= htmlspecialchars($pesanan['foto_menu']); ?>" 
                                                             alt="<?= htmlspecialchars($pesanan['nama_menu']); ?>"
                                                             class="w-12 h-12 rounded-lg object-cover">
                                                    <?php else: ?>
                                                        <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                                            <i class="fas fa-utensils text-gray-400"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <p class="font-medium text-gray-800"><?= htmlspecialchars($pesanan['nama_menu']); ?></p>
                                                        <p class="text-sm text-gray-500">#<?= $pesanan['id_pesanan']; ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div>
                                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($pesanan['nama_pembeli']); ?></p>
                                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($pesanan['telp_pembeli']); ?></p>
                                                    <?php if ($pesanan['alamat_pengiriman']): ?>
                                                        <p class="text-xs text-gray-400 mt-1 max-w-xs truncate">
                                                            <i class="fas fa-map-marker-alt"></i>
                                                            <?= htmlspecialchars($pesanan['alamat_pengiriman']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <p class="text-gray-800 font-medium"><?= $pesanan['jumlah']; ?> item</p>
                                            </td>
                                            <td class="py-4 px-6">
                                                <p class="font-semibold text-gray-800">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.'); ?></p>
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
                                                <span class="px-3 py-1 rounded-full text-xs font-medium <?= $statusColors[$pesanan['status_pesanan']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                                    <?= $pesanan['status_pesanan']; ?>
                                                </span>
                                            </td>
                                            <td class="py-4 px-6">
                                                <p class="text-sm text-gray-600"><?= date('d M Y', strtotime($pesanan['tanggal_pesan'])); ?></p>
                                                <p class="text-xs text-gray-500"><?= date('H:i', strtotime($pesanan['tanggal_pesan'])); ?></p>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="flex space-x-2">
                                                    <button onclick="showUpdateModal(<?= $pesanan['id_pesanan']; ?>, '<?= $pesanan['status_pesanan']; ?>')"
                                                            class="bg-gray-800 text-white px-3 py-2 rounded-lg text-sm hover:bg-gray-700 transition flex items-center space-x-1">
                                                        <i class="fas fa-edit text-xs"></i>
                                                        <span>Update</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="py-12 text-center">
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

    <!-- Modal Update Status -->
    <div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Update Status Pesanan</h2>
            </div>
            <div class="p-6">
                <form id="updateForm" method="POST">
                    <input type="hidden" name="id_pesanan" id="update_id_pesanan">
                    <input type="hidden" name="update_status" value="1">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Pesanan</label>
                            <select name="status_pesanan" id="update_status_pesanan" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition">
                                <option value="Menunggu">Menunggu</option>
                                <option value="Diproses">Diproses</option>
                                <option value="Dikirim">Dikirim</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 mt-6">
                        <button type="button" onclick="closeUpdateModal()"
                                class="flex-1 bg-gray-300 text-gray-700 px-4 py-3 rounded-xl font-semibold hover:bg-gray-400 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 bg-gray-800 text-white px-4 py-3 rounded-xl font-semibold hover:bg-gray-700 transition">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function filterOrders() {
            const status = document.getElementById('filterStatus').value;
            const url = new URL(window.location.href);
            if (status === 'semua') {
                url.searchParams.delete('status');
            } else {
                url.searchParams.set('status', status);
            }
            window.location.href = url.toString();
        }

        function showUpdateModal(idPesanan, currentStatus) {
            document.getElementById('update_id_pesanan').value = idPesanan;
            document.getElementById('update_status_pesanan').value = currentStatus;
            document.getElementById('updateModal').classList.remove('hidden');
            document.getElementById('updateModal').classList.add('flex');
        }

        function closeUpdateModal() {
            document.getElementById('updateModal').classList.add('hidden');
            document.getElementById('updateModal').classList.remove('flex');
        }

        // Close modal when clicking outside
        document.getElementById('updateModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUpdateModal();
            }
        });
    </script>
</body>
</html>