<?php
include '../koneksi.php';

session_start();

// Cek session admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit();
}

// Proses submit form
if (isset($_POST['submit'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi']);
    $tanggal_berita = mysqli_real_escape_string($conn, $_POST['tanggal_berita']);
    
    // Upload foto
    $foto_name = $_FILES['foto']['name'];
    $tmp_foto = $_FILES['foto']['tmp_name'];
    
    // Gunakan timestamp untuk menghindari nama file bentrok
    $foto_db = time() . '_' . $foto_name;
    $foto_path = '../uploads/' . $foto_db;
    
    // Buat folder uploads jika belum ada
    if (!is_dir('../uploads')) {
        mkdir('../uploads', 0777, true);
    }
    
    if (move_uploaded_file($tmp_foto, $foto_path)) {
        // Insert data ke database
        $query = "INSERT INTO berita (judul, isi, foto, tanggal_berita) VALUES ('$judul', '$isi', '$foto_db', '$tanggal_berita')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Berita berhasil ditambahkan!";
            header("Location: data_berita.php");
            exit();
        } else {
            $error = "Gagal menyimpan berita: " . mysqli_error($conn);
        }
    } else {
        $error = "Gagal mengunggah foto berita.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Berita - Sistem Pengaduan Masyarakat</title>
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
                <h2 class="mb-4">Tambah Berita Baru</h2>

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
                            <input type="text" class="form-control" id="judul" name="judul" required placeholder="Masukkan judul berita">
                        </div>

                        <div class="mb-3">
                            <label for="isi" class="form-label">Isi Berita</label>
                            <textarea class="form-control" id="isi" name="isi" rows="8" required placeholder="Tulis konten berita lengkap di sini..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_berita" class="form-label">Tanggal Pelaksanaan Acara</label>
                            <input type="date" class="form-control" id="tanggal_berita" name="tanggal_berita" required>
                        </div>

                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto Sampul</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/*" required>
                            <div class="form-text">Pilih file gambar (JPG, JPEG, PNG). Gambar ini akan ditampilkan di halaman utama.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Publikasikan Berita
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
