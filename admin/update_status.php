<?php
include '../koneksi.php';

session_start();

// Cek session admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

$id_pengaduan = $_GET['id'] ?? ($_POST['id_pengaduan'] ?? null);
if (!$id_pengaduan) {
    header("Location: data_pengaduan.php");
    exit();
}

$success = null;
$error = null;

// Proses update status dan tanggapan
if (isset($_POST['update'])) {
    $status = trim($_POST['status'] ?? 'Menunggu');
    $tanggapan = trim($_POST['tanggapan'] ?? '');
    
    // Proses upload foto bukti selesai
    $foto_selesai_db = null;
    $has_new_foto_selesai = false;
    
    if (isset($_FILES['foto_selesai']) && $_FILES['foto_selesai']['error'] === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($_FILES["foto_selesai"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_types)) {
            $tmp_name = $_FILES["foto_selesai"]["tmp_name"];
            $foto_selesai_db = "selesai_" . time() . "_" . uniqid() . "." . $file_extension;
            $mime_type = mime_content_type($tmp_name) ?: 'image/jpeg';
            
            $uploaded = upload_to_supabase($tmp_name, $foto_selesai_db, $mime_type, 'pengaduan');
            if ($uploaded) {
                $has_new_foto_selesai = true;
            } else {
                $error = "Gagal mengunggah foto bukti selesai ke Supabase Storage.";
            }
        } else {
            $error = "Format file tidak didukung. Hanya JPG, JPEG, PNG, GIF, dan WEBP.";
        }
    }
    
    if (!isset($error) && $pdo) {
        try {
            if ($has_new_foto_selesai) {
                $sql = "UPDATE pengaduan SET status = :status, tanggapan = :tanggapan, foto_selesai = :foto_selesai, tanggal_selesai = CASE WHEN :status = 'Selesai' THEN CURRENT_TIMESTAMP ELSE tanggal_selesai END WHERE id_pengaduan = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':status' => $status,
                    ':tanggapan' => $tanggapan,
                    ':foto_selesai' => $foto_selesai_db,
                    ':id' => $id_pengaduan
                ]);
            } else {
                $sql = "UPDATE pengaduan SET status = :status, tanggapan = :tanggapan, tanggal_selesai = CASE WHEN :status = 'Selesai' THEN CURRENT_TIMESTAMP ELSE tanggal_selesai END WHERE id_pengaduan = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':status' => $status,
                    ':tanggapan' => $tanggapan,
                    ':id' => $id_pengaduan
                ]);
            }
            $success = "Status dan tanggapan pengaduan berhasil diperbarui!";
        } catch (PDOException $e) {
            $error = "Gagal memperbarui data: " . $e->getMessage();
        }
    }
}

// Ambil data pengaduan berdasarkan ID
$data = null;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM pengaduan WHERE id_pengaduan = :id LIMIT 1");
        $stmt->execute([':id' => $id_pengaduan]);
        $data = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error mengambil data: " . $e->getMessage();
    }
}

if (!$data) {
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
                    <p class="mb-0">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_admin'] ?? 'Admin'); ?></p>
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
                        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Detail Pengaduan -->
                <div class="card card-custom p-4 mb-4">
                    <h4 class="mb-4"><i class="bi bi-file-earmark-text"></i> Detail Pengaduan</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Kode Laporan:</strong><br>
                            <span class="badge bg-dark fs-6"><?php echo htmlspecialchars($data['kode_laporan']); ?></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Tanggal Lapor:</strong><br>
                            <?php echo date('d F Y H:i', strtotime($data['tanggal_lapor'])); ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Nama Pelapor:</strong><br>
                            <?php echo htmlspecialchars($data['nama']); ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>No HP:</strong><br>
                            <?php echo htmlspecialchars($data['no_hp']); ?>
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>Judul Laporan:</strong><br>
                            <?php echo htmlspecialchars($data['judul_laporan']); ?>
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>Deskripsi:</strong><br>
                            <div style="white-space: pre-line;"><?php echo htmlspecialchars($data['deskripsi']); ?></div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>Lokasi:</strong><br>
                            <?php echo htmlspecialchars($data['lokasi']); ?>
                            <?php if (!empty($data['latitude']) && !empty($data['longitude'])): ?>
                                <div id="map"></div>
                                <input type="hidden" id="latitude" value="<?php echo htmlspecialchars($data['latitude']); ?>">
                                <input type="hidden" id="longitude" value="<?php echo htmlspecialchars($data['longitude']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>Foto Bukti Pengaduan:</strong><br>
                            <img src="<?php echo htmlspecialchars(get_file_url($data['foto'])); ?>" class="img-fluid rounded border mt-2" alt="Foto Bukti" style="max-height: 300px;">
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
                            <span class="badge <?php echo $status_badge; ?> fs-6 mt-1"><?php echo htmlspecialchars($data['status']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Form Update -->
                <div class="card card-custom p-4">
                    <h4 class="mb-4"><i class="bi bi-pencil-square"></i> Update Status & Tanggapan</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_pengaduan" value="<?php echo htmlspecialchars($data['id_pengaduan']); ?>">
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status Pengaduan</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Menunggu" <?php echo $data['status'] == 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="Diproses" <?php echo $data['status'] == 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                                <option value="Selesai" <?php echo $data['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tanggapan" class="form-label">Tanggapan Admin</label>
                            <textarea class="form-control" id="tanggapan" name="tanggapan" rows="5"><?php echo htmlspecialchars($data['tanggapan'] ?? ''); ?></textarea>
                            <div class="form-text">Berikan tanggapan atau penjelasan mengenai status tindak lanjut pengaduan ini.</div>
                        </div>
                        
                        <div class="mb-3" id="foto-selesai-container" style="<?php echo $data['status'] == 'Selesai' ? 'display: block;' : 'display: none;'; ?>">
                            <label for="foto_selesai" class="form-label">Foto Bukti Selesai (Opsional jika laporan telah beres)</label>
                            <input class="form-control" type="file" id="foto_selesai" name="foto_selesai" accept="image/*">
                            <?php if (!empty($data['foto_selesai'])): ?>
                                <div class="mt-2">
                                    <p class="mb-1">Foto bukti selesai saat ini:</p>
                                    <img src="<?php echo htmlspecialchars(get_file_url($data['foto_selesai'])); ?>" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Unggah foto sebagai bukti penanganan jika status adalah Selesai.</div>
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
        <?php if (!empty($data['latitude']) && !empty($data['longitude'])): ?>
            var lat = <?php echo floatval($data['latitude']); ?>;
            var lng = <?php echo floatval($data['longitude']); ?>;
            var map = L.map('map').setView([lat, lng], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
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
