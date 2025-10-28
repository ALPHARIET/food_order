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

// Proses tambah menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_menu'])) {
    $nama_menu = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    
    // Handle upload foto
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $filename;
        
        // Validasi file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (in_array(strtolower($file_extension), $allowed_types) && 
            $_FILES['foto']['size'] <= $max_size) {
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $foto = $filename;
            }
        }
    }
    
    $query = "INSERT INTO menu (id_penjual, nama_menu, deskripsi, harga, foto) 
              VALUES ('$id_penjual', '$nama_menu', '$deskripsi', '$harga', '$foto')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Menu berhasil ditambahkan!";
        header("Location: menu.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal menambahkan menu: " . mysqli_error($conn);
    }
}

// Proses edit menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_menu'])) {
    $id_menu = mysqli_real_escape_string($conn, $_POST['id_menu']);
    $nama_menu = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    
    // Cek apakah menu milik penjual ini
    $check_query = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu = '$id_menu' AND id_penjual = '$id_penjual'");
    if (mysqli_num_rows($check_query) === 0) {
        $_SESSION['error'] = "Menu tidak ditemukan!";
        header("Location: menu.php");
        exit;
    }
    
    $update_data = "nama_menu = '$nama_menu', deskripsi = '$deskripsi', harga = '$harga'";
    
    // Handle upload foto baru
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $target_dir = "../uploads/";
        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $filename;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 2 * 1024 * 1024;
        
        if (in_array(strtolower($file_extension), $allowed_types) && 
            $_FILES['foto']['size'] <= $max_size) {
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                // Hapus foto lama
                $old_foto = mysqli_fetch_assoc($check_query)['foto'];
                if ($old_foto && file_exists($target_dir . $old_foto)) {
                    unlink($target_dir . $old_foto);
                }
                $update_data .= ", foto = '$filename'";
            }
        }
    }
    
    $query = "UPDATE menu SET $update_data WHERE id_menu = '$id_menu' AND id_penjual = '$id_penjual'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Menu berhasil diupdate!";
        header("Location: menu.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal mengupdate menu: " . mysqli_error($conn);
    }
}

// Proses hapus menu
if (isset($_GET['hapus'])) {
    $id_menu = mysqli_real_escape_string($conn, $_GET['hapus']);
    
    // Cek apakah menu milik penjual ini
    $check_query = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu = '$id_menu' AND id_penjual = '$id_penjual'");
    if (mysqli_num_rows($check_query) > 0) {
        $menu = mysqli_fetch_assoc($check_query);
        
        // Hapus foto
        if ($menu['foto'] && file_exists("../uploads/" . $menu['foto'])) {
            unlink("../uploads/" . $menu['foto']);
        }
        
        $query = "DELETE FROM menu WHERE id_menu = '$id_menu' AND id_penjual = '$id_penjual'";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Menu berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus menu: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Menu tidak ditemukan!";
    }
    
    header("Location: menu.php");
    exit;
}

// Ambil data menu penjual
$query_menu = mysqli_query($conn, "SELECT * FROM menu WHERE id_penjual = '$id_penjual' ORDER BY id_menu DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - FoodOrder</title>
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
                    <a href="menu.php" class="flex items-center space-x-3 bg-gray-700 px-4 py-3 rounded-lg text-white">
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
                    <h1 class="text-2xl font-bold text-gray-800">Kelola Menu</h1>
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

                <!-- Form Tambah Menu -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Tambah Menu Baru</h2>
                    </div>
                    <div class="p-6">
                        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Menu</label>
                                <input type="text" name="nama_menu" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                                       placeholder="Masukkan nama menu">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Harga</label>
                                <input type="number" name="harga" required min="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                                       placeholder="Masukkan harga">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                                <textarea name="deskripsi" rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                                          placeholder="Deskripsikan menu Anda"></textarea>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Foto Menu</label>
                                <input type="file" name="foto" accept="image/*"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition">
                                <p class="text-sm text-gray-500 mt-2">Format: JPG, PNG, GIF (Maks. 2MB)</p>
                            </div>
                            
                            <div class="md:col-span-2">
                                <button type="submit" name="tambah_menu"
                                        class="bg-gray-800 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-700 transition duration-300 flex items-center justify-center space-x-2">
                                    <i class="fas fa-plus"></i>
                                    <span>Tambah Menu</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Daftar Menu -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Daftar Menu Anda</h2>
                    </div>
                    
                    <div class="p-6">
                        <?php if (mysqli_num_rows($query_menu) > 0): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php while ($menu = mysqli_fetch_assoc($query_menu)): ?>
                                    <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                                        <div class="relative">
                                            <?php if ($menu['foto']): ?>
                                                <img src="../uploads/<?= htmlspecialchars($menu['foto']); ?>" 
                                                     alt="<?= htmlspecialchars($menu['nama_menu']); ?>"
                                                     class="w-full h-48 object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                                    <i class="fas fa-utensils text-gray-400 text-4xl"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="absolute top-3 right-3 flex space-x-2">
                                                <button onclick="editMenu(<?= $menu['id_menu']; ?>)" 
                                                        class="bg-blue-500 text-white p-2 rounded-lg hover:bg-blue-600 transition">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="menu.php?hapus=<?= $menu['id_menu']; ?>" 
                                                   onclick="return confirm('Yakin ingin menghapus menu ini?')"
                                                   class="bg-red-500 text-white p-2 rounded-lg hover:bg-red-600 transition">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <div class="p-4">
                                            <h3 class="font-semibold text-gray-800 text-lg mb-2"><?= htmlspecialchars($menu['nama_menu']); ?></h3>
                                            <p class="text-gray-600 text-sm mb-3 line-clamp-2"><?= htmlspecialchars($menu['deskripsi']); ?></p>
                                            <div class="flex justify-between items-center">
                                                <span class="text-lg font-bold text-gray-800">Rp <?= number_format($menu['harga'], 0, ',', '.'); ?></span>
                                                <span class="text-xs text-gray-500">ID: #<?= $menu['id_menu']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-utensils text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum ada menu</h3>
                                <p class="text-gray-500">Tambahkan menu pertama Anda menggunakan form di atas</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Menu -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Edit Menu</h2>
            </div>
            <div class="p-6">
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_menu" id="edit_id_menu">
                    <input type="hidden" name="edit_menu" value="1">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Menu</label>
                            <input type="text" name="nama_menu" id="edit_nama_menu" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Harga</label>
                            <input type="number" name="harga" id="edit_harga" required min="0"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <textarea name="deskripsi" id="edit_deskripsi" rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Menu (Opsional)</label>
                            <input type="file" name="foto" accept="image/*"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition">
                            <p class="text-sm text-gray-500 mt-2">Kosongkan jika tidak ingin mengubah foto</p>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 mt-6">
                        <button type="button" onclick="closeEditModal()"
                                class="flex-1 bg-gray-300 text-gray-700 px-4 py-3 rounded-xl font-semibold hover:bg-gray-400 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 bg-gray-800 text-white px-4 py-3 rounded-xl font-semibold hover:bg-gray-700 transition">
                            Update Menu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editMenu(id) {
            // Fetch menu data via AJAX (simplified version - in real app, use AJAX)
            // For demo, we'll just show the modal
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>