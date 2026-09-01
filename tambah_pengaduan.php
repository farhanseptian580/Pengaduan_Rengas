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
    if (!$pdo) {
        $error = "Gagal terhubung ke database. Pastikan konfigurasi Supabase sudah benar di Environment Variables.";
    } else {
        $kode_laporan = generateKodeLaporan();
        $nama = trim($_POST['nama'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $judul_laporan = trim($_POST['judul_laporan'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $lokasi = trim($_POST['lokasi'] ?? '');
        $latitude = trim($_POST['latitude'] ?? '');
        $longitude = trim($_POST['longitude'] ?? '');
        
        // Upload foto bukti ke Supabase Storage
        $foto_db = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmp_foto = $_FILES['foto']['tmp_name'];
            $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_ext, $allowed_ext)) {
                $foto_db = 'pengaduan_' . time() . '_' . uniqid() . '.' . $file_ext;
                $mime_type = mime_content_type($tmp_foto) ?: 'image/jpeg';
                
                $uploaded = upload_to_supabase($tmp_foto, $foto_db, $mime_type, 'pengaduan');
                if (!$uploaded) {
                    global $last_storage_error;
                    $detail = !empty($last_storage_error) ? " ({$last_storage_error})" : "";
                    $error = "Gagal mengunggah foto ke Supabase Storage{$detail}. Pastikan menggunakan service_role key.";
                }
            } else {
                $error = "Format foto tidak didukung. Harap gunakan format JPG, JPEG, PNG, GIF, atau WEBP.";
            }
        } else {
            $error = "Harap lampirkan foto bukti pengaduan.";
        }
        
        // Insert data ke database Supabase
        if (!isset($error)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO pengaduan (kode_laporan, nama, no_hp, judul_laporan, deskripsi, lokasi, latitude, longitude, foto, status) 
                    VALUES (:kode_laporan, :nama, :no_hp, :judul_laporan, :deskripsi, :lokasi, :latitude, :longitude, :foto, 'Menunggu')
                ");
                
                $stmt->execute([
                    ':kode_laporan' => $kode_laporan,
                    ':nama' => $nama,
                    ':no_hp' => $no_hp,
                    ':judul_laporan' => $judul_laporan,
                    ':deskripsi' => $deskripsi,
                    ':lokasi' => $lokasi,
                    ':latitude' => $latitude ?: null,
                    ':longitude' => $longitude ?: null,
                    ':foto' => $foto_db
                ]);
                
                $success = true;
                $kode_laporan_result = $kode_laporan;
            } catch (PDOException $e) {
                $error = "Gagal menyimpan pengaduan ke database: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Buat Pengaduan - Sistem Pengaduan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.72), rgba(0, 0, 0, 0.72)), url('assets/Kantor-Kelurahan-Rengas.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        }
        .form-control, .form-select {
            border-radius: 10px;
            font-size: 1rem;
            min-height: 48px;
            padding: 10px 14px;
        }
        textarea.form-control {
            min-height: 120px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        #map {
            height: 280px;
            width: 100%;
            border-radius: 12px;
            margin-bottom: 12px;
            z-index: 1;
        }
        @media (min-width: 768px) {
            #map {
                height: 380px;
            }
        }
        .btn-submit {
            border-radius: 30px;
            min-height: 50px;
            font-weight: 600;
            font-size: 1.05rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-2 py-lg-3">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                <i class="bi bi-megaphone-fill text-primary me-2"></i>
                <span>Pengaduan Masyarakat</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse mt-2 mt-lg-0" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link py-2 px-3 rounded" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active py-2 px-3 rounded" href="tambah_pengaduan.php">Buat Pengaduan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 px-3 rounded" href="cek_status.php">Cek Status</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 px-3 rounded btn btn-outline-light text-start text-lg-center mt-2 mt-lg-0" href="login.php">
                            <i class="bi bi-shield-lock"></i> Login Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <section class="py-4 py-md-5 flex-grow-1">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8 col-xl-7">
                    <div class="card card-custom p-3 p-sm-4 p-md-5 bg-white">
                        <div class="card-body p-0">
                            <div class="text-center mb-4">
                                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold mb-2">Formulir Online</span>
                                <h2 class="fw-bold text-dark mb-1">
                                    <i class="bi bi-pencil-square text-primary me-1"></i> Buat Pengaduan
                                </h2>
                                <p class="text-muted small mb-0">Lengkapi data laporan Anda di bawah ini dengan jelas.</p>
                            </div>
                            
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success border-0 shadow-sm p-4 rounded-4 text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-check-circle-fill text-success display-4"></i>
                                    </div>
                                    <h4 class="fw-bold text-success mb-2">Pengaduan Berhasil Dikirim!</h4>
                                    <p class="text-muted mb-3">Simpan kode laporan ini untuk melacak perkembangan status penanganan laporan Anda.</p>
                                    
                                    <div class="p-3 bg-light rounded-3 border d-inline-block w-100 mb-3" style="max-width: 320px;">
                                        <small class="text-muted d-block mb-1">KODE LAPORAN ANDA:</small>
                                        <span class="fs-4 fw-bold font-monospace text-primary tracking-wide"><?php echo htmlspecialchars($kode_laporan_result); ?></span>
                                    </div>
                                    
                                    <div class="d-grid gap-2 mt-2">
                                        <a href="cek_status.php" class="btn btn-success btn-submit d-flex align-items-center justify-content-center shadow-sm">
                                            <i class="bi bi-search me-2"></i> Cek Status Sekarang
                                        </a>
                                        <a href="index.php" class="btn btn-outline-secondary rounded-pill py-2">
                                            Kembali ke Beranda
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger rounded-3 d-flex align-items-center mb-4">
                                        <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                                        <div><?php echo htmlspecialchars($error); ?></div>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>" placeholder="Nama lengkap Anda" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="no_hp" class="form-label fw-semibold">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($_POST['no_hp'] ?? ''); ?>" placeholder="Contoh: 08123456789" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="judul_laporan" class="form-label fw-semibold">Judul Pengaduan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="judul_laporan" name="judul_laporan" value="<?php echo htmlspecialchars($_POST['judul_laporan'] ?? ''); ?>" placeholder="Ringkasan masalah (contoh: Lampu Jalan Mati)" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label fw-semibold">Deskripsi Lengkap <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" placeholder="Ceritakan detail kejadian/keluhan Anda..." required><?php echo htmlspecialchars($_POST['deskripsi'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="lokasi" class="form-label fw-semibold">Alamat Lokasi Kejadian <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?php echo htmlspecialchars($_POST['lokasi'] ?? ''); ?>" placeholder="Nama jalan, RT/RW, atau patokan lokasi" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                                            <span>Titik Lokasi di Peta</span>
                                            <span class="badge bg-secondary-subtle text-secondary fw-normal">Opsional</span>
                                        </label>
                                        <div id="map"></div>
                                        <div class="form-text small"><i class="bi bi-geo-alt"></i> Klik / sentuh pada peta untuk menandai titik koordinat kejadian.</div>
                                        <input type="hidden" id="latitude" name="latitude" value="<?php echo htmlspecialchars($_POST['latitude'] ?? ''); ?>">
                                        <input type="hidden" id="longitude" name="longitude" value="<?php echo htmlspecialchars($_POST['longitude'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="foto" class="form-label fw-semibold">Foto Bukti Pengaduan <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*" required>
                                        <div class="form-text small">Format gambar: JPG, PNG, JPEG, WEBP. Maksimal 10MB.</div>
                                    </div>
                                    
                                    <button type="submit" name="submit" class="btn btn-primary btn-submit w-100 shadow">
                                        <i class="bi bi-send-fill me-2"></i> Kirim Pengaduan Sekarang
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
            <p class="mb-0 small text-light opacity-75">&copy; 2026 Sistem Pengaduan Masyarakat Kelurahan Rengas. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var initialLat = document.getElementById('latitude').value || -6.2831;
        var initialLng = document.getElementById('longitude').value || 106.7501;
        var map = L.map('map').setView([initialLat, initialLng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        var marker = null;
        if (document.getElementById('latitude').value && document.getElementById('longitude').value) {
            marker = L.marker([initialLat, initialLng]).addTo(map);
        }
        
        map.on('click', function(e) {
            if (marker) {
                map.removeLayer(marker);
            }
            marker = L.marker(e.latlng).addTo(map);
            document.getElementById('latitude').value = e.latlng.lat;
            document.getElementById('longitude').value = e.latlng.lng;
        });
    </script>
</body>
</html>
