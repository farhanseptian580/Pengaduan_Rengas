<?php
include '../koneksi.php';

// Cek session admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Cek ID parameter
if (isset($_GET['id']) && $pdo) {
    $id = $_GET['id'];
    
    try {
        // Ambil info foto terlebih dahulu
        $stmt = $pdo->prepare("SELECT foto FROM berita WHERE id_berita = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();
        
        if ($data) {
            $foto = $data['foto'];
            
            // Hapus file gambar dari Supabase Storage / lokal
            delete_from_supabase($foto, 'pengaduan');
            
            // Hapus record dari database
            $delete_stmt = $pdo->prepare("DELETE FROM berita WHERE id_berita = :id");
            $delete_stmt->execute([':id' => $id]);
        }
    } catch (PDOException $e) {
        error_log("Error deleting berita: " . $e->getMessage());
    }
}

header("Location: data_berita.php");
exit();
?>
