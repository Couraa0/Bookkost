-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Jun 2025 pada 13.19
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `booking_kost`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `kost_id` int(11) DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `kost_id`, `check_in`, `check_out`, `total_price`, `status`, `payment_method`, `payment_status`, `created_at`) VALUES
(1, 2, 1, '2025-05-29', '2025-08-29', 4500000.00, 'completed', 'Transfer Bank', 'paid', '2025-05-29 09:10:02'),
(2, 2, 2, '2025-05-30', '2025-06-30', 1200000.00, 'completed', 'Transfer Bank', 'paid', '2025-05-30 06:19:28'),
(30, 2, 24, '2025-05-30', '2025-06-30', 1400000.00, 'completed', 'Transfer_Bank', 'paid', '2025-05-30 15:17:18'),
(31, 23, 22, '2025-05-30', '2025-06-30', 1300000.00, 'completed', 'Transfer Bank', 'paid', '2025-05-30 15:24:47'),
(34, 2, 23, '2025-05-31', '2025-07-01', 1350000.00, 'completed', 'bank_transfer', 'paid', '2025-05-31 03:42:53'),
(35, 23, 25, '2025-05-31', '2025-08-31', 4350000.00, 'completed', 'ewallet', 'paid', '2025-05-31 03:55:16'),
(36, 24, 26, '2025-05-31', '2026-05-31', 18000000.00, 'completed', 'qris', 'paid', '2025-05-31 06:09:26'),
(37, 24, 27, '2025-05-31', '2025-08-31', 4650000.00, 'completed', 'minimarket', 'paid', '2025-05-31 06:12:12'),
(38, 24, 23, '2025-05-31', '2025-07-01', 1350000.00, 'confirmed', 'ewallet', 'paid', '2025-05-31 06:38:30'),
(39, 24, 22, '2025-05-31', '2025-08-31', 3900000.00, 'confirmed', 'qris', 'paid', '2025-05-31 10:08:24'),
(40, 2, 32, '2025-06-08', '2025-07-08', 1800000.00, 'pending', 'qris', 'paid', '2025-06-08 13:43:42'),
(41, 2, 41, '2025-06-10', '2025-07-10', 1200000.00, 'completed', 'qris', 'paid', '2025-06-10 10:58:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kost_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `kost_id`, `created_at`) VALUES
(4, 2, 22, '2025-05-30 15:07:17'),
(5, 23, 22, '2025-05-30 15:24:37'),
(6, 24, 26, '2025-05-31 06:09:15'),
(7, 24, 23, '2025-05-31 06:38:06'),
(8, 24, 22, '2025-05-31 10:08:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kost`
--

CREATE TABLE `kost` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `address` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `facilities` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `virtual_tour` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kost`
--

INSERT INTO `kost` (`id`, `owner_id`, `name`, `description`, `address`, `price`, `facilities`, `images`, `status`, `created_at`, `virtual_tour`) VALUES
(1, 4, 'Kost Mawar Indah', 'Kost nyaman dan strategis', 'Jl. Mawar No. 123, Jakarta', 1500000.00, 'AC, WiFi, Kamar Mandi Dalam, Parkir, Kasur', 'img/Kost1.jpg', 'active', '2025-05-28 16:34:17', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(2, 4, 'Kost Melati Asri', 'Kost dengan fasilitas lengkap', 'Jl. Melati No. 456, Bandung', 1200000.00, 'AC, WiFi, Dapur Bersama, Laundry', 'img/Kost2.jpg', 'active', '2025-05-28 16:34:17', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(22, 4, 'Kost Melati Bandung', 'Kost bersih dan aman', 'Jl. Cihampelas No. 21, Bandung', 1300000.00, 'AC, WiFi, Kamar Mandi Dalam', 'img/Kost3.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(23, 5, 'Kost Sakura Surabaya', 'Lingkungan nyaman', 'Jl. Darmo Permai No. 8, Surabaya', 1350000.00, 'WiFi, Laundry, Dapur Bersama', 'img/Kost4.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(24, 6, 'Kost Cemara Yogyakarta', 'Dekat kampus', 'Jl. Kaliurang Km 5, Yogyakarta', 1400000.00, 'AC, WiFi, Dapur Bersama', 'img/Kost5.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(25, 7, 'Kost Flamboyan Medan', 'Fasilitas lengkap', 'Jl. Gajah Mada No. 10, Medan', 1450000.00, 'AC, WiFi, Laundry, Parkir', 'img/Kost6.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(26, 8, 'Kost Bougenville Makassar', 'Harga terjangkau', 'Jl. Pengayoman No. 7, Makassar', 1500000.00, 'AC, WiFi, Kamar Mandi Dalam', 'img/Kost7.jpeg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(27, 9, 'Kost Kenanga Malang', 'Kost nyaman', 'Jl. Soekarno Hatta No. 3, Malang', 1550000.00, 'AC, WiFi, Laundry', 'img/Kost8.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(28, 10, 'Kost Teratai Semarang', 'Dekat pusat kota', 'Jl. Pandanaran No. 5, Semarang', 1600000.00, 'AC, WiFi, Kamar Mandi Dalam', 'img/Kost9.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(29, 11, 'Kost Dahlia Bali', 'Bersih dan asri', 'Jl. Sunset Road No. 99, Denpasar', 1650000.00, 'AC, WiFi, Parkir', 'img/Kost10.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(30, 12, 'Kost Mawar Depok', 'Kost putri eksklusif', 'Jl. Margonda Raya No. 11, Depok', 1700000.00, 'AC, WiFi, Kamar Mandi Dalam', 'img/Kost11.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(31, 13, 'Kost Kamboja Bogor', 'Dekat stasiun', 'Jl. Pajajaran No. 12, Bogor', 1750000.00, 'AC, WiFi, Laundry', 'img/Kost12.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(32, 14, 'Kost Angsana Palembang', 'Strategis dan tenang', 'Jl. Sudirman No. 3, Palembang', 1800000.00, 'AC, WiFi, Dapur Bersama', 'img/Kost13.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(33, 15, 'Kost Lavender Pontianak', 'Kost dengan balkon', 'Jl. Teuku Umar No. 14, Pontianak', 1850000.00, 'AC, WiFi, Balkon', 'img/Kost14.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(34, 16, 'Kost Seruni Pekanbaru', 'Full furnished', 'Jl. Soekarno Hatta No. 25, Pekanbaru', 1900000.00, 'AC, WiFi, Furnitur Lengkap', 'img/Kost15.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(35, 17, 'Kost Lili Tangerang', 'Privasi terjaga', 'Jl. BSD Raya No. 16, Tangerang', 1950000.00, 'AC, WiFi, Kamar Mandi Dalam', 'img/Kost16.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(36, 18, 'Kost Alamanda Bekasi', 'Dekat mall dan kampus', 'Jl. Ahmad Yani No. 88, Bekasi', 2000000.00, 'AC, WiFi, Parkir', 'img/Kost17.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(37, 19, 'Kost Teratai Putih Balikpapan', 'Akses 24 jam', 'Jl. MT Haryono No. 17, Balikpapan', 2050000.00, 'AC, WiFi, Keamanan 24 jam', 'img/Kost18.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(38, 20, 'Kost Sakura Ungu Manado', 'Kost campur', 'Jl. Sam Ratulangi No. 4, Manado', 2100000.00, 'AC, WiFi, Laundry', 'img/Kost19.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(39, 21, 'Kost Bougenville Biru Banjarmasin', 'Lingkungan asri', 'Jl. A. Yani Km 6, Banjarmasin', 2150000.00, 'AC, WiFi, Dapur Bersama', 'img/Kost20.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(40, 22, 'Kost Puspita Jayapura', 'Fasilitas mewah', 'Jl. Percetakan Negara No. 9, Jayapura', 2200000.00, 'AC, WiFi, Laundry, Gym', 'img/Kost21.jpg', 'active', '2025-05-30 06:50:18', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(41, 4, 'Kost Ajinomoto', 'Kost bagus, murah, deket kampus', 'Jalan Unsika No 1/24 -6.4044536,107.0893913', 1200000.00, 'AC, WiFi, Kamar Mandi Dalam, Parkir', 'img/kost_41_1748686401.jpg', 'active', '2025-05-31 09:26:28', 'https://www.youtube.com/watch?v=FicdWhMgadQ'),
(44, NULL, 'Kost Super Memet', 'Kost Murah, Fasilitas Terbagus', 'Karawang, Telukjambe Timur, Jalan Juki No 13', 300000.00, 'Kulkas Sharing, Kamar Mandi Luar, dan Keamanan', 'img/kost_1749543882_7204.jpg', 'active', '2025-06-10 08:24:42', 'https://www.youtube.com/watch?v=FicdWhMgadQ');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kost_reports`
--

CREATE TABLE `kost_reports` (
  `id` int(11) NOT NULL,
  `kost_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reason` varchar(255) NOT NULL,
  `detail` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `status` enum('pending','reviewed','dismissed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kost_reports`
--

INSERT INTO `kost_reports` (`id`, `kost_id`, `user_id`, `reason`, `detail`, `created_at`, `status`) VALUES
(1, 41, 2, 'Penipuan', 'Kebanyakan micin', '2025-06-08 20:53:48', 'reviewed');

-- --------------------------------------------------------

--
-- Struktur dari tabel `owner_requests`
--

CREATE TABLE `owner_requests` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ktp_image` varchar(255) NOT NULL,
  `selfie_image` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `kost_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `reviews`
--

INSERT INTO `reviews` (`id`, `booking_id`, `user_id`, `kost_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 2, 1, 5, 'Kost sangat bersih dan nyaman!', '2025-05-01 03:30:00'),
(2, 2, 2, 2, 5, 'Pemilik ramah dan lokasi strategis.', '2025-05-05 05:00:00'),
(4, 30, 2, 24, 4, 'Kostnya bersih, tapi suka ada tukang galon genit', '2025-05-30 15:21:40'),
(5, 31, 23, 22, 3, 'Penghuni kostnya suka berisik, tapi bapa kostnya ramah bangettt', '2025-05-30 15:27:23'),
(6, 34, 2, 23, 5, 'Mantul fasilitasnya!!!', '2025-05-31 03:47:35'),
(7, 35, 23, 25, 5, 'Lingkungan aman, damai, dan fasilitas mantul punya!', '2025-05-31 03:58:05'),
(8, 36, 24, 26, 5, 'Kostnya estetik, worth it banget!', '2025-05-31 06:11:44'),
(9, 37, 24, 27, 4, 'Bersih, rapih, wangi kostnya. Tapi kadang suka bocor atapnya!', '2025-05-31 06:13:26'),
(10, 38, 24, 23, 4, 'Mantul abis, kurang ajinomoto aja', '2025-05-31 06:41:21'),
(11, 39, 24, 22, 5, 'Mantul abis, kurang masako aja di dapur', '2025-05-31 10:11:11'),
(12, 41, 2, 41, 1, 'Pemilik kostnya tidak ramah', '2025-06-10 10:58:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('user','owner','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@bookkost.com', 'admin123', 'Admin BookKost', NULL, 'admin', '2025-05-28 16:34:17'),
(2, 'Rakha', 'rakha@gmail.com', 'rakha2005', 'Muhammad Rakha Syamputra', '087871310560', 'user', '2025-05-28 16:36:37'),
(3, 'admin2', 'admin2@bookkost.com', '$2y$10$eW5GmQfJK7gqkOX1ZTl6jOdU7fQnnh2Iu5hRlLxhlTGde6QAw4Z4y', 'AdminDua', '081234567890', 'admin', '2025-05-29 07:45:36'),
(4, 'Juki', 'Juki@gmail.com', 'Juki123', 'Marjuki Daud', '081298765432', 'owner', '2025-05-29 08:57:41'),
(5, 'melati', 'melati@bookkost.com', 'passmelati', 'Siti Melati', '081111111111', 'owner', '2025-05-30 06:50:13'),
(6, 'sakura', 'sakura@bookkost.com', 'passsakura', 'Yuki Sakura', '081222222222', 'owner', '2025-05-30 06:50:13'),
(7, 'cemara', 'cemara@bookkost.com', 'passcemara', 'Budi Cemara', '081333333333', 'owner', '2025-05-30 06:50:13'),
(8, 'flamboyan', 'flamboyan@bookkost.com', 'passflamboyan', 'Rina Flamboyan', '081444444444', 'owner', '2025-05-30 06:50:13'),
(9, 'bougenville', 'bougenville@bookkost.com', 'passbougenville', 'Andi Bougenville', '081555555555', 'owner', '2025-05-30 06:50:13'),
(10, 'kenanga', 'kenanga@bookkost.com', 'passkenanga', 'Dewi Kenanga', '081666666666', 'owner', '2025-05-30 06:50:13'),
(11, 'teratai', 'teratai@bookkost.com', 'pasteratai', 'Hendra Teratai', '081777777777', 'owner', '2025-05-30 06:50:13'),
(12, 'dahlia', 'dahlia@bookkost.com', 'passdahlia', 'Putri Dahlia', '081888888888', 'owner', '2025-05-30 06:50:13'),
(13, 'mawar', 'mawar@bookkost.com', 'passmawar', 'Rahmat Mawar', '081999999999', 'owner', '2025-05-30 06:50:13'),
(14, 'kamboja', 'kamboja@bookkost.com', 'passkamboja', 'Tina Kamboja', '082111111111', 'owner', '2025-05-30 06:50:13'),
(15, 'angsana', 'angsana@bookkost.com', 'passangsana', 'Galih Angsana', '082222222222', 'owner', '2025-05-30 06:50:13'),
(16, 'lavender', 'lavender@bookkost.com', 'passlavender', 'Fitri Lavender', '082333333333', 'owner', '2025-05-30 06:50:13'),
(17, 'seruni', 'seruni@bookkost.com', 'passseruni', 'Heri Seruni', '082444444444', 'owner', '2025-05-30 06:50:13'),
(18, 'lili', 'lili@bookkost.com', 'passlili', 'Dina Lili', '082555555555', 'owner', '2025-05-30 06:50:13'),
(19, 'alamanda', 'alamanda@bookkost.com', 'passalamanda', 'Yudi Alamanda', '082666666666', 'owner', '2025-05-30 06:50:13'),
(20, 'terataiputih', 'terataiputih@bookkost.com', 'passterataiputih', 'Rani Teratai Putih', '082777777777', 'owner', '2025-05-30 06:50:13'),
(21, 'sakuraungu', 'sakuraungu@bookkost.com', 'passsakuraungu', 'Krisna Sakura Ungu', '082888888888', 'owner', '2025-05-30 06:50:13'),
(22, 'bougenvillebiru', 'bougenvillebiru@bookkost.com', 'passbougenvillebiru', 'Eka Bougenville Biru', '082999999999', 'owner', '2025-05-30 06:50:13'),
(23, 'Rizky', 'anakjuki@gmail.com', 'Rizky123', 'Rizky Azhari Juki', '085311803904', 'user', '2025-05-30 15:23:48'),
(24, 'Yuuka', 'yuukaajinomoto@gmail.com', 'Yuuka123', 'Yuuka Natasya Ajinomoto', '089643169832', 'user', '2025-05-31 06:08:21'),
(30, 'Mamat', 'mamat@gmail.com', '123', 'Aji Mamat', '089888877766', 'owner', '2025-06-10 09:58:13');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `kost_id` (`kost_id`);

--
-- Indeks untuk tabel `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`kost_id`),
  ADD KEY `fk_favorites_kost` (`kost_id`);

--
-- Indeks untuk tabel `kost`
--
ALTER TABLE `kost`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indeks untuk tabel `kost_reports`
--
ALTER TABLE `kost_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kost_id` (`kost_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `owner_requests`
--
ALTER TABLE `owner_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `kost_id` (`kost_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT untuk tabel `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `kost`
--
ALTER TABLE `kost`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT untuk tabel `kost_reports`
--
ALTER TABLE `kost_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `owner_requests`
--
ALTER TABLE `owner_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`kost_id`) REFERENCES `kost` (`id`);

--
-- Ketidakleluasaan untuk tabel `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_favorites_kost` FOREIGN KEY (`kost_id`) REFERENCES `kost` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kost`
--
ALTER TABLE `kost`
  ADD CONSTRAINT `kost_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `kost_reports`
--
ALTER TABLE `kost_reports`
  ADD CONSTRAINT `kost_reports_ibfk_1` FOREIGN KEY (`kost_id`) REFERENCES `kost` (`id`),
  ADD CONSTRAINT `kost_reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`kost_id`) REFERENCES `kost` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
