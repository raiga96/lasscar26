<?php
/**
 * Padam Pentadbir - Admin SukanJTS Sarawak
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
        $_SESSION['error_msg'] = "ID pengguna tidak sah.";
        header("Location: index.php");
        exit;
    }

    // Halang pemadaman akaun sendiri
    if ($id == $_SESSION['admin_id']) {
        $_SESSION['error_msg'] = "Anda tidak dibenarkan memadam akaun anda sendiri.";
        header("Location: index.php");
        exit;
    }

    // Dapatkan nama pengguna sebelum dipadam untuk tujuan rekod audit
    $stmt_fetch = $conn->prepare("SELECT nama_penuh, emel FROM tbl_pengguna WHERE id = ? LIMIT 1");
    $stmt_fetch->bind_param("i", $id);
    $stmt_fetch->execute();
    $res = $stmt_fetch->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        $nama_penuh = $user['nama_penuh'];
        $emel = $user['emel'];
        $stmt_fetch->close();

        // 2. Lakukan Pemadaman (CASCADE/SET NULL di DB akan mengurus rekod log audit & keputusan direkod)
        $stmt_del = $conn->prepare("DELETE FROM tbl_pengguna WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        
        if ($stmt_del->execute()) {
            // Rekod Audit Log
            log_audit($conn, $_SESSION['admin_id'], 'delete', 'tbl_pengguna', $id, "Padam pengguna admin: $nama_penuh ($emel)");

            $_SESSION['success_msg'] = "Akaun pentadbir '$nama_penuh' berjaya dipadamkan dari sistem.";
        } else {
            $_SESSION['error_msg'] = "Gagal memadam pentadbir: " . $stmt_del->error;
        }
        $stmt_del->close();
    } else {
        $stmt_fetch->close();
        $_SESSION['error_msg'] = "Pengguna tidak dijumpai.";
    }
} else {
    $_SESSION['error_msg'] = "Kaedah permintaan tidak dibenarkan.";
}

header("Location: index.php");
exit;
