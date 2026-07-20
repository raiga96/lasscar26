<?php
/**
 * Padam Media - Admin SukanJTS Sarawak
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
        $_SESSION['error_msg'] = "ID media tidak sah.";
        header("Location: index.php");
        exit;
    }

    // Dapatkan maklumat fail untuk padam fizikal & log audit
    $stmt_fetch = $conn->prepare("SELECT tajuk, url_fail FROM tbl_galeri WHERE id = ? LIMIT 1");
    $stmt_fetch->bind_param("i", $id);
    $stmt_fetch->execute();
    $res = $stmt_fetch->get_result();

    if ($res->num_rows === 1) {
        $media = $res->fetch_assoc();
        $tajuk = $media['tajuk'];
        $url_fail = $media['url_fail'];
        $stmt_fetch->close();

        // 2. Lakukan Pemadaman DB
        $stmt_del = $conn->prepare("DELETE FROM tbl_galeri WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        
        if ($stmt_del->execute()) {
            // Padam fail media fizikal dari cakera pelayan
            $filepath = UPLOAD_DIR_GALERI . $url_fail;
            if (file_exists($filepath)) {
                @unlink($filepath);
            }

            // Rekod Audit Log
            log_audit($conn, $_SESSION['admin_id'], 'delete', 'tbl_galeri', $id, "Padam media: $tajuk");

            $_SESSION['success_msg'] = "Media '$tajuk' berjaya dipadamkan dari galeri.";
        } else {
            $_SESSION['error_msg'] = "Gagal memadam media dari pangkalan data: " . $stmt_del->error;
        }
        $stmt_del->close();
    } else {
        $stmt_fetch->close();
        $_SESSION['error_msg'] = "Fail media tidak dijumpai.";
    }
} else {
    $_SESSION['error_msg'] = "Kaedah permintaan tidak dibenarkan.";
}

header("Location: index.php");
exit;
