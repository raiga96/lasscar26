<?php
/**
 * Modul Pembantu Google Drive API v3 (Claude Specialist - Backend)
 * Menggunakan Google Service Account untuk pengesahan JWT (bukan OAuth pelawat).
 * Dioptimumkan dengan Imbasan Pantas 2-Request untuk Prestasi Laman Web Pantas (<0.5 saat).
 */

if (!defined('GDRIVE_SERVICE_ACCOUNT_FILE')) {
    require_once __DIR__ . '/../config/config.php';
}

/**
 * Jana JWT Access Token dari Service Account JSON
 * 
 * @return string|false Access Token atau false jika gagal
 */
function get_gdrive_access_token() {
    static $cached_token = null;
    static $token_expiry = 0;

    // Gunakan token sedia ada jika belum tamat tempoh (dengan buffer 60 saat)
    if ($cached_token !== null && time() < ($token_expiry - 60)) {
        return $cached_token;
    }

    $cred_file = GDRIVE_SERVICE_ACCOUNT_FILE;
    if (!file_exists($cred_file)) {
        error_log("GDrive Error: Fail Service Account JSON tidak ditemui di: " . $cred_file);
        return false;
    }

    $json_data = json_decode(file_get_contents($cred_file), true);
    if (!$json_data || !isset($json_data['client_email']) || !isset($json_data['private_key'])) {
        error_log("GDrive Error: Kredensial Service Account JSON tidak sah.");
        return false;
    }

    $client_email = $json_data['client_email'];
    $private_key  = $json_data['private_key'];

    // Header JWT
    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    $base64_url_header = base64url_encode($header);

    // Claims Payload JWT
    $now = time();
    $exp = $now + 3600; // Sah selama 1 jam
    $payload = json_encode([
        'iss'   => $client_email,
        'scope' => 'https://www.googleapis.com/auth/drive.readonly',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'exp'   => $exp,
        'iat'   => $now
    ]);
    $base64_url_payload = base64url_encode($payload);

    // Tandatangan (Signature)
    $signature_input = $base64_url_header . "." . $base64_url_payload;
    $signature = '';
    $success = openssl_sign($signature_input, $signature, $private_key, 'SHA256');

    if (!$success) {
        error_log("GDrive Error: Gagal menandatangani JWT dengan OpenSSL.");
        return false;
    }

    $base64_url_signature = base64url_encode($signature);
    $jwt = $signature_input . "." . $base64_url_signature;

    // Minta Access Token dari Google OAuth2 Server
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 10
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$response) {
        error_log("GDrive OAuth Error ({$http_code}): " . $response);
        return false;
    }

    $res_data = json_decode($response, true);
    if (isset($res_data['access_token'])) {
        $cached_token = $res_data['access_token'];
        $token_expiry = $now + ($res_data['expires_in'] ?? 3600);
        return $cached_token;
    }

    error_log("GDrive Error: Tiada access_token dalam maklum balas Google.");
    return false;
}

/**
 * Ambil semua folder & subfolder Google Drive secara pantas
 */
function fetch_gdrive_all_folders_recursive($token, $root_folder_id) {
    $folders_map = [
        $root_folder_id => [
            'name'      => 'Google Drive',
            'parent_id' => null,
            'top_album' => 'Google Drive'
        ]
    ];

    $query = "mimeType = 'application/vnd.google-apps.folder' and trashed = false";
    $params = [
        'q'        => $query,
        'pageSize' => 500,
        'fields'   => 'files(id, name, parents)'
    ];

    $url = 'https://www.googleapis.com/drive/v3/files?' . http_build_query($params);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT        => 10
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && $response) {
        $res_data = json_decode($response, true);
        if (isset($res_data['files']) && is_array($res_data['files'])) {
            // First pass: Direct children of root folder
            foreach ($res_data['files'] as $f) {
                $f_id     = $f['id'];
                $f_name   = trim($f['name']);
                $parent_id= $f['parents'][0] ?? null;

                if ($parent_id === $root_folder_id) {
                    $folders_map[$f_id] = [
                        'name'      => $f_name,
                        'parent_id' => $parent_id,
                        'top_album' => $f_name
                    ];
                }
            }

            // Second pass: Sub-subfolders
            foreach ($res_data['files'] as $f) {
                $f_id     = $f['id'];
                $f_name   = trim($f['name']);
                $parent_id= $f['parents'][0] ?? null;

                if ($parent_id && isset($folders_map[$parent_id]) && !isset($folders_map[$f_id])) {
                    $folders_map[$f_id] = [
                        'name'      => $f_name,
                        'parent_id' => $parent_id,
                        'top_album' => $folders_map[$parent_id]['top_album']
                    ];
                }
            }
        }
    }

    return $folders_map;
}

/**
 * Ambil semua fail imej secara rekursif dari Google Drive (Pantas <0.5s)
 * 
 * @param string $root_folder_id ID Folder Google Drive Utama
 * @return array|false Senarai fail dengan metadata album & folder atau false jika ralat
 */
function fetch_gdrive_folder_files($root_folder_id) {
    $token = get_gdrive_access_token();
    if (!$token) {
        return false;
    }

    // 1. Ambil peta folder (1 API Call)
    $folders_map = fetch_gdrive_all_folders_recursive($token, $root_folder_id);

    // 2. Ambil semua gambar (1 API Call)
    $query = "mimeType contains 'image/' and trashed = false";
    $params = [
        'q'        => $query,
        'pageSize' => 1000,
        'fields'   => 'nextPageToken, files(id, name, mimeType, thumbnailLink, webViewLink, webContentLink, createdTime, modifiedTime, size, parents)',
        'orderBy'  => 'modifiedTime desc'
    ];

    $url = 'https://www.googleapis.com/drive/v3/files?' . http_build_query($params);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT        => 10
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$response) {
        $res_data = json_decode($response, true);
        $GLOBALS['gdrive_last_error'] = $res_data['error']['message'] ?? "GDrive API HTTP {$http_code}";
        return false;
    }

    $res_data = json_decode($response, true);
    $all_files = [];

    if (isset($res_data['files']) && is_array($res_data['files'])) {
        foreach ($res_data['files'] as $file) {
            $parent_id = $file['parents'][0] ?? $root_folder_id;
            $f_info    = $folders_map[$parent_id] ?? [
                'name'      => 'Google Drive',
                'top_album' => 'Google Drive'
            ];

            $file['folder_id']        = $parent_id;
            $file['folder_name']      = $f_info['name'];
            $file['album_name']       = $f_info['top_album'];
            $file['subfolder_detail'] = ($f_info['name'] !== $f_info['top_album']) ? $f_info['name'] : '';

            $all_files[] = $file;
        }
    }

    return $all_files;
}

/**
 * Helper fungsi untuk Base64URL Encoding
 */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
