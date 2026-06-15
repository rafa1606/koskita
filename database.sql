SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- database

-- area
CREATE TABLE `area` (
  `id` int(11) NOT NULL,
  `nama_area` varchar(100) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- data
INSERT INTO `area` (`id`, `nama_area`, `latitude`, `longitude`) VALUES
(1, 'UGM (Universitas Gadjah Mada)', -7.7713000, 110.3776000),
(2, 'UNY (Universitas Negeri Yogyakarta)', -7.7752000, 110.3878000),
(3, 'UPN Veteran Yogyakarta', -7.7619000, 110.4081000),
(4, 'UII Terpadu (Kaliurang)', -7.6873000, 110.4147000),
(5, 'Malioboro', -7.7926000, 110.3658000);

-- kamar
CREATE TABLE `kamar` (
  `id` int(11) NOT NULL,
  `id_kos` int(11) NOT NULL,
  `id_tipe` int(11) NOT NULL,
  `nomor_kamar` varchar(20) NOT NULL,
  `harga` int(11) NOT NULL,
  `fasilitas` varchar(255) DEFAULT NULL,
  `status` enum('tersedia','penuh') NOT NULL DEFAULT 'tersedia',
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- data
INSERT INTO `kamar` (`id`, `id_kos`, `id_tipe`, `nomor_kamar`, `harga`, `fasilitas`, `status`, `foto`) VALUES
(1, 1, 6, 'A1', 900000, 'Kasur, lemari, meja belajar, WiFi', 'tersedia', NULL),
(2, 1, 2, 'A2', 1300000, 'Kasur 2, lemari, meja, WiFi, kamar luas', 'tersedia', NULL),
(3, 1, 3, 'A3', 1800000, 'AC, kamar mandi dalam, WiFi, water heater', 'penuh', NULL),
(4, 2, 6, 'B1', 1000000, 'Kasur, lemari, WiFi, dapur bersama', 'tersedia', NULL),
(5, 2, 3, 'B2', 2000000, 'AC, kamar mandi dalam, smart TV, WiFi cepat', 'tersedia', NULL),
(6, 3, 7, 'C1', 850000, 'Kasur, lemari, meja, WiFi', 'tersedia', NULL),
(7, 3, 2, 'C2', 1250000, 'Kasur 2, lemari besar, WiFi', 'tersedia', NULL),
(8, 4, 6, 'D1', 800000, 'Kasur, lemari, kipas angin, WiFi', 'tersedia', NULL),
(9, 4, 3, 'D2', 1600000, 'AC, kamar mandi dalam, WiFi', 'tersedia', NULL),
(10, 5, 2, 'E1', 1400000, 'Kasur 2, lemari, meja, WiFi, balkon', 'tersedia', NULL),
(11, 5, 3, 'E2', 1900000, 'AC, kamar mandi dalam, water heater, WiFi', 'penuh', NULL),
(12, 6, 7, 'F1', 750000, 'Kasur, lemari, WiFi', 'tersedia', NULL),
(13, 6, 2, 'F2', 1150000, 'Kasur 2, lemari, meja, WiFi', 'tersedia', NULL),
(14, 7, 7, 'G1', 1100000, 'Kasur, lemari, WiFi, lokasi pusat kota', 'tersedia', NULL),
(15, 7, 3, 'G2', 2100000, 'AC, kamar mandi dalam, WiFi, dekat Malioboro', 'tersedia', NULL),
(16, 1, 4, 'A4', 1100000, 'AC, kasur, lemari, meja belajar, WiFi', 'tersedia', NULL),
(17, 2, 4, 'B3', 1200000, 'AC, kasur, lemari, WiFi, dapur bersama', 'tersedia', NULL),
(18, 3, 4, 'C3', 1050000, 'AC, kasur, lemari, meja, WiFi', 'tersedia', NULL),
(19, 4, 1, 'D3', 750000, 'Kasur, lemari, kipas angin, kamar mandi luar, WiFi', 'tersedia', NULL),
(20, 5, 1, 'E3', 800000, 'Kasur, lemari, meja, kamar mandi luar, WiFi', 'tersedia', NULL),
(21, 6, 1, 'F3', 700000, 'Kasur, lemari, kamar mandi luar, WiFi', 'tersedia', NULL),
(22, 8, 4, 'A12', 1170000, 'WIFI, KAMAR MANDI DALAM, AC', 'tersedia', ''),
(23, 8, 5, 'A13', 8000000, 'WIFI, KAMAR MANDI DALAM', 'tersedia', '');

-- kos
CREATE TABLE `kos` (
  `id` int(11) NOT NULL,
  `id_pemilik` int(11) DEFAULT NULL,
  `nama_kos` varchar(120) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- data
INSERT INTO `kos` (`id`, `id_pemilik`, `nama_kos`, `alamat`, `latitude`, `longitude`, `deskripsi`, `foto`, `created_at`) VALUES
(1, 1, 'Kos Pogung Indah', 'Jl. Pogung Lor, Sinduadi, Sleman', -7.7665000, 110.3735000, 'Kos eksklusif dekat UGM, lingkungan tenang dan strategis.', 'kos_6a22fa4f7ccc01.54425388.jpg', '2026-06-04 11:27:30'),
(2, 1, 'Kos Cendana Sagan', 'Jl. Sagan Kidul, Terban, Yogyakarta', -7.7820000, 110.3790000, 'Lokasi premium di Sagan, akses mudah ke kampus dan pusat kota.', 'kos_6a22fa5e32f464.84332566.jpg', '2026-06-04 11:27:30'),
(3, 1, 'Kos Klebengan Residence', 'Jl. Klebengan, Caturtunggal, Sleman', -7.7672000, 110.3850000, 'Kos modern dengan parkir luas, dekat banyak kampus.', 'kos_6a22fa6c0d7d16.97041579.jpg', '2026-06-04 11:27:30'),
(4, 1, 'Kos Karangmalang Asri', 'Jl. Karangmalang, Caturtunggal, Sleman', -7.7745000, 110.3895000, 'Bersebelahan dengan UNY, cocok untuk mahasiswa.', 'kos_6a22fa79018019.90326467.jpg', '2026-06-04 11:27:30'),
(5, 1, 'Kos Anggrek Seturan', 'Jl. Seturan Raya, Caturtunggal, Sleman', -7.7820000, 110.4020000, 'Area Seturan yang ramai, dekat banyak kuliner dan kafe.', 'kos_6a22fa84899468.98049106.jpg', '2026-06-04 11:27:30'),
(6, 1, 'Kos Mawar Condongcatur', 'Jl. Anggajaya, Condongcatur, Sleman', -7.7585000, 110.4045000, 'Dekat UPN Veteran, suasana asri dan nyaman.', 'kos_6a22fa8fbc5f86.56907150.jpg', '2026-06-04 11:27:30'),
(7, 1, 'Kos Melati Malioboro', 'Jl. Sosrowijayan, Sosromenduran, Yogya', -7.7960000, 110.3640000, 'Di jantung kota, beberapa langkah dari Malioboro.', 'kos_6a22fa98909778.80952355.jpg', '2026-06-04 11:27:30'),
(8, 9, 'Kos Omah Ayu', 'klebengan Sumberadi Mlati, belakanh burjo Kabogoh, Jl. Jeruk No.10, Kocoran, Caturtunggal, Kec. Depok, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55281', -7.7653115, 110.3844985, '', 'kos_6a2d5a838f7467.00103179.jpeg', '2026-06-13 13:26:27');

-- reservasi
CREATE TABLE `reservasi` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_kamar` int(11) NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `durasi` int(11) NOT NULL,
  `status` enum('pending','diterima','ditolak') NOT NULL DEFAULT 'pending',
  `tanggal_pesan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- data
INSERT INTO `reservasi` (`id`, `id_user`, `id_kamar`, `tanggal_masuk`, `durasi`, `status`, `tanggal_pesan`) VALUES
(1, 2, 1, '2025-07-01', 6, 'diterima', '2026-06-04 11:27:30'),
(2, 3, 5, '2025-07-15', 12, 'diterima', '2026-06-04 11:27:30'),
(3, 2, 10, '2025-08-01', 3, 'ditolak', '2026-06-04 11:27:30'),
(4, 4, 9, '2026-06-24', 2, 'diterima', '2026-06-04 17:13:24'),
(6, 2, 21, '2026-06-15', 3, 'pending', '2026-06-13 18:39:18');

-- tipe
CREATE TABLE `tipe_kamar` (
  `id` int(11) NOT NULL,
  `nama_tipe` varchar(50) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- data
INSERT INTO `tipe_kamar` (`id`, `nama_tipe`, `deskripsi`) VALUES
(1, 'Kamar Mandi Luar', 'Kamar standar dengan kamar mandi bersama di luar.'),
(2, 'Kamar Mandi Dalam', 'Kamar dengan kamar mandi pribadi di dalam kamar.'),
(3, 'Eksklusif', 'Kamar premium dengan AC, kamar mandi dalam, dan fasilitas lengkap.'),
(4, 'AC', 'Kamar dengan fasilitas AC, cocok untuk iklim panas.'),
(5, 'Non AC', 'Kamar tanpa AC, harga lebih terjangkau.'),
(6, 'Kos Putra', 'Khusus penghuni laki-laki.'),
(7, 'Kos Putri', 'Khusus penghuni perempuan.');

-- user
CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('penyewa','pemilik','admin') DEFAULT 'penyewa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- data
INSERT INTO `user` (`id`, `nama`, `email`, `telepon`, `foto_profil`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@koskita.com', '', 'user_1_1780639089.png', '$2y$10$lwTZI0irHxdYkm7lQW58H.Ebi3xulKTyOonMSJcfU7BM/b2EgZBKe', 'admin', '2026-06-04 11:27:30'),
(2, 'Budi Santoso', 'budi@gmail.com', NULL, NULL, '$2y$10$5d40MBzwt2yxWQdKEBk.Y.cIwVfSpKxXeJcz5hMNhRfZGxtBKX5HG', 'penyewa', '2026-06-04 11:27:30'),
(3, 'Siti Nurhaliza', 'siti@gmail.com', NULL, NULL, '$2y$10$tseHuazb7o/pYQK3SDdXL.EGF5VEjuSfaQWINwduB2O94dPQGkmRi', 'penyewa', '2026-06-04 11:27:30'),
(4, 'Messi GOAT', 'rafairham80@gmail.com', NULL, NULL, '$2y$10$lJ/LnnSibGeGM.rsy6DWSuFLVgMHs7wBI3eTW929kdrcntqVc4hVG', 'penyewa', '2026-06-04 13:37:56'),
(9, 'Asep Saipudin', 'asep@koskita.com', '', 'user_9_1780651272.png', '$2y$10$h4D035zpjLgRFO439odS2OONMbRqkWYl9T9r174C/N/1XHAVvGLSS', 'pemilik', '2026-06-05 07:52:32');

-- indexes

-- area
ALTER TABLE `area`
  ADD PRIMARY KEY (`id`);

-- kamar
ALTER TABLE `kamar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kamar_kos` (`id_kos`),
  ADD KEY `fk_kamar_tipe` (`id_tipe`);

-- kos
ALTER TABLE `kos`
  ADD PRIMARY KEY (`id`);

-- reservasi
ALTER TABLE `reservasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_resv_user` (`id_user`),
  ADD KEY `fk_resv_kamar` (`id_kamar`);

-- tipe
ALTER TABLE `tipe_kamar`
  ADD PRIMARY KEY (`id`);

-- user
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

-- increment

-- area
ALTER TABLE `area`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- kamar
ALTER TABLE `kamar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

-- kos
ALTER TABLE `kos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

-- reservasi
ALTER TABLE `reservasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

-- tipe
ALTER TABLE `tipe_kamar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

-- user
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

-- constraints

-- kamar
ALTER TABLE `kamar`
  ADD CONSTRAINT `fk_kamar_kos` FOREIGN KEY (`id_kos`) REFERENCES `kos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_kamar_tipe` FOREIGN KEY (`id_tipe`) REFERENCES `tipe_kamar` (`id`);

-- reservasi
ALTER TABLE `reservasi`
  ADD CONSTRAINT `fk_resv_kamar` FOREIGN KEY (`id_kamar`) REFERENCES `kamar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_resv_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
