<?php
include 'koneksi.php';

// Generate kode laporan otomatis
function generateKodeLaporan() {
    $prefix = 'LPR';
    $timestamp = date('Ymd');
    $random = rand(1000, 9999);
    return $prefix . $timestamp . $random;
}

// Proses submit form
if (isset($_POST['submit'])) {
    $kode_laporan = generateKodeLaporan();
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $judul_laporan = mysqli_real_escape_string($conn, $_POST['judul_laporan']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
    
    // Upload foto
    $foto = $_FILES['foto']['name'];
    $tmp_foto = $_FILES['foto']['tmp_name'];
    $foto_path = 'uploads/' . $foto;
    
    move_uploaded_file($tmp_foto, $foto_path);
    
    // Insert data ke database
    $query = "INSERT INTO pengaduan (kode_laporan, nama, no_hp, judul_laporan, deskripsi, lokasi, latitude, longitude, foto, status) 
              VALUES ('$kode_laporan', '$nama', '$no_hp', '$judul_laporan', '$deskripsi', '$lokasi', '$latitude', '$longitude', '$foto', 'Menunggu')";
    
    if (mysqli_query($conn, $query)) {
        $success = true;
        $kode_laporan_result = $kode_laporan;
    } else {
        $error = "Gagal mengirim pengaduan: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pengaduan - Sistem Pengaduan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.65)), url('assets/Kantor-Kelurahan-Rengas.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .card-custom {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        #map {
            height: 400px;
            width: 100%;
            border-radius: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-megaphone-fill"></i> Sistem Pengaduan Masyarakat
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="tambah_pengaduan.php">Buat Pengaduan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cek_status.php">Cek Status</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card card-custom p-4">
                        <div class="card-body">
                            <h2 class="text-center mb-4">
                                <i class="bi bi-pencil-square"></i> Form Pengaduan Masyarakat
                            </h2>
                            
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success">
                                    <h4><i class="bi bi-check-circle"></i> Pengaduan Berhasil Dikirim!</h4>
                                    <p>Kode Laporan Anda: <strong><?php echo $kode_laporan_result; ?></strong></p>
                                    <p class="mb-0">Simpan kode laporan ini untuk tracking status pengaduan Anda.</p>
                                    <a href="cek_status.php" class="btn btn-success mt-3">Cek Status Sekarang</a>
                                </div>
                            <?php elseif (isset($error)): ?>
                                <div class="alert alert-danger">
                                    <?php echo $error; ?>
                                </div>
                            <?php else: ?>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama" name="nama" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="no_hp" class="form-label">Nomor HP</label>
                                        <input type="text" class="form-control" id="no_hp" name="no_hp" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="judul_laporan" class="form-label">Judul Laporan</label>
                                        <input type="text" class="form-control" id="judul_laporan" name="judul_laporan" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi Laporan</label>
                                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="lokasi" class="form-label">Lokasi Kejadian</label>
                                        <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Ketik alamat lengkap" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Lokasi di Peta</label>
                                        <div id="map"></div>
                                        <div class="form-text">Klik pada peta untuk menentukan lokasi kejadian</div>
                                        <input type="hidden" id="latitude" name="latitude">
                                        <input type="hidden" id="longitude" name="longitude">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="foto" class="form-label">Foto Bukti</label>
                                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*" required>
                                        <div class="form-text">Upload foto sebagai bukti pengaduan (JPG, PNG, JPEG)</div>
                                    </div>
                                    
                                    <button type="submit" name="submit" class="btn btn-primary w-100 btn-lg">
                                        <i class="bi bi-send"></i> Kirim Pengaduan
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 Sistem Pengaduan Masyarakat. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Inisialisasi peta (pusat ke Indonesia)
        var map = L.map('map').setView([-2.5489, 118.0149], 5);
        
        // Tambahkan tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        var marker = null;
        
        // Event klik pada peta
        map.on('click', function(e) {
            // Hapus marker lama jika ada
            if (marker) {
                map.removeLayer(marker);
            }
            
            // Tambahkan marker baru
            marker = L.marker(e.latlng).addTo(map);
            
            // Simpan koordinat ke input hidden
            document.getElementById('latitude').value = e.latlng.lat;
            document.getElementById('longitude').value = e.latlng.lng;
            
            // Tampilkan koordinat
            alert('Lokasi dipilih!\nLatitude: ' + e.latlng.lat + '\nLongitude: ' + e.latlng.lng);
        });
    </script>
</body>
</html>
