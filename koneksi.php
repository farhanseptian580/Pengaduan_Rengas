<?php
// Koneksi Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pengaduan_masyarakat";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
