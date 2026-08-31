<?php
include '../koneksi.php';

session_start();

// Cek session admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: data_berita.php");
    exit();
}

$error = null;

// Ambil data berita
$data = null;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM berita WHERE id_berita = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error mengambil data: " . $e->getMessage();
    }
}

if (!$data) {
    header("Location: data_berita.php");
    exit();
}

// Proses submit form edit
if (isset($_POST['submit'])) {
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $tanggal_berita = trim($_POST['tanggal_berita'] ?? '');
    $foto_db = $data['foto']; // Default pakai foto lama
    
    if (empty($judul) || empty($isi) || empty($tanggal_berita)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Cek jika ada unggahan foto baru
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_ext, $allowed_ext)) {
                $tmp_foto = $_FILES['foto']['tmp_name'];
                $new_foto_db = 'berita_' . time() . '_' . uniqid() . '.' . $file_ext;
                $mime_type = mime_content_type($tmp_foto) ?: 'image/jpeg';
                
                $uploaded = upload_to_supabase($tmp_foto, $new_foto_db, $mime_type, 'pengaduan');
                if ($uploaded) {
                    $foto_db = $new_foto_db;
                } else {
                    $error = "Gagal mengunggah foto baru ke Supabase Storage.";
                }
            } else {
                $error = "Format file tidak didukung. Harap gunakan JPG, JPEG, PNG, GIF, atau WEBP.";
            }
        }
        
        if (!isset($error) && $pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE berita SET judul = :judul, isi = :isi, foto = :foto, tanggal_berita = :tanggal_berita WHERE id_berita = :id");
                $stmt->execute([
                    ':judul' => $judul,
                    ':isi' => $isi,
                    ':foto' => $foto_db,
                    ':tanggal_berita' => $tanggal_berita,
                    ':id' => $id
                ]);
                
                header("Location: data_berita.php");
                exit();
            } catch (PDOException $e) {
                $error = "Gagal memperbarui berita: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Berita - Sistem Pengaduan Masyarakat</title>
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
        .current-img {
            max-width: 200px;
            border-radius: 10px;
            margin-top: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
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
                    <a class="nav-link" href="data_pengaduan.php">
                        <i class="bi bi-table"></i> Data Pengaduan
                    </a>
                    <a class="nav-link active" href="data_berita.php">
                        <i class="bi bi-newspaper"></i> Data Berita
                    </a>
                    <a class="nav-link" href="../logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h2 class="mb-4">Edit Berita</h2>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card card-custom p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Berita</label>
                            <input type="text" class="form-control" id="judul" name="judul" value="<?php echo htmlspecialchars($data['judul']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="isi" class="form-label">Isi Berita</label>
                            <textarea class="form-control" id="isi" name="isi" rows="8" required><?php echo htmlspecialchars($data['isi']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_berita" class="form-label">Tanggal Pelaksanaan Acara</label>
                            <input type="date" class="form-control" id="tanggal_berita" name="tanggal_berita" value="<?php echo htmlspecialchars($data['tanggal_berita']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="foto" class="form-label">Ubah Foto Sampul</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                            <div class="form-text">Biarkan kosong jika tidak ingin mengubah foto.</div>
                            <div class="mt-2">
                                <label class="form-label d-block">Foto Saat Ini:</label>
                                <img src="<?php echo htmlspecialchars(get_file_url($data['foto'])); ?>" class="current-img border" alt="Foto Saat Ini">
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Simpan Perubahan
                            </button>
                            <a href="data_berita.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
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
