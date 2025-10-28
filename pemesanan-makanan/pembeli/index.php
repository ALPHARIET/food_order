<?php
include '../config/database.php';
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: ../auth/login.php");
    exit;
}

$id_pembeli = $_SESSION['id_user'];
$query_pembeli = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_pembeli'");
$pembeli = mysqli_fetch_assoc($query_pembeli);

$sort = isset($_GET['sort']) && strtolower($_GET['sort']) === 'asc' ? 'ASC' : 'DESC';
$base = htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?'));

$query_menu = mysqli_query($conn, "
    SELECT m.*, p.nama_penjual, COUNT(ps.id_pesanan) AS total_pesanan
    FROM menu m
    JOIN penjual p ON m.id_penjual = p.id_penjual
    LEFT JOIN pesanan ps ON ps.id_menu = m.id_menu
    GROUP BY m.id_menu
    ORDER BY m.harga $sort
");

$pesanan_aktif = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM pesanan 
    WHERE id_pembeli = '$id_pembeli' 
    AND status_pesanan IN ('Menunggu', 'Diproses', 'Dikirim')
")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beranda - FoodOrder</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
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
          <a href="pesanan_saya.php" class="relative flex items-center space-x-2 text-gray-700 hover:text-gray-900 transition">
            <i class="fas fa-shopping-cart"></i>
            <span>Pesanan Saya</span>
            <?php if ($pesanan_aktif > 0): ?>
              <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                <?= $pesanan_aktif; ?>
              </span>
            <?php endif; ?>
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

  <section class="bg-gradient-to-r from-gray-800 to-gray-900 text-white">
    <div class="container mx-auto px-4 py-16 text-center">
      <h2 class="text-4xl font-bold mb-4">Selamat Datang di FoodOrder</h2>
      <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
        Temukan berbagai pilihan makanan lezat dari penjual terpercaya. 
        Pesan sekarang dan nikmati pengalaman kuliner terbaik.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="#menu" class="bg-white text-gray-800 px-8 py-3 rounded-xl font-semibold hover:bg-gray-100 transition flex items-center justify-center space-x-2">
          <i class="fas fa-utensils"></i>
          <span>Jelajahi Menu</span>
        </a>
        <a href="pesanan_saya.php" class="border border-white text-white px-8 py-3 rounded-xl font-semibold hover:bg-white hover:text-gray-800 transition flex items-center justify-center space-x-2">
          <i class="fas fa-shopping-cart"></i>
          <span>Lihat Pesanan</span>
        </a>
      </div>
    </div>
  </section>

  <main class="flex-grow container mx-auto px-4 py-12">
    <?php if (isset($_GET['success'])): ?>
      <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-8 flex items-center space-x-2">
        <i class="fas fa-check-circle"></i>
        <span><?= htmlspecialchars($_GET['success']); ?></span>
      </div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-8 flex items-center space-x-2">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= htmlspecialchars($_GET['error']); ?></span>
      </div>
    <?php endif; ?>

    <section id="menu" class="mb-12">
      <div class="text-center mb-10">
        <h2 class="text-3xl font-bold text-gray-800 mb-4">🍽️ Menu Terbaru</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
          Jelajahi berbagai pilihan makanan lezat dari penjual terpercaya. 
          Setiap hidangan dibuat dengan bahan berkualitas dan penuh cita rasa.
        </p>
        <div class="flex justify-center gap-4 mt-6">
          <a href="<?= $base; ?>?sort=asc" class="px-6 py-2 rounded-lg font-medium <?= $sort === 'ASC' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-800'; ?> hover:bg-gray-700 transition">TERMURAH</a>
          <a href="<?= $base; ?>?sort=desc" class="px-6 py-2 rounded-lg font-medium <?= $sort === 'DESC' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-800'; ?> hover:bg-gray-700 transition">TERMAHAL</a>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php if (mysqli_num_rows($query_menu) > 0): ?>
          <?php while ($menu = mysqli_fetch_assoc($query_menu)): ?>
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100">
              <div class="relative">
                <?php if ($menu['foto']): ?>
                  <img src="../uploads/<?= htmlspecialchars($menu['foto']); ?>" alt="<?= htmlspecialchars($menu['nama_menu']); ?>" class="w-full h-48 object-cover">
                <?php else: ?>
                  <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-utensils text-gray-400 text-4xl"></i>
                  </div>
                <?php endif; ?>
                <div class="absolute top-3 right-3">
                  <span class="bg-gray-800 text-white px-3 py-1 rounded-full text-sm font-medium">
                    Rp <?= number_format($menu['harga'], 0, ',', '.'); ?>
                  </span>
                </div>
              </div>
              <div class="p-6">
                <div class="flex justify-between items-start mb-3">
                  <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($menu['nama_menu']); ?></h3>
                </div>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($menu['deskripsi']); ?></p>
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                      <i class="fas fa-store text-xs text-gray-600"></i>
                    </div>
                    <span class="text-sm text-gray-600"><?= htmlspecialchars($menu['nama_penjual']); ?></span>
                  </div>
                </div>
                <a href="detail_menu.php?id=<?= $menu['id_menu']; ?>" class="w-full bg-gray-800 text-white text-center rounded-xl py-3 font-medium hover:bg-gray-700 transition duration-300 flex items-center justify-center space-x-2">
                  <i class="fas fa-eye"></i>
                  <span>Lihat Detail</span>
                </a>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="col-span-full text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-utensils text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum ada menu tersedia</h3>
            <p class="text-gray-500">Silakan kembali lagi nanti untuk melihat menu terbaru.</p>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
        <div class="p-6">
          <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-shipping-fast text-2xl text-blue-600"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Pengiriman Cepat</h3>
          <p class="text-gray-600">Pesanan Anda akan dikirim dengan cepat dan aman sampai ke tangan Anda.</p>
        </div>
        <div class="p-6">
          <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-shield-alt text-2xl text-green-600"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Terjamin Aman</h3>
          <p class="text-gray-600">Semua penjual telah melalui proses verifikasi untuk menjamin kualitas.</p>
        </div>
        <div class="p-6">
          <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-headset text-2xl text-purple-600"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Bantuan 24/7</h3>
          <p class="text-gray-600">Tim support kami siap membantu Anda kapan saja melalui live chat.</p>
        </div>
      </div>
    </section>
  </main>

  <footer class="bg-gray-800 text-white pt-12 pb-6">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <div>
          <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
              <i class="fas fa-utensils text-gray-800"></i>
            </div>
            <h3 class="text-xl font-bold">FoodOrder</h3>
          </div>
          <p class="text-gray-400 text-sm">Platform pemesanan makanan online terpercaya dengan berbagai pilihan menu terbaik dari penjual berkualitas.</p>
        </div>
        <div>
          <h4 class="font-semibold mb-4">Tautan Cepat</h4>
          <ul class="space-y-2 text-gray-400">
            <li><a href="index.php" class="hover:text-white transition">Beranda</a></li>
            <li><a href="pesanan_saya.php" class="hover:text-white transition">Pesanan Saya</a></li>
            <li><a href="#" class="hover:text-white transition">Cara Pesan</a></li>
            <li><a href="#" class="hover:text-white transition">Bantuan</a></li>
          </ul>
        </div>
        <div>
          <h4 class="font-semibold mb-4">Kontak</h4>
          <ul class="space-y-2 text-gray-400">
            <li class="flex items-center space-x-2"><i class="fas fa-envelope"></i><span>support@foodorder.com</span></li>
            <li class="flex items-center space-x-2"><i class="fas fa-phone"></i><span>+62 21 1234 5678</span></li>
            <li class="flex items-center space-x-2"><i class="fas fa-map-marker-alt"></i><span>Jakarta, Indonesia</span></li>
          </ul>
        </div>
        <div>
          <h4 class="font-semibold mb-4">Ikuti Kami</h4>
          <div class="flex space-x-4">
            <a href="#" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-gray-600 transition"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-gray-600 transition"><i class="fab fa-instagram"></i></a>
            <a href="#" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-gray-600 transition"><i class="fab fa-twitter"></i></a>
          </div>
        </div>
      </div>
      <div class="border-t border-gray-700 pt-6 text-center">
        <p class="text-gray-400 text-sm">&copy; <?= date("Y"); ?> FoodOrder. All rights reserved.</p>
      </div>
    </div>
  </footer>
</body>
</html>
