<?php
include 'koneksi.php';

$result = null;
$error = null;
$data = null;

if (isset($_POST['submit'])) {
    $kode_laporan = trim($_POST['kode_laporan'] ?? '');
    
    if (empty($kode_laporan)) {
        $error = "Silakan masukkan kode laporan!";
    } elseif (!$pdo) {
        $error = "Gagal terhubung ke database Supabase. Periksa pengaturan koneksi Anda.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM pengaduan WHERE kode_laporan = :kode_laporan LIMIT 1");
            $stmt->execute([':kode_laporan' => $kode_laporan]);
            $data = $stmt->fetch();
            
            if (!$data) {
                $error = "Kode laporan tidak ditemukan! Pastikan kode laporan yang dimasukkan sudah benar.";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan saat mencari laporan: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Cek Status - Sistem Pengaduan Masyarakat</title>
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
        .status-badge {
            font-size: 1.05rem;
            padding: 8px 18px;
            border-radius: 25px;
            display: inline-flex;
            align-items: center;
            font-weight: 600;
        }
        .status-menunggu {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .status-diproses {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        .status-selesai {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        #map {
            height: 250px;
            width: 100%;
            border-radius: 12px;
            margin-top: 10px;
            z-index: 1;
        }
        @media (min-width: 768px) {
            #map {
                height: 320px;
            }
        }
        .form-control-lg {
            min-height: 48px;
            font-size: 1rem;
            border-radius: 10px;
        }
        .btn-search {
            min-height: 48px;
            border-radius: 10px;
            font-weight: 600;
            padding: 0 24px;
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
                        <a class="nav-link py-2 px-3 rounded" href="tambah_pengaduan.php">Buat Pengaduan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active py-2 px-3 rounded" href="cek_status.php">Cek Status</a>
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
                                <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill fw-semibold mb-2">Tracking Laporan</span>
                                <h2 class="fw-bold text-dark mb-1">
                                    <i class="bi bi-search text-warning me-1"></i> Cek Status Pengaduan
                                </h2>
                                <p class="text-muted small mb-0">Masukkan kode laporan unik yang Anda peroleh saat membuat pengaduan.</p>
                            </div>
                            
                            <form method="POST" class="mb-4">
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <input type="text" class="form-control form-control-lg flex-grow-1" 
                                           id="kode_laporan" name="kode_laporan" 
                                           placeholder="Contoh: LPR202609011234" 
                                           value="<?php echo isset($_POST['kode_laporan']) ? htmlspecialchars($_POST['kode_laporan']) : ''; ?>" required>
                                    <button type="submit" name="submit" class="btn btn-primary btn-search d-flex align-items-center justify-content-center shadow-sm">
                                        <i class="bi bi-search me-2"></i> Lacak Laporan
                                    </button>
                                </div>
                            </form>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger rounded-3 d-flex align-items-center mb-0">
                                    <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($data): ?>
                                <div class="card border border-primary-subtle rounded-4 p-3 p-sm-4 bg-light shadow-sm">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-3 border-bottom">
                                        <div>
                                            <small class="text-muted d-block">Kode Laporan:</small>
                                            <span class="badge bg-dark font-monospace fs-6 px-3 py-2"><?php echo htmlspecialchars($data['kode_laporan']); ?></span>
                                        </div>
                                        <div>
                                            <?php
                                            $status_class = '';
                                            $status_icon = '';
                                            if ($data['status'] == 'Menunggu') {
                                                $status_class = 'status-menunggu';
                                                $status_icon = 'bi-hourglass-split';
                                            } elseif ($data['status'] == 'Diproses') {
                                                $status_class = 'status-diproses';
                                                $status_icon = 'bi-gear-wide-connected';
                                            } else {
                                                $status_class = 'status-selesai';
                                                $status_icon = 'bi-check-circle-fill';
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <i class="bi <?php echo $status_icon; ?> me-1"></i> <?php echo htmlspecialchars($data['status']); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-sm-6">
                                            <small class="text-muted d-block">Tanggal Lapor</small>
                                            <span class="fw-semibold text-dark"><?php echo date('d F Y H:i', strtotime($data['tanggal_lapor'])); ?> WIB</span>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <small class="text-muted d-block">Nama Pelapor</small>
                                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($data['nama']); ?></span>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted d-block">Judul Pengaduan</small>
                                            <h5 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($data['judul_laporan']); ?></h5>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted d-block">Deskripsi Keluhan</small>
                                            <div class="p-3 bg-white rounded-3 border text-secondary small lh-base" style="white-space: pre-line;"><?php echo htmlspecialchars($data['deskripsi']); ?></div>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted d-block">Lokasi Kejadian</small>
                                            <p class="fw-semibold text-dark mb-1"><?php echo htmlspecialchars($data['lokasi']); ?></p>
                                            <?php if (!empty($data['latitude']) && !empty($data['longitude'])): ?>
                                                <div id="map"></div>
                                                <input type="hidden" id="latitude" value="<?php echo htmlspecialchars($data['latitude']); ?>">
                                                <input type="hidden" id="longitude" value="<?php echo htmlspecialchars($data['longitude']); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted d-block mb-1">Foto Bukti Pengaduan</small>
                                            <img src="<?php echo htmlspecialchars(get_file_url($data['foto'])); ?>" class="img-fluid rounded-3 border w-100 shadow-sm" alt="Foto Bukti" style="max-height: 280px; object-fit: cover;" loading="lazy">
                                        </div>

                                        <?php if (!empty($data['tanggapan'])): ?>
                                            <div class="col-12">
                                                <div class="p-3 bg-success-subtle rounded-3 border border-success-subtle">
                                                    <strong class="text-success-emphasis d-flex align-items-center mb-1">
                                                        <i class="bi bi-chat-quote-fill me-2"></i> Tanggapan Resmi Kelurahan:
                                                    </strong>
                                                    <div class="text-dark small lh-base" style="white-space: pre-line;"><?php echo htmlspecialchars($data['tanggapan']); ?></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($data['foto_selesai']) && $data['status'] == 'Selesai'): ?>
                                            <div class="col-12">
                                                <small class="text-success fw-semibold d-block mb-1"><i class="bi bi-patch-check-fill"></i> Foto Bukti Laporan Selesai Ditangani:</small>
                                                <img src="<?php echo htmlspecialchars(get_file_url($data['foto_selesai'])); ?>" class="img-fluid rounded-3 border border-success w-100 shadow-sm" alt="Foto Selesai" style="max-height: 280px; object-fit: cover;" loading="lazy">
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
            <p class="mb-0 small text-light opacity-75">&copy; 2026 Sistem Pengaduan Masyarakat Kelurahan Rengas. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        <?php if ($data && !empty($data['latitude']) && !empty($data['longitude'])): ?>
            var lat = <?php echo floatval($data['latitude']); ?>;
            var lng = <?php echo floatval($data['longitude']); ?>;
            var map = L.map('map').setView([lat, lng], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            L.marker([lat, lng]).addTo(map)
                .bindPopup('Lokasi Kejadian: <?php echo addslashes(htmlspecialchars($data['lokasi'])); ?>')
                .openPopup();
        <?php endif; ?>
    </script>
</body>
</html>
