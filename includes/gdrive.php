<?php
/**
 * Modul Pembantu Google Drive API v3 (Claude Specialist - Backend)
 * Menggunakan Google Service Account untuk pengesahan JWT (bukan OAuth pelawat).
 * Menyokong imbasan rekursif subfolder & sub-subfolder (10 Sukan & Acara).
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
        CURLOPT_TIMEOUT        => 15
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
 * Ambil semua subfolder secara rekursif dari folder utama Google Drive
 * 
 * @param string $token Access Token Google Drive
 * @param string $root_folder_id ID Folder Utama
 * @return array Map [folder_id => ['name' => ..., 'parent_id' => ..., 'top_album' => ...]]
 */
function fetch_gdrive_all_folders_recursive($token, $root_folder_id) {
    $folders_map = [
        $root_folder_id => [
            'name'      => 'Google Drive',
            'parent_id' => null,
            'top_album' => 'Google Drive'
        ]
    ];

    $queue = [$root_folder_id];

    while (!empty($queue)) {
        $current_parent_id = array_shift($queue);
        $current_top_album = $folders_map[$current_parent_id]['top_album'];

        $page_token = null;

        do {
            $query = "'{$current_parent_id}' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false";
            $params = [
                'q'        => $query,
                'pageSize' => 100,
                'fields'   => 'nextPageToken, files(id, name, parents)'
            ];
            if ($page_token) {
                $params['pageToken'] = $page_token;
            }

            $url = 'https://www.googleapis.com/drive/v3/files?' . http_build_query($params);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $token,
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT        => 15
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code !== 200 || !$response) {
                break;
            }

            $res_data = json_decode($response, true);
            if (isset($res_data['files']) && is_array($res_data['files'])) {
                foreach ($res_data['files'] as $f) {
                    $f_id   = $f['id'];
                    $f_name = trim($f['name']);

                    // Tentukan album utama (Top Album e.g. "Futsal", "Badminton")
                    $top_album = ($current_parent_id === $root_folder_id) ? $f_name : $current_top_album;

                    $folders_map[$f_id] = [
                        'name'      => $f_name,
                        'parent_id' => $current_parent_id,
                        'top_album' => $top_album
                    ];

                    $queue[] = $f_id;
                }
            }

            $page_token = $res_data['nextPageToken'] ?? null;

        } while ($page_token !== null);
    }

    return $folders_map;
}

/**
 * Ambil semua fail imej secara rekursif dari folder utama dan semua subfolder Google Drive
 * 
 * @param string $root_folder_id ID Folder Google Drive Utama
 * @return array|false Senarai fail dengan metadata album & folder atau false jika ralat
 */
function fetch_gdrive_folder_files($root_folder_id) {
    $token = get_gdrive_access_token();
    if (!$token) {
        return false;
    }

    // 1. Ambil peta semua subfolder & sub-subfolder secara rekursif
    $folders_map = fetch_gdrive_all_folders_recursive($token, $root_folder_id);

    $all_files = [];

    // 2. Imbas gambar dari setiap folder yang ditemui
    foreach ($folders_map as $folder_id => $f_info) {
        $page_token = null;

        do {
            $query = "'{$folder_id}' in parents and mimeType contains 'image/' and trashed = false";
            $params = [
                'q'        => $query,
                'pageSize' => 100,
                'fields'   => 'nextPageToken, files(id, name, mimeType, thumbnailLink, webViewLink, webContentLink, createdTime, modifiedTime, size, parents)',
                'orderBy'  => 'modifiedTime desc'
            ];

            if ($page_token) {
                $params['pageToken'] = $page_token;
            }

            $url = 'https://www.googleapis.com/drive/v3/files?' . http_build_query($params);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $token,
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT        => 20
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code !== 200 || !$response) {
                break;
            }

            $res_data = json_decode($response, true);
            if (isset($res_data['files']) && is_array($res_data['files'])) {
                foreach ($res_data['files'] as $file) {
                    // Lampirkan maklumat folder & album ke dalam metadata fail
                    $file['folder_id']        = $folder_id;
                    $file['folder_name']      = $f_info['name'];
                    $file['album_name']       = $f_info['top_album'];
                    $file['subfolder_detail'] = ($f_info['name'] !== $f_info['top_album']) ? $f_info['name'] : '';

                    $all_files[] = $file;
                }
            }

            $page_token = $res_data['nextPageToken'] ?? null;

        } while ($page_token !== null);
    }

    return $all_files;
}

/**
 * Helper fungsi untuk Base64URL Encoding
 */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
