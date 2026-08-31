<?php
include 'koneksi.php';

$result = null;
$error = null;

if (isset($_POST['submit'])) {
    $kode_laporan = mysqli_real_escape_string($conn, $_POST['kode_laporan']);
    
    $query = "SELECT * FROM pengaduan WHERE kode_laporan = '$kode_laporan'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
    } else {
        $error = "Kode laporan tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status - Sistem Pengaduan Masyarakat</title>
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
        .status-badge {
            font-size: 1.2rem;
            padding: 10px 20px;
            border-radius: 25px;
        }
        .status-menunggu {
            background-color: #ffc107;
            color: #000;
        }
        .status-diproses {
            background-color: #0dcaf0;
            color: #000;
        }
        .status-selesai {
            background-color: #198754;
            color: #fff;
        }
        #map {
            height: 300px;
            width: 100%;
            border-radius: 10px;
            margin-top: 10px;
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
                        <a class="nav-link" href="tambah_pengaduan.php">Buat Pengaduan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="cek_status.php">Cek Status</a>
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
                                <i class="bi bi-search"></i> Cek Status Pengaduan
                            </h2>
                            
                            <form method="POST">
                                <div class="input-group mb-4">
                                    <input type="text" class="form-control form-control-lg" 
                                           id="kode_laporan" name="kode_laporan" 
                                           placeholder="Masukkan Kode Laporan (contoh: LPR202405260001)" 
                                           value="<?php echo isset($_POST['kode_laporan']) ? $_POST['kode_laporan'] : ''; ?>" required>
                                    <button type="submit" name="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-search"></i> Cari
                                    </button>
                                </div>
                            </form>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($data)): ?>
                                <div class="alert alert-info">
                                    <h4 class="alert-heading"><i class="bi bi-info-circle"></i> Detail Pengaduan</h4>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <strong>Kode Laporan:</strong><br>
                                            <?php echo $data['kode_laporan']; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Tanggal Lapor:</strong><br>
                                            <?php echo date('d F Y H:i', strtotime($data['tanggal_lapor'])); ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Nama Pelapor:</strong><br>
                                            <?php echo $data['nama']; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>No HP:</strong><br>
                                            <?php echo $data['no_hp']; ?>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <strong>Judul Laporan:</strong><br>
                                            <?php echo $data['judul_laporan']; ?>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <strong>Deskripsi:</strong><br>
                                            <?php echo $data['deskripsi']; ?>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <strong>Lokasi:</strong><br>
                                            <?php echo $data['lokasi']; ?>
                                            <?php if ($data['latitude'] && $data['longitude']): ?>
                                                <div id="map"></div>
                                                <input type="hidden" id="latitude" value="<?php echo $data['latitude']; ?>">
                                                <input type="hidden" id="longitude" value="<?php echo $data['longitude']; ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <strong>Foto Bukti:</strong><br>
                                            <img src="uploads/<?php echo $data['foto']; ?>" class="img-fluid rounded" alt="Foto Bukti" style="max-height: 300px;">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <strong>Status:</strong><br>
                                            <?php
                                            $status_class = '';
                                            if ($data['status'] == 'Menunggu') {
                                                $status_class = 'status-menunggu';
                                            } elseif ($data['status'] == 'Diproses') {
                                                $status_class = 'status-diproses';
                                            } else {
                                                $status_class = 'status-selesai';
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?> status-badge">
                                                <i class="bi bi-clock"></i> <?php echo $data['status']; ?>
                                            </span>
                                        </div>
                                        <?php if ($data['tanggapan']): ?>
                                            <div class="col-md-12 mb-3">
                                                <strong>Tanggapan Admin:</strong><br>
                                                <div class="alert alert-success">
                                                    <?php echo $data['tanggapan']; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($data['foto_selesai']) && $data['status'] == 'Selesai'): ?>
                                            <div class="col-md-12 mb-3">
                                                <strong>Foto Bukti Laporan Selesai:</strong><br>
                                                <img src="uploads/<?php echo $data['foto_selesai']; ?>" class="img-fluid rounded border border-success p-1" alt="Foto Bukti Selesai" style="max-height: 300px;">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
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
        <?php if (isset($data) && $data['latitude'] && $data['longitude']): ?>
            // Inisialisasi peta dengan koordinat dari database
            var lat = <?php echo $data['latitude']; ?>;
            var lng = <?php echo $data['longitude']; ?>;
            var map = L.map('map').setView([lat, lng], 15);
            
            // Tambahkan tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            // Tambahkan marker di lokasi
            L.marker([lat, lng]).addTo(map)
                .bindPopup('Lokasi Kejadian')
                .openPopup();
        <?php endif; ?>
    </script>
</body>
</html>
