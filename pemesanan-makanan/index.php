<?php
include 'config/database.php';
$sort = isset($_GET['sort']) && strtolower($_GET['sort']) === 'asc' ? 'asc' : 'desc';
$order = $sort === 'asc' ? 'ASC' : 'DESC';
$base = htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?'));
$query = mysqli_query($conn, "
  SELECT 
    menu.id_menu,
    menu.nama_menu,
    menu.deskripsi,
    menu.harga,
    menu.foto,
    penjual.nama_penjual,
    COUNT(pesanan.id_pesanan) AS total_pesanan,
    LEFT(menu.nama_menu, 10) AS potongan_nama,
    MID(menu.deskripsi, 1, 50) AS ringkasan
  FROM menu
  INNER JOIN penjual ON menu.id_penjual = penjual.id_penjual
  LEFT JOIN pesanan ON menu.id_menu = pesanan.id_menu
  GROUP BY menu.id_menu
  ORDER BY menu.harga $order
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FoodOrder - Pemesanan Makanan Premium</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .card-hover { transition: all 0.3s ease; }
    .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    .sort-btn { 
      transition: all 0.2s ease; 
      border: 1px solid #e5e7eb;
    }
    .sort-btn.active { 
      background-color: #1f2937; 
      color: white;
      border-color: #1f2937;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="container mx-auto flex justify-between items-center px-6 py-4">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-gradient-to-r from-gray-700 to-gray-900 rounded-lg flex items-center justify-center">
          <i class="fas fa-utensils text-white text-lg"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Food<span class="text-gray-600">Order</span></h1>
      </div>
      <nav class="flex space-x-4">
        <a href="auth/login.php" class="bg-gray-800 text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-700 transition duration-300 flex items-center space-x-2">
          <i class="fas fa-sign-in-alt"></i><span>Masuk</span>
        </a>
        <a href="auth/register.php" class="border border-gray-800 text-gray-800 px-6 py-2 rounded-lg font-medium hover:bg-gray-800 hover:text-white transition duration-300 flex items-center space-x-2">
          <i class="fas fa-user-plus"></i><span>Daftar</span>
        </a>
      </nav>
    </div>
  </header>

  <section class="bg-gradient-to-r from-gray-800 to-gray-900 text-white">
    <div class="container mx-auto px-6 py-16 text-center">
      <div class="max-w-3xl mx-auto">
        <h2 class="text-4xl md:text-5xl font-bold mb-6">Nikmati Pengalaman Kuliner Terbaik</h2>
        <p class="text-xl text-gray-100 mb-8 leading-relaxed">Temukan berbagai pilihan makanan berkualitas dari penjual terpercaya. Pesan dengan mudah, bayar dengan aman, dan nikmati di mana saja.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="#menu" class="bg-white text-gray-800 px-8 py-4 rounded-xl font-semibold hover:bg-gray-100 transition duration-300 flex items-center justify-center space-x-2">
            <i class="fas fa-utensils"></i><span>Jelajahi Menu</span>
          </a>
          <a href="#" class="border border-white text-white px-8 py-4 rounded-xl font-semibold hover:bg-white hover:text-gray-800 transition duration-300 flex items-center justify-center space-x-2">
            <i class="fas fa-store"></i><span>Lihat Penjual</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <main id="menu" class="flex-grow container mx-auto px-6 py-16">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold text-gray-800 mb-4">🍽️ Menu Pilihan Terbaik</h2>
      <p class="text-gray-600 max-w-2xl mx-auto mb-6">Temukan berbagai hidangan lezat yang siap memanjakan lidah Anda.</p>
      
      <!-- Tombol Sortir yang Disederhanakan -->
      <div class="flex justify-center gap-2">
        <a href="<?php echo $base; ?>?sort=asc" class="sort-btn px-4 py-2 rounded-lg font-medium <?php echo $sort === 'asc' ? 'active' : 'bg-white text-gray-800 hover:bg-gray-100'; ?>" onclick="saveScrollPosition()">
          <i class="fas fa-arrow-up mr-1"></i>Termurah
        </a>
        <a href="<?php echo $base; ?>?sort=desc" class="sort-btn px-4 py-2 rounded-lg font-medium <?php echo $sort === 'desc' ? 'active' : 'bg-white text-gray-800 hover:bg-gray-100'; ?>" onclick="saveScrollPosition()">
          <i class="fas fa-arrow-down mr-1"></i>Termahal
        </a>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
      <?php if (mysqli_num_rows($query) > 0) { while ($row = mysqli_fetch_assoc($query)) { ?>
        <div class="bg-white rounded-2xl shadow-md card-hover overflow-hidden border border-gray-100">
          <div class="relative">
            <img src="uploads/<?php echo htmlspecialchars($row['foto']); ?>" alt="Foto Menu" class="w-full h-48 object-cover">
            <div class="absolute top-4 right-4">
              <span class="bg-gray-800 text-white px-3 py-1 rounded-full text-xs font-medium">
                Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>
              </span>
            </div>
          </div>
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($row['potongan_nama']); ?></h3>
            <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($row['ringkasan']); ?>...</p>
            <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
              <span><i class="fas fa-store mr-1"></i><?php echo htmlspecialchars($row['nama_penjual']); ?></span>
              <span><i class="fas fa-shopping-basket mr-1"></i><?php echo $row['total_pesanan']; ?> Pesanan</span>
            </div>
            <a href="pembeli/detail_menu.php?id=<?php echo $row['id_menu']; ?>" class="block w-full bg-gray-800 text-white text-center rounded-xl py-3 font-medium hover:bg-gray-700 transition duration-300 flex items-center justify-center space-x-2">
              <i class="fas fa-shopping-cart"></i><span>Pesan Sekarang</span>
            </a>
          </div>
        </div>
      <?php } } else { ?>
        <div class="col-span-4 text-center py-12 text-gray-500">Belum ada menu tersedia.</div>
      <?php } ?>
    </div>
  </main>

  <footer class="bg-gray-800 text-white pt-12 pb-6">
    <div class="container mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <div>
          <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
              <i class="fas fa-utensils text-gray-800 text-lg"></i>
            </div>
            <h3 class="text-xl font-bold">FoodOrder</h3>
          </div>
          <p class="text-gray-400 text-sm">Platform pemesanan makanan online terpercaya.</p>
        </div>
        <div>
          <h4 class="font-semibold mb-4">Tautan Cepat</h4>
          <ul class="space-y-2 text-gray-400">
            <li><a href="#" class="hover:text-white transition">Tentang Kami</a></li>
            <li><a href="#" class="hover:text-white transition">Cara Pesan</a></li>
            <li><a href="#" class="hover:text-white transition">Syarat & Ketentuan</a></li>
            <li><a href="#" class="hover:text-white transition">Kebijakan Privasi</a></li>
          </ul>
        </div>
        <div>
          <h4 class="font-semibold mb-4">Kontak</h4>
          <ul class="space-y-2 text-gray-400">
            <li class="flex items-center space-x-2"><i class="fas fa-envelope"></i><span>support@foodorder.com</span></li>
            <li class="flex items-center space-x-2"><i class="fas fa-phone"></i><span>+62 21 1234 5678</span></li>
            <li class="flex items-center space-x-2"><i class="fas fa-map-marker-alt"></i><span>Bengkulu, Indonesia</span></li>
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
        <p class="text-gray-400 text-sm">&copy; <?php echo date("Y"); ?> FoodOrder. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <script>
    // Fungsi untuk menyimpan posisi scroll sebelum berpindah halaman
    function saveScrollPosition() {
      sessionStorage.setItem('scrollPosition', window.scrollY);
    }

    // Fungsi untuk mengembalikan posisi scroll setelah halaman dimuat
    function restoreScrollPosition() {
      const scrollPosition = sessionStorage.getItem('scrollPosition');
      if (scrollPosition) {
        window.scrollTo(0, parseInt(scrollPosition));
        sessionStorage.removeItem('scrollPosition'); // Hapus setelah digunakan
      }
    }

    // Panggil fungsi restoreScrollPosition saat halaman selesai dimuat
    window.addEventListener('load', restoreScrollPosition);

    // Alternatif: Gunakan event listener untuk semua tombol sortir
    document.addEventListener('DOMContentLoaded', function() {
      const sortButtons = document.querySelectorAll('.sort-btn');
      sortButtons.forEach(button => {
        button.addEventListener('click', saveScrollPosition);
      });
    });
  </script>
</body>
</html>