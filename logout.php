<?php
include 'koneksi.php';

// Hapus session serverless dan cookie auth
destroy_serverless_session();

// Redirect ke halaman login
header("Location: login.php");
exit();
?>
