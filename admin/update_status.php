<?php
include '../koneksi.php';

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
                global $last_storage_error;
                $error = "Gagal mengunggah foto bukti selesai ke Supabase Storage (" . ($last_storage_error ?: 'Periksa key') . ").";
            }
        } else {
            $error = "Format file tidak didukung. Hanya JPG, JPEG, PNG, GIF, dan WEBP.";
        }
    }
    
    if (!isset($error) && $pdo) {
        try {
            // Logika SQL yang bebas dari ambiguitas parameter PostgreSQL (SQLSTATE 42P08)
            $selesai_clause = ($status === 'Selesai') ? "tanggal_selesai = CURRENT_TIMESTAMP" : "tanggal_selesai = NULL";
            
            if ($has_new_foto_selesai) {
                $sql = "UPDATE pengaduan SET status = :status, tanggapan = :tanggapan, foto_selesai = :foto_selesai, {$selesai_clause} WHERE id_pengaduan = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':status' => $status,
                    ':tanggapan' => $tanggapan,
                    ':foto_selesai' => $foto_selesai_db,
                    ':id' => $id_pengaduan
                ]);
            } else {
                $sql = "UPDATE pengaduan SET status = :status, tanggapan = :tanggapan, {$selesai_clause} WHERE id_pengaduan = :id";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Update Status - Sistem Pengaduan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
        #map {
            height: 240px;
            width: 100%;
            border-radius: 12px;
            margin-top: 8px;
            z-index: 1;
        }
        @media (min-width: 768px) {
            #map {
                height: 320px;
            }
        }
        .form-select, .form-control {
            min-height: 48px;
            border-radius: 10px;
            font-size: 1rem;
        }
        .btn-action-lg {
            min-height: 48px;
            border-radius: 30px;
            font-weight: 600;
            padding: 0 24px;
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
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Tindak Lanjut Pengaduan</h2>
                        <p class="text-muted small mb-0">Update status, respon tanggapan, dan upload bukti penyelesaian.</p>
                    </div>
                    <div>
                        <a href="data_pengaduan.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success rounded-3 d-flex align-items-center mb-4">
                        <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                        <div><?php echo htmlspecialchars($success); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger rounded-3 d-flex align-items-center mb-4">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>
                
                <!-- Detail Pengaduan -->
                <div class="card card-custom p-3 p-sm-4 mb-4 shadow-sm">
                    <h5 class="fw-bold mb-3 pb-2 border-bottom"><i class="bi bi-file-earmark-text text-primary me-2"></i> Informasi Laporan Warga</h5>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <small class="text-muted d-block">Kode Laporan:</small>
                            <span class="badge bg-dark font-monospace fs-6 px-3 py-2"><?php echo htmlspecialchars($data['kode_laporan']); ?></span>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small class="text-muted d-block">Waktu Lapor:</small>
                            <span class="fw-medium text-dark"><?php echo date('d F Y H:i', strtotime($data['tanggal_lapor'])); ?> WIB</span>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small class="text-muted d-block">Nama Pelapor:</small>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($data['nama']); ?></span>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small class="text-muted d-block">Kontak WhatsApp:</small>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $data['no_hp']); ?>" target="_blank" class="text-decoration-none text-success fw-bold">
                                <i class="bi bi-whatsapp me-1"></i> <?php echo htmlspecialchars($data['no_hp']); ?>
                            </a>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Judul Pengaduan:</small>
                            <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($data['judul_laporan']); ?></h6>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Deskripsi Keluhan:</small>
                            <div class="p-3 bg-light rounded-3 border text-secondary small lh-base" style="white-space: pre-line;"><?php echo htmlspecialchars($data['deskripsi']); ?></div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Lokasi Kejadian:</small>
                            <p class="fw-semibold text-dark mb-1"><?php echo htmlspecialchars($data['lokasi']); ?></p>
                            <?php if (!empty($data['latitude']) && !empty($data['longitude'])): ?>
                                <div id="map"></div>
                                <input type="hidden" id="latitude" value="<?php echo htmlspecialchars($data['latitude']); ?>">
                                <input type="hidden" id="longitude" value="<?php echo htmlspecialchars($data['longitude']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block mb-1">Foto Bukti Pengaduan:</small>
                            <img src="<?php echo htmlspecialchars(get_file_url($data['foto'])); ?>" class="img-fluid rounded-3 border shadow-sm w-100" alt="Foto Bukti" style="max-height: 300px; object-fit: cover;" loading="lazy">
                        </div>
                    </div>
                </div>

                <!-- Form Update -->
                <div class="card card-custom p-3 p-sm-4 shadow-sm">
                    <h5 class="fw-bold mb-3 pb-2 border-bottom"><i class="bi bi-pencil-square text-primary me-2"></i> Form Tanggapan & Perubahan Status</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_pengaduan" value="<?php echo htmlspecialchars($data['id_pengaduan']); ?>">
                        
                        <div class="mb-3">
                            <label for="status" class="form-label fw-semibold">Status Pengaduan <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Menunggu" <?php echo $data['status'] == 'Menunggu' ? 'selected' : ''; ?>>⏳ Menunggu (Belum Diproses)</option>
                                <option value="Diproses" <?php echo $data['status'] == 'Diproses' ? 'selected' : ''; ?>>⚙️ Diproses (Sedang Ditangani)</option>
                                <option value="Selesai" <?php echo $data['status'] == 'Selesai' ? 'selected' : ''; ?>>✅ Selesai (Masalah Tuntas)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tanggapan" class="form-label fw-semibold">Tanggapan Resmi Kelurahan</label>
                            <textarea class="form-control" id="tanggapan" name="tanggapan" rows="4" placeholder="Tuliskan penjelasan tindak lanjut untuk warga..."><?php echo htmlspecialchars($data['tanggapan'] ?? ''); ?></textarea>
                            <div class="form-text small">Tanggapan ini akan langsung terlihat oleh warga saat melakukan cek status pengaduan.</div>
                        </div>
                        
                        <div class="mb-4" id="foto-selesai-container" style="<?php echo $data['status'] == 'Selesai' ? 'display: block;' : 'display: none;'; ?>">
                            <label for="foto_selesai" class="form-label fw-semibold">Foto Bukti Penanganan Selesai</label>
                            <input class="form-control" type="file" id="foto_selesai" name="foto_selesai" accept="image/*">
                            <?php if (!empty($data['foto_selesai'])): ?>
                                <div class="mt-2 p-2 bg-light rounded-3 border d-inline-block">
                                    <small class="text-muted d-block mb-1">Foto bukti saat ini:</small>
                                    <img src="<?php echo htmlspecialchars(get_file_url($data['foto_selesai'])); ?>" class="rounded" style="max-height: 140px; object-fit: cover;" alt="Foto Selesai">
                                </div>
                            <?php endif; ?>
                            <div class="form-text small">Unggah foto hasil penanganan sebagai bukti laporan telah tuntas.</div>
                        </div>
                        
                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 pt-2">
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button type="submit" name="update" class="btn btn-primary btn-action-lg shadow-sm">
                                    <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                                </button>
                                <a href="data_pengaduan.php" class="btn btn-outline-secondary btn-action-lg d-flex align-items-center justify-content-center">
                                    Batal
                                </a>
                            </div>
                            <div>
                                <a href="hapus_pengaduan.php?id=<?php echo $data['id_pengaduan']; ?>" class="btn btn-outline-danger btn-action-lg w-100 d-flex align-items-center justify-content-center" onclick="return confirm('Apakah Anda yakin ingin menghapus pengaduan ini (Kode: <?php echo htmlspecialchars($data['kode_laporan']); ?>)?\n\nData dan file foto terkait akan dihapus secara permanen.')">
                                    <i class="bi bi-trash me-1"></i> Hapus Pengaduan
                                </a>
                            </div>
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
