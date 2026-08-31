<?php
// ===================================================
// DIAGNOSTIK KONEKSI SUPABASE & ENVIRONMENT
// ===================================================

$start_time = microtime(true);
include_once __DIR__ . '/koneksi.php';

$wants_json = (isset($_GET['format']) && $_GET['format'] === 'json') || 
              (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

$diagnostics = [
    'timestamp' => date('Y-m-d H:i:s T'),
    'environment' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Vercel Serverless',
        'pdo_pgsql_installed' => extension_loaded('pdo_pgsql'),
        'curl_installed' => extension_loaded('curl'),
    ],
    'env_variables' => [
        'DB_HOST' => !empty($host) ? $host : 'KOSONG / TIDAK DITEMUKAN',
        'DB_PORT' => !empty($port) ? $port : 'KOSONG',
        'DB_NAME' => !empty($db) ? $db : 'KOSONG',
        'DB_USER' => !empty($user) ? $user : 'KOSONG',
        'DB_PASS' => !empty($pass) ? 'TERISI (' . strlen($pass) . ' karakter)' : 'KOSONG',
        'SUPABASE_URL' => !empty($supabase_url) ? $supabase_url : 'KOSONG',
        'SUPABASE_KEY' => !empty($supabase_key) ? 'TERISI (' . substr($supabase_key, 0, 10) . '...)' : 'KOSONG',
        'SUPABASE_BUCKET' => !empty($supabase_bucket) ? $supabase_bucket : 'KOSONG',
    ],
    'database' => [
        'status' => 'FAILED',
        'latency_ms' => null,
        'message' => null,
        'tables' => [],
    ],
    'storage' => [
        'status' => 'FAILED',
        'latency_ms' => null,
        'message' => null,
        'bucket_found' => false,
        'bucket_public' => false,
    ]
];

// 1. Tes Koneksi Database PostgreSQL
$db_start = microtime(true);
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT NOW() as current_time, version() as pg_version");
        $db_info = $stmt->fetch();
        
        $diagnostics['database']['status'] = 'CONNECTED';
        $diagnostics['database']['latency_ms'] = round((microtime(true) - $db_start) * 1000, 2);
        $diagnostics['database']['message'] = 'Koneksi ke Supabase PostgreSQL berhasil.';
        $diagnostics['database']['server_time'] = $db_info['current_time'] ?? null;
        $diagnostics['database']['version'] = $db_info['pg_version'] ?? null;

        // Cek tabel
        $tables_check = ['admin', 'berita', 'pengaduan'];
        foreach ($tables_check as $tbl) {
            try {
                $count = $pdo->query("SELECT COUNT(*) FROM {$tbl}")->fetchColumn();
                $diagnostics['database']['tables'][$tbl] = [
                    'status' => 'EXISTS',
                    'row_count' => (int)$count
                ];
            } catch (Exception $e) {
                $diagnostics['database']['tables'][$tbl] = [
                    'status' => 'NOT_FOUND_OR_ERROR',
                    'error' => $e->getMessage()
                ];
            }
        }
    } catch (Exception $e) {
        $diagnostics['database']['status'] = 'FAILED';
        $diagnostics['database']['message'] = $e->getMessage();
    }
} else {
    $diagnostics['database']['status'] = 'FAILED';
    $diagnostics['database']['message'] = $db_error ?: 'Gagal inisialisasi koneksi PDO PostgreSQL.';
}

// 2. Tes Koneksi Supabase Storage API
$storage_start = microtime(true);
if (!empty($supabase_url) && !empty($supabase_key)) {
    // Cek detail bucket langsung
    $ch_b = curl_init();
    curl_setopt($ch_b, CURLOPT_URL, "{$supabase_url}/storage/v1/bucket/{$supabase_bucket}");
    curl_setopt($ch_b, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_b, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch_b, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$supabase_key}",
        "apikey: {$supabase_key}"
    ]);
    $response_b = curl_exec($ch_b);
    $http_code_b = curl_getinfo($ch_b, CURLINFO_HTTP_CODE);
    curl_close($ch_b);

    $diagnostics['storage']['latency_ms'] = round((microtime(true) - $storage_start) * 1000, 2);

    if ($http_code_b === 200) {
        $bucket_info = json_decode($response_b, true);
        $diagnostics['storage']['status'] = 'CONNECTED';
        $diagnostics['storage']['message'] = "Supabase Storage API aktif (HTTP {$http_code_b}).";
        $diagnostics['storage']['bucket_found'] = true;
        $diagnostics['storage']['bucket_public'] = !empty($bucket_info['public']);
    } else {
        // Fallback cek list bucket
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$supabase_url}/storage/v1/bucket");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$supabase_key}",
            "apikey: {$supabase_key}"
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if ($http_code >= 200 && $http_code < 300) {
            $buckets = json_decode($response, true);
            $diagnostics['storage']['status'] = 'CONNECTED';
            $diagnostics['storage']['message'] = "Supabase Storage API aktif (HTTP {$http_code}).";
            
            if (is_array($buckets)) {
                foreach ($buckets as $b) {
                    if (isset($b['name']) && $b['name'] === $supabase_bucket) {
                        $diagnostics['storage']['bucket_found'] = true;
                        $diagnostics['storage']['bucket_public'] = !empty($b['public']);
                        break;
                    }
                }
            }
        } else {
            $diagnostics['storage']['status'] = 'FAILED';
            $diagnostics['storage']['message'] = $curl_err ? "cURL Error: {$curl_err}" : "Supabase API merespon HTTP {$http_code}: {$response}";
        }
    }
} else {
    $diagnostics['storage']['status'] = 'NOT_CONFIGURED';
    $diagnostics['storage']['message'] = 'SUPABASE_URL atau SUPABASE_KEY belum diisi di Environment Variables.';
}

$diagnostics['total_execution_ms'] = round((microtime(true) - $start_time) * 1000, 2);

// Output JSON jika diminta
if ($wants_json) {
    header('Content-Type: application/json');
    echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Koneksi Supabase & Vercel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #0f172a; color: #e2e8f0; font-family: system-ui, -apple-system, sans-serif; }
        .card-custom { background-color: #1e293b; border: 1px solid #334155; border-radius: 12px; }
        .badge-success-custom { background-color: #059669; color: white; }
        .badge-danger-custom { background-color: #dc2626; color: white; }
        .badge-warning-custom { background-color: #d97706; color: white; }
        code { background-color: #090d16; color: #38bdf8; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-activity text-primary"></i> Status Koneksi Supabase</h2>
                <p class="text-secondary mb-0">Halaman Diagnostik Sistem Pengaduan Masyarakat</p>
            </div>
            <div>
                <a href="?format=json" class="btn btn-outline-info btn-sm me-2"><i class="bi bi-braces"></i> Format JSON</a>
                <a href="index.php" class="btn btn-primary btn-sm"><i class="bi bi-house"></i> Ke Beranda</a>
            </div>
        </div>

        <!-- 1. Database PostgreSQL -->
        <div class="card card-custom p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0"><i class="bi bi-database-fill-gear text-info"></i> 1. Database Supabase PostgreSQL</h4>
                <?php if ($diagnostics['database']['status'] === 'CONNECTED'): ?>
                    <span class="badge badge-success-custom fs-6"><i class="bi bi-check-circle-fill"></i> TERHUBUNG (<?php echo $diagnostics['database']['latency_ms']; ?> ms)</span>
                <?php else: ?>
                    <span class="badge badge-danger-custom fs-6"><i class="bi bi-x-circle-fill"></i> GAGAL TERHUBUNG</span>
                <?php endif; ?>
            </div>
            
            <p class="mb-3 <?php echo $diagnostics['database']['status'] === 'CONNECTED' ? 'text-success' : 'text-danger'; ?>">
                <strong>Status Pesan:</strong> <?php echo htmlspecialchars($diagnostics['database']['message']); ?>
            </p>

            <?php if (!empty($diagnostics['database']['tables'])): ?>
                <h6 class="text-secondary mb-2">Tabel Database:</h6>
                <div class="table-responsive">
                    <table class="table table-dark table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Nama Tabel</th>
                                <th>Status Tabel</th>
                                <th>Jumlah Data (Rows)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diagnostics['database']['tables'] as $tname => $tinfo): ?>
                                <tr>
                                    <td><code><?php echo $tname; ?></code></td>
                                    <td>
                                        <?php if ($tinfo['status'] === 'EXISTS'): ?>
                                            <span class="badge bg-success">Tersedia</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Error: <?php echo htmlspecialchars($tinfo['error'] ?? 'Tidak Ditemukan'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo $tinfo['row_count'] ?? 0; ?></strong> baris</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- 2. Supabase Storage API -->
        <div class="card card-custom p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0"><i class="bi bi-cloud-arrow-up-fill text-warning"></i> 2. Supabase Storage API (Upload Foto)</h4>
                <?php if ($diagnostics['storage']['status'] === 'CONNECTED'): ?>
                    <span class="badge badge-success-custom fs-6"><i class="bi bi-check-circle-fill"></i> TERHUBUNG (<?php echo $diagnostics['storage']['latency_ms']; ?> ms)</span>
                <?php elseif ($diagnostics['storage']['status'] === 'NOT_CONFIGURED'): ?>
                    <span class="badge badge-warning-custom fs-6"><i class="bi bi-exclamation-circle-fill"></i> BELUM DIKONFIGURASI</span>
                <?php else: ?>
                    <span class="badge badge-danger-custom fs-6"><i class="bi bi-x-circle-fill"></i> GAGAL</span>
                <?php endif; ?>
            </div>
            
            <p class="mb-3 <?php echo $diagnostics['storage']['status'] === 'CONNECTED' ? 'text-success' : 'text-danger'; ?>">
                <strong>Status Pesan:</strong> <?php echo htmlspecialchars($diagnostics['storage']['message']); ?>
            </p>

            <?php if ($diagnostics['storage']['status'] === 'CONNECTED'): ?>
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="p-3 bg-dark rounded">
                            <small class="text-secondary d-block">Bucket <code><?php echo htmlspecialchars($supabase_bucket); ?></code> Ditemukan:</small>
                            <span class="fw-bold <?php echo $diagnostics['storage']['bucket_found'] ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $diagnostics['storage']['bucket_found'] ? '✓ YA' : '✗ TIDAK DITEMUKAN (Buat bucket bernama "' . htmlspecialchars($supabase_bucket) . '" di Supabase)'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-dark rounded">
                            <small class="text-secondary d-block">Status Public Bucket:</small>
                            <span class="fw-bold <?php echo $diagnostics['storage']['bucket_public'] ? 'text-success' : 'text-warning'; ?>">
                                <?php echo $diagnostics['storage']['bucket_public'] ? '✓ PUBLIC (Bisa diakses publik)' : '⚠ BUKAN PUBLIC (Aktifkan toggle Public bucket)'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- 3. Ringkasan Environment Variables -->
        <div class="card card-custom p-4 mb-4">
            <h4 class="mb-3"><i class="bi bi-sliders text-success"></i> 3. Status Environment Variables</h4>
            <div class="table-responsive">
                <table class="table table-dark table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Nama Variable</th>
                            <th>Nilai / Status Terdeteksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($diagnostics['env_variables'] as $vname => $vval): ?>
                            <tr>
                                <td><code><?php echo $vname; ?></code></td>
                                <td>
                                    <?php if (strpos($vval, 'KOSONG') !== false): ?>
                                        <span class="text-danger"><i class="bi bi-x-circle"></i> <?php echo $vval; ?></span>
                                    <?php else: ?>
                                        <span class="text-success"><i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($vval); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center text-secondary small">
            Waktu eksekusi diagnostik: <?php echo $diagnostics['total_execution_ms']; ?> ms &bull; <?php echo $diagnostics['timestamp']; ?>
        </div>
    </div>
</body>
</html>
