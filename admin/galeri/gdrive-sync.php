<?php
/**
 * Admin AJAX Endpoint - Paksa Penyegerakan Google Drive API v3 (Claude Specialist)
 */
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/gdrive_sync.php';

header('Content-Type: application/json');

// Semak sesi pentadbir
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Sesi anda telah tamat. Sila log masuk semula.']);
    exit;
}

// Semak token CSRF
$token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    echo json_encode(['success' => false, 'message' => 'Token keselamatan CSRF tidak sah.']);
    exit;
}

// Jalankan sync dipaksa ($force = true)
$result = sync_gdrive_gallery($conn, GDRIVE_FOLDER_ID, true);

echo json_encode($result);
exit;
