<?php
include '../koneksi.php';

// Cek session admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

$berita_data = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM berita ORDER BY tanggal_berita DESC, id_berita DESC");
        $berita_data = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching berita: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Data Berita - Sistem Pengaduan Masyarakat</title>
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
        .img-thumbnail-custom {
            width: 70px;
            height: 48px;
            object-fit: cover;
            border-radius: 8px;
        }
        .btn-action-sm {
            min-height: 36px;
            padding: 4px 12px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            font-size: 0.85rem;
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
                    <a class="nav-link" href="data_pengaduan.php">
                        <i class="bi bi-table me-2 fs-5"></i> Data Pengaduan
                    </a>
                    <a class="nav-link active" href="data_berita.php">
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
                        <a class="nav-link text-light py-2" href="data_pengaduan.php">
                            <i class="bi bi-table me-2"></i> Data Pengaduan
                        </a>
                        <a class="nav-link text-white active bg-primary rounded-3 mb-1" href="data_berita.php">
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
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Manajemen Berita</h2>
                        <p class="text-muted small mb-0">Publikasikan informasi dan kegiatan terbaru kelurahan.</p>
                    </div>
                    <div>
                        <a href="tambah_berita.php" class="btn btn-primary rounded-pill px-3 py-2 shadow-sm d-flex align-items-center" style="min-height: 44px;">
                            <i class="bi bi-plus-circle-fill me-2"></i> Tambah Berita Baru
                        </a>
                    </div>
                </div>

                <!-- Table Data -->
                <div class="card card-custom p-3 p-sm-4 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary small">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Foto Sampul</th>
                                    <th>Judul Berita</th>
                                    <th>Tanggal Publikasi</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                if (!empty($berita_data)) {
                                    foreach ($berita_data as $row) {
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td>
                                                <img src="<?php echo htmlspecialchars(get_file_url($row['foto'])); ?>" class="img-thumbnail-custom border" alt="Foto" loading="lazy">
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($row['judul']); ?></div>
                                                <small class="text-muted d-block text-truncate" style="max-width: 320px;">
                                                    <?php echo htmlspecialchars(substr(strip_tags($row['isi']), 0, 90)); ?>...
                                                </small>
                                            </td>
                                            <td class="small text-muted"><?php echo date('d/m/Y', strtotime($row['tanggal_berita'])); ?></td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <a href="edit_berita.php?id=<?php echo $row['id_berita']; ?>" class="btn btn-sm btn-outline-primary btn-action-sm">
                                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                                    </a>
                                                    <a href="hapus_berita.php?id=<?php echo $row['id_berita']; ?>" class="btn btn-sm btn-outline-danger btn-action-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus berita ini?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center text-muted py-5'>Belum ada artikel berita yang dipublikasikan</td></tr>";
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
