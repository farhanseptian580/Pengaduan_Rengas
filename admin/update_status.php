<?php
include '../koneksi.php';

session_start();

// Cek session admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit();
}

// Proses update status dan tanggapan
if (isset($_POST['update'])) {
    $id_pengaduan = $_POST['id_pengaduan'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $tanggapan = mysqli_real_escape_string($conn, $_POST['tanggapan']);
    
    // Proses upload foto bukti selesai
    $foto_selesai_query = "";
    if (isset($_FILES['foto_selesai']) && $_FILES['foto_selesai']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["foto_selesai"]["name"], PATHINFO_EXTENSION);
        $file_name = "selesai_" . time() . "_" . uniqid() . "." . $file_extension;
        $target_file = $target_dir . $file_name;
        
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES["foto_selesai"]["tmp_name"], $target_file)) {
                $foto_selesai_query = ", foto_selesai = '$file_name'";
            } else {
                $error = "Gagal mengupload foto bukti selesai.";
            }
        } else {
            $error = "Format file tidak didukung. Hanya JPG, JPEG, PNG, dan GIF.";
        }
    }
    
    if (!isset($error)) {
        $query = "UPDATE pengaduan SET status = '$status', tanggapan = '$tanggapan' $foto_selesai_query WHERE id_pengaduan = '$id_pengaduan'";
        
        if (mysqli_query($conn, $query)) {
            $success = "Status dan tanggapan berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui: " . mysqli_error($conn);
        }
    }
}

// Ambil data pengaduan berdasarkan ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM pengaduan WHERE id_pengaduan = '$id'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
} else {
    header("Location: data_pengaduan.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status - Sistem Pengaduan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
        #map {
            height: 300px;
            width: 100%;
            border-radius: 10px;
            margin-top: 10px;
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
                <h2 class="mb-4">Update Status Pengaduan</h2>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Detail Pengaduan -->
                <div class="card card-custom p-4 mb-4">
                    <h4 class="mb-4"><i class="bi bi-file-earmark-text"></i> Detail Pengaduan</h4>
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
                            <img src="../uploads/<?php echo $data['foto']; ?>" class="img-fluid rounded" alt="Foto Bukti" style="max-height: 300px;">
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>Status Saat Ini:</strong><br>
                            <?php
                            $status_badge = '';
                            if ($data['status'] == 'Menunggu') {
                                $status_badge = 'bg-warning text-dark';
                            } elseif ($data['status'] == 'Diproses') {
                                $status_badge = 'bg-info text-dark';
                            } else {
                                $status_badge = 'bg-success text-white';
                            }
                            ?>
                            <span class="badge <?php echo $status_badge; ?> fs-6"><?php echo $data['status']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Form Update -->
                <div class="card card-custom p-4">
                    <h4 class="mb-4"><i class="bi bi-pencil-square"></i> Update Status & Tanggapan</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_pengaduan" value="<?php echo $data['id_pengaduan']; ?>">
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Menunggu" <?php echo $data['status'] == 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="Diproses" <?php echo $data['status'] == 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                                <option value="Selesai" <?php echo $data['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tanggapan" class="form-label">Tanggapan Admin</label>
                            <textarea class="form-control" id="tanggapan" name="tanggapan" rows="5"><?php echo $data['tanggapan']; ?></textarea>
                            <div class="form-text">Berikan tanggapan atau penjelasan mengenai status pengaduan ini.</div>
                        </div>
                        
                        <div class="mb-3" id="foto-selesai-container" style="<?php echo $data['status'] == 'Selesai' ? 'display: block;' : 'display: none;'; ?>">
                            <label for="foto_selesai" class="form-label">Foto Bukti Selesai</label>
                            <input class="form-control" type="file" id="foto_selesai" name="foto_selesai" accept="image/*">
                            <?php if (!empty($data['foto_selesai'])): ?>
                                <div class="mt-2">
                                    <p class="mb-1">Foto saat ini:</p>
                                    <img src="../uploads/<?php echo $data['foto_selesai']; ?>" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Unggah foto sebagai bukti jika laporan telah selesai.</div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" name="update" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Simpan Perubahan
                            </button>
                            <a href="data_pengaduan.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        <?php if ($data['latitude'] && $data['longitude']): ?>
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
        
        // Toggle foto selesai upload field based on status
        const statusSelect = document.getElementById('status');
        const fotoSelesaiContainer = document.getElementById('foto-selesai-container');
        
        if (statusSelect && fotoSelesaiContainer) {
            statusSelect.addEventListener('change', function() {
                if (this.value === 'Selesai') {
                    fotoSelesaiContainer.style.display = 'block';
                } else {
                    fotoSelesaiContainer.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
