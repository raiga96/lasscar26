<?php
/**
 * Fail Log Keluar - SukanJTS Sarawak
 * Memusnahkan sesi secara selamat dan merekod log audit tindakan.
 */

// Mulakan sesi jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Panggil dependencies
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    
    // Rekod tindakan logout ke audit log sebelum memusnahkan sesi
    log_audit($conn, $admin_id, 'logout', 'tbl_pengguna', $admin_id, "Pengguna log keluar sistem");
}

// Hapus semua pembolehubah sesi
session_unset();

// Musnahkan sesi fizikal
session_destroy();

// Redirect ke halaman login dengan mesej makluman
session_start();
$_SESSION['login_error'] = "Anda telah berjaya log keluar dari sistem.";
header("Location: " . BASE_URL . "admin/auth/login.php");
exit;
