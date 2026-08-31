-- Buat Database
CREATE DATABASE IF NOT EXISTS pengaduan_masyarakat;
USE pengaduan_masyarakat;

-- Tabel Admin
CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL
);

-- Insert Data Admin (Password: admin123)
INSERT INTO admin (username, password, nama_lengkap) VALUES 
('admin', 'admin123', 'Administrator');

-- Tabel Pengaduan
CREATE TABLE IF NOT EXISTS pengaduan (
    id_pengaduan INT AUTO_INCREMENT PRIMARY KEY,
    kode_laporan VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    judul_laporan VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    lokasi VARCHAR(200) NOT NULL,
    latitude VARCHAR(50),
    longitude VARCHAR(50),
    foto VARCHAR(255) NOT NULL,
    status ENUM('Menunggu', 'Diproses', 'Selesai') DEFAULT 'Menunggu',
    tanggapan TEXT,
    foto_selesai VARCHAR(255) NULL,
    tanggal_lapor DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Berita
CREATE TABLE IF NOT EXISTS berita (
    id_berita INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    isi TEXT NOT NULL,
    foto VARCHAR(255) NOT NULL,
    tanggal_berita DATE NOT NULL,
    tanggal_dibuat DATETIME DEFAULT CURRENT_TIMESTAMP
);

