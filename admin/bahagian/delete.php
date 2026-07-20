<?php
/**
 * Padam Kontinjen - Admin SukanJTS Sarawak
 * CRUD: Delete (Proses Backend sahaja via POST)
 */

session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/auth-check.php';

// Pastikan hanya super_admin boleh mengakses modul ini
confirm_access(['super_admin']);

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
        $_SESSION['error_msg'] = "ID kontinjen tidak sah.";
        header("Location: index.php");
        exit;
    }

    // Dapatkan nama dan logo sebelum dipadam untuk tujuan rekod audit & unlink logo
    $stmt_fetch = $conn->prepare("SELECT nama_bahagian, logo_url FROM tbl_bahagian WHERE id = ? LIMIT 1");
    $stmt_fetch->bind_param("i", $id);
    $stmt_fetch->execute();
    $res = $stmt_fetch->get_result();

    if ($res->num_rows === 1) {
        $kontinjen = $res->fetch_assoc();
        $nama_bahagian = $kontinjen['nama_bahagian'];
        $logo_url = $kontinjen['logo_url'];
        $stmt_fetch->close();

        // 2. Lakukan Pemadaman (CASCADE di DB akan memadam rekod tbl_pasukan berkaitan)
        $stmt_del = $conn->prepare("DELETE FROM tbl_bahagian WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        
        if ($stmt_del->execute()) {
            // Padam fail logo jika bukan default_logo.png
            if ($logo_url && $logo_url !== 'default_logo.png' && file_exists(UPLOAD_DIR_LOGO . $logo_url)) {
                @unlink(UPLOAD_DIR_LOGO . $logo_url);
            }

            // Rekod Audit Log
            log_audit($conn, $_SESSION['admin_id'], 'delete', 'tbl_bahagian', $id, "Padam kontinjen: $nama_bahagian");

            $_SESSION['success_msg'] = "Kontinjen '$nama_bahagian' berjaya dipadamkan dari sistem.";
        } else {
            $_SESSION['error_msg'] = "Gagal memadam kontinjen: " . $stmt_del->error;
        }
        $stmt_del->close();
    } else {
        $stmt_fetch->close();
        $_SESSION['error_msg'] = "Kontinjen tidak dijumpai.";
    }
} else {
    $_SESSION['error_msg'] = "Kaedah permintaan tidak dibenarkan.";
}

header("Location: index.php");
exit;
