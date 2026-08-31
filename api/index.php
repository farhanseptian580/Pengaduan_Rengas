<?php
// ===================================================
// Vercel Serverless Entry Point Router
// ===================================================

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)
);

// Bersihkan path
$path = ltrim($uri, '/');

// Jika mengakses root "/", arahkan ke index.php
if ($path === '' || $path === '/') {
    $path = 'index.php';
}

// Jika mengakses direktori admin, arahkan ke dashboard
if ($path === 'admin' || $path === 'admin/') {
    $path = 'admin/dashboard.php';
}

// Tentukan path file target
$target = __DIR__ . '/../' . $path;

// Jika tidak ada ekstensi .php tapi ada file .php nya
if (!file_exists($target) && file_exists($target . '.php')) {
    $target = $target . '.php';
}

if (file_exists($target) && is_file($target)) {
    // Set working directory ke lokasi file agar include/require relatif berfungsi
    chdir(dirname($target));
    require $target;
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>Halaman yang Anda cari tidak ditemukan.</p>";
}
