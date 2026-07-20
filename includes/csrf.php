<?php
/**
 * Modul Perlindungan Cross-Site Request Forgery (CSRF) - SukanJTS Sarawak
 * Digunakan untuk menjana dan mengesahkan token keselamatan bagi kiriman borang POST admin.
 */

// Mulakan sesi jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Menjana token CSRF baharu (jika tiada) dan menyimpan ke dalam sesi.
 * 
 * @return string Token CSRF
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Mengesahkan sama ada token CSRF yang dikirimkan berpadanan dengan sesi.
 * 
 * @param string|null $token Token yang dihantar dari borang POST
 * @return bool
 */
function verify_csrf_token(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    // Guna hash_equals untuk mengelakkan serangan timing attack
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Memaparkan medan input tersembunyi (hidden input) CSRF untuk borang HTML.
 * 
 * @return void
 */
function csrf_field(): void {
    $token = generate_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
}
