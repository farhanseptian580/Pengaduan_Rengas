<?php
// ===================================================
// KONEKSI DATABASE & HELPER SUPABASE
// ===================================================

// Fungsi sederhana untuk memuat file .env jika ada (Lokal / Development)
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return;
    }
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                putenv(sprintf('%s=%s', $key, $value));
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Cari file .env di direktori saat ini atau direktori induk
$env_path = file_exists(__DIR__ . '/.env') ? __DIR__ . '/.env' : __DIR__ . '/../.env';
loadEnv($env_path);

// Helper untuk mengambil environment variable dengan fallback
function getEnvVar($key, $default = '') {
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    return $default;
}

// Konfigurasi Database PostgreSQL Supabase
$host = getEnvVar('DB_HOST', getEnvVar('PGHOST', 'localhost'));
$port = getEnvVar('DB_PORT', getEnvVar('PGPORT', '5432'));
$db   = getEnvVar('DB_NAME', getEnvVar('PGDATABASE', 'postgres'));
$user = getEnvVar('DB_USER', getEnvVar('PGUSER', 'postgres'));
$pass = getEnvVar('DB_PASS', getEnvVar('PGPASSWORD', ''));

// Dukungan jika pengguna memasukkan DATABASE_URL / POSTGRES_URL lengkap (URI Connection String)
$database_url = getEnvVar('DATABASE_URL', getEnvVar('POSTGRES_URL', ''));
if (!empty($database_url)) {
    $parsed_url = parse_url($database_url);
    if ($parsed_url) {
        if (!empty($parsed_url['host'])) $host = $parsed_url['host'];
        if (!empty($parsed_url['port'])) $port = $parsed_url['port'];
        if (!empty($parsed_url['user'])) $user = urldecode($parsed_url['user']);
        if (!empty($parsed_url['pass'])) $pass = urldecode($parsed_url['pass']);
        if (!empty($parsed_url['path'])) $db   = ltrim(urldecode($parsed_url['path']), '/');
    }
}

// Bersihkan format host jika pengguna menyertakan https:// atau port di dalamnya
$host = preg_replace('#^https?://#', '', trim($host));
$host = rtrim($host, '/');
if (strpos($host, ':') !== false) {
    list($host_clean, $port_clean) = explode(':', $host, 2);
    $host = $host_clean;
    if (!empty($port_clean)) $port = $port_clean;
}

// Konfigurasi Supabase Storage
$supabase_url    = rtrim(getEnvVar('SUPABASE_URL', ''), '/');
$supabase_key    = getEnvVar('SUPABASE_KEY', getEnvVar('SUPABASE_ANON_KEY', getEnvVar('SUPABASE_SERVICE_ROLE_KEY', '')));
$supabase_bucket = getEnvVar('SUPABASE_BUCKET', 'pengaduan');

// Inisialisasi Koneksi PDO PostgreSQL
$conn = null;
$pdo = null;
$db_error = null;

try {
    // Validasi apakah kredensial sudah diisi atau masih localhost/kosong
    if ($host === 'localhost' || empty($pass)) {
        throw new Exception("Kredensial database belum dikonfigurasi di Environment Variables.");
    }

    $dsn = "pgsql:host={$host};port={$port};dbname={$db}";
    
    // Tambahkan sslmode jika koneksi cloud
    if ($host !== 'localhost' && $host !== '127.0.0.1') {
        $dsn .= ";sslmode=require";
    }

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 5,
    ]);
    
    // Alias $conn untuk kompatibilitas
    $conn = $pdo;
} catch (Throwable $e) {
    $db_error = $e->getMessage();
    error_log("Database connection error: " . $db_error);
    $conn = null;
    $pdo = null;
}

// ---------------------------------------------------
// Helper Storage Supabase (Upload, URL, & Delete)
// ---------------------------------------------------

$last_storage_error = null;

/**
 * Upload file ke Supabase Storage Bucket
 */
function upload_to_supabase($tmp_file_path, $destination_filename, $mime_type = 'application/octet-stream', $bucket = 'pengaduan') {
    global $supabase_url, $supabase_key, $supabase_bucket, $last_storage_error;
    $last_storage_error = null;
    
    if (empty($bucket)) {
        $bucket = !empty($supabase_bucket) ? $supabase_bucket : 'pengaduan';
    }

    if (empty($supabase_url) || empty($supabase_key)) {
        $last_storage_error = "SUPABASE_URL atau SUPABASE_KEY belum diisi di Environment Variables.";
        return false;
    }

    $url = "{$supabase_url}/storage/v1/object/{$bucket}/{$destination_filename}";
    $file_content = @file_get_contents($tmp_file_path);
    if ($file_content === false) {
        $last_storage_error = "Tidak dapat membaca file temporary yang diunggah.";
        return false;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $file_content);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$supabase_key}",
        "apikey: {$supabase_key}",
        "Content-Type: {$mime_type}",
        "x-upsert: true"
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err = curl_error($ch);
    curl_close($ch);

    if ($http_code === 200 || $http_code === 201) {
        return true;
    }

    if (!empty($curl_err)) {
        $last_storage_error = "cURL: {$curl_err}";
    } else {
        $json = json_decode($response, true);
        $msg = $json['message'] ?? ($json['error'] ?? $response);
        $last_storage_error = "HTTP {$http_code}: {$msg}";
    }
    error_log("Supabase storage upload failed: {$last_storage_error}");
    return false;
}

/**
 * Dapatkan URL publik file dari Supabase Storage atau fallback lokal
 */
function get_file_url($filename, $bucket = 'pengaduan') {
    global $supabase_url, $supabase_bucket;

    if (empty($filename)) {
        return 'assets/placeholder.jpg';
    }

    if (strpos($filename, 'http://') === 0 || strpos($filename, 'https://') === 0) {
        return $filename;
    }

    if (empty($bucket)) {
        $bucket = !empty($supabase_bucket) ? $supabase_bucket : 'pengaduan';
    }

    if (!empty($supabase_url)) {
        return "{$supabase_url}/storage/v1/object/public/{$bucket}/{$filename}";
    }

    $base_prefix = file_exists(__DIR__ . '/admin') ? '' : '../';
    return $base_prefix . 'uploads/' . $filename;
}

/**
 * Hapus file dari Supabase Storage Bucket
 */
function delete_from_supabase($filename, $bucket = 'pengaduan') {
    global $supabase_url, $supabase_key, $supabase_bucket;

    if (empty($filename)) {
        return true;
    }

    if (empty($bucket)) {
        $bucket = !empty($supabase_bucket) ? $supabase_bucket : 'pengaduan';
    }

    if (empty($supabase_url) || empty($supabase_key)) {
        $local_path = __DIR__ . '/uploads/' . $filename;
        if (file_exists($local_path)) {
            unlink($local_path);
        }
        return true;
    }

    $url = "{$supabase_url}/storage/v1/object/{$bucket}/{$filename}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$supabase_key}",
        "apikey: {$supabase_key}"
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code >= 200 && $http_code < 300);
}
?>
