<?php
include '../koneksi.php';

session_start();

// Cek session admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit();
}

// Filter berdasarkan status
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = '';
if ($status_filter) {
    $where_clause = "WHERE status = '$status_filter'";
}

// Query untuk mendapatkan data pengaduan
$query = "SELECT * FROM pengaduan $where_clause ORDER BY tanggal_lapor DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengaduan - Sistem Pengaduan Masyarakat</title>
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
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4">
                    <h4><i class="bi bi-megaphone-fill"></i> Admin Panel</h4>
                    <hr>
                    <p class="mb-0">Selamat datang, <?php echo $_SESSION['nama_admin']; ?></p>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link active" href="data_pengaduan.php">
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
                <h2 class="mb-4">Data Pengaduan</h2>
                
                <!-- Filter Status -->
                <div class="card card-custom p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter Berdasarkan Status</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="btn-group w-100" role="group">
                                <a href="data_pengaduan.php" class="btn btn-outline-primary <?php echo $status_filter == '' ? 'active' : ''; ?>">Semua</a>
                                <a href="data_pengaduan.php?status=Menunggu" class="btn btn-outline-warning <?php echo $status_filter == 'Menunggu' ? 'active' : ''; ?>">Menunggu</a>
                                <a href="data_pengaduan.php?status=Diproses" class="btn btn-outline-info <?php echo $status_filter == 'Diproses' ? 'active' : ''; ?>">Diproses</a>
                                <a href="data_pengaduan.php?status=Selesai" class="btn btn-outline-success <?php echo $status_filter == 'Selesai' ? 'active' : ''; ?>">Selesai</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Data -->
                <div class="card card-custom p-4">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Laporan</th>
                                    <th>Nama</th>
                                    <th>No HP</th>
                                    <th>Judul Laporan</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
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
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $row['kode_laporan']; ?></td>
                                            <td><?php echo $row['nama']; ?></td>
                                            <td><?php echo $row['no_hp']; ?></td>
                                            <td><?php echo $row['judul_laporan']; ?></td>
                                            <td><?php echo $row['lokasi']; ?></td>
                                            <td><span class="badge <?php echo $status_badge; ?>"><?php echo $row['status']; ?></span></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_lapor'])); ?></td>
                                            <td>
                                                <a href="update_status.php?id=<?php echo $row['id_pengaduan']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i> Update
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='text-center'>Tidak ada data pengaduan</td></tr>";
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
