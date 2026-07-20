<?php
/**
 * Padam Jadual Perlawanan - Admin SukanJTS Sarawak
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
        $_SESSION['error_msg'] = "ID jadual tidak sah.";
        header("Location: index.php");
        exit;
    }

    // Dapatkan maklumat perlawanan untuk log audit
    $stmt_fetch = $conn->prepare("SELECT j.id, s.nama_sukan, j.pusingan FROM tbl_jadual_perlawanan j 
                                  JOIN tbl_sukan s ON j.sukan_id = s.id 
                                  WHERE j.id = ? LIMIT 1");
    $stmt_fetch->bind_param("i", $id);
    $stmt_fetch->execute();
    $res = $stmt_fetch->get_result();

    if ($res->num_rows === 1) {
        $data = $res->fetch_assoc();
        $sukan_name = $data['nama_sukan'];
        $pusingan = $data['pusingan'];
        $stmt_fetch->close();

        // 2. Lakukan Pemadaman (CASCADE di DB akan memadam rekod tbl_keputusan berkaitan)
        $stmt_del = $conn->prepare("DELETE FROM tbl_jadual_perlawanan WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        
        if ($stmt_del->execute()) {
            // Rekod Audit Log
            log_audit($conn, $_SESSION['admin_id'], 'delete', 'tbl_jadual_perlawanan', $id, "Padam jadual perlawanan ID $id: Sukan $sukan_name ($pusingan)");

            $_SESSION['success_msg'] = "Jadual perlawanan berjaya dipadamkan dari sistem.";
        } else {
            $_SESSION['error_msg'] = "Gagal memadam jadual: " . $stmt_del->error;
        }
        $stmt_del->close();
    } else {
        $stmt_fetch->close();
        $_SESSION['error_msg'] = "Jadual perlawanan tidak dijumpai.";
    }
} else {
    $_SESSION['error_msg'] = "Kaedah permintaan tidak dibenarkan.";
}

header("Location: index.php");
exit;
