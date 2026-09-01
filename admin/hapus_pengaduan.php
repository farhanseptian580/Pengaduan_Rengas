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
        // Ambil info file foto & foto_selesai sebelum dihapus
        $stmt = $pdo->prepare("SELECT foto, foto_selesai FROM pengaduan WHERE id_pengaduan = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();
        
        if ($data) {
            // Hapus file foto bukti pengaduan dari Supabase Storage
            if (!empty($data['foto'])) {
                delete_from_supabase($data['foto'], 'pengaduan');
            }
            
            // Hapus file foto selesai dari Supabase Storage jika ada
            if (!empty($data['foto_selesai'])) {
                delete_from_supabase($data['foto_selesai'], 'pengaduan');
            }
            
            // Hapus data dari tabel database
            $delete_stmt = $pdo->prepare("DELETE FROM pengaduan WHERE id_pengaduan = :id");
            $delete_stmt->execute([':id' => $id]);
        }
    } catch (PDOException $e) {
        error_log("Error deleting pengaduan: " . $e->getMessage());
    }
}

// Redirect kembali ke halaman data pengaduan
header("Location: data_pengaduan.php");
exit();
?>
