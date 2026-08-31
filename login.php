<?php
include 'koneksi.php';

session_start();

// Cek jika sudah login
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin/dashboard.php");
    exit();
}

// Proses login
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi!";
    } elseif (!$pdo) {
        $error = "Gagal terhubung ke database Supabase. Periksa pengaturan koneksi Anda.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();
            
            // Cek password (bisa plaintext seperti di sql bawaan atau password_verify jika hash)
            if ($admin && ($password === $admin['password'] || (function_exists('password_verify') && password_verify($password, $admin['password'])))) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['id_admin'] = $admin['id_admin'];
                $_SESSION['nama_admin'] = $admin['nama_lengkap'];
                header("Location: admin/dashboard.php");
                exit();
            } else {
                $error = "Username atau password salah!";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan saat memproses login: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sistem Pengaduan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.65)), url('assets/Kantor-Kelurahan-Rengas.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-custom {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card card-custom p-4">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-lock display-4 text-primary"></i>
                            <h3 class="mt-3">Login Admin</h3>
                            <p class="text-muted">Masuk untuk mengelola pengaduan</p>
                        </div>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <button type="submit" name="login" class="btn btn-primary w-100 btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <a href="index.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
