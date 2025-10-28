<?php
include '../config/database.php';
session_start();

// Pastikan user sudah login dan berperan sebagai pembeli
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: ../auth/login.php");
    exit;
}

// Pastikan parameter id menu dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$id_menu = intval($_GET['id']);
$id_pembeli = $_SESSION['id_user'];

// Ambil data menu
$query_menu = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu='$id_menu'");
if (mysqli_num_rows($query_menu) == 0) {
    header("Location: ../index.php?error=menu_not_found");
    exit;
}
$menu = mysqli_fetch_assoc($query_menu);

// Jika form dikirim
if (isset($_POST['checkout'])) {
    $jumlah = intval($_POST['jumlah']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $total_harga = $jumlah * $menu['harga'];

    if ($jumlah <= 0) {
        $error = "Jumlah pesanan tidak boleh nol atau negatif.";
    } else {
        // Simpan data ke tabel pesanan
        $insert = mysqli_query($conn, "INSERT INTO pesanan (id_menu, id_pembeli, jumlah, total_harga, alamat_pengiriman, status_pesanan)
                    VALUES ('$id_menu', '$id_pembeli', '$jumlah', '$total_harga', '$alamat', 'Menunggu')");

        if ($insert) {
            header("Location: pesanan_saya.php?success=Pesanan berhasil dibuat");
            exit;
        } else {
            $error = "Terjadi kesalahan: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - Pemesanan Makanan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Script kecil untuk update total harga otomatis
    function updateTotal() {
      let harga = parseFloat(document.getElementById('harga').value);
      let jumlah = parseInt(document.getElementById('jumlah').value);
      let total = harga * jumlah;
      document.getElementById('total').textContent = "Rp " + total.toLocaleString('id-ID');
    }
  </script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center px-4">

  <div class="bg-white p-6 rounded-2xl shadow-md w-full max-w-lg">
    <h2 class="text-2xl font-bold text-orange-500 text-center mb-6">🛒 Checkout Pesanan</h2>

    <?php if (isset($error)): ?>
      <div class="bg-red-100 text-red-600 p-3 rounded-md mb-4 text-center font-medium">
        <?= htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <div class="mb-6 text-center">
      <img src="../uploads/<?= htmlspecialchars($menu['foto']); ?>" alt="<?= htmlspecialchars($menu['nama_menu']); ?>" 
           class="w-48 h-48 mx-auto object-cover rounded-lg shadow">
      <h3 class="text-xl font-semibold text-gray-800 mt-3"><?= htmlspecialchars($menu['nama_menu']); ?></h3>
      <p class="text-orange-500 font-bold">Rp <?= number_format($menu['harga'], 0, ',', '.'); ?></p>
    </div>

    <form method="POST" class="space-y-4">
      <input type="hidden" id="harga" value="<?= $menu['harga']; ?>">

      <div>
        <label class="block text-gray-700 font-medium mb-1">Jumlah Pesanan</label>
        <input type="number" name="jumlah" id="jumlah" min="1" value="1" onchange="updateTotal()" 
               class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-orange-400 focus:outline-none" required>
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-1">Alamat Pengiriman</label>
        <textarea name="alamat" rows="3" required class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                  placeholder="Masukkan alamat lengkap Anda"></textarea>
      </div>

      <div class="bg-gray-50 p-3 rounded-md text-center text-lg font-semibold text-gray-700">
        Total Harga: <span id="total">Rp <?= number_format($menu['harga'], 0, ',', '.'); ?></span>
      </div>

      <button type="submit" name="checkout" class="w-full bg-orange-500 text-white py-2 rounded-md font-semibold hover:bg-orange-600 transition">
        Konfirmasi Pesanan
      </button>
    </form>

    <p class="text-center text-sm text-gray-600 mt-4">
      <a href="../index.php" class="text-orange-500 hover:underline">← Kembali ke Menu</a>
    </p>
  </div>

</body>
</html>