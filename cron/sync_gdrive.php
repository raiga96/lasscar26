<?php
/**
 * Skrip CLI Cron Job Penyegerakan Google Drive API v3 (Claude Specialist - Backend)
 * Boleh dijalankan melalui Cron Job pelayan:
 * php /path/to/lasscar26/cron/sync_gdrive.php --force
 */

if (php_sapi_name() !== 'cli' && !isset($_GET['secret'])) {
    die("Akses CLI sahaja.");
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/gdrive_sync.php';

$force = in_array('--force', $argv ?? []) || isset($_GET['force']);

echo "[" . date('Y-m-d H:i:s') . "] Memulakan penyegerakan Google Drive...\n";
$res = sync_gdrive_gallery($conn, GDRIVE_FOLDER_ID, $force);

if ($res['success']) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $res['message'] . "\n";
} else {
    echo "[" . date('Y-m-d H:i:s') . "] RALAT: " . $res['message'] . "\n";
}
