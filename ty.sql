CREATE DATABASE pemesanan_makanan;
USE pemesanan_makanan;

-- Tabel utama untuk semua pengguna (login)
CREATE TABLE users (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  no_telp VARCHAR(20),
  role ENUM('penjual', 'pembeli') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Penjual (profil tambahan)
CREATE TABLE penjual (
  id_penjual INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT,
  nama_penjual VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  no_telp VARCHAR(15),
  email VARCHAR(100),
  FOREIGN KEY (id_user) REFERENCES users(id_user)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabel Pembeli (profil tambahan)
CREATE TABLE pembeli (
  id_pembeli INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT,
  nama_pembeli VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  alamat TEXT,
  no_telp VARCHAR(15),
  email VARCHAR(100),
  FOREIGN KEY (id_user) REFERENCES users(id_user)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabel Menu
CREATE TABLE menu (
  id_menu INT AUTO_INCREMENT PRIMARY KEY,
  id_penjual INT,
  nama_menu VARCHAR(100) NOT NULL,
  deskripsi TEXT,
  harga DECIMAL(10,2) NOT NULL,
  foto VARCHAR(255),
  FOREIGN KEY (id_penjual) REFERENCES penjual(id_penjual)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabel Pesanan
CREATE TABLE pesanan (
  id_pesanan INT AUTO_INCREMENT PRIMARY KEY,
  id_menu INT NOT NULL,
  id_pembeli INT NOT NULL,
  jumlah INT NOT NULL,
  total_harga DECIMAL(12,2) NOT NULL,
  alamat_pengiriman TEXT,
  status_pesanan ENUM('Menunggu', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan') DEFAULT 'Menunggu',
  tanggal_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  catatan VARCHAR(255),
  FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (id_pembeli) REFERENCES pembeli(id_pembeli)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tambahkan akun contoh penjual
INSERT INTO users (nama, username, password, email, no_telp, role)
VALUES ('Warung Sederhana', 'warung1', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'warung1@gmail.com', '08123456789', 'penjual');

-- Hubungkan akun user ke tabel penjual
INSERT INTO penjual (id_user, nama_penjual, username, password, no_telp, email)
VALUES (1, 'Warung Sederhana', 'warung1', MD5('123456'), '08123456789', 'warung1@gmail.com');


-- DATA DUMMY KOMPREHENSIF UNTUK DEBUGGING LAPORAN
USE pemesanan_makanan;

-- 1. HAPUS DATA LAMA JIKA ADA (optional)
-- DELETE FROM pesanan WHERE id_pesanan > 0;
-- DELETE FROM menu WHERE id_menu > 0;
-- DELETE FROM pembeli WHERE id_pembeli > 0;
-- DELETE FROM penjual WHERE id_penjual > 0;
-- DELETE FROM users WHERE id_user > 0;

-- 2. TAMBAH USER DAN PENJUAL LEBIH BANYAK
INSERT INTO users (nama, username, password, email, no_telp, role) VALUES
-- Penjual tambahan
('Kedai Bakso Malang', 'bakso_malang', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'baksomalang@email.com', '08123456790', 'penjual'),
('Warung Tegal', 'warteg_sedap', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'warteg@email.com', '08123456791', 'penjual'),
('Pizza Italia', 'pizza_italia', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'pizza@email.com', '08123456792', 'penjual'),
('Cafe Kopi', 'cafe_kopi', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'cafekopi@email.com', '08123456793', 'penjual'),

-- Pembeli tambahan (20 pembeli)
('Rina Melati', 'rina_m', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'rina@email.com', '08123456794', 'pembeli'),
('Fajar Nugroho', 'fajar_n', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'fajar@email.com', '08123456795', 'pembeli'),
('Maya Sari', 'maya_s', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'maya@email.com', '08123456796', 'pembeli'),
('Rizki Pratama', 'rizki_p', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'rizki@email.com', '08123456797', 'pembeli'),
('Diana Putri', 'diana_p', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'diana@email.com', '08123456798', 'pembeli'),
('Hendra Wijaya', 'hendra_w', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'hendra@email.com', '08123456799', 'pembeli'),
('Lina Marlina', 'lina_m', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'lina@email.com', '08123456800', 'pembeli'),
('Agus Setiawan', 'agus_s', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'agus@email.com', '08123456801', 'pembeli'),
('Siti Rahayu', 'siti_r', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'siti@email.com', '08123456802', 'pembeli'),
('Bayu Kurniawan', 'bayu_k', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'bayu@email.com', '08123456803', 'pembeli'),
('Nina Astuti', 'nina_a', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'nina@email.com', '08123456804', 'pembeli'),
('Rudi Hermawan', 'rudi_h', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'rudi@email.com', '08123456805', 'pembeli'),
('Mira Dewi', 'mira_d', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'mira@email.com', '08123456806', 'pembeli'),
('Ari Wibowo', 'ari_w', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'ari@email.com', '08123456807', 'pembeli'),
('Yuni Kartika', 'yuni_k', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'yuni@email.com', '08123456808', 'pembeli'),
('Firman Syah', 'firman_s', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'firman@email.com', '08123456809', 'pembeli'),
('Desi Anggraeni', 'desi_a', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'desi@email.com', '08123456810', 'pembeli'),
('Rizky Fadilah', 'rizky_f', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'rizky@email.com', '08123456811', 'pembeli'),
('Wulan Sari', 'wulan_s', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'wulan@email.com', '08123456812', 'pembeli'),
('Eko Prasetyo', 'eko_p', '$2y$10$3FVRV74P5yzhvCsWemQAEuMuvlBw7jG221QbXnRDqvmSUlK8PIe12', 'eko@email.com', '08123456813', 'pembeli');

-- 3. HUBUNGKAN KE TABEL PENJUAL DAN PEMBELI
INSERT INTO penjual (id_user, nama_penjual, username, password, no_telp, email) VALUES
(6, 'Kedai Bakso Malang', 'bakso_malang', MD5('123456'), '08123456790', 'baksomalang@email.com'),
(7, 'Warung Tegal', 'warteg_sedap', MD5('123456'), '08123456791', 'warteg@email.com'),
(8, 'Pizza Italia', 'pizza_italia', MD5('123456'), '08123456792', 'pizza@email.com'),
(9, 'Cafe Kopi', 'cafe_kopi', MD5('123456'), '08123456793', 'cafekopi@email.com');

INSERT INTO pembeli (id_user, nama_pembeli, username, password, alamat, no_telp, email) VALUES
(10, 'Rina Melati', 'rina_m', MD5('123456'), 'Jl. Anggrek No. 10, Jakarta', '08123456794', 'rina@email.com'),
(11, 'Fajar Nugroho', 'fajar_n', MD5('123456'), 'Jl. Mawar No. 11, Jakarta', '08123456795', 'fajar@email.com'),
(12, 'Maya Sari', 'maya_s', MD5('123456'), 'Jl. Melati No. 12, Jakarta', '08123456796', 'maya@email.com'),
(13, 'Rizki Pratama', 'rizki_p', MD5('123456'), 'Jl. Kenanga No. 13, Jakarta', '08123456797', 'rizki@email.com'),
(14, 'Diana Putri', 'diana_p', MD5('123456'), 'Jl. Flamboyan No. 14, Jakarta', '08123456798', 'diana@email.com'),
(15, 'Hendra Wijaya', 'hendra_w', MD5('123456'), 'Jl. Cempaka No. 15, Jakarta', '08123456799', 'hendra@email.com'),
(16, 'Lina Marlina', 'lina_m', MD5('123456'), 'Jl. Teratai No. 16, Jakarta', '08123456800', 'lina@email.com'),
(17, 'Agus Setiawan', 'agus_s', MD5('123456'), 'Jl. Kamboja No. 17, Jakarta', '08123456801', 'agus@email.com'),
(18, 'Siti Rahayu', 'siti_r', MD5('123456'), 'Jl. Sakura No. 18, Jakarta', '08123456802', 'siti@email.com'),
(19, 'Bayu Kurniawan', 'bayu_k', MD5('123456'), 'Jl. Tulip No. 19, Jakarta', '08123456803', 'bayu@email.com'),
(20, 'Nina Astuti', 'nina_a', MD5('123456'), 'Jl. Lavender No. 20, Jakarta', '08123456804', 'nina@email.com'),
(21, 'Rudi Hermawan', 'rudi_h', MD5('123456'), 'Jl. Daisy No. 21, Jakarta', '08123456805', 'rudi@email.com'),
(22, 'Mira Dewi', 'mira_d', MD5('123456'), 'Jl. Sunflower No. 22, Jakarta', '08123456806', 'mira@email.com'),
(23, 'Ari Wibowo', 'ari_w', MD5('123456'), 'Jl. Orchid No. 23, Jakarta', '08123456807', 'ari@email.com'),
(24, 'Yuni Kartika', 'yuni_k', MD5('123456'), 'Jl. Lily No. 24, Jakarta', '08123456808', 'yuni@email.com'),
(25, 'Firman Syah', 'firman_s', MD5('123456'), 'Jl. Rose No. 25, Jakarta', '08123456809', 'firman@email.com'),
(26, 'Desi Anggraeni', 'desi_a', MD5('123456'), 'Jl. Jasmine No. 26, Jakarta', '08123456810', 'desi@email.com'),
(27, 'Rizky Fadilah', 'rizky_f', MD5('123456'), 'Jl. Violet No. 27, Jakarta', '08123456811', 'rizky@email.com'),
(28, 'Wulan Sari', 'wulan_s', MD5('123456'), 'Jl. Poppy No. 28, Jakarta', '08123456812', 'wulan@email.com'),
(29, 'Eko Prasetyo', 'eko_p', MD5('123456'), 'Jl. Magnolia No. 29, Jakarta', '08123456813', 'eko@email.com');

-- 4. TAMBAH MENU LEBIH BANYAK UNTUK SEMUA PENJUAL
INSERT INTO menu (id_penjual, nama_menu, deskripsi, harga, foto) VALUES
-- Warung Sederhana (id_penjual = 1) - 10 menu
(1, 'Nasi Goreng Spesial', 'Nasi goreng dengan telur, ayam, dan sayuran segar', 25000, 'nasi_goreng.jpg'),
(1, 'Mie Ayam Jamur', 'Mie ayam dengan jamur shitake dan pangsit goreng', 20000, 'mie_ayam.jpg'),
(1, 'Gado-gado', 'Salad sayuran dengan bumbu kacang khas Indonesia', 18000, 'gado_gado.jpg'),
(1, 'Sate Ayam', 'Sate ayam dengan bumbu kacang dan lontong', 30000, 'sate_ayam.jpg'),
(1, 'Es Jeruk', 'Es jeruk segar dengan potongan jeruk', 8000, 'es_jeruk.jpg'),
(1, 'Soto Ayam', 'Soto ayam dengan suwiran ayam dan soun', 22000, 'soto_ayam.jpg'),
(1, 'Bakso Urat', 'Bakso urat sapi dengan kuah kaldu sapi', 25000, 'bakso_urat.jpg'),
(1, 'Nasi Rames', 'Nasi dengan lauk pauk komplit', 28000, 'nasi_rames.jpg'),
(1, 'Teh Manis', 'Teh manis hangat/dingin', 5000, 'teh_manis.jpg'),
(1, 'Kerupuk', 'Kerupuk udang renyah', 3000, 'kerupuk.jpg'),

-- Restoran Padang (id_penjual = 2) - 10 menu
(2, 'Rendang Daging', 'Rendang daging sapi dengan bumbu rempah khas Padang', 35000, 'rendang.jpg'),
(2, 'Ayam Pop', 'Ayam kampung rebus dengan sambal merah khas Padang', 28000, 'ayam_pop.jpg'),
(2, 'Gulai Ikan', 'Gulai ikan kakap dengan kuah kuning yang gurih', 32000, 'gulai_ikan.jpg'),
(2, 'Dendeng Balado', 'Daging sapi kering dengan sambal balado pedas', 38000, 'dendeng.jpg'),
(2, 'Nasi Putih', 'Nasi putih pulen', 5000, 'nasi_putih.jpg'),
(2, 'Sambal Hijau', 'Sambal ijo khas Padang', 8000, 'sambal_hijau.jpg'),
(2, 'Sayur Nangka', 'Sayur nangka muda khas Padang', 15000, 'sayur_nangka.jpg'),
(2, 'Telur Balado', 'Telur rebus dengan sambal balado', 12000, 'telur_balado.jpg'),
(2, 'Perkedel Kentang', 'Perkedel kentang goreng', 10000, 'perkedel.jpg'),
(2, 'Es Teh Tawar', 'Es teh tawar segar', 4000, 'es_teh.jpg'),

-- Kedai Bakso Malang (id_penjual = 3) - 8 menu
(3, 'Bakso Jumbo', 'Bakso sapi jumbo dengan kuah kaldu', 20000, 'bakso_jumbo.jpg'),
(3, 'Bakso Beranak', 'Bakso besar berisi bakso kecil', 25000, 'bakso_beranak.jpg'),
(3, 'Mie Ayam Bakso', 'Mie ayam dengan bakso sapi', 22000, 'mie_ayam_bakso.jpg'),
(3, 'Pangsit Goreng', 'Pangsit goreng renyah', 12000, 'pangsit_goreng.jpg'),
(3, 'Es Jeruk Nipis', 'Es jeruk nipis segar', 7000, 'es_jeruk_nipis.jpg'),
(3, 'Teh Botol', 'Teh botol sosro', 8000, 'teh_botol.jpg'),
(3, 'Kerupuk Puli', 'Kerupuk puli khas Malang', 5000, 'kerupuk_puli.jpg'),
(3, 'Bakso Tenis', 'Bakso kecil-kecil seperti bola tenis', 18000, 'bakso_tenis.jpg'),

-- Warung Tegal (id_penjual = 4) - 8 menu
(4, 'Nasi Rames Komplit', 'Nasi dengan lauk pauk komplit ala warteg', 25000, 'nasi_rames_warteg.jpg'),
(4, 'Ayam Goreng', 'Ayam goreng kremes', 20000, 'ayam_goreng.jpg'),
(4, 'Tempe Goreng', 'Tempe goreng renyah', 5000, 'tempe_goreng.jpg'),
(4, 'Tahu Goreng', 'Tahu goreng isi', 5000, 'tahu_goreng.jpg'),
(4, 'Sayur Asem', 'Sayur asem segar', 8000, 'sayur_asem.jpg'),
(4, 'Kering Tempe', 'Kering tempe manis pedas', 10000, 'kering_tempe.jpg'),
(4, 'Sambal Terasi', 'Sambal terasi pedas', 3000, 'sambal_terasi.jpg'),
(4, 'Es Teh Manis', 'Es teh manis segar', 5000, 'es_teh_manis.jpg'),

-- Pizza Italia (id_penjual = 5) - 6 menu
(5, 'Pizza Margherita', 'Pizza dengan tomat, mozzarella, dan basil', 75000, 'pizza_margherita.jpg'),
(5, 'Pizza Pepperoni', 'Pizza dengan pepperoni dan keju mozzarella', 85000, 'pizza_pepperoni.jpg'),
(5, 'Pizza Supreme', 'Pizza dengan berbagai topping daging dan sayuran', 95000, 'pizza_supreme.jpg'),
(5, 'Spaghetti Carbonara', 'Spaghetti dengan saus carbonara', 45000, 'spaghetti_carbonara.jpg'),
(5, 'Garlic Bread', 'Roti bawang putih panggang', 25000, 'garlic_bread.jpg'),
(5, 'Coca Cola', 'Minuman bersoda Coca Cola', 15000, 'coca_cola.jpg'),

-- Cafe Kopi (id_penjual = 6) - 6 menu
(6, 'Espresso', 'Kopi espresso single shot', 20000, 'espresso.jpg'),
(6, 'Cappuccino', 'Cappuccino dengan foam susu', 30000, 'cappuccino.jpg'),
(6, 'Latte', 'Latte dengan seni latte art', 35000, 'latte.jpg'),
(6, 'Americano', 'Kopi americano hitam', 25000, 'americano.jpg'),
(6, 'Croissant', 'Croissant butter panggang', 25000, 'croissant.jpg'),
(6, 'Red Velvet Cake', 'Kue red velvet lembut', 35000, 'red_velvet.jpg');

-- 5. GENERATE PESANAN DALAM JUMLAH BESAR (200+ pesanan)
-- Function untuk generate random date dalam range
DELIMITER //
CREATE FUNCTION random_date(start_date DATE, end_date DATE) RETURNS DATE
BEGIN
    RETURN DATE_ADD(start_date, INTERVAL FLOOR(RAND() * DATEDIFF(end_date, start_date)) DAY);
END//
DELIMITER ;

-- Function untuk generate random datetime dalam range
DELIMITER //
CREATE FUNCTION random_datetime(start_date DATE, end_date DATE) RETURNS DATETIME
BEGIN
    RETURN DATE_ADD(
        DATE_ADD(start_date, INTERVAL FLOOR(RAND() * DATEDIFF(end_date, start_date)) DAY),
        INTERVAL FLOOR(RAND() * 86400) SECOND
    );
END//
DELIMITER ;

-- Insert 200+ pesanan dummy dengan variasi yang luas
INSERT INTO pesanan (id_menu, id_pembeli, jumlah, total_harga, alamat_pengiriman, status_pesanan, tanggal_pesan, catatan)
SELECT 
    m.id_menu,
    FLOOR(1 + RAND() * 29) as id_pembeli, -- Random pembeli dari 29 pembeli
    FLOOR(1 + RAND() * 5) as jumlah, -- Random jumlah 1-5
    m.harga * FLOOR(1 + RAND() * 5) as total_harga, -- Total berdasarkan jumlah
    CONCAT('Jl. Contoh No. ', FLOOR(1 + RAND() * 100), ', Jakarta') as alamat_pengiriman,
    ELT(FLOOR(1 + RAND() * 5), 'Menunggu', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan') as status_pesanan,
    random_datetime('2024-01-01', '2024-03-19') as tanggal_pesan,
    ELT(FLOOR(1 + RAND() * 8), 
        'Tidak pakai pedas', 
        'Extra pedas', 
        'Tambah saus', 
        'Bungkus rapat', 
        'Antar cepat', 
        'Tidak pakai bawang', 
        'Porsi besar', 
        'Tidak ada catatan'
    ) as catatan
FROM menu m
CROSS JOIN (SELECT 1 as n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) as multiplier
WHERE m.id_penjual = 1  -- Fokus ke Warung Sederhana untuk testing
LIMIT 250;

-- 6. BUAT PESANAN KHUSUS UNTUK TESTING SCENARIO TERTENTU
-- High value orders untuk testing statistik
INSERT INTO pesanan (id_menu, id_pembeli, jumlah, total_harga, alamat_pengiriman, status_pesanan, tanggal_pesan, catatan) VALUES
(1, 1, 10, 250000, 'Jl. Merdeka No. 123, Jakarta', 'Selesai', '2024-03-15 12:00:00', 'Pesanan catering meeting'),
(4, 2, 8, 240000, 'Jl. Sudirman No. 45, Jakarta', 'Selesai', '2024-03-10 18:30:00', 'Pesanan acara keluarga'),
(1, 3, 15, 375000, 'Jl. Thamrin No. 67, Jakarta', 'Selesai', '2024-03-05 11:45:00', 'Pesanan kantor'),

-- Bulk orders untuk testing best customer
(1, 1, 3, 75000, 'Jl. Merdeka No. 123, Jakarta', 'Selesai', '2024-03-18 13:20:00', 'Pesanan rutin mingguan'),
(2, 1, 2, 40000, 'Jl. Merdeka No. 123, Jakarta', 'Selesai', '2024-03-17 19:15:00', 'Mie ayam favorit'),
(3, 1, 1, 18000, 'Jl. Merdeka No. 123, Jakarta', 'Selesai', '2024-03-16 12:30:00', 'Gado-gado sehat'),
(4, 1, 2, 60000, 'Jl. Merdeka No. 123, Jakarta', 'Selesai', '2024-03-15 18:45:00', 'Sate ayam spesial'),

-- Orders dengan berbagai waktu untuk testing jam sibuk
(1, 4, 1, 25000, 'Jl. Gatot Subroto No. 89, Jakarta', 'Selesai', '2024-03-19 07:30:00', 'Sarapan pagi'),
(2, 5, 1, 20000, 'Jl. Contoh No. 50, Jakarta', 'Selesai', '2024-03-19 08:15:00', 'Mie ayam sarapan'),
(1, 6, 2, 50000, 'Jl. Contoh No. 51, Jakarta', 'Selesai', '2024-03-19 12:00:00', 'Lunch break'),
(3, 7, 1, 18000, 'Jl. Contoh No. 52, Jakarta', 'Selesai', '2024-03-19 12:30:00', 'Makan siang'),
(4, 8, 3, 90000, 'Jl. Contoh No. 53, Jakarta', 'Selesai', '2024-03-19 13:15:00', 'Pesanan teman kantor'),
(1, 9, 1, 25000, 'Jl. Contoh No. 54, Jakarta', 'Selesai', '2024-03-19 18:00:00', 'Makan malam'),
(2, 10, 2, 40000, 'Jl. Contoh No. 55, Jakarta', 'Selesai', '2024-03-19 19:30:00', 'Dinner keluarga'),
(4, 11, 1, 30000, 'Jl. Contoh No. 56, Jakarta', 'Selesai', '2024-03-19 20:45:00', 'Malam minggu'),

-- Orders dengan status berbeda untuk distribusi
(1, 12, 1, 25000, 'Jl. Contoh No. 57, Jakarta', 'Menunggu', NOW(), 'Pesanan baru masuk'),
(2, 13, 2, 40000, 'Jl. Contoh No. 58, Jakarta', 'Diproses', NOW(), 'Sedang dimasak'),
(3, 14, 1, 18000, 'Jl. Contoh No. 59, Jakarta', 'Dikirim', DATE_SUB(NOW(), INTERVAL 30 MINUTE), 'Dalam pengiriman'),
(4, 15, 1, 30000, 'Jl. Contoh No. 60, Jakarta', 'Dibatalkan', DATE_SUB(NOW(), INTERVAL 1 HOUR), 'Pembeli cancel'),

-- Orders untuk testing filter bulan berbeda
(1, 16, 2, 50000, 'Jl. Contoh No. 61, Jakarta', 'Selesai', '2024-01-15 12:00:00', 'Pesanan Januari'),
(2, 17, 1, 20000, 'Jl. Contoh No. 62, Jakarta', 'Selesai', '2024-01-20 13:00:00', 'Pesanan akhir Januari'),
(3, 18, 3, 54000, 'Jl. Contoh No. 63, Jakarta', 'Selesai', '2024-02-10 11:30:00', 'Pesanan Februari'),
(4, 19, 2, 60000, 'Jl. Contoh No. 64, Jakarta', 'Selesai', '2024-02-25 18:45:00', 'Pesanan akhir Februari');

-- 7. DATA UNTUK TESTING TREND BULANAN
INSERT INTO pesanan (id_menu, id_pembeli, jumlah, total_harga, alamat_pengiriman, status_pesanan, tanggal_pesan, catatan)
SELECT 
    m.id_menu,
    FLOOR(1 + RAND() * 29) as id_pembeli,
    FLOOR(1 + RAND() * 3) as jumlah,
    m.harga * FLOOR(1 + RAND() * 3) as total_harga,
    CONCAT('Jl. Trend No. ', FLOOR(1 + RAND() * 100), ', Jakarta') as alamat_pengiriman,
    'Selesai' as status_pesanan,
    DATE_ADD('2024-01-01', INTERVAL FLOOR(RAND() * 78) DAY) as tanggal_pesan, -- Random date Jan-Mar
    'Pesanan testing trend' as catatan
FROM menu m
WHERE m.id_penjual = 1
LIMIT 100;

-- 8. VERIFIKASI DATA YANG SUDAH DIBUAT
SELECT 
    'Total Data' as Kategori,
    COUNT(*) as Jumlah
FROM (
    SELECT 'Users' as type, COUNT(*) as count FROM users
    UNION ALL SELECT 'Penjual', COUNT(*) FROM penjual
    UNION ALL SELECT 'Pembeli', COUNT(*) FROM pembeli
    UNION ALL SELECT 'Menu', COUNT(*) FROM menu
    UNION ALL SELECT 'Pesanan', COUNT(*) FROM pesanan
) as counts;

-- Statistik khusus untuk Warung Sederhana (id_penjual = 1)
SELECT 
    'Warung Sederhana Stats' as Metric,
    COUNT(*) as Value
FROM (
    SELECT 'Total Pesanan' as metric, COUNT(*) as value FROM pesanan p JOIN menu m ON p.id_menu = m.id_menu WHERE m.id_penjual = 1
    UNION ALL SELECT 'Pesanan Selesai', COUNT(*) FROM pesanan p JOIN menu m ON p.id_menu = m.id_menu WHERE m.id_penjual = 1 AND p.status_pesanan = 'Selesai'
    UNION ALL SELECT 'Total Pendapatan', SUM(total_harga) FROM pesanan p JOIN menu m ON p.id_menu = m.id_menu WHERE m.id_penjual = 1 AND p.status_pesanan = 'Selesai'
    UNION ALL SELECT 'Rata-rata Transaksi', AVG(total_harga) FROM pesanan p JOIN menu m ON p.id_menu = m.id_menu WHERE m.id_penjual = 1 AND p.status_pesanan = 'Selesai'
    UNION ALL SELECT 'Menu Terpopuler', (SELECT COUNT(*) FROM pesanan p JOIN menu m ON p.id_menu = m.id_menu WHERE m.id_penjual = 1 AND m.nama_menu = 'Nasi Goreng Spesial')
) as stats;

-- Tampilkan distribusi status pesanan
SELECT 
    status_pesanan,
    COUNT(*) as total_pesanan,
    SUM(total_harga) as total_nilai,
    ROUND(AVG(total_harga), 2) as rata_rata
FROM pesanan p 
JOIN menu m ON p.id_menu = m.id_menu 
WHERE m.id_penjual = 1
GROUP BY status_pesanan 
ORDER BY 
    CASE status_pesanan
        WHEN 'Menunggu' THEN 1
        WHEN 'Diproses' THEN 2
        WHEN 'Dikirim' THEN 3
        WHEN 'Selesai' THEN 4
        WHEN 'Dibatalkan' THEN 5
    END;

-- Tampilkan top 5 menu terpopuler
SELECT 
    m.nama_menu,
    COUNT(p.id_pesanan) as total_terjual,
    SUM(p.jumlah) as total_item,
    SUM(p.total_harga) as total_pendapatan
FROM pesanan p 
JOIN menu m ON p.id_menu = m.id_menu 
WHERE m.id_penjual = 1
AND p.status_pesanan = 'Selesai'
GROUP BY m.id_menu, m.nama_menu
ORDER BY total_terjual DESC, total_pendapatan DESC
LIMIT 5;

-- Tampilkan top 5 pelanggan terbaik
SELECT 
    pb.nama_pembeli,
    COUNT(p.id_pesanan) as total_pesanan,
    SUM(p.total_harga) as total_pengeluaran,
    ROUND(AVG(p.total_harga), 2) as rata_rata_transaksi
FROM pesanan p 
JOIN pembeli pb ON p.id_pembeli = pb.id_pembeli
JOIN menu m ON p.id_menu = m.id_menu 
WHERE m.id_penjual = 1
AND p.status_pesanan = 'Selesai'
GROUP BY pb.id_pembeli, pb.nama_pembeli
ORDER BY total_pengeluaran DESC
LIMIT 5;