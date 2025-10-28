<?php
include '../config/database.php';
session_start();

// Pastikan pembeli sudah login
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: ../auth/login.php");
    exit;
}

$id_pembeli = $_SESSION['id_user'];

// Ambil informasi pembeli
$query_pembeli = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_pembeli'");
$pembeli = mysqli_fetch_assoc($query_pembeli);

// Ambil data pesanan pembeli dari database (join dengan tabel menu dan penjual)
$query_pesanan = mysqli_query($conn, "
    SELECT p.*, m.nama_menu, m.foto, m.harga, pen.nama_penjual
    FROM pesanan p
    JOIN menu m ON p.id_menu = m.id_menu
    JOIN penjual pen ON m.id_penjual = pen.id_penjual
    WHERE p.id_pembeli = '$id_pembeli'
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

// Hitung pesanan berdasarkan status
$stats_query = mysqli_query($conn, "
    SELECT 
        status_pesanan,
        COUNT(*) as total
    FROM pesanan 
    WHERE id_pembeli = '$id_pembeli'
    GROUP BY status_pesanan
");

$stats = [];
while ($row = mysqli_fetch_assoc($stats_query)) {
    $stats[$row['status_pesanan']] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan Saya - FoodOrder</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

  <!-- Header -->
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center py-4">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-gradient-to-r from-gray-700 to-gray-900 rounded-lg flex items-center justify-center">
            <i class="fas fa-utensils text-white"></i>
          </div>
          <h1 class="text-2xl font-bold text-gray-800">Food<span class="text-gray-600">Order</span></h1>
        </div>
        
        <div class="flex items-center space-x-6">
          <a href="index.php" class="text-gray-700 hover:text-gray-900 transition flex items-center space-x-2">
            <i class="fas fa-home"></i>
            <span>Beranda</span>
          </a>
          <a href="pesanan_saya.php" class="text-gray-900 font-semibold flex items-center space-x-2">
            <i class="fas fa-shopping-cart"></i>
            <span>Pesanan Saya</span>
          </a>
          
          <div class="flex items-center space-x-3">
            <div class="text-right">
              <p class="text-sm text-gray-600">Halo,</p>
              <p class="font-semibold text-gray-800"><?= htmlspecialchars($pembeli['nama']); ?></p>
            </div>
            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
              <i class="fas fa-user text-gray-600"></i>
            </div>
          </div>
          
          <a href="../auth/logout.php" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition flex items-center space-x-2">
            <i class="fas fa-sign-out-alt"></i>
            <span>Keluar</span>
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="flex-grow container mx-auto px-4 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-2">Pesanan Saya</h1>
      <p class="text-gray-600">Kelola dan lacak semua pesanan Anda di satu tempat</p>
    </div>

    <!-- Notifikasi -->
    <?php if (isset($_GET['success'])): ?>
      <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center space-x-2">
        <i class="fas fa-check-circle"></i>
        <span><?= htmlspecialchars($_GET['success']); ?></span>
      </div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center space-x-2">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= htmlspecialchars($_GET['error']); ?></span>
      </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
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

    <!-- Daftar Pesanan -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800">Riwayat Pesanan</h2>
      </div>
      
      <div class="p-6">
        <?php if (mysqli_num_rows($query_pesanan) == 0): ?>
          <div class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-shopping-cart text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum ada pesanan</h3>
            <p class="text-gray-500 mb-6">Mulai pesan makanan favorit Anda dari beranda</p>
            <a href="index.php" class="bg-gray-800 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition inline-flex items-center space-x-2">
              <i class="fas fa-utensils"></i>
              <span>Jelajahi Menu</span>
            </a>
          </div>
        <?php else: ?>
          <div class="space-y-6">
            <?php while ($pesanan = mysqli_fetch_assoc($query_pesanan)): ?>
              <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                  <!-- Info Menu -->
                  <div class="flex items-start space-x-4 flex-1">
                    <?php if ($pesanan['foto']): ?>
                      <img src="../uploads/<?= htmlspecialchars($pesanan['foto']); ?>" 
                           alt="<?= htmlspecialchars($pesanan['nama_menu']); ?>" 
                           class="w-20 h-20 rounded-xl object-cover">
                    <?php else: ?>
                      <div class="w-20 h-20 bg-gray-200 rounded-xl flex items-center justify-center">
                        <i class="fas fa-utensils text-gray-400"></i>
                      </div>
                    <?php endif; ?>
                    
                    <div class="flex-1">
                      <h3 class="text-lg font-semibold text-gray-800 mb-1">
                        <?= htmlspecialchars($pesanan['nama_menu']); ?>
                      </h3>
                      <p class="text-gray-600 text-sm mb-2">
                        <i class="fas fa-store text-xs"></i>
                        <?= htmlspecialchars($pesanan['nama_penjual']); ?>
                      </p>
                      <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                        <span>Jumlah: <?= $pesanan['jumlah']; ?> item</span>
                        <span>Harga: Rp <?= number_format($pesanan['harga'], 0, ',', '.'); ?></span>
                        <span>Total: Rp <?= number_format($pesanan['total_harga'], 0, ',', '.'); ?></span>
                      </div>
                      <p class="text-xs text-gray-400 mt-2">
                        <i class="fas fa-clock"></i>
                        Dipesan pada <?= date('d M Y, H:i', strtotime($pesanan['tanggal_pesan'])); ?>
                      </p>
                    </div>
                  </div>

                  <!-- Status & Aksi -->
                  <div class="flex flex-col items-end space-y-3">
                    <?php
                    $statusColors = [
                      'Menunggu' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                      'Diproses' => 'bg-blue-100 text-blue-800 border-blue-200',
                      'Dikirim' => 'bg-orange-100 text-orange-800 border-orange-200',
                      'Selesai' => 'bg-green-100 text-green-800 border-green-200',
                      'Dibatalkan' => 'bg-red-100 text-red-800 border-red-200'
                    ];
                    $colorClass = $statusColors[$pesanan['status_pesanan']] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                    ?>
                    <span class="px-4 py-2 rounded-full text-sm font-medium border <?= $colorClass; ?>">
                      <?= $pesanan['status_pesanan']; ?>
                    </span>
                    
                    <?php if (in_array($pesanan['status_pesanan'], ['Menunggu', 'Diproses'])): ?>
                      <a href="batal_pesanan.php?id=<?= $pesanan['id_pesanan']; ?>"
                         onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');"
                         class="text-red-600 hover:text-red-700 font-medium text-sm flex items-center space-x-1">
                         <i class="fas fa-times"></i>
                         <span>Batalkan Pesanan</span>
                      </a>
                    <?php elseif ($pesanan['status_pesanan'] === 'Dikirim'): ?>
                      <a href="selesai_pesanan.php?id=<?= $pesanan['id_pesanan']; ?>"
                         onclick="return confirm('Konfirmasi pesanan sudah diterima?');"
                         class="text-green-600 hover:text-green-700 font-medium text-sm flex items-center space-x-1">
                         <i class="fas fa-check"></i>
                         <span>Konfirmasi Selesai</span>
                      </a>
                    <?php else: ?>
                      <span class="text-gray-400 text-sm">Tidak ada aksi</span>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Alamat Pengiriman -->
                <?php if ($pesanan['alamat_pengiriman']): ?>
                  <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-600">
                      <i class="fas fa-map-marker-alt text-gray-400"></i>
                      <span class="font-medium">Alamat pengiriman:</span> 
                      <?= htmlspecialchars($pesanan['alamat_pengiriman']); ?>
                    </p>
                  </div>
                <?php endif; ?>
              </div>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white py-6 mt-12">
    <div class="container mx-auto px-4 text-center">
      <p class="text-gray-400 text-sm">
        &copy; <?= date("Y"); ?> FoodOrder. All rights reserved.
      </p>
    </div>
  </footer>

</body>
</html>