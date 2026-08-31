<?php
include '../koneksi.php';

session_start();

// Cek session admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit();
}

// Cek ID parameter
if (!isset($_GET['id'])) {
    header("Location: data_berita.php");
    exit();
}

$id = $_GET['id'];
$query = "SELECT * FROM berita WHERE id_berita = '$id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: data_berita.php");
    exit();
}

$data = mysqli_fetch_assoc($result);

// Proses submit form
if (isset($_POST['submit'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi']);
    $foto_db = $data['foto']; // Default foto lama
    
    // Cek jika ada unggah foto baru
    if ($_FILES['foto']['name'] != '') {
        $foto_name = $_FILES['foto']['name'];
        $tmp_foto = $_FILES['foto']['tmp_name'];
        
        $new_foto_db = time() . '_' . $foto_name;
        $foto_path = '../uploads/' . $new_foto_db;
        
        if (move_uploaded_file($tmp_foto, $foto_path)) {
            // Hapus foto lama jika ada
            if (file_exists('../uploads/' . $data['foto'])) {
                unlink('../uploads/' . $data['foto']);
            }
            $foto_db = $new_foto_db;
        } else {
            $error = "Gagal mengunggah foto baru.";
        }
    }
    
    if (!isset($error)) {
        $tanggal_berita = mysqli_real_escape_string($conn, $_POST['tanggal_berita']);
        // Update data ke database
        $update_query = "UPDATE berita SET judul = '$judul', isi = '$isi', foto = '$foto_db', tanggal_berita = '$tanggal_berita' WHERE id_berita = '$id'";
        
        if (mysqli_query($conn, $update_query)) {
            header("Location: data_berita.php");
            exit();
        } else {
            $error = "Gagal memperbarui berita: " . mysqli_error($conn);
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
                    <p class="mb-0">Selamat datang, <?php echo $_SESSION['nama_admin']; ?></p>
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
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
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
                                <img src="../uploads/<?php echo htmlspecialchars($data['foto']); ?>" class="current-img" alt="Foto Saat Ini">
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
