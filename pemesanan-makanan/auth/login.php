<?php
session_start();
include '../config/database.php';

// Jika sudah login, langsung arahkan ke halaman masing-masing
if (isset($_SESSION['role'])) {
  if ($_SESSION['role'] === 'penjual') {
    header('Location: ../penjual/dashboard.php');
    exit;
  } elseif ($_SESSION['role'] === 'pembeli') {
    header('Location: ../pembeli/index.php');
    exit;
  }
}

// Proses login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);

  // Cek apakah user adalah penjual
  $query_penjual = mysqli_query($conn, "SELECT * FROM penjual WHERE username='$username'");
  $query_pembeli = mysqli_query($conn, "SELECT * FROM pembeli WHERE username='$username'");

  if (mysqli_num_rows($query_penjual) > 0) {
    $penjual = mysqli_fetch_assoc($query_penjual);
    if ($penjual['password'] === md5($password)) {
      $_SESSION['id_user'] = $penjual['id_penjual'];
      $_SESSION['nama'] = $penjual['nama_penjual'];
      $_SESSION['role'] = 'penjual';
      header('Location: ../penjual/dashboard.php');
      exit;
    } else {
      $error = 'Password salah!';
    }
  } elseif (mysqli_num_rows($query_pembeli) > 0) {
    $pembeli = mysqli_fetch_assoc($query_pembeli);
    if ($pembeli['password'] === md5($password)) {
      $_SESSION['id_user'] = $pembeli['id_pembeli'];
      $_SESSION['nama'] = $pembeli['nama_pembeli'];
      $_SESSION['role'] = 'pembeli';
      header('Location: ../pembeli/index.php');
      exit;
    } else {
      $error = 'Password salah!';
    }
  } else {
    $error = 'Akun tidak ditemukan!';
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - FoodOrder</title>
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

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
  <div class="flex w-full max-w-6xl bg-white rounded-2xl overflow-hidden card-shadow">
    <!-- Left Side - Illustration -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-r from-gray-800 to-gray-900 items-center justify-center p-12">
      <div class="text-white text-center">
        <div class="w-32 h-32 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-8">
          <i class="fas fa-utensils text-5xl text-white"></i>
        </div>
        <h2 class="text-3xl font-bold mb-4">Selamat Datang Kembali</h2>
        <p class="text-lg opacity-90">Masuk ke akun Anda untuk melanjutkan pengalaman kuliner terbaik</p>
        <div class="mt-8 space-y-4 text-left">
          <div class="flex items-center space-x-3">
            <i class="fas fa-check-circle text-white/80"></i>
            <span>Akses menu eksklusif</span>
          </div>
          <div class="flex items-center space-x-3">
            <i class="fas fa-check-circle text-white/80"></i>
            <span>Pesan dengan mudah</span>
          </div>
          <div class="flex items-center space-x-3">
            <i class="fas fa-check-circle text-white/80"></i>
            <span>Kelola pesanan Anda</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="w-full lg:w-1/2 p-8 lg:p-12">
      <div class="text-center mb-8">
        <a href="../index.php" class="inline-flex items-center space-x-2 text-gray-600 hover:text-gray-800 transition mb-6">
          <i class="fas fa-arrow-left"></i>
          <span>Kembali ke Beranda</span>
        </a>
        <div class="w-16 h-16 bg-gradient-to-r from-gray-700 to-gray-900 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-lock text-white text-2xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Masuk ke Akun</h1>
        <p class="text-gray-600">Silakan masuk dengan kredensial Anda</p>
      </div>

      <?php if ($error) { ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center space-x-2">
          <i class="fas fa-exclamation-circle"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php } ?>

      <form method="POST" class="space-y-6">
        <div>
          <label class="block text-gray-700 font-semibold mb-2">Username</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-user text-gray-400"></i>
            </div>
            <input type="text" name="username" required
                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                   placeholder="Masukkan username Anda">
          </div>
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-2">Password</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-lock text-gray-400"></i>
            </div>
            <input type="password" name="password" required
                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-gray-800 focus:outline-none transition"
                   placeholder="Masukkan password Anda">
          </div>
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center space-x-2">
            <input type="checkbox" class="rounded border-gray-300 text-gray-800 focus:ring-gray-800">
            <span class="text-gray-700 text-sm">Ingat saya</span>
          </label>
          <a href="#" class="text-sm text-gray-600 hover:text-gray-800 transition">Lupa password?</a>
        </div>

        <button type="submit"
                class="w-full bg-gray-800 text-white py-3 rounded-xl font-semibold hover:bg-gray-700 transition duration-300 flex items-center justify-center space-x-2">
          <i class="fas fa-sign-in-alt"></i>
          <span>Masuk ke Akun</span>
        </button>
      </form>

      <div class="mt-8 text-center">
        <p class="text-gray-600">
          Belum punya akun?
          <a href="register.php" class="text-gray-800 font-semibold hover:underline transition">Daftar Sekarang</a>
        </p>
      </div>

      <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="text-center">
          <p class="text-gray-500 text-sm mb-4">Atau masuk sebagai</p>
          <div class="flex space-x-4 justify-center">
            <a href="../index.php" class="flex items-center space-x-2 bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
              <i class="fas fa-user text-gray-600"></i>
              <span class="text-sm text-gray-700">Pembeli</span>
            </a>
            <a href="../penjual/dashboard.php" class="flex items-center space-x-2 bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
              <i class="fas fa-store text-gray-600"></i>
              <span class="text-sm text-gray-700">Penjual</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>