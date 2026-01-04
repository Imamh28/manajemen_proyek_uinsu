-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2026 at 03:52 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `manajemen_proyek_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `daftar_tahapans`
--

CREATE TABLE `daftar_tahapans` (
  `id_tahapan` varchar(255) NOT NULL,
  `nama_tahapan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daftar_tahapans`
--

INSERT INTO `daftar_tahapans` (`id_tahapan`, `nama_tahapan`) VALUES
('TH01', 'Briefing & Perencanaan'),
('TH02', 'Desain & Mockup'),
('TH03', 'Pengembangan'),
('TH04', 'Revisi & UAT'),
('TH05', 'Deployment'),
('TH06', 'Selesai');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_proyeks`
--

CREATE TABLE `jadwal_proyeks` (
  `id_jadwal` varchar(255) NOT NULL,
  `plan_mulai` date NOT NULL,
  `mulai` date DEFAULT NULL,
  `durasi` int(11) DEFAULT NULL,
  `plan_selesai` date NOT NULL,
  `selesai` date DEFAULT NULL,
  `status` enum('Sesuai Jadwal','Terlambat','Lebih Cepat','Belum Mulai') NOT NULL,
  `proyek_id_proyek` varchar(45) NOT NULL,
  `daftar_tahapans_id_tahapan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_proyeks`
--

INSERT INTO `jadwal_proyeks` (`id_jadwal`, `plan_mulai`, `mulai`, `durasi`, `plan_selesai`, `selesai`, `status`, `proyek_id_proyek`, `daftar_tahapans_id_tahapan`) VALUES
('JDL001', '2026-01-04', '2026-01-03', 4, '2026-01-07', '2026-01-03', 'Lebih Cepat', 'PRJ001', 'TH01');

-- --------------------------------------------------------

--
-- Table structure for table `karyawan`
--

CREATE TABLE `karyawan` (
  `id_karyawan` varchar(45) NOT NULL,
  `nama_karyawan` varchar(45) NOT NULL,
  `no_telepon_karyawan` varchar(45) DEFAULT NULL,
  `email` varchar(45) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id_role` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id_karyawan`, `nama_karyawan`, `no_telepon_karyawan`, `email`, `password`, `role_id_role`) VALUES
('KR001', 'Andi Wijaya', '081234567890', 'andi.wijaya@example.com', '$2y$10$610WSeUriFJuaUIZ5iKbI./JnYAc58n3japG6kqnB9j6yEZMAdNtW', 'RL002'),
('KR002', 'Budi Santoso', '081234567891', 'budi.santoso@example.com', '$2y$10$9vaUh0DVUu2q7F7GU2TZ8uEhoESqq6ASdln3OUIiHX8UX.MPqNSWS', 'RL003'),
('KR003', 'Citra Lestari', '081234567892', 'citra.lestari@example.com', '$2y$10$xRwQSZdJnnimHbuYDIN6BuJYZj2Avwpmx8o9zk8QyAB67Ihnfls.u', 'RL001'),
('KR004', 'Dewi Anggraini', '081234567893', 'dewi.anggraini@example.com', '$2y$10$MI8s6wpx0iiPwcfZ8VPbUe8khDxF7yMGY7X8bs7Gfo0RnRklJ.3.6', 'RL002'),
('KR005', 'Imam Hatris Ekaputra', '081265230266', 'imammakmum@gmail.com', '$2y$10$bK/Br3O.3y81A0E2MMMi3OH0w4wU/4ESkpcjvzUHjBiz/dIcmvwty', 'RL002'),
('KR006', 'Muhammad Azis', '0812345678910', 'azizsyahputra0311@gmail.com', '$2y$10$Wez72uRVCrAbJUMqmyk5y.3qvGtLSsP1RH5wI53eNnspxWR6q/ZWW', 'RL003'),
('KR007', 'M Ariyo Syahraza', '0812345678901', 'ariyo@gmail.com', '$2y$10$QK6NhY0qXklcKVMhpsRXFeWfjLXeyzXTL8PrF3F4P/PiUFU4JUYi6', 'RL003');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_menus`
--

CREATE TABLE `kategori_menus` (
  `id_kategori_menus` int(11) NOT NULL,
  `nama_kategori_menu` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori_menus`
--

INSERT INTO `kategori_menus` (`id_kategori_menus`, `nama_kategori_menu`) VALUES
(1, 'Manajemen Utama'),
(2, 'Proyek & Klien'),
(3, 'Pengaturan');

-- --------------------------------------------------------

--
-- Table structure for table `klien`
--

CREATE TABLE `klien` (
  `id_klien` varchar(45) NOT NULL,
  `nama_klien` varchar(45) NOT NULL,
  `no_telepon_klien` varchar(45) DEFAULT NULL,
  `email_klien` varchar(45) DEFAULT NULL,
  `alamat_klien` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `klien`
--

INSERT INTO `klien` (`id_klien`, `nama_klien`, `no_telepon_klien`, `email_klien`, `alamat_klien`) VALUES
('KL001', 'PT. Maju Mundur', '0215550101', 'kontak@majumundur.com', 'Jl. Jend. Sudirman Kav. 52-53, Jakarta'),
('KL002', 'CV. Sejahtera Bersama', '031770202', 'info@sejahterabersama.co.id', 'Jl. Basuki Rahmat No. 12, Surabaya'),
('KL003', 'PT. Wahana Wihana', '0213456789', 'wahanawihana@yahoo.com', 'Jl. Wahana Wijaya No. 6, Kec. Medan Amplas');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id_menu` int(11) NOT NULL,
  `nama_menu` varchar(45) NOT NULL,
  `url` varchar(255) NOT NULL,
  `kategori_menus_id_kategori_menus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id_menu`, `nama_menu`, `url`, `kategori_menus_id_kategori_menus`) VALUES
(1, 'Dashboard', '/dashboard', 1),
(2, 'Daftar Proyek', '/proyek', 2),
(3, 'Daftar Klien', '/klien', 2),
(4, 'Manajemen Karyawan', '/karyawan', 3),
(5, 'Pengaturan Role', '/roles', 3);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` varchar(16) NOT NULL,
  `title` varchar(120) NOT NULL,
  `body` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `link_admin` varchar(255) DEFAULT NULL,
  `link_pm` varchar(255) DEFAULT NULL,
  `link_mandor` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `body`, `link`, `link_admin`, `link_pm`, `link_mandor`, `is_read`, `created_at`) VALUES
(131, 'KR004', 'Proyek Baru Ditambahkan', 'Proyek PRJ001 baru saja ditambahkan. Status awal: Menunggu.', 'index.php?r=dashboard', 'index.php?r=proyek', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-02 00:12:40'),
(132, 'KR005', 'Proyek Baru Ditambahkan', 'Proyek PRJ001 baru saja ditambahkan. Status awal: Menunggu.', 'index.php?r=dashboard', 'index.php?r=proyek', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-02 00:12:40'),
(134, 'KR006', 'Proyek Baru Ditambahkan', 'Proyek PRJ001 baru saja ditambahkan. Status awal: Menunggu.', 'index.php?r=dashboard', 'index.php?r=proyek', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-02 00:12:40'),
(135, 'KR007', 'Proyek Baru Ditambahkan', 'Proyek PRJ001 baru saja ditambahkan. Status awal: Menunggu.', 'index.php?r=dashboard', 'index.php?r=proyek', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-02 00:12:40'),
(137, 'KR004', 'Pembayaran Ditambahkan', 'Pembayaran PAY (DP) untuk proyek PRJ001 berhasil ditambahkan.', 'index.php?r=dashboard', 'index.php?r=pembayaran', 'index.php?r=pembayaran', 'index.php?r=dashboard', 0, '2026-01-02 01:19:20'),
(138, 'KR005', 'Pembayaran Ditambahkan', 'Pembayaran PAY (DP) untuk proyek PRJ001 berhasil ditambahkan.', 'index.php?r=dashboard', 'index.php?r=pembayaran', 'index.php?r=pembayaran', 'index.php?r=dashboard', 0, '2026-01-02 01:19:20'),
(140, 'KR004', 'Proyek Baru Ditambahkan', 'Proyek PRJ001 (Sembako Peduli Sumatera) ditambahkan. Status: Menunggu. Lakukan pembayaran terlebih dahulu, setelah itu PM membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=proyek', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-02 20:30:18'),
(141, 'KR005', 'Proyek Baru Ditambahkan', 'Proyek PRJ001 (Sembako Peduli Sumatera) ditambahkan. Status: Menunggu. Lakukan pembayaran terlebih dahulu, setelah itu PM membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=proyek', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-02 20:30:18'),
(143, 'KR004', 'Pembayaran Ditambahkan', 'Pembayaran PAY001 untuk proyek PRJ001 (Jenis: DP) berhasil ditambahkan. PM dapat membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=pembayaran', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-02 20:33:43'),
(144, 'KR005', 'Pembayaran Ditambahkan', 'Pembayaran PAY001 untuk proyek PRJ001 (Jenis: DP) berhasil ditambahkan. PM dapat membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=pembayaran', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-02 20:33:43'),
(149, 'KR004', 'Pengajuan Tahapan Baru', 'Mandor mengajukan TH01 untuk proyek PRJ001.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=tahapan-aktif&proyek=PRJ001', 0, '2026-01-02 20:49:54'),
(150, 'KR005', 'Pengajuan Tahapan Baru', 'Mandor mengajukan TH01 untuk proyek PRJ001.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=tahapan-aktif&proyek=PRJ001', 0, '2026-01-02 20:49:54'),
(156, 'KR004', 'Proyek Baru Ditambahkan', 'Proyek PRJ001 (Sembako Peduli Sumatera) ditambahkan. Status: Menunggu. Lakukan pembayaran terlebih dahulu, setelah itu PM membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=proyek', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-02 23:33:06'),
(157, 'KR005', 'Proyek Baru Ditambahkan', 'Proyek PRJ001 (Sembako Peduli Sumatera) ditambahkan. Status: Menunggu. Lakukan pembayaran terlebih dahulu, setelah itu PM membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=proyek', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-02 23:33:06'),
(159, 'KR004', 'Pembayaran Ditambahkan', 'Pembayaran PAY001 untuk proyek PRJ001 (Jenis: DP) berhasil ditambahkan. PM dapat membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=pembayaran', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-02 23:34:10'),
(160, 'KR005', 'Pembayaran Ditambahkan', 'Pembayaran PAY001 untuk proyek PRJ001 (Jenis: DP) berhasil ditambahkan. PM dapat membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=pembayaran', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-02 23:34:10'),
(165, 'KR004', 'Pengajuan Tahapan Baru', 'Mandor mengajukan TH01 untuk proyek PRJ001.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=tahapan-aktif&proyek=PRJ001', 0, '2026-01-02 23:37:50'),
(166, 'KR005', 'Pengajuan Tahapan Baru', 'Mandor mengajukan TH01 untuk proyek PRJ001.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=tahapan-aktif&proyek=PRJ001', 0, '2026-01-02 23:37:50'),
(173, 'KR004', 'Pengajuan Tahapan Baru', 'Mandor mengajukan TH02 untuk proyek PRJ001.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=tahapan-aktif&proyek=PRJ001', 0, '2026-01-02 23:45:58'),
(174, 'KR005', 'Pengajuan Tahapan Baru', 'Mandor mengajukan TH02 untuk proyek PRJ001.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=tahapan-aktif&proyek=PRJ001', 0, '2026-01-02 23:45:58'),
(178, 'KR004', 'Proyek Baru Ditambahkan', 'Proyek PRJ001 (Sembako Untuk Sumatera) ditambahkan. Status: Menunggu. Lakukan pembayaran terlebih dahulu, setelah itu PM membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=proyek', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-03 14:36:58'),
(179, 'KR005', 'Proyek Baru Ditambahkan', 'Proyek PRJ001 (Sembako Untuk Sumatera) ditambahkan. Status: Menunggu. Lakukan pembayaran terlebih dahulu, setelah itu PM membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=proyek', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-03 14:36:58'),
(181, 'KR004', 'Pembayaran Ditambahkan', 'Pembayaran PAY001 untuk proyek PRJ001 (Jenis: DP) berhasil ditambahkan. PM dapat membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=pembayaran', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-03 14:37:44'),
(182, 'KR005', 'Pembayaran Ditambahkan', 'Pembayaran PAY001 untuk proyek PRJ001 (Jenis: DP) berhasil ditambahkan. PM dapat membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=pembayaran', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2026-01-03 14:37:44'),
(187, 'KR004', 'Pengajuan Tahapan Baru', 'Mandor mengajukan TH01 untuk proyek PRJ001.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=tahapan-aktif&proyek=PRJ001', 0, '2026-01-03 14:40:37'),
(188, 'KR005', 'Pengajuan Tahapan Baru', 'Mandor mengajukan TH01 untuk proyek PRJ001.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=tahapan-aktif&proyek=PRJ001', 0, '2026-01-03 14:40:37');

-- --------------------------------------------------------

--
-- Table structure for table `pembayarans`
--

CREATE TABLE `pembayarans` (
  `id_pem_bayaran` varchar(255) NOT NULL,
  `jenis_pembayaran` enum('DP','Termin','Pelunasan') NOT NULL,
  `sub_total` decimal(15,2) NOT NULL,
  `pajak_pembayaran` decimal(15,2) DEFAULT 0.00,
  `total_pembayaran` decimal(15,2) NOT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `tanggal_bayar` date DEFAULT NULL,
  `status_pembayaran` enum('Lunas','Belum Lunas') NOT NULL DEFAULT 'Belum Lunas',
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `proyek_id_proyek` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayarans`
--

INSERT INTO `pembayarans` (`id_pem_bayaran`, `jenis_pembayaran`, `sub_total`, `pajak_pembayaran`, `total_pembayaran`, `tanggal_jatuh_tempo`, `tanggal_bayar`, `status_pembayaran`, `bukti_pembayaran`, `proyek_id_proyek`) VALUES
('PAY001', 'DP', 50000000.00, 5000000.00, 55000000.00, '2026-01-31', '2026-01-03', 'Belum Lunas', 'uploads/bukti/BKT_PAY001_20260103_083744_a5b8f1.png', 'PRJ001');

-- --------------------------------------------------------

--
-- Table structure for table `proyek`
--

CREATE TABLE `proyek` (
  `id_proyek` varchar(45) NOT NULL,
  `nama_proyek` varchar(45) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `total_biaya_proyek` decimal(15,2) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `tanggal_mulai` varchar(45) DEFAULT NULL,
  `tanggal_selesai` varchar(45) DEFAULT NULL,
  `status` enum('Menunggu','Berjalan','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu',
  `current_tahapan_id` varchar(255) DEFAULT NULL,
  `quotation` varchar(255) DEFAULT NULL,
  `gambar_kerja` varchar(255) DEFAULT NULL,
  `karyawan_id_pic_sales` varchar(45) NOT NULL,
  `karyawan_id_pic_site` varchar(45) NOT NULL,
  `klien_id_klien` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proyek`
--

INSERT INTO `proyek` (`id_proyek`, `nama_proyek`, `deskripsi`, `total_biaya_proyek`, `alamat`, `tanggal_mulai`, `tanggal_selesai`, `status`, `current_tahapan_id`, `quotation`, `gambar_kerja`, `karyawan_id_pic_sales`, `karyawan_id_pic_site`, `klien_id_klien`) VALUES
('PRJ001', 'Sembako Untuk Sumatera', 'Program ini hasil dari kepedulian warga Indonesia untuk Sumatera', 110000000.00, 'Jl. Tri Dharma No.9, Padang Bulan, Kec. Medan Baru, Kota Medan, Sumatera Utara 20222', '2026-01-03', '2026-01-31', 'Berjalan', 'TH02', 'QUO202601001', 'uploads/proyek/proyek_20260103_083658_95bc73.png', 'KR003', 'KR002', 'KL002');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id_role` varchar(45) NOT NULL,
  `nama_role` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id_role`, `nama_role`) VALUES
('RL001', 'Admin'),
('RL002', 'Project Manager'),
('RL003', 'Mandor');

-- --------------------------------------------------------

--
-- Table structure for table `role_menu`
--

CREATE TABLE `role_menu` (
  `menus_id_menu` int(11) NOT NULL,
  `role_id_role` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_menu`
--

INSERT INTO `role_menu` (`menus_id_menu`, `role_id_role`) VALUES
(1, 'RL001'),
(1, 'RL002'),
(1, 'RL003'),
(2, 'RL001'),
(2, 'RL002'),
(2, 'RL003'),
(3, 'RL001'),
(3, 'RL002'),
(3, 'RL003'),
(4, 'RL001'),
(5, 'RL001');

-- --------------------------------------------------------

--
-- Table structure for table `tahapan_update_requests`
--

CREATE TABLE `tahapan_update_requests` (
  `id` bigint(20) NOT NULL,
  `proyek_id_proyek` varchar(45) NOT NULL,
  `requested_tahapan_id` varchar(255) NOT NULL,
  `requested_by` varchar(45) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `request_note` text DEFAULT NULL,
  `bukti_foto` varchar(255) DEFAULT NULL,
  `bukti_dokumen` varchar(255) DEFAULT NULL,
  `review_note` text DEFAULT NULL,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_by` varchar(45) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tahapan_update_requests`
--

INSERT INTO `tahapan_update_requests` (`id`, `proyek_id_proyek`, `requested_tahapan_id`, `requested_by`, `status`, `request_note`, `bukti_foto`, `bukti_dokumen`, `review_note`, `requested_at`, `reviewed_by`, `reviewed_at`) VALUES
(12, 'PRJ001', 'TH01', 'KR002', 'approved', 'Saya sudah menyelesaikan tahap perencanaan bersama dengan stakeholder terkait', 'uploads/tahapan/buktifoto/FOTO_TAHAP_PRJ001_TH01_20260103_084037_e72fe555.png', 'uploads/tahapan/buktidokumen/DOC_TAHAP_PRJ001_TH01_20260103_084037_2629cc08.pdf', 'Saya suka dengan prosesnya, lanjutkan ya', '2026-01-03 14:40:37', 'KR001', '2026-01-03 16:16:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daftar_tahapans`
--
ALTER TABLE `daftar_tahapans`
  ADD PRIMARY KEY (`id_tahapan`);

--
-- Indexes for table `jadwal_proyeks`
--
ALTER TABLE `jadwal_proyeks`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `fk_jadwal_proyek_idx` (`proyek_id_proyek`),
  ADD KEY `fk_jadwal_tahapan_idx` (`daftar_tahapans_id_tahapan`);

--
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id_karyawan`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`),
  ADD KEY `fk_karyawan_role_idx` (`role_id_role`);

--
-- Indexes for table `kategori_menus`
--
ALTER TABLE `kategori_menus`
  ADD PRIMARY KEY (`id_kategori_menus`);

--
-- Indexes for table `klien`
--
ALTER TABLE `klien`
  ADD PRIMARY KEY (`id_klien`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id_menu`),
  ADD KEY `fk_menus_kategori_idx` (`kategori_menus_id_kategori_menus`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read_created` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `pembayarans`
--
ALTER TABLE `pembayarans`
  ADD PRIMARY KEY (`id_pem_bayaran`),
  ADD KEY `fk_pembayaran_proyek_idx` (`proyek_id_proyek`);

--
-- Indexes for table `proyek`
--
ALTER TABLE `proyek`
  ADD PRIMARY KEY (`id_proyek`),
  ADD KEY `fk_proyek_sales_idx` (`karyawan_id_pic_sales`),
  ADD KEY `fk_proyek_site_idx` (`karyawan_id_pic_site`),
  ADD KEY `fk_proyek_klien_idx` (`klien_id_klien`),
  ADD KEY `fk_proyek_current_tahapan` (`current_tahapan_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id_role`);

--
-- Indexes for table `role_menu`
--
ALTER TABLE `role_menu`
  ADD PRIMARY KEY (`menus_id_menu`,`role_id_role`),
  ADD KEY `fk_rolemenu_role_idx` (`role_id_role`),
  ADD KEY `fk_rolemenu_menu_idx` (`menus_id_menu`);

--
-- Indexes for table `tahapan_update_requests`
--
ALTER TABLE `tahapan_update_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tur_tahapan` (`requested_tahapan_id`),
  ADD KEY `fk_tur_user` (`requested_by`),
  ADD KEY `tur_idx_status` (`status`),
  ADD KEY `tur_idx_proyek` (`proyek_id_proyek`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kategori_menus`
--
ALTER TABLE `kategori_menus`
  MODIFY `id_kategori_menus` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=191;

--
-- AUTO_INCREMENT for table `tahapan_update_requests`
--
ALTER TABLE `tahapan_update_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jadwal_proyeks`
--
ALTER TABLE `jadwal_proyeks`
  ADD CONSTRAINT `fk_jadwal_proyek` FOREIGN KEY (`proyek_id_proyek`) REFERENCES `proyek` (`id_proyek`),
  ADD CONSTRAINT `fk_jadwal_tahapan` FOREIGN KEY (`daftar_tahapans_id_tahapan`) REFERENCES `daftar_tahapans` (`id_tahapan`);

--
-- Constraints for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD CONSTRAINT `fk_karyawan_role` FOREIGN KEY (`role_id_role`) REFERENCES `role` (`id_role`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `fk_menus_kategori` FOREIGN KEY (`kategori_menus_id_kategori_menus`) REFERENCES `kategori_menus` (`id_kategori_menus`);

--
-- Constraints for table `pembayarans`
--
ALTER TABLE `pembayarans`
  ADD CONSTRAINT `fk_pembayaran_proyek` FOREIGN KEY (`proyek_id_proyek`) REFERENCES `proyek` (`id_proyek`);

--
-- Constraints for table `proyek`
--
ALTER TABLE `proyek`
  ADD CONSTRAINT `fk_proyek_current_tahapan` FOREIGN KEY (`current_tahapan_id`) REFERENCES `daftar_tahapans` (`id_tahapan`),
  ADD CONSTRAINT `fk_proyek_klien` FOREIGN KEY (`klien_id_klien`) REFERENCES `klien` (`id_klien`),
  ADD CONSTRAINT `fk_proyek_sales` FOREIGN KEY (`karyawan_id_pic_sales`) REFERENCES `karyawan` (`id_karyawan`),
  ADD CONSTRAINT `fk_proyek_site` FOREIGN KEY (`karyawan_id_pic_site`) REFERENCES `karyawan` (`id_karyawan`);

--
-- Constraints for table `role_menu`
--
ALTER TABLE `role_menu`
  ADD CONSTRAINT `fk_rolemenu_menu` FOREIGN KEY (`menus_id_menu`) REFERENCES `menus` (`id_menu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rolemenu_role` FOREIGN KEY (`role_id_role`) REFERENCES `role` (`id_role`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tahapan_update_requests`
--
ALTER TABLE `tahapan_update_requests`
  ADD CONSTRAINT `fk_tur_proyek` FOREIGN KEY (`proyek_id_proyek`) REFERENCES `proyek` (`id_proyek`),
  ADD CONSTRAINT `fk_tur_tahapan` FOREIGN KEY (`requested_tahapan_id`) REFERENCES `daftar_tahapans` (`id_tahapan`),
  ADD CONSTRAINT `fk_tur_user` FOREIGN KEY (`requested_by`) REFERENCES `karyawan` (`id_karyawan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
