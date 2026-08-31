-- ===================================================
-- Skema Database PostgreSQL untuk Supabase
-- Sistem Pengaduan Masyarakat
-- ===================================================

-- 1. Tabel Admin
CREATE TABLE IF NOT EXISTS admin (
    id_admin SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL
);

-- 2. Tabel Berita
CREATE TABLE IF NOT EXISTS berita (
    id_berita SERIAL PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    isi TEXT NOT NULL,
    foto VARCHAR(255) NOT NULL,
    tanggal_berita DATE NOT NULL,
    tanggal_dibuat TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel Pengaduan
CREATE TABLE IF NOT EXISTS pengaduan (
    id_pengaduan SERIAL PRIMARY KEY,
    kode_laporan VARCHAR(50) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    judul_laporan VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    lokasi VARCHAR(200) NOT NULL,
    latitude VARCHAR(50) DEFAULT NULL,
    longitude VARCHAR(50) DEFAULT NULL,
    foto VARCHAR(255) NOT NULL,
    foto_hasil VARCHAR(255) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'Menunggu' CHECK (status IN ('Menunggu', 'Diproses', 'Selesai')),
    tanggapan TEXT DEFAULT NULL,
    foto_selesai VARCHAR(255) DEFAULT NULL,
    tanggal_lapor TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    tanggal_selesai TIMESTAMP WITH TIME ZONE DEFAULT NULL
);

-- ---------------------------------------------------
-- Data Awal (Seed Data)
-- ---------------------------------------------------

-- Admin Default (Username: admin, Password: admin123)
INSERT INTO admin (username, password, nama_lengkap)
VALUES ('admin', 'admin123', 'Administrator')
ON CONFLICT (username) DO NOTHING;

-- Data Berita Awal
INSERT INTO berita (judul, isi, foto, tanggal_berita)
VALUES 
(
    'Peringatan HUT ke-81 RI, Kelurahan Rengas Pererat Kebersamaan Warga',
    'HADIRILAH !!! Perayaan 17 Agustus 2026, di Lapangan Rengas, Kota Tangerang Selatan. Kelurahan Rengas turut memeriahkan peringatan HUT ke-81 Kemerdekaan Republik Indonesia melalui berbagai kegiatan yang melibatkan masyarakat.',
    '1781882555_images.jfif',
    '2026-08-17'
),
(
    'SEMARAK PESTA RAKYAT!!',
    'HADIRILAH Semarak Pesta Rakyat! Pesta Rakyat Kelurahan Rengas berlangsung meriah dan diikuti dengan antusias oleh masyarakat.',
    '1788185413_images.jfif',
    '2026-08-25'
)
ON CONFLICT DO NOTHING;
