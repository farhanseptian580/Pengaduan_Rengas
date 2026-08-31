<?php
include '../koneksi.php';

session_start();

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Pengaduan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        @media (min-width: 768px) {
            .sidebar {
                min-height: 100vh;
            }
        }
        .sidebar .nav-link {
            color: white;
            padding: 15px 20px;
            margin: 5px 10px;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
        }
        .card-custom {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card-custom:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            padding: 30px;
        }
        .stat-icon {
            font-size: 3rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4">
                    <h4><i class="bi bi-megaphone-fill"></i> Admin</h4>
                    <hr>
                    <p class="mb-0">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_admin'] ?? 'Admin'); ?></p>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="data_pengaduan.php">
                        <i class="bi bi-table"></i> Data Pengaduan
                    </a>
                    <a class="nav-link" href="data_berita.php">
                        <i class="bi bi-newspaper"></i> Data Berita
                    </a>
                    <a class="nav-link" href="../logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h2 class="mb-4">Dashboard</h2>
                
                <!-- Statistik Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card card-custom stat-card bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3><?php echo $total_pengaduan; ?></h3>
                                    <p class="mb-0">Total Pengaduan</p>
                                </div>
                                <i class="bi bi-file-earmark-text stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card card-custom stat-card bg-warning text-dark">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3><?php echo $menunggu; ?></h3>
                                    <p class="mb-0">Menunggu</p>
                                </div>
                                <i class="bi bi-clock stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card card-custom stat-card bg-info text-dark">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3><?php echo $diproses; ?></h3>
                                    <p class="mb-0">Diproses</p>
                                </div>
                                <i class="bi bi-gear stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card card-custom stat-card bg-success text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3><?php echo $selesai; ?></h3>
                                    <p class="mb-0">Selesai</p>
                                </div>
                                <i class="bi bi-check-circle stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengaduan Terbaru -->
                <div class="card card-custom p-4">
                    <h4 class="mb-4"><i class="bi bi-clock-history"></i> Pengaduan Terbaru</h4>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Judul</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_pengaduan)): ?>
                                    <?php foreach ($recent_pengaduan as $row): ?>
                                        <?php
                                        $status_badge = '';
                                        if ($row['status'] == 'Menunggu') {
                                            $status_badge = 'bg-warning text-dark';
                                        } elseif ($row['status'] == 'Diproses') {
                                            $status_badge = 'bg-info text-dark';
                                        } else {
                                            $status_badge = 'bg-success text-white';
                                        }
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($row['kode_laporan']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($row['judul_laporan']); ?></td>
                                            <td><span class="badge <?php echo $status_badge; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_lapor'])); ?></td>
                                            <td>
                                                <a href="update_status.php?id=<?php echo $row['id_pengaduan']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i> Detail / Update
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Belum ada data pengaduan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="data_pengaduan.php" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Lihat Semua Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
