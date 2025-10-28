<?php
include '../config/database.php';
session_start();

// Inisialisasi pesan
$message = "";

// Proses registrasi saat form dikirim
if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $role = $_POST['role']; // penjual atau pembeli

    // Cek apakah username sudah digunakan
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        $message = "❌ Username sudah digunakan, silakan pilih yang lain.";
    } else {
        // Cek apakah email sudah digunakan
        $cek_email = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($cek_email) > 0) {
            $message = "❌ Email sudah digunakan, silakan gunakan email lain.";
        } else {
            // Hash password dengan MD5 (sesuai dengan sistem yang ada)
            $password_hash = md5($password);
            
            // Simpan ke tabel users
            $query_user = "INSERT INTO users (nama, username, password, email, no_telp, role) 
                          VALUES ('$nama', '$username', '$password_hash', '$email', '$no_telp', '$role')";
            
            if (mysqli_query($conn, $query_user)) {
                $user_id = mysqli_insert_id($conn);
                
                // Simpan ke tabel penjual atau pembeli sesuai role
                if ($role === 'penjual') {
                    $query_penjual = "INSERT INTO penjual (id_user, nama_penjual, username, password, no_telp, email) 
                                     VALUES ('$user_id', '$nama', '$username', '$password_hash', '$no_telp', '$email')";
                    mysqli_query($conn, $query_penjual);
                } elseif ($role === 'pembeli') {
                    $query_pembeli = "INSERT INTO pembeli (id_user, nama_pembeli, username, password, no_telp, email) 
                                     VALUES ('$user_id', '$nama', '$username', '$password_hash', '$no_telp', '$email')";
                    mysqli_query($conn, $query_pembeli);
                }
                
                $message = "✅ Registrasi berhasil! Silakan login.";
            } else {
                $message = "❌ Terjadi kesalahan: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Akun - FoodOrder</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .gradient-bg {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .card-shadow {
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
  </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
  <div class="flex w-full max-w-6xl bg-white rounded-2xl overflow-hidden card-shadow">
    <!-- Left Side - Illustration -->
    <div class="hidden lg:flex lg:w-1/2 gradient-bg items-center justify-center p-12">
      <div class="text-white text-center">
        <div class="w-32 h-32 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-8">
          <i class="fas fa-user-plus text-5xl text-white"></i>
        </div>
        <h2 class="text-3xl font-bold mb-4">Bergabung Dengan Kami</h2>
        <p class="text-lg opacity-90">Daftar sekarang dan nikmati pengalaman pemesanan makanan terbaik</p>
        <div class="mt-8 space-y-4 text-left">
          <div class="flex items-center space-x-3">
            <i class="fas fa-check-circle text-white/80"></i>
            <span>Akses menu eksklusif</span>
          </div>
          <div class="flex items-center space-x-3">
            <i class="fas fa-check-circle text-white/80"></i>
            <span>Pesan dengan mudah dan cepat</span>
          </div>
          <div class="flex items-center space-x-3">
            <i class="fas fa-check-circle text-white/80"></i>
            <span>Kelola bisnis makanan Anda</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side - Register Form -->
    <div class="w-full lg:w-1/2 p-8 lg:p-12">
      <div class="text-center mb-8">
        <a href="../index.php" class="inline-flex items-center space-x-2 text-gray-600 hover:text-gray-800 transition mb-6">
          <i class="fas fa-arrow-left"></i>
          <span>Kembali ke Beranda</span>
        </a>
        <div class="w-16 h-16 bg-gradient-to-r from-gray-700 to-gray-900 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-user-plus text-white text-2xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Buat Akun Baru</h1>
        <p class="text-gray-600">Isi data diri Anda untuk mulai bergabung</p>
      </div>

      <?php if ($message): ?>
        <div class="<?= strpos($message, '❌') !== false ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700'; ?> border px-4 py-3 rounded-xl mb-6 flex items-center space-x-2">
          <i class="<?= strpos($message, '❌') !== false ? 'fas fa-exclamation-circle' : 'fas fa-check-circle'; ?>"></i>
          <span><?= str_replace(['❌', '✅'], '', $message); ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Nama Lengkap</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-user text-gray-400"></i>
              </div>
              <input type="text" name="nama" required
                     class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                     placeholder="Masukkan nama lengkap">
            </div>
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Username</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-at text-gray-400"></i>
              </div>
              <input type="text" name="username" required
                     class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                     placeholder="Pilih username">
            </div>
          </div>
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-2">Email</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-envelope text-gray-400"></i>
            </div>
            <input type="email" name="email" required
                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                   placeholder="Masukkan email Anda">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-gray-700 font-semibold mb-2">Password</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-lock text-gray-400"></i>
              </div>
              <input type="password" name="password" required
                     class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                     placeholder="Buat password">
            </div>
          </div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">No. Telepon</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-phone text-gray-400"></i>
              </div>
              <input type="text" name="no_telp" required
                     class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                     placeholder="Contoh: 08123456789">
            </div>
          </div>
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-2">Daftar Sebagai</label>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="relative">
              <input type="radio" name="role" value="pembeli" required class="sr-only peer">
              <div class="p-4 border-2 border-gray-300 rounded-xl cursor-pointer transition-all peer-checked:border-gray-800 peer-checked:bg-gray-50 hover:border-gray-400">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user text-blue-600"></i>
                  </div>
                  <div>
                    <p class="font-semibold text-gray-800">Pembeli</p>
                    <p class="text-sm text-gray-600">Pesan makanan favorit</p>
                  </div>
                </div>
              </div>
            </label>

            <label class="relative">
              <input type="radio" name="role" value="penjual" required class="sr-only peer">
              <div class="p-4 border-2 border-gray-300 rounded-xl cursor-pointer transition-all peer-checked:border-gray-800 peer-checked:bg-gray-50 hover:border-gray-400">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-store text-green-600"></i>
                  </div>
                  <div>
                    <p class="font-semibold text-gray-800">Penjual</p>
                    <p class="text-sm text-gray-600">Jual makanan Anda</p>
                  </div>
                </div>
              </div>
            </label>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <input type="checkbox" required class="rounded border-gray-300 text-gray-800 focus:ring-gray-800">
          <span class="text-sm text-gray-700">
            Saya menyetujui
            <a href="#" class="text-gray-800 font-semibold hover:underline">Syarat & Ketentuan</a>
            dan
            <a href="#" class="text-gray-800 font-semibold hover:underline">Kebijakan Privasi</a>
          </span>
        </div>

        <button type="submit" name="register"
                class="w-full bg-gray-800 text-white py-3 rounded-xl font-semibold hover:bg-gray-700 transition duration-300 flex items-center justify-center space-x-2">
          <i class="fas fa-user-plus"></i>
          <span>Daftar Sekarang</span>
        </button>
      </form>

      <div class="mt-8 text-center">
        <p class="text-gray-600">
          Sudah punya akun?
          <a href="login.php" class="text-gray-800 font-semibold hover:underline transition">Masuk di sini</a>
        </p>
      </div>

      <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="text-center">
          <p class="text-gray-500 text-sm mb-4">Atau daftar sebagai</p>
          <div class="flex space-x-4 justify-center">
            <a href="?role=pembeli" class="flex items-center space-x-2 bg-blue-100 text-blue-700 px-4 py-2 rounded-lg hover:bg-blue-200 transition">
              <i class="fas fa-user"></i>
              <span class="text-sm font-medium">Pembeli</span>
            </a>
            <a href="?role=penjual" class="flex items-center space-x-2 bg-green-100 text-green-700 px-4 py-2 rounded-lg hover:bg-green-200 transition">
              <i class="fas fa-store"></i>
              <span class="text-sm font-medium">Penjual</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Auto-select role from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const role = urlParams.get('role');
    if (role) {
      const radio = document.querySelector(`input[value="${role}"]`);
      if (radio) radio.checked = true;
    }

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const password = document.querySelector('input[name="password"]').value;
      const telp = document.querySelector('input[name="no_telp"]').value;
      
      if (password.length < 6) {
        e.preventDefault();
        alert('Password harus minimal 6 karakter!');
        return false;
      }
      
      if (!/^[0-9+-\s()]{10,}$/.test(telp)) {
        e.preventDefault();
        alert('Format nomor telepon tidak valid!');
        return false;
      }
    });
  </script>
</body>
</html>