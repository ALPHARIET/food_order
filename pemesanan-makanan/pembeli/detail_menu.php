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

// Pastikan ada ID menu yang dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?error=Menu tidak valid");
    exit;
}

$id_menu = intval($_GET['id']);

// Ambil data menu dari database beserta informasi penjual
$query = mysqli_query($conn, "
    SELECT m.*, p.nama_penjual, p.no_telp as telp_penjual 
    FROM menu m 
    JOIN penjual p ON m.id_penjual = p.id_penjual 
    WHERE m.id_menu='$id_menu'
");

if (mysqli_num_rows($query) == 0) {
    header("Location: index.php?error=Menu tidak ditemukan");
    exit;
}

$menu = mysqli_fetch_assoc($query);

// Proses tambah ke pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jumlah = intval($_POST['jumlah']);
    $alamat_pengiriman = mysqli_real_escape_string($conn, $_POST['alamat_pengiriman']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    $total = $menu['harga'] * $jumlah;

    if ($jumlah <= 0) {
        $error = "Jumlah tidak boleh kurang dari 1.";
    } elseif (empty($alamat_pengiriman)) {
        $error = "Alamat pengiriman harus diisi.";
    } else {
        $insert = mysqli_query($conn, "
            INSERT INTO pesanan (id_menu, id_pembeli, jumlah, total_harga, alamat_pengiriman, catatan, status_pesanan) 
            VALUES ('$id_menu', '$id_pembeli', '$jumlah', '$total', '$alamat_pengiriman', '$catatan', 'Menunggu')
        ");

        if ($insert) {
            header("Location: pesanan_saya.php?success=Pesanan berhasil dibuat! Silakan tunggu konfirmasi dari penjual.");
            exit;
        } else {
            $error = "Terjadi kesalahan sistem, silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Menu - FoodOrder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">

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
                    <a href="pesanan_saya.php" class="text-gray-700 hover:text-gray-900 transition flex items-center space-x-2">
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
    <main class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="index.php" class="inline-flex items-center text-sm text-gray-700 hover:text-gray-900">
                        <i class="fas fa-home mr-2"></i>
                        Beranda
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-sm text-gray-500">Detail Menu</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Gambar Menu -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-center">
                    <?php if ($menu['foto']): ?>
                        <img src="../uploads/<?= htmlspecialchars($menu['foto']); ?>" 
                             alt="<?= htmlspecialchars($menu['nama_menu']); ?>" 
                             class="rounded-2xl w-full max-w-md h-80 object-cover shadow-md">
                    <?php else: ?>
                        <div class="w-full max-w-md h-80 bg-gray-200 rounded-2xl flex items-center justify-center shadow-md">
                            <i class="fas fa-utensils text-4xl text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informasi Menu & Form Pemesanan -->
            <div class="space-y-6">
                <!-- Informasi Menu -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h1 class="text-3xl font-bold text-gray-800 mb-3"><?= htmlspecialchars($menu['nama_menu']); ?></h1>
                    
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="flex items-center space-x-2 text-gray-600">
                            <i class="fas fa-store"></i>
                            <span class="font-medium"><?= htmlspecialchars($menu['nama_penjual']); ?></span>
                        </div>
                        <div class="flex items-center space-x-2 text-gray-600">
                            <i class="fas fa-phone"></i>
                            <span><?= htmlspecialchars($menu['telp_penjual']); ?></span>
                        </div>
                    </div>

                    <p class="text-gray-700 text-lg leading-relaxed mb-6">
                        <?= htmlspecialchars($menu['deskripsi']); ?>
                    </p>

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-2xl font-bold text-gray-800">
                                Rp <?= number_format($menu['harga'], 0, ',', '.'); ?>
                            </p>
                            <p class="text-sm text-gray-500">Harga per item</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Ketersediaan</p>
                            <p class="text-green-600 font-semibold flex items-center space-x-1">
                                <i class="fas fa-check-circle"></i>
                                <span>Tersedia</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Pemesanan -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Form Pemesanan</h2>

                    <?php if (isset($error)): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center space-x-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?= $error; ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jumlah Pesanan
                            </label>
                            <div class="flex items-center space-x-4">
                                <button type="button" onclick="decreaseQuantity()" class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center hover:bg-gray-300 transition">
                                    <i class="fas fa-minus text-gray-600"></i>
                                </button>
                                <input type="number" id="jumlah" name="jumlah" value="1" min="1" 
                                       class="w-20 text-center border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-gray-800 focus:border-gray-800">
                                <button type="button" onclick="increaseQuantity()" class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center hover:bg-gray-300 transition">
                                    <i class="fas fa-plus text-gray-600"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Alamat Pengiriman <span class="text-red-500">*</span>
                            </label>
                            <textarea name="alamat_pengiriman" rows="3" required
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                                      placeholder="Masukkan alamat lengkap pengiriman"><?= htmlspecialchars($pembeli['alamat'] ?? ''); ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan untuk Penjual (Opsional)
                            </label>
                            <textarea name="catatan" rows="2"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                                      placeholder="Contoh: Tidak pedas, tambah kantong, dll."></textarea>
                        </div>

                        <!-- Ringkasan Pesanan -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <h3 class="font-semibold text-gray-800 mb-3">Ringkasan Pesanan</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Harga per item</span>
                                    <span>Rp <?= number_format($menu['harga'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Jumlah</span>
                                    <span id="summary-quantity">1</span>
                                </div>
                                <div class="border-t border-gray-200 pt-2 flex justify-between font-semibold text-gray-800">
                                    <span>Total</span>
                                    <span id="summary-total">Rp <?= number_format($menu['harga'], 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex space-x-4">
                            <a href="index.php" 
                               class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-400 transition text-center flex items-center justify-center space-x-2">
                                <i class="fas fa-arrow-left"></i>
                                <span>Kembali</span>
                            </a>
                            <button type="submit" 
                                    class="flex-1 bg-gray-800 text-white py-3 rounded-xl font-semibold hover:bg-gray-700 transition flex items-center justify-center space-x-2">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Pesan Sekarang</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        const harga = <?= $menu['harga']; ?>;
        
        function updateSummary() {
            const quantity = parseInt(document.getElementById('jumlah').value);
            const total = harga * quantity;
            
            document.getElementById('summary-quantity').textContent = quantity;
            document.getElementById('summary-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
        
        function increaseQuantity() {
            const input = document.getElementById('jumlah');
            input.value = parseInt(input.value) + 1;
            updateSummary();
        }
        
        function decreaseQuantity() {
            const input = document.getElementById('jumlah');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                updateSummary();
            }
        }
        
        // Update summary when quantity changes
        document.getElementById('jumlah').addEventListener('input', updateSummary);
        
        // Initial summary update
        updateSummary();
    </script>
</body>
</html>