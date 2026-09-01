<?php
// ===================================================
// SESSION HELPER UNTUK VERCEL SERVERLESS (STATELESS)
// ===================================================

if (!defined('SESSION_HELPER_LOADED')) {
    define('SESSION_HELPER_LOADED', true);

    // Aktifkan output buffering sedini mungkin agar header cookie bisa dikirim tanpa error
    if (ob_get_level() == 0) {
        ob_start();
    }

    function get_session_secret() {
        $sec = getenv('SESSION_SECRET');
        if (!empty($sec)) return $sec;
        if (!empty($_ENV['SESSION_SECRET'])) return $_ENV['SESSION_SECRET'];
        return 'kelurahan_rengas_session_secure_key_2026_x89@supabase';
    }

    /**
     * Simpan session ke signed cookie secara aman
     */
    function save_serverless_session() {
        $cookie_name = 'rengas_admin_auth';
        $secret = get_session_secret();

        if (isset($_SESSION) && !empty($_SESSION['admin_logged_in'])) {
            $payload = $_SESSION;
            $payload['__expires_at'] = time() + (86400 * 7); // Berlaku 7 hari
            $payload_b64 = base64_encode(json_encode($payload));
            $signature = hash_hmac('sha256', $payload_b64, $secret);
            $cookie_val = $payload_b64 . '.' . $signature;

            $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

            // Gunakan setcookie standar
            setcookie($cookie_name, $cookie_val, [
                'expires' => time() + (86400 * 7),
                'path' => '/',
                'secure' => $is_https,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            $_COOKIE[$cookie_name] = $cookie_val;
            return true;
        }
        return false;
    }

    /**
     * Inisialisasi session berbasis signed cookie agar persisten di serverless Lambda Vercel
     */
    function init_serverless_session() {
        // 1. Jalankan session_start bawaan PHP terlebih dahulu
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $cookie_name = 'rengas_admin_auth';
        $secret = get_session_secret();

        // 2. Jika $_SESSION belum ada/hilang karena stateless Lambda, pulihkan dari signed cookie
        if (empty($_SESSION['admin_logged_in']) && isset($_COOKIE[$cookie_name])) {
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
        unset($_COOKIE[$cookie_name]);

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_unset();
            @session_destroy();
        }
    }
}
