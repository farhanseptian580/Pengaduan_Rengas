<?php
include 'koneksi.php';

// Ambil berita terbaru (3 berita terbaru)
$query_berita = "SELECT * FROM berita ORDER BY tanggal_berita DESC LIMIT 3";
$result_berita = mysqli_query($conn, $query_berita);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            background: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.65)), url('assets/Kantor-Kelurahan-Rengas.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            padding-bottom: 20px;
        }
        .hero-section {
            padding: 100px 0 40px 0;
        }
        .card-custom {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card-custom:hover {
            transform: translateY(-10px);
        }
        .btn-custom {
            border-radius: 25px;
            padding: 12px 30px;
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
                        <a class="nav-link active" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tambah_pengaduan.php">Buat Pengaduan</a>
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

    <!-- Hero & Features Section Wrapper -->
    <div class="hero-wrapper">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-8">
                        <h1 class="display-4 fw-bold mb-4">Sistem Pengaduan Masyarakat</h1>
                        <p class="lead mb-5">Layanan pengaduan online untuk menyampaikan aspirasi, saran, dan keluhan Anda kepada pemerintah dengan mudah dan cepat.</p>
                        <a href="tambah_pengaduan.php" class="btn btn-light btn-custom btn-lg me-2">
                            <i class="bi bi-plus-circle"></i> Buat Pengaduan
                        </a>
                        <a href="cek_status.php" class="btn btn-outline-light btn-custom btn-lg">
                            <i class="bi bi-search"></i> Cek Status
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-5">
            <div class="container">
               <div class="row justify-content-center">
                   <div class="col-md-5 col-lg-4 mb-4">
                        <div class="card card-custom h-100 p-4 text-center position-relative">
                            <div class="card-body">
                                <i class="bi bi-pencil-square display-4 text-primary mb-3"></i>
                                <h4 class="card-title text-dark">Buat Pengaduan</h4>
                                <p class="card-text text-muted">Isi formulir pengaduan dengan lengkap dan lampirkan bukti foto untuk mempercepat proses penanganan.</p>
                                <a href="tambah_pengaduan.php" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card card-custom h-100 p-4 text-center position-relative">
                            <div class="card-body">
                                <i class="bi bi-eye display-4 text-warning mb-3"></i>
                                <h4 class="card-title text-dark">Tracking Status</h4>
                                <p class="card-text text-muted">Pantau status laporan Anda secara real-time: Menunggu, Diproses, atau Selesai.</p>
                                <a href="cek_status.php" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Berita Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold"><i class="bi bi-newspaper"></i> Berita Terbaru</h2>
                <p class="text-muted">Ikuti perkembangan dan informasi terbaru mengenai kelurahan kami</p>
            </div>
            <div class="row">
                <?php if (mysqli_num_rows($result_berita) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result_berita)): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card card-custom h-100">
                                <img src="uploads/<?php echo htmlspecialchars($row['foto']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['judul']); ?>" style="height: 200px; object-fit: cover; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                                <div class="card-body d-flex flex-column bg-white" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                                    <small class="text-primary mb-2 d-block">
                                        <i class="bi bi-calendar3"></i> <?php echo date('d M Y', strtotime($row['tanggal_berita'])); ?>
                                    </small>
                                    <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($row['judul']); ?></h5>
                                    <p class="card-text text-muted flex-grow-1">
                                        <?php 
                                        $isi = strip_tags($row['isi']);
                                        echo strlen($isi) > 120 ? substr($isi, 0, 120) . '...' : $isi; 
                                        ?>
                                    </p>
                                    <a href="#" class="btn btn-outline-primary btn-sm mt-3 align-self-start btn-custom" data-bs-toggle="modal" data-bs-target="#modalBerita<?php echo $row['id_berita']; ?>" style="border-radius: 20px; padding: 6px 20px;">
                                        Baca Selengkapnya
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Detail Berita -->
                        <div class="modal fade" id="modalBerita<?php echo $row['id_berita']; ?>" tabindex="-1" aria-labelledby="modalLabel<?php echo $row['id_berita']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content" style="border-radius: 15px; border: none; overflow: hidden;">
                                    <img src="uploads/<?php echo htmlspecialchars($row['foto']); ?>" class="w-100" alt="<?php echo htmlspecialchars($row['judul']); ?>" style="max-height: 400px; object-fit: cover;">
                                    <div class="modal-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="badge bg-primary">
                                                <i class="bi bi-calendar3"></i> <?php echo date('d F Y', strtotime($row['tanggal_berita'])); ?>
                                            </span>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <h3 class="fw-bold mb-3"><?php echo htmlspecialchars($row['judul']); ?></h3>
                                        <div style="white-space: pre-line;" class="text-muted leading-relaxed">
                                            <?php echo htmlspecialchars($row['isi']); ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0">
                                        <button type="button" class="btn btn-secondary btn-custom" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <div class="p-5 text-muted">
                            <i class="bi bi-newspaper display-3"></i>
                            <p class="mt-3 fs-5">Belum ada berita terbaru saat ini.</p>
                        </div>
                    </div>
                <?php endif; ?>
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
</body>
</html>
