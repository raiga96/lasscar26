<?php
/**
 * Google Drive Secure Image Streamer / Proxy (Claude Specialist - Backend)
 * Menggunakan Service Account Token untuk menstrim gambar terus dari Google Drive API
 * ke pelayar pengguna jika pautan langsung mengalami sekatan atau CORS.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/gdrive.php';

$file_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($file_id) || !preg_match('/^[a-zA-Z0-9_-]+$/', $file_id)) {
    http_response_code(400);
    echo "ID Fail Google Drive tidak sah.";
    exit;
}

$token = get_gdrive_access_token();

// Minta kandungan fail dari Google Drive API v3 (alt=media)
$url = "https://www.googleapis.com/drive/v3/files/{$file_id}?alt=media";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $token
    ],
    CURLOPT_TIMEOUT        => 30
]);

$data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($http_code === 200 && $data) {
    // Sembunyikan header PHP & kembalikan cache pelayar 24 jam
    header("Content-Type: " . ($content_type ?: "image/jpeg"));
    header("Content-Length: " . strlen($data));
    header("Cache-Control: public, max-age=86400");
    header("Pragma: public");
    echo $data;
    exit;
} else {
    // Gunakan fallback imej placeholder jika fail tidak dapat dicapai
    header("Content-Type: image/svg+xml");
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300">
        <rect width="400" height="300" fill="#1e293b"/>
        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#94a3b8" font-family="sans-serif" font-size="16">Google Drive Image Unavailable</text>
    </svg>';
    exit;
}
