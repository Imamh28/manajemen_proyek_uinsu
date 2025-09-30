-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2025 at 02:03 PM
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
-- Table structure for table `brand`
--

CREATE TABLE `brand` (
  `id_brand` int(11) NOT NULL,
  `nama_brand` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brand`
--

INSERT INTO `brand` (`id_brand`, `nama_brand`) VALUES
(1, 'Maju Properti'),
(2, 'Mundur Logistik'),
(3, 'Sejahtera Snack'),
(4, 'Sejahtera Minuman');

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
('JDL001', '2025-09-30', '2025-09-20', 17, '2025-10-16', '2025-09-20', 'Lebih Cepat', 'PRJ003', 'TH01'),
('JDL002', '2025-09-23', NULL, 2, '2025-09-24', NULL, 'Belum Mulai', 'PRJ003', 'TH02');

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
(2, 'KR006', 'Jadwal Ditambahkan', 'Jadwal JDL untuk proyek PRJ002 berhasil ditambahkan.', '/manajemen_proyek_uinsu/app/index.php?r=penjadwalan&proyek=PRJ002', '/manajemen_proyek_uinsu/app/index.php?r=penjadwalan&proyek=PRJ002', '/manajemen_proyek_uinsu/app/index.php?r=penjadwalan&proyek=PRJ002', '/manajemen_proyek_uinsu/app/index.php?r=penjadwalan&proyek=PRJ002', 0, '2025-09-02 01:29:16'),
(5, 'KR004', 'Jadwal Ditambahkan', 'Jadwal JDL untuk proyek PRJ002 berhasil ditambahkan.', '/manajemen_proyek_uinsu/app/index.php?r=penjadwalan&proyek=PRJ002', '/manajemen_proyek_uinsu/app/index.php?r=penjadwalan&proyek=PRJ002', '/manajemen_proyek_uinsu/app/index.php?r=penjadwalan&proyek=PRJ002', '/manajemen_proyek_uinsu/app/index.php?r=penjadwalan&proyek=PRJ002', 0, '2025-09-02 01:29:16'),
(6, 'KR005', 'Jadwal Ditambahkan', 'Jadwal JDL untuk proyek PRJ002 berhasil ditambahkan.', '/manajemen_proyek_uinsu/app/index.php?r=penjadwalan&proyek=PRJ002', '/manajemen_proyek_uinsu/app/index.php?r=penjadwalan&proyek=PRJ002', '/manajemen_proyek_uinsu/app/index.php?r=penjadwalan&proyek=PRJ002', '/manajemen_proyek_uinsu/app/index.php?r=penjadwalan&proyek=PRJ002', 0, '2025-09-02 01:29:16'),
(8, 'KR006', 'Proyek Baru Ditambahkan', 'Proyek PRJ003 baru saja ditambahkan. PM harap membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=penjadwalan&proyek=PRJ003', 'index.php?r=dashboard', 0, '2025-09-18 20:36:33'),
(11, 'KR004', 'Proyek Baru Ditambahkan', 'Proyek PRJ003 baru saja ditambahkan. PM harap membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=penjadwalan&proyek=PRJ003', 'index.php?r=dashboard', 0, '2025-09-18 20:36:33'),
(12, 'KR005', 'Proyek Baru Ditambahkan', 'Proyek PRJ003 baru saja ditambahkan. PM harap membuat penjadwalan.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=penjadwalan&proyek=PRJ003', 'index.php?r=dashboard', 0, '2025-09-18 20:36:33'),
(14, 'KR004', 'Pembayaran Dihapus', 'Pembayaran #PAY006 telah dihapus.', 'index.php?r=dashboard', 'index.php?r=pembayaran', 'index.php?r=pembayaran', 'index.php?r=dashboard', 0, '2025-09-19 01:27:32'),
(15, 'KR005', 'Pembayaran Dihapus', 'Pembayaran #PAY006 telah dihapus.', 'index.php?r=dashboard', 'index.php?r=pembayaran', 'index.php?r=pembayaran', 'index.php?r=dashboard', 0, '2025-09-19 01:27:32'),
(17, 'KR006', 'Pembayaran Dihapus', 'Pembayaran #PAY006 telah dihapus.', 'index.php?r=dashboard', 'index.php?r=pembayaran', 'index.php?r=pembayaran', 'index.php?r=dashboard', 0, '2025-09-19 01:27:32'),
(19, 'KR004', 'Karyawan Ditambahkan', 'Karyawan M Ariyo Syahraza berhasil ditambahkan.', 'index.php?r=dashboard', 'index.php?r=karyawan', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-19 15:00:14'),
(20, 'KR005', 'Karyawan Ditambahkan', 'Karyawan M Ariyo Syahraza berhasil ditambahkan.', 'index.php?r=dashboard', 'index.php?r=karyawan', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-19 15:00:14'),
(22, 'KR006', 'Karyawan Ditambahkan', 'Karyawan M Ariyo Syahraza berhasil ditambahkan.', 'index.php?r=dashboard', 'index.php?r=karyawan', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-19 15:00:14'),
(23, 'KR007', 'Karyawan Ditambahkan', 'Karyawan M Ariyo Syahraza berhasil ditambahkan.', 'index.php?r=dashboard', 'index.php?r=karyawan', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-19 15:00:14'),
(25, 'KR007', 'Proyek Diperbarui', 'Proyek PRJ002 diperbarui. PM harap meninjau penjadwalan.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=penjadwalan&proyek=PRJ002', 'index.php?r=dashboard', 0, '2025-09-20 00:08:41'),
(26, 'KR006', 'Proyek Diperbarui', 'Proyek PRJ002 diperbarui. PM harap meninjau penjadwalan.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=penjadwalan&proyek=PRJ002', 'index.php?r=dashboard', 0, '2025-09-20 00:08:41'),
(29, 'KR004', 'Proyek Diperbarui', 'Proyek PRJ002 diperbarui. PM harap meninjau penjadwalan.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=penjadwalan&proyek=PRJ002', 'index.php?r=dashboard', 0, '2025-09-20 00:08:41'),
(30, 'KR005', 'Proyek Diperbarui', 'Proyek PRJ002 diperbarui. PM harap meninjau penjadwalan.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=penjadwalan&proyek=PRJ002', 'index.php?r=dashboard', 0, '2025-09-20 00:08:41'),
(32, 'KR007', 'Proyek Diperbarui', 'Proyek PRJ001 diperbarui. PM harap meninjau penjadwalan.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2025-09-20 01:49:06'),
(33, 'KR006', 'Proyek Diperbarui', 'Proyek PRJ001 diperbarui. PM harap meninjau penjadwalan.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2025-09-20 01:49:06'),
(36, 'KR004', 'Proyek Diperbarui', 'Proyek PRJ001 diperbarui. PM harap meninjau penjadwalan.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2025-09-20 01:49:06'),
(37, 'KR005', 'Proyek Diperbarui', 'Proyek PRJ001 diperbarui. PM harap meninjau penjadwalan.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=penjadwalan&proyek=PRJ001', 'index.php?r=dashboard', 0, '2025-09-20 01:49:06'),
(39, 'KR004', 'Klien Diperbarui', 'Klien PT. Maju Mundur telah diperbarui.', 'index.php?r=dashboard', 'index.php?r=klien', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-20 13:38:56'),
(40, 'KR005', 'Klien Diperbarui', 'Klien PT. Maju Mundur telah diperbarui.', 'index.php?r=dashboard', 'index.php?r=klien', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-20 13:38:56'),
(42, 'KR006', 'Klien Diperbarui', 'Klien PT. Maju Mundur telah diperbarui.', 'index.php?r=dashboard', 'index.php?r=klien', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-20 13:38:56'),
(43, 'KR007', 'Klien Diperbarui', 'Klien PT. Maju Mundur telah diperbarui.', 'index.php?r=dashboard', 'index.php?r=klien', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-20 13:38:56'),
(45, 'KR004', 'Klien Diperbarui', 'Klien CV. Sejahtera Bersama telah diperbarui.', 'index.php?r=dashboard', 'index.php?r=klien', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-20 13:39:10'),
(46, 'KR005', 'Klien Diperbarui', 'Klien CV. Sejahtera Bersama telah diperbarui.', 'index.php?r=dashboard', 'index.php?r=klien', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-20 13:39:10'),
(48, 'KR006', 'Klien Diperbarui', 'Klien CV. Sejahtera Bersama telah diperbarui.', 'index.php?r=dashboard', 'index.php?r=klien', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-20 13:39:10'),
(49, 'KR007', 'Klien Diperbarui', 'Klien CV. Sejahtera Bersama telah diperbarui.', 'index.php?r=dashboard', 'index.php?r=klien', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-20 13:39:10'),
(51, 'KR004', 'Klien Ditambahkan', 'Klien PT. Wahana Wihana berhasil ditambahkan.', 'index.php?r=dashboard', 'index.php?r=klien', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-20 13:41:34'),
(52, 'KR005', 'Klien Ditambahkan', 'Klien PT. Wahana Wihana berhasil ditambahkan.', 'index.php?r=dashboard', 'index.php?r=klien', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-20 13:41:34'),
(54, 'KR006', 'Klien Ditambahkan', 'Klien PT. Wahana Wihana berhasil ditambahkan.', 'index.php?r=dashboard', 'index.php?r=klien', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-20 13:41:34'),
(55, 'KR007', 'Klien Ditambahkan', 'Klien PT. Wahana Wihana berhasil ditambahkan.', 'index.php?r=dashboard', 'index.php?r=klien', 'index.php?r=dashboard', 'index.php?r=dashboard', 0, '2025-09-20 13:41:34'),
(57, 'KR007', 'Jadwal Ditambahkan', 'Jadwal JDL001 untuk proyek PRJ003 berhasil dibuat.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-aktif&proyek=PRJ003', 0, '2025-09-20 23:30:40'),
(58, 'KR006', 'Jadwal Ditambahkan', 'Jadwal JDL001 untuk proyek PRJ003 berhasil dibuat.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-aktif&proyek=PRJ003', 0, '2025-09-20 23:30:40'),
(61, 'KR004', 'Jadwal Ditambahkan', 'Jadwal JDL001 untuk proyek PRJ003 berhasil dibuat.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-aktif&proyek=PRJ003', 0, '2025-09-20 23:30:40'),
(62, 'KR005', 'Jadwal Ditambahkan', 'Jadwal JDL001 untuk proyek PRJ003 berhasil dibuat.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-aktif&proyek=PRJ003', 0, '2025-09-20 23:30:40'),
(64, 'KR007', 'Pengajuan Tahapan Baru', 'Mandor mengajukan tahapan TH01 untuk proyek PRJ003.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=dashboard', 0, '2025-09-20 23:39:14'),
(65, 'KR006', 'Pengajuan Tahapan Baru', 'Mandor mengajukan tahapan TH01 untuk proyek PRJ003.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=dashboard', 0, '2025-09-20 23:39:14'),
(68, 'KR004', 'Pengajuan Tahapan Baru', 'Mandor mengajukan tahapan TH01 untuk proyek PRJ003.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=dashboard', 0, '2025-09-20 23:39:14'),
(69, 'KR005', 'Pengajuan Tahapan Baru', 'Mandor mengajukan tahapan TH01 untuk proyek PRJ003.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=dashboard', 0, '2025-09-20 23:39:14'),
(72, 'KR007', 'Jadwal Ditambahkan', 'Jadwal JDL002 untuk proyek PRJ003 berhasil dibuat.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-aktif&proyek=PRJ003', 0, '2025-09-20 23:43:34'),
(73, 'KR006', 'Jadwal Ditambahkan', 'Jadwal JDL002 untuk proyek PRJ003 berhasil dibuat.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-aktif&proyek=PRJ003', 0, '2025-09-20 23:43:34'),
(76, 'KR004', 'Jadwal Ditambahkan', 'Jadwal JDL002 untuk proyek PRJ003 berhasil dibuat.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-aktif&proyek=PRJ003', 0, '2025-09-20 23:43:34'),
(77, 'KR005', 'Jadwal Ditambahkan', 'Jadwal JDL002 untuk proyek PRJ003 berhasil dibuat.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-aktif&proyek=PRJ003', 0, '2025-09-20 23:43:34'),
(79, 'KR007', 'Pengajuan Tahapan Baru', 'Mandor mengajukan tahapan TH01 untuk proyek PRJ003.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=dashboard', 0, '2025-09-20 23:44:27'),
(80, 'KR006', 'Pengajuan Tahapan Baru', 'Mandor mengajukan tahapan TH01 untuk proyek PRJ003.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=dashboard', 0, '2025-09-20 23:44:27'),
(83, 'KR004', 'Pengajuan Tahapan Baru', 'Mandor mengajukan tahapan TH01 untuk proyek PRJ003.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=dashboard', 0, '2025-09-20 23:44:27'),
(84, 'KR005', 'Pengajuan Tahapan Baru', 'Mandor mengajukan tahapan TH01 untuk proyek PRJ003.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=dashboard', 0, '2025-09-20 23:44:27'),
(86, 'KR007', 'Pengajuan Tahapan Baru', 'Mandor mengajukan tahapan TH02 untuk proyek PRJ003.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=dashboard', 0, '2025-09-20 23:44:47'),
(87, 'KR006', 'Pengajuan Tahapan Baru', 'Mandor mengajukan tahapan TH02 untuk proyek PRJ003.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=dashboard', 0, '2025-09-20 23:44:47'),
(90, 'KR004', 'Pengajuan Tahapan Baru', 'Mandor mengajukan tahapan TH02 untuk proyek PRJ003.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=dashboard', 0, '2025-09-20 23:44:47'),
(91, 'KR005', 'Pengajuan Tahapan Baru', 'Mandor mengajukan tahapan TH02 untuk proyek PRJ003.', 'index.php?r=dashboard', 'index.php?r=dashboard', 'index.php?r=tahapan-approval', 'index.php?r=dashboard', 0, '2025-09-20 23:44:47');

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
('PAY001', 'DP', 7500000.00, 750000.00, 8250000.00, '2025-07-10', '2025-07-05', 'Lunas', 'bukti/pay001.jpg', 'PRJ001'),
('PAY002', 'DP', 100000000.00, 10000000.00, 110000000.00, '2025-06-20', '2025-06-18', 'Lunas', 'bukti/pay002.jpg', 'PRJ002'),
('PAY003', 'Termin', 10000000000.00, 1000000000.00, 11000000000.00, '2025-08-20', '2025-08-30', 'Belum Lunas', NULL, 'PRJ002'),
('PAY004', 'Pelunasan', 120000.00, 12000.00, 132000.00, '2025-09-19', '2025-09-15', 'Lunas', 'uploads/bukti/BKT_PAY004_1758217205.png', 'PRJ001'),
('PAY005', 'DP', 10000000.00, 1000000.00, 11000000.00, '2025-11-21', '2025-09-30', 'Belum Lunas', 'uploads/bukti/BKT_PAY005_1758217763.png', 'PRJ003');

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
  `brand_id_brand` int(11) NOT NULL,
  `karyawan_id_pic_sales` varchar(45) NOT NULL,
  `karyawan_id_pic_site` varchar(45) NOT NULL,
  `klien_id_klien` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proyek`
--

INSERT INTO `proyek` (`id_proyek`, `nama_proyek`, `deskripsi`, `total_biaya_proyek`, `alamat`, `tanggal_mulai`, `tanggal_selesai`, `status`, `current_tahapan_id`, `quotation`, `gambar_kerja`, `brand_id_brand`, `karyawan_id_pic_sales`, `karyawan_id_pic_site`, `klien_id_klien`) VALUES
('PRJ001', 'Website E-commerce Snack', 'Pembuatan website e-commerce untuk produk snack Sejahtera.', 1500000000.00, 'Online', '2025-07-01', '2025-09-01', 'Berjalan', NULL, 'QUO/2025/001', 'uploads/proyek/proyek_20250919_204906_1fe40e.jpg', 3, 'KR002', 'KR001', 'KL002'),
('PRJ002', 'Renovasi Kantor Maju', 'Renovasi interior kantor pusat PT. Maju Mundur.', 25000000000.00, 'Jl. Jend. Sudirman Kav. 52-53, Jakarta', '2025-06-15', '2025-09-27', 'Berjalan', NULL, 'QUO/2025/002', 'uploads/proyek/proyek_20250919_190841_c3ff5b.png', 1, 'KR002', 'KR004', 'KL001'),
('PRJ003', 'Desain Arsitektur Kopikuni', 'Ini adalah sebuah desain arsitektur', 2500000000.00, 'Jalan Braga No. 5', '2025-09-18', '2025-10-17', 'Menunggu', NULL, 'QUO/2025/003', 'uploads/proyek/proyek_20250918_153633_92b094.jpg', 1, 'KR005', 'KR006', 'KL002');

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
  `review_note` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_by` varchar(45) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tahapan_update_requests`
--

INSERT INTO `tahapan_update_requests` (`id`, `proyek_id_proyek`, `requested_tahapan_id`, `requested_by`, `status`, `request_note`, `review_note`, `note`, `requested_at`, `reviewed_by`, `reviewed_at`) VALUES
(3, 'PRJ003', 'TH01', 'KR002', 'approved', 'Saya sudah melakukan Perencanaan', NULL, 'Saya sudah melakukan Perencanaan', '2025-09-20 23:39:14', 'KR001', '2025-09-20 23:40:16'),
(4, 'PRJ003', 'TH01', 'KR002', 'rejected', '', NULL, '', '2025-09-20 23:44:27', 'KR001', '2025-09-20 23:53:04'),
(5, 'PRJ003', 'TH02', 'KR002', 'rejected', 'Kamu Terlalu Cepat', NULL, 'Kamu Terlalu Cepat', '2025-09-20 23:44:47', 'KR001', '2025-09-20 23:53:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brand`
--
ALTER TABLE `brand`
  ADD PRIMARY KEY (`id_brand`);

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
  ADD KEY `fk_proyek_brand_idx` (`brand_id_brand`),
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
-- AUTO_INCREMENT for table `brand`
--
ALTER TABLE `brand`
  MODIFY `id_brand` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `tahapan_update_requests`
--
ALTER TABLE `tahapan_update_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  ADD CONSTRAINT `fk_proyek_brand` FOREIGN KEY (`brand_id_brand`) REFERENCES `brand` (`id_brand`),
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
