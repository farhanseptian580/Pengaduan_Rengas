# Sistem Pengaduan Masyarakat (Supabase & Vercel Ready)

Sistem Informasi dan Layanan Pengaduan Masyarakat Online berbasis PHP dengan database **Supabase (PostgreSQL)**, penyimpanan media **Supabase Storage**, dan konfigurasi deployment serverless **Vercel** melalui **GitHub**.

---

## 📁 Struktur Folder

```
bulan_website/
├── .env.example             # Template konfigurasi Supabase
├── .gitignore               # Daftar file yang diabaikan Git
├── vercel.json              # Konfigurasi runtime PHP untuk Vercel
├── supabase_schema.sql      # Skrip DDL Database & Storage Supabase
├── koneksi.php              # Koneksi PDO PostgreSQL & Helper Supabase Storage
├── index.php                # Halaman Beranda & Berita
├── tambah_pengaduan.php     # Form Pengaduan + Upload Bukti + Leaflet Map
├── cek_status.php           # Tracking status pengaduan dengan kode laporan
├── login.php                # Login Administrator
├── logout.php               # Logout Administrator
│
├── admin/
│   ├── dashboard.php        # Ringkasan statistik laporan
│   ├── data_pengaduan.php   # Tabel data & filter status pengaduan
│   ├── update_status.php    # Tindak lanjut laporan & upload bukti selesai
│   ├── data_berita.php      # Manajemen berita kelurahan
│   ├── tambah_berita.php    # Form tambah berita + upload gambar
│   ├── edit_berita.php      # Form edit berita
│   └── hapus_berita.php     # Hapus berita
│
└── assets/                  # File statis gambar dan styling
```

---

## 🚀 Panduan Setup & Hosting

### 1. Setup Database di Supabase
1. Daftar atau login ke [Supabase](https://supabase.com).
2. Buat project baru.
3. Buka menu **SQL Editor**, buka file `supabase_schema.sql` pada repository ini, salin seluruh isinya lalu klik **Run**.
4. Pastikan di menu **Storage** sudah terdapat bucket bernama `pengaduan` dengan status **Public**.

---

### 2. Jalankan di Komputer Lokal (Development)
1. Salin `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
2. Isi nilai kredensial dari dashboard Supabase (**Project Settings** ➔ **Database** dan **API**):
   ```ini
   DB_HOST=db.xxxxxxxxxxxxxxxxxxxx.supabase.co
   DB_PORT=5432
   DB_NAME=postgres
   DB_USER=postgres
   DB_PASS=PasswordDatabaseAnda

   SUPABASE_URL=https://xxxxxxxxxxxxxxxxxxxx.supabase.co
   SUPABASE_KEY=anon-key-atau-service-role-key
   SUPABASE_BUCKET=pengaduan
   ```
3. Jalankan built-in web server PHP:
   ```bash
   php -S localhost:8000
   ```
4. Buka di browser: `http://localhost:8000`.

---

### 3. Deploy ke Vercel lewat GitHub

1. **Inisialisasi Git & Push ke GitHub**:
   ```bash
   git init
   git add .
   git commit -m "Migrate to Supabase and Vercel"
   git remote add origin https://github.com/USERNAME/NAMA_REPO.git
   git branch -M main
   git push -u origin main
   ```

2. **Import ke Vercel**:
   - Buka [vercel.com](https://vercel.com) dan login dengan GitHub.
   - Klik **"Add New..."** ➔ **"Project"** ➔ Import repository kamu.

3. **Atur Environment Variables di Vercel**:
   Pada menu konfigurasi project Vercel sebelum menekan tombol Deploy, tambahkan variabel berikut di tab **Environment Variables**:
   - `DB_HOST`: Host PostgreSQL Supabase
   - `DB_PORT`: `5432`
   - `DB_NAME`: `postgres`
   - `DB_USER`: `postgres`
   - `DB_PASS`: Password database Supabase kamu
   - `SUPABASE_URL`: URL project Supabase (`https://<project-ref>.supabase.co`)
   - `SUPABASE_KEY`: Supabase API Key (Anon / Service Role Key)
   - `SUPABASE_BUCKET`: `pengaduan`

4. Klik **Deploy** dan website kamu sudah online!

---

## 🔐 Akun Default Admin
- **Username**: `admin`
- **Password**: `admin123`
