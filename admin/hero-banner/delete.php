<?php
/**
 * Padam Hero Banner - Admin SukanJTS Sarawak
 * CRUD: Delete (Proses Backend sahaja via POST)
 */

session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/auth-check.php';

// Pastikan hanya super_admin atau media boleh mengakses modul ini
confirm_access(['super_admin', 'media']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $csrf_token = $_POST['csrf_token'] ?? '';

    // 1. Sahkan CSRF
    if (!verify_csrf_token($csrf_token)) {
        $_SESSION['error_msg'] = "Token keselamatan tidak sah.";
        header("Location: index.php");
        exit;
    }

    if ($id <= 0) {
        $_SESSION['error_msg'] = "ID banner tidak sah.";
        header("Location: index.php");
        exit;
    }

    // Dapatkan nama fail banner sebelum dipadam untuk padam fizikal & log audit
    $stmt_fetch = $conn->prepare("SELECT tajuk, url_imej FROM tbl_hero_banner WHERE id = ? LIMIT 1");
    $stmt_fetch->bind_param("i", $id);
    $stmt_fetch->execute();
    $res = $stmt_fetch->get_result();

    if ($res->num_rows === 1) {
        $banner = $res->fetch_assoc();
        $tajuk = $banner['tajuk'];
        $url_imej = $banner['url_imej'];
        $stmt_fetch->close();

        // 2. Lakukan Pemadaman DB
        $stmt_del = $conn->prepare("DELETE FROM tbl_hero_banner WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        
        if ($stmt_del->execute()) {
            // Padam fail banner fizikal dari cakera pelayan
            $filepath = UPLOAD_DIR_HERO . $url_imej;
            if (file_exists($filepath)) {
                @unlink($filepath);
            }

            // Rekod Audit Log
            log_audit($conn, $_SESSION['admin_id'], 'delete', 'tbl_hero_banner', $id, "Padam banner: $tajuk");

            $_SESSION['success_msg'] = "Hero banner '$tajuk' berjaya dipadamkan.";
        } else {
            $_SESSION['error_msg'] = "Gagal memadam hero banner dari pangkalan data: " . $stmt_del->error;
        }
        $stmt_del->close();
    } else {
        $stmt_fetch->close();
        $_SESSION['error_msg'] = "Banner tidak dijumpai.";
    }
} else {
    $_SESSION['error_msg'] = "Kaedah permintaan tidak dibenarkan.";
}

header("Location: index.php");
exit;
