<?php
// ===================================================
// SESSION HELPER UNTUK VERCEL SERVERLESS (STATELESS)
// ===================================================

if (!defined('SESSION_HELPER_LOADED')) {
    define('SESSION_HELPER_LOADED', true);

    function get_session_secret() {
        $sec = getenv('SESSION_SECRET');
        if (!empty($sec)) return $sec;
        if (!empty($_ENV['SESSION_SECRET'])) return $_ENV['SESSION_SECRET'];
        return 'kelurahan_rengas_session_secure_key_2026_x89@supabase';
    }

    /**
     * Inisialisasi session berbasis signed cookie agar persisten di serverless Lambda Vercel
     */
    function init_serverless_session() {
        $cookie_name = 'rengas_admin_auth';
        $secret = get_session_secret();

        if (!isset($_SESSION)) {
            $_SESSION = [];
        }

        // Baca data session dari cookie bertanda tangan HMAC
        if (isset($_COOKIE[$cookie_name])) {
            $parts = explode('.', $_COOKIE[$cookie_name], 2);
            if (count($parts) === 2) {
                list($payload_b64, $signature) = $parts;
                $expected_sig = hash_hmac('sha256', $payload_b64, $secret);
                if (hash_equals($expected_sig, $signature)) {
                    $decoded = json_decode(base64_decode($payload_b64), true);
                    if (is_array($decoded) && (!isset($decoded['__expires_at']) || $decoded['__expires_at'] > time())) {
                        foreach ($decoded as $k => $v) {
                            if ($k !== '__expires_at') {
                                $_SESSION[$k] = $v;
                            }
                        }
                    }
                }
            }
        }

        // Buka session bawaan jika belum aktif
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        // Sinkronisasi session ke signed cookie saat script selesai dieksekusi
        register_shutdown_function(function() use ($cookie_name, $secret) {
            if (isset($_SESSION) && !empty($_SESSION['admin_logged_in'])) {
                $payload = $_SESSION;
                $payload['__expires_at'] = time() + (86400 * 7); // Berlaku 7 hari
                $payload_b64 = base64_encode(json_encode($payload));
                $signature = hash_hmac('sha256', $payload_b64, $secret);
                $cookie_val = $payload_b64 . '.' . $signature;

                $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

                setcookie($cookie_name, $cookie_val, [
                    'expires' => time() + (86400 * 7),
                    'path' => '/',
                    'secure' => $is_https,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
        });
    }

    /**
     * Hapus session dan cookie auth
     */
    function destroy_serverless_session() {
        $cookie_name = 'rengas_admin_auth';
        $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        setcookie($cookie_name, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $is_https,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_unset();
            @session_destroy();
        }
    }
}
