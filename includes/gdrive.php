<?php
/**
 * Modul Pembantu Google Drive API v3 (Claude Specialist - Backend)
 * Menggunakan Google Service Account untuk pengesahan JWT (bukan OAuth pelawat).
 * Mengambil metadata gambar & fail dari folder Google Drive tanpa SDK luaran.
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
 * Ambil senarai fail imej dari folder Google Drive
 * 
 * @param string $folder_id ID Folder Google Drive
 * @return array|false Senarai fail atau false jika ralat
 */
function fetch_gdrive_folder_files($folder_id) {
    $token = get_gdrive_access_token();
    if (!$token) {
        return false;
    }

    $all_files = [];
    $page_token = null;

    do {
        // Query fail dalam folder, jenis imej sahaja, bukan trashed
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
            error_log("GDrive API Error ({$http_code}): " . $response);
            return false;
        }

        $res_data = json_decode($response, true);
        if (isset($res_data['files']) && is_array($res_data['files'])) {
            foreach ($res_data['files'] as $file) {
                $all_files[] = $file;
            }
        }

        $page_token = $res_data['nextPageToken'] ?? null;

    } while ($page_token !== null);

    return $all_files;
}

/**
 * Helper fungsi untuk Base64URL Encoding
 */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
