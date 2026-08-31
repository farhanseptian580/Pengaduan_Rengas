-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2026 at 05:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pengaduan_masyarakat`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama_lengkap`) VALUES
(1, 'admin', 'admin123', 'Administrator');

-- --------------------------------------------------------

--
-- Table structure for table `berita`
--

CREATE TABLE `berita` (
  `id_berita` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `foto` varchar(255) NOT NULL,
  `tanggal_berita` date NOT NULL,
  `tanggal_dibuat` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `berita`
--

INSERT INTO `berita` (`id_berita`, `judul`, `isi`, `foto`, `tanggal_berita`, `tanggal_dibuat`) VALUES
(4, 'Peringatan HUT ke-81 RI, Kelurahan Rengas Pererat Kebersamaan Warga', 'HADIRILAH !!!\r\nPerayaan 17 Agustus 2026, di Lapangan Rengas, Kota Tangerang Selatan\r\n\r\nKelurahan Rengas turut memeriahkan peringatan HUT ke-81 Kemerdekaan Republik Indonesia melalui berbagai kegiatan yang melibatkan masyarakat. Kegiatan berlangsung meriah dengan penuh semangat kebersamaan, gotong royong, dan nasionalisme.\r\n\r\nBANYAK HADIAH DAN DOORPRICE MENARIK!!!\r\njangan lupa datang!! ', '1781882555_images.jfif', '2026-08-17', '2026-06-19 22:22:35'),
(5, 'SEMARAK PESTA RAKYAT!!', 'HADIRILAH Semarak Pesta Rakyat!\r\n\r\nPesta Rakyat Kelurahan Rengas berlangsung meriah dan diikuti dengan antusias oleh masyarakat.\r\nKegiatan ini menjadi momentum untuk mempererat silaturahmi, kebersamaan, dan semangat gotong royong antarwarga.\r\n\r\nJangan lupa dateng ye ncang ncing!!!', '1788185413_images.jfif', '2026-08-25', '2026-06-19 22:23:49');

-- --------------------------------------------------------

--
-- Table structure for table `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id_pengaduan` int(11) NOT NULL,
  `kode_laporan` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `judul_laporan` varchar(200) NOT NULL,
  `deskripsi` text NOT NULL,
  `lokasi` varchar(200) NOT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `foto` varchar(255) NOT NULL,
  `foto_hasil` varchar(255) DEFAULT NULL,
  `status` enum('Menunggu','Diproses','Selesai') DEFAULT 'Menunggu',
  `tanggapan` text DEFAULT NULL,
  `foto_selesai` varchar(255) DEFAULT NULL,
  `tanggal_lapor` datetime DEFAULT current_timestamp(),
  `tanggal_selesai` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaduan`
--

INSERT INTO `pengaduan` (`id_pengaduan`, `kode_laporan`, `nama`, `no_hp`, `judul_laporan`, `deskripsi`, `lokasi`, `latitude`, `longitude`, `foto`, `foto_hasil`, `status`, `tanggapan`, `foto_selesai`, `tanggal_lapor`, `tanggal_selesai`) VALUES
(5, 'LPR202607179421', 'ody', '089653246765', 'jalan berlubang', 'jalan berlubang di gang kramat, di dekat turunan perbatasan ke arah rengas', 'gang kramat, tangerang selatan', '-6.283152896123405', '106.75010025501251', 'usecase.jpg', NULL, 'Selesai', 'sudah selesai, terima kasih atas pengaduannya', 'selesai_1784290958_6a5a1e8e6ad71.jpg', '2026-07-17 19:21:22', NULL),
(6, 'LPR202607178020', 'bulan', '0895803279467', 'jalanan berlubang', 'Jalan berlubang cukup besar dan dalam yang berisiko membahayakan pengguna jalan/pengendara jika malam hari atau ketika sedang menggunakan kecepatan tinggi.', 'Jalan Mabad Bawah III', '-6.2839313960005585', '106.75434887409212', 'jV41q.jpg', NULL, 'Menunggu', NULL, NULL, '2026-07-18 04:07:11', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `berita`
--
ALTER TABLE `berita`
  ADD PRIMARY KEY (`id_berita`);

--
-- Indexes for table `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id_pengaduan`),
  ADD UNIQUE KEY `kode_laporan` (`kode_laporan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `berita`
--
ALTER TABLE `berita`
  MODIFY `id_berita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id_pengaduan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
