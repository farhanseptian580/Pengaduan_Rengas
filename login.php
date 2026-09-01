<?php
include 'koneksi.php';

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
        global $db_error;
        $error = "Gagal terhubung ke database Supabase (" . ($db_error ?: 'Periksa pengaturan Environment Variables di Vercel') . ").";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();
            
            if ($admin && ($password === $admin['password'] || (function_exists('password_verify') && password_verify($password, $admin['password'])))) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['id_admin'] = $admin['id_admin'];
                $_SESSION['nama_admin'] = $admin['nama_lengkap'];
                save_serverless_session();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Login Admin - Sistem Pengaduan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.72), rgba(0, 0, 0, 0.72)), url('assets/Kantor-Kelurahan-Rengas.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .card-custom {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        .form-control {
            border-radius: 10px;
            min-height: 48px;
            font-size: 1rem;
        }
        .input-group-text {
            border-radius: 10px 0 0 10px;
            background-color: #f1f5f9;
            min-width: 46px;
            justify-content: center;
        }
        .input-group .form-control {
            border-radius: 0 10px 10px 0;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        .btn-login {
            border-radius: 30px;
            min-height: 48px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-9 col-md-6 col-lg-5 col-xl-4">
                <div class="card card-custom p-3 p-sm-4 p-md-4 bg-white">
                    <div class="card-body p-0">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 70px; height: 70px;">
                                <i class="bi bi-shield-lock-fill fs-1"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1">Login Admin</h3>
                            <p class="text-muted small mb-0">Panel Pengelola Pengaduan Kelurahan</p>
                        </div>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger rounded-3 d-flex align-items-center py-2 px-3 mb-3 small">
                                <i class="bi bi-exclamation-triangle-fill fs-5 me-2 flex-shrink-0"></i>
                                <div><?php echo htmlspecialchars($error); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold small">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill text-muted"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" placeholder="Masukkan username" required autocomplete="username">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold small">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key-fill text-muted"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                                </div>
                            </div>
                            
                            <button type="submit" name="login" class="btn btn-primary btn-login w-100 shadow-sm mt-2">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Dashboard
                            </button>
                        </form>
                        
                        <div class="text-center mt-4 pt-2 border-top">
                            <a href="index.php" class="text-decoration-none text-muted small d-inline-flex align-items-center">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda Warga
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
