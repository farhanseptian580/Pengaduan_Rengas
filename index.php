<?php
include 'koneksi.php';

// Ambil berita terbaru (3 berita terbaru)
$berita_list = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM berita ORDER BY tanggal_berita DESC, id_berita DESC LIMIT 3");
        $berita_list = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error query berita: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Sistem Pengaduan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .hero-wrapper {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/Kantor-Kelurahan-Rengas.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            padding-bottom: 30px;
        }
        .hero-section {
            padding: 60px 0 30px 0;
        }
        @media (min-width: 768px) {
            .hero-section {
                padding: 100px 0 50px 0;
            }
        }
        .hero-title {
            font-size: clamp(1.8rem, 4.5vw, 2.75rem);
            font-weight: 800;
            line-height: 1.25;
        }
        .hero-lead {
            font-size: clamp(1rem, 2.2vw, 1.25rem);
        }
        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }
        .btn-custom {
            border-radius: 30px;
            padding: 12px 28px;
            font-weight: 600;
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            touch-action: manipulation;
        }
        .news-img {
            height: 190px;
            object-fit: cover;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }
        @media (max-width: 576px) {
            .hero-btn-group .btn {
                width: 100%;
            }
            .news-img {
                height: 160px;
            }
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
                        <a class="nav-link active py-2 px-3 rounded" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 px-3 rounded" href="tambah_pengaduan.php">Buat Pengaduan</a>
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

    <!-- Hero & Features Section Wrapper -->
    <div class="hero-wrapper">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-9 col-xl-8">
                        <span class="badge bg-primary px-3 py-2 rounded-pill mb-3 fw-semibold">Kelurahan Rengas Online</span>
                        <h1 class="hero-title mb-3">Layanan Pengaduan & Aspirasi Warga</h1>
                        <p class="hero-lead text-light opacity-90 mb-4 px-2">Sampaikan aspirasi, saran, dan keluhan Anda secara transparan, mudah, dan cepat ditangani oleh pemerintah kelurahan.</p>
                        
                        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 hero-btn-group mb-2">
                            <a href="tambah_pengaduan.php" class="btn btn-primary btn-custom shadow">
                                <i class="bi bi-plus-circle-fill me-2"></i> Buat Pengaduan
                            </a>
                            <a href="cek_status.php" class="btn btn-outline-light btn-custom">
                                <i class="bi bi-search me-2"></i> Cek Status Laporan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-3 py-md-4">
            <div class="container">
                <div class="row justify-content-center g-3 g-md-4">
                    <div class="col-12 col-md-6 col-lg-5">
                        <div class="card card-custom h-100 p-3 p-sm-4 text-center position-relative">
                            <div class="card-body">
                                <div class="mb-3">
                                    <i class="bi bi-pencil-square display-5 text-primary"></i>
                                </div>
                                <h4 class="card-title text-dark fw-bold mb-2">Buat Pengaduan</h4>
                                <p class="card-text text-muted small">Isi formulir pengaduan, sertakan titik lokasi peta, dan lampirkan bukti foto untuk penanganan cepat.</p>
                                <a href="tambah_pengaduan.php" class="stretched-link" aria-label="Buat Pengaduan"></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-5">
                        <div class="card card-custom h-100 p-3 p-sm-4 text-center position-relative">
                            <div class="card-body">
                                <div class="mb-3">
                                    <i class="bi bi-eye display-5 text-warning"></i>
                                </div>
                                <h4 class="card-title text-dark fw-bold mb-2">Tracking Real-Time</h4>
                                <p class="card-text text-muted small">Pantau status penanganan laporan secara transparan: Menunggu, Diproses, atau Selesai.</p>
                                <a href="cek_status.php" class="stretched-link" aria-label="Cek Status Laporan"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Berita Section -->
    <section class="py-5 bg-light flex-grow-1">
        <div class="container">
            <div class="text-center mb-4 mb-md-5">
                <h2 class="fw-bold mb-2"><i class="bi bi-newspaper text-primary me-2"></i> Berita & Pengumuman</h2>
                <p class="text-muted">Informasi dan kegiatan terbaru di lingkungan Kelurahan Rengas</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <?php if (!empty($berita_list)): ?>
                    <?php foreach ($berita_list as $row): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card card-custom h-100 d-flex flex-column">
                                <img src="<?php echo htmlspecialchars(get_file_url($row['foto'])); ?>" class="card-img-top news-img" alt="<?php echo htmlspecialchars($row['judul']); ?>" loading="lazy">
                                <div class="card-body d-flex flex-column bg-white p-3 p-sm-4" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                                    <small class="text-primary fw-semibold mb-2 d-block">
                                        <i class="bi bi-calendar3 me-1"></i> <?php echo date('d M Y', strtotime($row['tanggal_berita'])); ?>
                                    </small>
                                    <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($row['judul']); ?></h5>
                                    <p class="card-text text-muted flex-grow-1 small">
                                        <?php 
                                        $isi = strip_tags($row['isi']);
                                        echo strlen($isi) > 110 ? substr($isi, 0, 110) . '...' : $isi; 
                                        ?>
                                    </p>
                                    <button type="button" class="btn btn-outline-primary btn-sm mt-3 align-self-start btn-custom" data-bs-toggle="modal" data-bs-target="#modalBerita<?php echo $row['id_berita']; ?>" style="border-radius: 20px; padding: 6px 18px; min-height: 38px;">
                                        Baca Selengkapnya
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Detail Berita -->
                        <div class="modal fade" id="modalBerita<?php echo $row['id_berita']; ?>" tabindex="-1" aria-labelledby="modalLabel<?php echo $row['id_berita']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                                <div class="modal-content border-0 rounded-4 shadow overflow-hidden">
                                    <img src="<?php echo htmlspecialchars(get_file_url($row['foto'])); ?>" class="w-100" alt="<?php echo htmlspecialchars($row['judul']); ?>" style="max-height: 350px; object-fit: cover;">
                                    <div class="modal-body p-3 p-sm-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="badge bg-primary px-3 py-2 rounded-pill">
                                                <i class="bi bi-calendar3 me-1"></i> <?php echo date('d F Y', strtotime($row['tanggal_berita'])); ?>
                                            </span>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <h3 class="fw-bold mb-3"><?php echo htmlspecialchars($row['judul']); ?></h3>
                                        <div style="white-space: pre-line;" class="text-muted lh-base">
                                            <?php echo htmlspecialchars($row['isi']); ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0 py-2">
                                        <button type="button" class="btn btn-secondary btn-custom" data-bs-dismiss="modal" style="min-height: 40px; padding: 8px 20px;">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="p-4 text-muted">
                            <i class="bi bi-newspaper display-3 text-secondary mb-3 d-block"></i>
                            <p class="fs-5 mb-0">Belum ada berita terbaru saat ini.</p>
                        </div>
                    </div>
                <?php endif; ?>
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
</body>
</html>
