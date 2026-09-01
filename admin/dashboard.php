<?php
include '../koneksi.php';

// Cek session admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

$total_pengaduan = 0;
$menunggu = 0;
$diproses = 0;
$selesai = 0;
$recent_pengaduan = [];

if ($pdo) {
    try {
        $total_pengaduan = (int)$pdo->query("SELECT COUNT(*) FROM pengaduan")->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pengaduan WHERE status = :status");
        
        $stmt->execute([':status' => 'Menunggu']);
        $menunggu = (int)$stmt->fetchColumn();
        
        $stmt->execute([':status' => 'Diproses']);
        $diproses = (int)$stmt->fetchColumn();
        
        $stmt->execute([':status' => 'Selesai']);
        $selesai = (int)$stmt->fetchColumn();
        
        $recent_stmt = $pdo->query("SELECT * FROM pengaduan ORDER BY tanggal_lapor DESC LIMIT 5");
        $recent_pengaduan = $recent_stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Dashboard query error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Dashboard Admin - Sistem Pengaduan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
        }
        @media (min-width: 992px) {
            .sidebar {
                min-height: 100vh;
            }
        }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 18px;
            margin: 4px 12px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255,255,255,0.08);
        }
        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
        }
        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
            background: #ffffff;
        }
        .stat-card {
            border-radius: 16px;
            padding: 16px 18px;
            transition: transform 0.2s ease;
        }
        @media (min-width: 768px) {
            .stat-card {
                padding: 24px;
            }
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .stat-icon {
            font-size: 2rem;
            opacity: 0.85;
        }
        @media (min-width: 768px) {
            .stat-icon {
                font-size: 2.8rem;
            }
        }
        .table-responsive {
            border-radius: 12px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .btn-action {
            min-height: 36px;
            padding: 6px 14px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <!-- Mobile Top Navigation Bar -->
    <nav class="navbar navbar-dark bg-dark d-lg-none sticky-top px-3 py-2 shadow-sm">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
            <i class="bi bi-shield-fill-check text-primary me-2"></i>
            <span>Admin Rengas</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebarOffcanvas" aria-controls="adminSidebarOffcanvas" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
    </nav>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Desktop Sidebar -->
            <div class="col-lg-3 col-xl-2 sidebar d-none d-lg-block p-0">
                <div class="p-4 border-bottom border-secondary border-opacity-25">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-megaphone-fill text-primary me-2"></i> Kelurahan Rengas</h5>
                    <small class="text-secondary d-block">Admin: <?php echo htmlspecialchars($_SESSION['nama_admin'] ?? 'Admin'); ?></small>
                </div>
                <nav class="nav flex-column py-3">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-2 fs-5"></i> Dashboard
                    </a>
                    <a class="nav-link" href="data_pengaduan.php">
                        <i class="bi bi-table me-2 fs-5"></i> Data Pengaduan
                    </a>
                    <a class="nav-link" href="data_berita.php">
                        <i class="bi bi-newspaper me-2 fs-5"></i> Data Berita
                    </a>
                    <a class="nav-link text-danger mt-4" href="../logout.php">
                        <i class="bi bi-box-arrow-right me-2 fs-5"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Mobile Offcanvas Sidebar -->
            <div class="offcanvas offcanvas-start bg-dark text-white d-lg-none" tabindex="-1" id="adminSidebarOffcanvas" aria-labelledby="offcanvasLabel">
                <div class="offcanvas-header border-bottom border-secondary">
                    <h5 class="offcanvas-title fw-bold" id="offcanvasLabel">
                        <i class="bi bi-shield-fill-check text-primary me-2"></i> Panel Admin
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body p-0">
                    <div class="p-3 bg-secondary bg-opacity-25 mb-2">
                        <small class="text-secondary d-block">Login sebagai:</small>
                        <strong class="text-white"><?php echo htmlspecialchars($_SESSION['nama_admin'] ?? 'Administrator'); ?></strong>
                    </div>
                    <nav class="nav flex-column p-2">
                        <a class="nav-link text-white active bg-primary rounded-3 mb-1" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                        <a class="nav-link text-light py-2" href="data_pengaduan.php">
                            <i class="bi bi-table me-2"></i> Data Pengaduan
                        </a>
                        <a class="nav-link text-light py-2" href="data_berita.php">
                            <i class="bi bi-newspaper me-2"></i> Data Berita
                        </a>
                        <hr class="border-secondary my-2">
                        <a class="nav-link text-danger py-2" href="../logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-12 col-lg-9 col-xl-10 p-3 p-sm-4 p-md-5">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Dashboard</h2>
                        <p class="text-muted small mb-0">Selamat datang kembali, <strong><?php echo htmlspecialchars($_SESSION['nama_admin'] ?? 'Admin'); ?></strong>!</p>
                    </div>
                    <div>
                        <a href="../index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Web Warga
                        </a>
                    </div>
                </div>
                
                <!-- Statistik Cards (2x2 on Mobile, 4 in a row on Desktop) -->
                <div class="row g-2 g-sm-3 g-md-4 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card card-custom stat-card bg-primary text-white h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold mb-0"><?php echo $total_pengaduan; ?></h3>
                                    <small class="opacity-90">Total Laporan</small>
                                </div>
                                <i class="bi bi-file-earmark-text stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card card-custom stat-card bg-warning text-dark h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold mb-0"><?php echo $menunggu; ?></h3>
                                    <small class="opacity-90">Menunggu</small>
                                </div>
                                <i class="bi bi-clock-history stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card card-custom stat-card bg-info text-dark h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold mb-0"><?php echo $diproses; ?></h3>
                                    <small class="opacity-90">Diproses</small>
                                </div>
                                <i class="bi bi-gear-wide-connected stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card card-custom stat-card bg-success text-white h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold mb-0"><?php echo $selesai; ?></h3>
                                    <small class="opacity-90">Selesai</small>
                                </div>
                                <i class="bi bi-check-circle-fill stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengaduan Terbaru -->
                <div class="card card-custom p-3 p-sm-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i> Pengaduan Terbaru</h5>
                        <a href="data_pengaduan.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            Lihat Semua <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary small">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Pelapor</th>
                                    <th>Judul Pengaduan</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_pengaduan)): ?>
                                    <?php foreach ($recent_pengaduan as $row): ?>
                                        <?php
                                        $status_badge = '';
                                        if ($row['status'] == 'Menunggu') {
                                            $status_badge = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                        } elseif ($row['status'] == 'Diproses') {
                                            $status_badge = 'bg-info-subtle text-info-emphasis border border-info-subtle';
                                        } else {
                                            $status_badge = 'bg-success-subtle text-success-emphasis border border-success-subtle';
                                        }
                                        ?>
                                        <tr>
                                            <td><span class="badge bg-dark font-monospace"><?php echo htmlspecialchars($row['kode_laporan']); ?></span></td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 220px;"><?php echo htmlspecialchars($row['judul_laporan']); ?></div>
                                            </td>
                                            <td><span class="badge <?php echo $status_badge; ?> px-2 py-1"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                            <td class="small text-muted"><?php echo date('d/m/Y', strtotime($row['tanggal_lapor'])); ?></td>
                                            <td class="text-end">
                                                <a href="update_status.php?id=<?php echo $row['id_pengaduan']; ?>" class="btn btn-sm btn-primary btn-action">
                                                    <i class="bi bi-pencil-square me-1"></i> Tindak Lanjut
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Belum ada data pengaduan masuk</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
