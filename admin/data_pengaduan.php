<?php
include '../koneksi.php';

// Cek session admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Filter berdasarkan status
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$data_pengaduan = [];

if ($pdo) {
    try {
        if (!empty($status_filter)) {
            $stmt = $pdo->prepare("SELECT * FROM pengaduan WHERE status = :status ORDER BY tanggal_lapor DESC");
            $stmt->execute([':status' => $status_filter]);
        } else {
            $stmt = $pdo->query("SELECT * FROM pengaduan ORDER BY tanggal_lapor DESC");
        }
        $data_pengaduan = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching pengaduan: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Data Pengaduan - Sistem Pengaduan Masyarakat</title>
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
        .table-responsive {
            border-radius: 12px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .filter-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 4px;
        }
        .filter-scroll::-webkit-scrollbar {
            height: 4px;
        }
        .filter-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .btn-filter {
            white-space: nowrap;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.9rem;
            min-height: 38px;
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
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-2 fs-5"></i> Dashboard
                    </a>
                    <a class="nav-link active" href="data_pengaduan.php">
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
                        <a class="nav-link text-light py-2" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                        <a class="nav-link text-white active bg-primary rounded-3 mb-1" href="data_pengaduan.php">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Data Pengaduan</h2>
                        <p class="text-muted small mb-0">Kelola dan tanggapi seluruh laporan warga.</p>
                    </div>
                </div>
                
                <!-- Filter Status (Scrollable on Mobile) -->
                <div class="card card-custom p-3 p-sm-4 mb-4 shadow-sm">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <span class="fw-bold small text-secondary"><i class="bi bi-funnel me-1"></i> Filter Status Laporan:</span>
                        <div class="filter-scroll d-flex gap-2">
                            <a href="data_pengaduan.php" class="btn btn-filter <?php echo $status_filter == '' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                                Semua (<?php echo count($data_pengaduan); ?>)
                            </a>
                            <a href="data_pengaduan.php?status=Menunggu" class="btn btn-filter <?php echo $status_filter == 'Menunggu' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark'; ?>">
                                <i class="bi bi-clock-history me-1"></i> Menunggu
                            </a>
                            <a href="data_pengaduan.php?status=Diproses" class="btn btn-filter <?php echo $status_filter == 'Diproses' ? 'btn-info text-dark' : 'btn-outline-info text-dark'; ?>">
                                <i class="bi bi-gear-wide-connected me-1"></i> Diproses
                            </a>
                            <a href="data_pengaduan.php?status=Selesai" class="btn btn-filter <?php echo $status_filter == 'Selesai' ? 'btn-success' : 'btn-outline-success'; ?>">
                                <i class="bi bi-check-circle-fill me-1"></i> Selesai
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Table Data -->
                <div class="card card-custom p-3 p-sm-4 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary small">
                                <tr>
                                    <th width="4%">No</th>
                                    <th>Kode Laporan</th>
                                    <th>Nama Pelapor</th>
                                    <th>No HP</th>
                                    <th>Judul Pengaduan</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                if (!empty($data_pengaduan)) {
                                    foreach ($data_pengaduan as $row) {
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
                                            <td><?php echo $no++; ?></td>
                                            <td><span class="badge bg-dark font-monospace"><?php echo htmlspecialchars($row['kode_laporan']); ?></span></td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td><a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $row['no_hp']); ?>" target="_blank" class="text-decoration-none text-success small"><i class="bi bi-whatsapp"></i> <?php echo htmlspecialchars($row['no_hp']); ?></a></td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($row['judul_laporan']); ?>">
                                                    <?php echo htmlspecialchars($row['judul_laporan']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 160px;" title="<?php echo htmlspecialchars($row['lokasi']); ?>">
                                                    <?php echo htmlspecialchars($row['lokasi']); ?>
                                                </div>
                                            </td>
                                            <td><span class="badge <?php echo $status_badge; ?> px-2 py-1"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                            <td class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($row['tanggal_lapor'])); ?></td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <a href="update_status.php?id=<?php echo $row['id_pengaduan']; ?>" class="btn btn-sm btn-primary rounded-pill px-3 py-1" style="min-height: 36px; display: inline-flex; align-items: center;">
                                                        <i class="bi bi-pencil-square me-1"></i> Detail
                                                    </a>
                                                    <a href="hapus_pengaduan.php?id=<?php echo $row['id_pengaduan']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" style="min-height: 36px; display: inline-flex; align-items: center;" onclick="return confirm('Apakah Anda yakin ingin menghapus pengaduan ini (Kode: <?php echo htmlspecialchars($row['kode_laporan']); ?>)?\n\nData dan file foto terkait akan dihapus secara permanen.')" title="Hapus Pengaduan">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='text-center text-muted py-5'>Tidak ada data pengaduan yang ditemukan</td></tr>";
                                }
                                ?>
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
