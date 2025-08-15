-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2025 at 07:26 PM
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
('JDL001', '2025-07-01', '2025-07-01', 7, '2025-07-08', NULL, 'Sesuai Jadwal', 'PRJ001', 'TH01'),
('JDL002', '2025-07-09', NULL, 14, '2025-07-23', NULL, 'Belum Mulai', 'PRJ001', 'TH02'),
('JDL003', '2025-06-15', '2025-06-15', 10, '2025-06-25', NULL, 'Sesuai Jadwal', 'PRJ002', 'TH01');

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
('KR001', 'Andi Wijaya', '081234567890', 'andi.wijaya@example.com', 'hashed_password_1', 'RL002'),
('KR002', 'Budi Santoso', '081234567891', 'budi.santoso@example.com', 'hashed_password_2', 'RL003'),
('KR003', 'Citra Lestari', '081234567892', 'citra.lestari@example.com', 'hashed_password_3', 'RL001'),
('KR004', 'Dewi Anggraini', '081234567893', 'dewi.anggraini@example.com', 'hashed_password_4', 'RL002');

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
('KL001', 'PT. Maju Mundur', '021-555-0101', 'kontak@majumundur.com', 'Jl. Jend. Sudirman Kav. 52-53, Jakarta'),
('KL002', 'CV. Sejahtera Bersama', '031-777-0202', 'info@sejahterabersama.co.id', 'Jl. Basuki Rahmat No. 12, Surabaya');

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
('PAY003', 'Termin', 100000000.00, 10000000.00, 110000000.00, '2025-08-20', NULL, 'Belum Lunas', NULL, 'PRJ002');

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

INSERT INTO `proyek` (`id_proyek`, `nama_proyek`, `deskripsi`, `total_biaya_proyek`, `alamat`, `tanggal_mulai`, `tanggal_selesai`, `status`, `quotation`, `gambar_kerja`, `brand_id_brand`, `karyawan_id_pic_sales`, `karyawan_id_pic_site`, `klien_id_klien`) VALUES
('PRJ001', 'Website E-commerce Snack', 'Pembuatan website e-commerce untuk produk snack Sejahtera.', 15000000.00, 'Online', '2025-07-01', '2025-09-01', 'Berjalan', 'QUO/2025/001', 'GK/2025/001', 3, 'KR002', 'KR001', 'KL002'),
('PRJ002', 'Renovasi Kantor Maju', 'Renovasi interior kantor pusat PT. Maju Mundur.', 250000000.00, 'Jl. Jend. Sudirman Kav. 52-53, Jakarta', '2025-06-15', NULL, 'Berjalan', 'QUO/2025/002', 'GK/2025/002', 1, 'KR002', 'KR004', 'KL001');

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
  ADD KEY `fk_proyek_klien_idx` (`klien_id_klien`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brand`
--
ALTER TABLE `brand`
  MODIFY `id_brand` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kategori_menus`
--
ALTER TABLE `kategori_menus`
  MODIFY `id_kategori_menus` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  ADD CONSTRAINT `fk_proyek_klien` FOREIGN KEY (`klien_id_klien`) REFERENCES `klien` (`id_klien`),
  ADD CONSTRAINT `fk_proyek_sales` FOREIGN KEY (`karyawan_id_pic_sales`) REFERENCES `karyawan` (`id_karyawan`),
  ADD CONSTRAINT `fk_proyek_site` FOREIGN KEY (`karyawan_id_pic_site`) REFERENCES `karyawan` (`id_karyawan`);

--
-- Constraints for table `role_menu`
--
ALTER TABLE `role_menu`
  ADD CONSTRAINT `fk_rolemenu_menu` FOREIGN KEY (`menus_id_menu`) REFERENCES `menus` (`id_menu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rolemenu_role` FOREIGN KEY (`role_id_role`) REFERENCES `role` (`id_role`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
