<?php
include '../koneksi.php';

// Cek session admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

$error = null;

// Proses submit form
if (isset($_POST['submit'])) {
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $tanggal_berita = trim($_POST['tanggal_berita'] ?? '');
    
    if (empty($judul) || empty($isi) || empty($tanggal_berita)) {
        $error = "Semua field wajib diisi!";
    } elseif (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $error = "Harap unggah foto sampul berita.";
    } elseif (!$pdo) {
        $error = "Gagal terhubung ke database Supabase.";
    } else {
        $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $tmp_foto = $_FILES['foto']['tmp_name'];
            $foto_db = 'berita_' . time() . '_' . uniqid() . '.' . $file_ext;
            $mime_type = mime_content_type($tmp_foto) ?: 'image/jpeg';
            
            $uploaded = upload_to_supabase($tmp_foto, $foto_db, $mime_type, 'pengaduan');
            if ($uploaded) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO berita (judul, isi, foto, tanggal_berita) VALUES (:judul, :isi, :foto, :tanggal_berita)");
                    $stmt->execute([
                        ':judul' => $judul,
                        ':isi' => $isi,
                        ':foto' => $foto_db,
                        ':tanggal_berita' => $tanggal_berita
                    ]);
                    
                    header("Location: data_berita.php");
                    exit();
                } catch (PDOException $e) {
                    $error = "Gagal menyimpan berita ke database: " . $e->getMessage();
                }
            } else {
                global $last_storage_error;
                $error = "Gagal mengunggah foto ke Supabase Storage (" . ($last_storage_error ?: 'Periksa key') . ").";
            }
        } else {
            $error = "Format file tidak didukung. Format yang diizinkan: JPG, JPEG, PNG, GIF, WEBP.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Tambah Berita - Sistem Pengaduan Masyarakat</title>
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
        .form-control, .form-select {
            min-height: 48px;
            border-radius: 10px;
            font-size: 1rem;
        }
        textarea.form-control {
            min-height: 140px;
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
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Tambah Berita Baru</h2>
                        <p class="text-muted small mb-0">Publikasikan pengumuman atau kegiatan kelurahan terbaru.</p>
                    </div>
                    <div>
                        <a href="data_berita.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger rounded-3 d-flex align-items-center mb-4">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>

                <div class="card card-custom p-3 p-sm-4 shadow-sm">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="judul" class="form-label fw-semibold">Judul Berita <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="judul" name="judul" value="<?php echo htmlspecialchars($_POST['judul'] ?? ''); ?>" required placeholder="Masukkan judul berita yang menarik">
                        </div>

                        <div class="mb-3">
                            <label for="isi" class="form-label fw-semibold">Isi Berita Lengkap <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="isi" name="isi" rows="7" required placeholder="Tulis konten berita lengkap di sini..."><?php echo htmlspecialchars($_POST['isi'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_berita" class="form-label fw-semibold">Tanggal Pelaksanaan / Publikasi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_berita" name="tanggal_berita" value="<?php echo htmlspecialchars($_POST['tanggal_berita'] ?? date('Y-m-d')); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="foto" class="form-label fw-semibold">Foto Sampul Berita <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/*" required>
                            <div class="form-text small">Pilih file gambar (JPG, PNG, JPEG, WEBP). Foto akan otomatis diunggah ke Supabase Storage.</div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-2 pt-2">
                            <button type="submit" name="submit" class="btn btn-primary btn-action-lg shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> Publikasikan Berita
                            </button>
                            <a href="data_berita.php" class="btn btn-outline-secondary btn-action-lg d-flex align-items-center justify-content-center">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
