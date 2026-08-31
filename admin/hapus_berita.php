<?php
include '../koneksi.php';

session_start();

// Cek session admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit();
}

// Cek ID parameter
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Ambil info foto terlebih dahulu untuk dihapus dari folder uploads
    $query_select = "SELECT foto FROM berita WHERE id_berita = '$id'";
    $result_select = mysqli_query($conn, $query_select);
    
    if (mysqli_num_rows($result_select) > 0) {
        $data = mysqli_fetch_assoc($result_select);
        $foto = $data['foto'];
        
        // Hapus file gambar jika ada
        if (file_exists('../uploads/' . $foto)) {
            unlink('../uploads/' . $foto);
        }
        
        // Hapus record dari database
        $query_delete = "DELETE FROM berita WHERE id_berita = '$id'";
        mysqli_query($conn, $query_delete);
    }
}

header("Location: data_berita.php");
exit();
?>
