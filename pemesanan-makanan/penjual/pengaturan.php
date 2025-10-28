<?php
session_start();
include '../config/database.php';

// Pastikan hanya penjual yang bisa mengakses
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../auth/login.php");
    exit;
}

$id_penjual = $_SESSION['id_user'];

// Ambil informasi penjual dari tabel users dan penjual
$query_penjual = mysqli_query($conn, "
    SELECT u.*, p.nama_penjual, p.no_telp as telp_penjual, p.email as email_penjual 
    FROM users u 
    LEFT JOIN penjual p ON u.id_user = p.id_user 
    WHERE u.id_user = '$id_penjual'
");
$penjual = mysqli_fetch_assoc($query_penjual);

$success = '';
$error = '';

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profil'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
    
    // Cek apakah username sudah digunakan oleh user lain
    $check_username = mysqli_query($conn, "SELECT id_user FROM users WHERE username = '$username' AND id_user != '$id_penjual'");
    if (mysqli_num_rows($check_username) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        // Cek apakah email sudah digunakan oleh user lain
        $check_email = mysqli_query($conn, "SELECT id_user FROM users WHERE email = '$email' AND id_user != '$id_penjual'");
        if (mysqli_num_rows($check_email) > 0) {
            $error = "Email sudah digunakan!";
        } else {
            // Update tabel users
            $update_user = mysqli_query($conn, "
                UPDATE users 
                SET nama = '$nama', username = '$username', email = '$email', no_telp = '$no_telp' 
                WHERE id_user = '$id_penjual'
            ");
            
            // Update tabel penjual
            $update_penjual = mysqli_query($conn, "
                UPDATE penjual 
                SET nama_penjual = '$nama', username = '$username', email = '$email', no_telp = '$no_telp' 
                WHERE id_user = '$id_penjual'
            ");
            
            if ($update_user) {
                $_SESSION['nama'] = $nama;
                $success = "Profil berhasil diupdate!";
                // Refresh data
                $query_penjual = mysqli_query($conn, "
                    SELECT u.*, p.nama_penjual, p.no_telp as telp_penjual, p.email as email_penjual 
                    FROM users u 
                    LEFT JOIN penjual p ON u.id_user = p.id_user 
                    WHERE u.id_user = '$id_penjual'
                ");
                $penjual = mysqli_fetch_assoc($query_penjual);
            } else {
                $error = "Gagal mengupdate profil: " . mysqli_error($conn);
            }
        }
    }
}

// Proses update password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $password_lama = mysqli_real_escape_string($conn, $_POST['password_lama']);
    $password_baru = mysqli_real_escape_string($conn, $_POST['password_baru']);
    $konfirmasi_password = mysqli_real_escape_string($conn, $_POST['konfirmasi_password']);
    
    // Verifikasi password lama
    $check_password = mysqli_query($conn, "SELECT password FROM penjual WHERE id_user = '$id_penjual'");
    $data_penjual = mysqli_fetch_assoc($check_password);
    
    if (md5($password_lama) !== $data_penjual['password']) {
        $error = "Password lama salah!";
    } elseif ($password_baru !== $konfirmasi_password) {
        $error = "Konfirmasi password tidak cocok!";
    } elseif (strlen($password_baru) < 6) {
        $error = "Password baru harus minimal 6 karakter!";
    } else {
        $password_hash = md5($password_baru);
        $update_password = mysqli_query($conn, "
            UPDATE penjual 
            SET password = '$password_hash' 
            WHERE id_user = '$id_penjual'
        ");
        
        if ($update_password) {
            $success = "Password berhasil diubah!";
        } else {
            $error = "Gagal mengubah password: " . mysqli_error($conn);
        }
    }
}

// Proses update toko
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_toko'])) {
    $nama_toko = mysqli_real_escape_string($conn, $_POST['nama_toko']);
    $deskripsi_toko = mysqli_real_escape_string($conn, $_POST['deskripsi_toko']);
    $alamat_toko = mysqli_real_escape_string($conn, $_POST['alamat_toko']);
    $jam_operasional = mysqli_real_escape_string($conn, $_POST['jam_operasional']);
    
    // Update informasi toko (dalam contoh ini kita update di tabel users dan penjual)
    $update_toko = mysqli_query($conn, "
        UPDATE users 
        SET nama = '$nama_toko' 
        WHERE id_user = '$id_penjual'
    ");
    
    if ($update_toko) {
        $_SESSION['nama'] = $nama_toko;
        $success = "Informasi toko berhasil diupdate!";
        // Refresh data
        $query_penjual = mysqli_query($conn, "
            SELECT u.*, p.nama_penjual, p.no_telp as telp_penjual, p.email as email_penjual 
            FROM users u 
            LEFT JOIN penjual p ON u.id_user = p.id_user 
            WHERE u.id_user = '$id_penjual'
        ");
        $penjual = mysqli_fetch_assoc($query_penjual);
    } else {
        $error = "Gagal mengupdate informasi toko: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - FoodOrder</title>
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
                    <a href="pesanan.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Pesanan</span>
                    </a>
                    <a href="laporan.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                        <i class="fas fa-chart-bar"></i>
                        <span>Laporan</span>
                    </a>
                    <a href="pengaturan.php" class="flex items-center space-x-3 bg-gray-700 px-4 py-3 rounded-lg text-white">
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
                    <h1 class="text-2xl font-bold text-gray-800">Pengaturan</h1>
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
                <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center space-x-2">
                        <i class="fas fa-check-circle"></i>
                        <span><?= $success; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center space-x-2">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= $error; ?></span>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Profil Pengguna -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-800 flex items-center space-x-2">
                                <i class="fas fa-user-circle text-gray-600"></i>
                                <span>Profil Pengguna</span>
                            </h2>
                        </div>
                        <div class="p-6">
                            <form method="POST">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                        <input type="text" name="nama" value="<?= htmlspecialchars($penjual['nama']); ?>" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                                        <input type="text" name="username" value="<?= htmlspecialchars($penjual['username']); ?>" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <input type="email" name="email" value="<?= htmlspecialchars($penjual['email']); ?>" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                                        <input type="tel" name="no_telp" value="<?= htmlspecialchars($penjual['no_telp']); ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition">
                                    </div>
                                    
                                    <div>
                                        <button type="submit" name="update_profil"
                                                class="w-full bg-gray-800 text-white py-3 rounded-xl font-semibold hover:bg-gray-700 transition duration-300 flex items-center justify-center space-x-2">
                                            <i class="fas fa-save"></i>
                                            <span>Update Profil</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Ubah Password -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-800 flex items-center space-x-2">
                                <i class="fas fa-lock text-gray-600"></i>
                                <span>Ubah Password</span>
                            </h2>
                        </div>
                        <div class="p-6">
                            <form method="POST">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Lama</label>
                                        <input type="password" name="password_lama" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                                               placeholder="Masukkan password lama">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                                        <input type="password" name="password_baru" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                                               placeholder="Masukkan password baru">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                                        <input type="password" name="konfirmasi_password" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                                               placeholder="Konfirmasi password baru">
                                    </div>
                                    
                                    <div>
                                        <button type="submit" name="update_password"
                                                class="w-full bg-gray-800 text-white py-3 rounded-xl font-semibold hover:bg-gray-700 transition duration-300 flex items-center justify-center space-x-2">
                                            <i class="fas fa-key"></i>
                                            <span>Ubah Password</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Informasi Toko -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 lg:col-span-2">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-800 flex items-center space-x-2">
                                <i class="fas fa-store text-gray-600"></i>
                                <span>Informasi Toko</span>
                            </h2>
                        </div>
                        <div class="p-6">
                            <form method="POST">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Toko</label>
                                        <input type="text" name="nama_toko" value="<?= htmlspecialchars($penjual['nama']); ?>" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                                               placeholder="Nama toko atau restoran Anda">
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Toko</label>
                                        <textarea name="deskripsi_toko" rows="3"
                                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                                                  placeholder="Deskripsikan toko atau restoran Anda"><?= htmlspecialchars($penjual['nama']); ?> - Menyediakan berbagai macam makanan lezat dan berkualitas.</textarea>
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Toko</label>
                                        <textarea name="alamat_toko" rows="2"
                                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                                                  placeholder="Alamat lengkap toko atau restoran">Jl. Contoh No. 123, Jakarta</textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Jam Operasional</label>
                                        <input type="text" name="jam_operasional" value="08:00 - 22:00"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                                               placeholder="Contoh: 08:00 - 22:00">
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <button type="submit" name="update_toko"
                                                class="w-full bg-gray-800 text-white py-3 rounded-xl font-semibold hover:bg-gray-700 transition duration-300 flex items-center justify-center space-x-2">
                                            <i class="fas fa-store"></i>
                                            <span>Update Informasi Toko</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Informasi Akun -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 lg:col-span-2">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-800 flex items-center space-x-2">
                                <i class="fas fa-info-circle text-gray-600"></i>
                                <span>Informasi Akun</span>
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Penjual</label>
                                        <p class="px-4 py-3 bg-gray-100 rounded-xl text-gray-800">#<?= $penjual['id_user']; ?></p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                        <p class="px-4 py-3 bg-gray-100 rounded-xl text-gray-800">Penjual</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Bergabung</label>
                                        <p class="px-4 py-3 bg-gray-100 rounded-xl text-gray-800">
                                            <?= date('d F Y', strtotime($penjual['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Akun</label>
                                        <p class="px-4 py-3 bg-green-100 text-green-800 rounded-xl font-medium">
                                            <i class="fas fa-check-circle"></i> Aktif
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Terakhir Login</label>
                                        <p class="px-4 py-3 bg-gray-100 rounded-xl text-gray-800">
                                            <?= date('d F Y H:i'); ?>
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Aksi</label>
                                        <div class="flex space-x-3">
                                            <a href="../auth/logout.php" 
                                               class="flex-1 bg-red-600 text-white py-3 rounded-xl font-semibold hover:bg-red-700 transition duration-300 flex items-center justify-center space-x-2">
                                                <i class="fas fa-sign-out-alt"></i>
                                                <span>Logout</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validasi form password
        document.querySelector('form[name="update_password"]')?.addEventListener('submit', function(e) {
            const passwordBaru = document.querySelector('input[name="password_baru"]').value;
            const konfirmasiPassword = document.querySelector('input[name="konfirmasi_password"]').value;
            
            if (passwordBaru.length < 6) {
                e.preventDefault();
                alert('Password baru harus minimal 6 karakter!');
                return false;
            }
            
            if (passwordBaru !== konfirmasiPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok!');
                return false;
            }
        });

        // Validasi form profil
        document.querySelector('form[name="update_profil"]')?.addEventListener('submit', function(e) {
            const email = document.querySelector('input[name="email"]').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Format email tidak valid!');
                return false;
            }
        });
    </script>
</body>
</html>