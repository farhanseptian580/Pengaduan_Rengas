# Sistem Pengaduan Masyarakat

Sistem pengaduan masyarakat berbasis PHP native dan MySQL menggunakan XAMPP.

## Struktur Folder

```
bulan_website/
│
├── index.php                    # Halaman beranda untuk user
├── koneksi.php                  # Koneksi database
├── login.php                    # Halaman login admin
├── dashboard.php                # Dashboard user (opsional)
├── tambah_pengaduan.php         # Form pengaduan user
├── cek_status.php               # Cek status laporan user
├── logout.php                   # Logout admin
├── database.sql                 # File SQL untuk setup database
├── README.md                    # Dokumentasi
│
├── admin/
│   ├── dashboard.php            # Dashboard admin
│   ├── data_pengaduan.php       # Data semua pengaduan
│   └── update_status.php        # Update status dan tanggapan
│
├── assets/                      # Folder untuk assets (CSS, JS, gambar)
└── uploads/                     # Folder untuk upload foto bukti
```

## Persyaratan

- XAMPP (Apache + MySQL)
- PHP 7.0 atau lebih tinggi
- MySQL/MariaDB
- Browser modern (Chrome, Firefox, Edge, dll)

## Cara Instalasi

### 1. Setup Database

1. Buka XAMPP Control Panel
2. Start Apache dan MySQL
3. Buka phpMyAdmin di browser: http://localhost/phpmyadmin
4. Buat database baru dengan nama: `pengaduan_masyarakat`
5. Import file `database.sql` ke database tersebut

Atau jalankan perintah SQL berikut di phpMyAdmin:

```sql
CREATE DATABASE IF NOT EXISTS pengaduan_masyarakat;
USE pengaduan_masyarakat;

CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL
);

INSERT INTO admin (username, password, nama_lengkap) VALUES 
('admin', 'admin123', 'Administrator');

CREATE TABLE IF NOT EXISTS pengaduan (
    id_pengaduan INT AUTO_INCREMENT PRIMARY KEY,
    kode_laporan VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    judul_laporan VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    lokasi VARCHAR(200) NOT NULL,
    foto VARCHAR(255) NOT NULL,
    status ENUM('Menunggu', 'Diproses', 'Selesai') DEFAULT 'Menunggu',
    tanggapan TEXT,
    tanggal_lapor DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### 2. Setup Project

1. Copy folder `bulan_website` ke `C:\xampp\htdocs\`
2. Pastikan struktur folder sudah benar
3. Pastikan folder `uploads` memiliki permission write

### 3. Akses Website

- Halaman utama: http://localhost/bulan_website/
- Login admin: http://localhost/bulan_website/login.php

## Fitur User

### 1. Halaman Beranda
- Melihat informasi sistem pengaduan
- Navigasi ke fitur-fitur lain

### 2. Buat Pengaduan
- Mengisi form pengaduan (nama, no HP, judul, deskripsi, lokasi)
- Upload foto bukti
- Mendapatkan kode laporan otomatis

### 3. Cek Status
- Tracking status laporan menggunakan kode laporan
- Melihat detail laporan
- Melihat tanggapan admin

## Fitur Admin

### 1. Login Admin
- Username: `admin`
- Password: `admin123`

### 2. Dashboard
- Melihat statistik pengaduan (total, menunggu, diproses, selesai)
- Melihat pengaduan terbaru

### 3. Data Pengaduan
- Melihat semua data pengaduan
- Filter berdasarkan status
- Update status dan tanggapan

### 4. Update Status
- Mengubah status laporan (Menunggu, Diproses, Selesai)
- Memberikan tanggapan pada laporan

## Database Schema

### Tabel Admin
- `id_admin` - ID admin (Primary Key, Auto Increment)
- `username` - Username untuk login
- `password` - Password untuk login
- `nama_lengkap` - Nama lengkap admin

### Tabel Pengaduan
- `id_pengaduan` - ID pengaduan (Primary Key, Auto Increment)
- `kode_laporan` - Kode unik laporan
- `nama` - Nama pelapor
- `no_hp` - Nomor HP pelapor
- `judul_laporan` - Judul laporan
- `deskripsi` - Deskripsi lengkap laporan
- `lokasi` - Lokasi kejadian
- `foto` - Nama file foto bukti
- `status` - Status laporan (Menunggu, Diproses, Selesai)
- `tanggapan` - Tanggapan dari admin
- `tanggal_lapor` - Tanggal dan waktu lapor

## Teknologi yang Digunakan

- **PHP Native** - Backend tanpa framework
- **MySQL/MariaDB** - Database
- **Bootstrap 5** - Framework CSS untuk tampilan modern
- **Bootstrap Icons** - Icon library
- **mysqli** - PHP MySQL extension untuk koneksi database

## Catatan Penting

1. Pastikan XAMPP sudah berjalan (Apache dan MySQL)
2. Pastikan database sudah dibuat dengan nama yang benar
3. Pastikan folder `uploads` memiliki permission write
4. Password admin default: `admin123` (sebaiknya diubah untuk produksi)
5. Sistem menggunakan CDN untuk Bootstrap, jadi membutuhkan koneksi internet

## Troubleshooting

### Error Koneksi Database
- Pastikan MySQL sudah berjalan di XAMPP
- Cek konfigurasi di `koneksi.php`
- Pastikan nama database sudah benar

### Error Upload Foto
- Pastikan folder `uploads` ada dan memiliki permission write
- Cek ukuran file foto (maksimal sesuai konfigurasi PHP)

### Tampilan Tidak Berfungsi
- Pastikan terkoneksi internet (untuk CDN Bootstrap)
- Cek browser yang digunakan

## Lisensi

Sistem ini dibuat untuk tujuan pembelajaran dan dapat dimodifikasi sesuai kebutuhan.

## Kontak

Untuk pertanyaan atau bantuan, silakan hubungi developer.
