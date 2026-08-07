<?php
/**
 * Fail Konfigurasi Global - Sistem Pengurusan Sukan JTS Sarawak (SukanJTS)
 * Mengandungi tetapan pangkalan data, pembolehubah sistem, dan tetapan keselamatan sesi.
 */

// Tetapan ralat (production-ready: sembunyikan ralat mentah ke paparan awam, catat dalam log jika perlu)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error_log.txt');

// Konfigurasi Pangkalan Data
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'KataLaluan4kik@JTS');
define('DB_NAME', 'sistem_sukan_jts');

// Tetapan Sesi (Security)
define('SESSION_LIFETIME', 1800); // 30 minit (dalam saat)

// Konfigurasi Kejohanan / Maklumat Sistem
define('APP_NAME', 'LASSCAR 2026');
define('APP_FULL_NAME', 'Landas Sport Carnival 2026');
define('TOURNAMENT_TITLE', 'Landas Sport Carnival 2026 (LASSCAR)');
define('TOURNAMENT_THEME', 'Good Sports, Strong Unity');
define('TOURNAMENT_DATE', '20 - 25 Julai 2026');
define('TOURNAMENT_LOCATION', 'Stadium Perpaduan, Kuching, Sarawak');
define('CHAIRMAN_NAME', 'Datu Awang Zamhari Bin Awang Mahmood');
define('CHAIRMAN_ROLE', 'Pengarah Jabatan Tanah dan Survei Sarawak');
define('CHAIRMAN_WELCOME_MESSAGE', 'Selamat datang ke portal LASSCAR 2026. Kejohanan ini dibina untuk memperkukuh ukhuwah, membina semangat kesukanan yang tinggi, serta memelihara tahap integriti yang tinggi di kalangan seluruh kakitangan Jabatan Tanah dan Survei Sarawak di semua pejabat bahagian serta agensi jemputan. Selamat bertanding!');

// Pengisytiharan URL Asas (Base URL) untuk mempermudah navigasi fail
define('BASE_URL', '/lasscar26/');

// Tetapan Had Muat Naik File (File Upload Limits)
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB (Am)
define('MAX_HERO_BANNER_SIZE', 20 * 1024 * 1024); // 20MB (Khas untuk Hero Banner)
define('MAX_VIDEO_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_IMAGE_MIMES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_VIDEO_MIMES', ['video/mp4', 'video/webm']);

// Jalur Direktori Upload Fizikal
define('UPLOAD_DIR_LOGO', __DIR__ . '/../assets/uploads/logo-bahagian/');
define('UPLOAD_DIR_GALERI', __DIR__ . '/../assets/uploads/galeri/');
define('UPLOAD_DIR_HERO', __DIR__ . '/../assets/uploads/hero/');

// Konfigurasi Google Drive API v3
define('GDRIVE_SERVICE_ACCOUNT_FILE', __DIR__ . '/gdrive-service-account.json');
define('GDRIVE_FOLDER_ID', getenv('GDRIVE_FOLDER_ID') ?: '1_SAMPLE_GDRIVE_FOLDER_ID'); // ID Folder Google Drive Utama
define('GDRIVE_SYNC_TTL', 180); // 3 minit selang masa penyegerakan automatik (dalam saat)

