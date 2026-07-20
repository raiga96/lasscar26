<?php
/**
 * Padam Sukan - Admin SukanJTS Sarawak
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
        $_SESSION['error_msg'] = "ID sukan tidak sah.";
        header("Location: index.php");
        exit;
    }

    // Dapatkan nama sukan sebelum dipadam untuk tujuan rekod audit
    $stmt_fetch = $conn->prepare("SELECT nama_sukan FROM tbl_sukan WHERE id = ? LIMIT 1");
    $stmt_fetch->bind_param("i", $id);
    $stmt_fetch->execute();
    $res = $stmt_fetch->get_result();

    if ($res->num_rows === 1) {
        $sukan = $res->fetch_assoc();
        $nama_sukan = $sukan['nama_sukan'];
        $stmt_fetch->close();

        // 2. Lakukan Pemadaman (CASCADE di DB akan memadam perlawanan dan pendaftaran pasukan berkaitan)
        $stmt_del = $conn->prepare("DELETE FROM tbl_sukan WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        
        if ($stmt_del->execute()) {
            // Rekod Audit Log
            log_audit($conn, $_SESSION['admin_id'], 'delete', 'tbl_sukan', $id, "Padam sukan: $nama_sukan");

            $_SESSION['success_msg'] = "Sukan '$nama_sukan' berjaya dipadamkan dari sistem.";
        } else {
            $_SESSION['error_msg'] = "Gagal memadam sukan: " . $stmt_del->error;
        }
        $stmt_del->close();
    } else {
        $stmt_fetch->close();
        $_SESSION['error_msg'] = "Acara sukan tidak dijumpai.";
    }
} else {
    $_SESSION['error_msg'] = "Kaedah permintaan tidak dibenarkan.";
}

header("Location: index.php");
exit;
