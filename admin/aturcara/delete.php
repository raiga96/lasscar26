<?php
/**
 * Padam Aturcara - Admin SukanJTS Sarawak
 * CRUD: Delete (Proses Backend sahaja via POST)
 */

session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/auth-check.php';

// Pastikan super_admin atau editor sahaja boleh mengakses modul ini
confirm_access(['super_admin', 'editor']);

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
        $_SESSION['error_msg'] = "ID aturcara tidak sah.";
        header("Location: index.php");
        exit;
    }

    // Dapatkan nama aktiviti sebelum dipadam untuk tujuan rekod audit
    $stmt_fetch = $conn->prepare("SELECT aktiviti FROM tbl_aturcara WHERE id = ? LIMIT 1");
    $stmt_fetch->bind_param("i", $id);
    $stmt_fetch->execute();
    $res = $stmt_fetch->get_result();

    if ($res->num_rows === 1) {
        $aturcara = $res->fetch_assoc();
        $aktiviti = $aturcara['aktiviti'];
        $stmt_fetch->close();

        // 2. Lakukan Pemadaman
        $stmt_del = $conn->prepare("DELETE FROM tbl_aturcara WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        
        if ($stmt_del->execute()) {
            // Rekod Audit Log
            log_audit($conn, $_SESSION['admin_id'], 'delete', 'tbl_aturcara', $id, "Padam aturcara: $aktiviti");

            $_SESSION['success_msg'] = "Aturcara '$aktiviti' berjaya dipadamkan.";
        } else {
            $_SESSION['error_msg'] = "Gagal memadam aturcara: " . $stmt_del->error;
        }
        $stmt_del->close();
    } else {
        $stmt_fetch->close();
        $_SESSION['error_msg'] = "Aturcara tidak dijumpai.";
    }
} else {
    $_SESSION['error_msg'] = "Kaedah permintaan tidak dibenarkan.";
}

header("Location: index.php");
exit;
