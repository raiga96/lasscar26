<?php
/**
 * Fail Helper Utama - SukanJTS Sarawak
 * Mengandungi fungsi-fungsi sanitasi, keselamatan audit log, kawalan rate limiting,
 * format tarikh tempatan, dan muat naik fail selamat.
 */

// Panggil sambungan pangkalan data jika belum ada
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/config.php';
}

/**
 * Sanitasi output teks untuk menghalang serangan Cross-Site Scripting (XSS).
 * 
 * @param string|null $data
 * @return string
 */
function sanitize(?string $data): string {
    if ($data === null) {
        return '';
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Mendapatkan alamat IP pelawat secara selamat.
 * 
 * @return string
 */
function get_client_ip(): string {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    // Sanitasi IP address
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
}

/**
 * Merekod tindakan pentadbir ke dalam log audit (Append-only).
 * 
 * @param mysqli $conn Objek sambungan MySQLi
 * @param int|null $pengguna_id ID admin yang melakukan tindakan
 * @param string $tindakan Jenis tindakan ('create', 'update', 'delete', 'login', 'logout')
 * @param string $jadual Nama jadual yang disentuh
 * @param int|null $rekod_id ID baris rekod yang diubah
 * @param string|null $butiran Maklumat tambahan transaksi
 * @return bool
 */
function log_audit(mysqli $conn, ?int $pengguna_id, string $tindakan, string $jadual, ?int $rekod_id, ?string $butiran): bool {
    $ip = get_client_ip();
    
    // Guna prepared statements untuk keselamatan optimum
    $stmt = $conn->prepare("INSERT INTO tbl_audit_log (pengguna_id, tindakan, jadual_disentuh, rekod_id, butiran, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Gagal menyediakan prepared statement log_audit: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("ississ", $pengguna_id, $tindakan, $jadual, $rekod_id, $butiran, $ip);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Semak status brute-force rate limit bagi akaun e-mel tertentu.
 * Akaun dikunci selama 15 minit jika gagal 5 kali berturut-turut.
 * 
 * @param mysqli $conn
 * @param string $emel
 * @return array Mengembalikan ['status' => true/false, 'remain_seconds' => int, 'message' => string]
 */
function check_login_attempts(mysqli $conn, string $emel): array {
    $stmt = $conn->prepare("SELECT percubaan_gagal, dikunci_sehingga FROM tbl_pengguna WHERE emel = ?");
    if (!$stmt) {
        return ['status' => false, 'message' => 'Ralat sistem'];
    }
    
    $stmt->bind_param("s", $emel);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return ['status' => false, 'message' => 'E-mel tidak wujud'];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user['dikunci_sehingga'] !== null) {
        $locked_time = strtotime($user['dikunci_sehingga']);
        $current_time = time();
        
        if ($locked_time > $current_time) {
            $remain = $locked_time - $current_time;
            return [
                'status' => true,
                'remain_seconds' => $remain,
                'message' => "Akaun ini dikunci sementara kerana 5 kegagalan cubaan login. Sila cuba lagi dalam masa " . ceil($remain / 60) . " minit."
            ];
        } else {
            // Sesi kunci telah tamat, set semula percubaan gagal
            reset_failed_login($conn, $emel);
        }
    }
    
    return ['status' => false, 'message' => 'Akaun aktif'];
}

/**
 * Rekod kegagalan log masuk dan kemas kini status kunci (lockout) jika perlu.
 * 
 * @param mysqli $conn
 * @param string $emel
 * @return void
 */
function record_failed_login(mysqli $conn, string $emel): void {
    // Kemas kini percubaan_gagal
    $stmt = $conn->prepare("UPDATE tbl_pengguna SET percubaan_gagal = percubaan_gagal + 1 WHERE emel = ?");
    $stmt->bind_param("s", $emel);
    $stmt->execute();
    $stmt->close();
    
    // Semak sama ada perlu dikunci (kunci 15 minit jika percubaan >= 5)
    $stmt_check = $conn->prepare("SELECT percubaan_gagal FROM tbl_pengguna WHERE emel = ?");
    $stmt_check->bind_param("s", $emel);
    $stmt_check->execute();
    $res = $stmt_check->get_result();
    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        if ($user['percubaan_gagal'] >= 5) {
            // Set dikunci sehingga 15 minit akan datang
            $stmt_lock = $conn->prepare("UPDATE tbl_pengguna SET dikunci_sehingga = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE emel = ?");
            $stmt_lock->bind_param("s", $emel);
            $stmt_lock->execute();
            $stmt_lock->close();
        }
    }
    $stmt_check->close();
}

/**
 * Tetapkan semula percubaan gagal log masuk bagi akaun e-mel.
 * 
 * @param mysqli $conn
 * @param string $emel
 * @return void
 */
function reset_failed_login(mysqli $conn, string $emel): void {
    $stmt = $conn->prepare("UPDATE tbl_pengguna SET percubaan_gagal = 0, dikunci_sehingga = NULL WHERE emel = ?");
    $stmt->bind_param("s", $emel);
    $stmt->execute();
    $stmt->close();
}

/**
 * Memformat tarikh standard (YYYY-MM-DD) kepada format Bahasa Melayu.
 * Contoh: 2026-07-20 -> 20 Julai 2026
 * 
 * @param string $date
 * @return string
 */
function format_date(string $date): string {
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }
    
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
        5 => 'Mei', 6 => 'Jun', 7 => 'Khamis', 8 => 'Ogos', // Wait, 7 is Julai, 8 is Ogos! Let's fix this month translation
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember'
    ];
    // Ah, 7 is Julai! Let's correct it:
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
        5 => 'Mei', 6 => 'Jun', 7 => 'Julai', 8 => 'Ogos',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month_num = (int)date('n', $timestamp);
    $year = date('Y', $timestamp);
    
    return "$day " . $months[$month_num] . " $year";
}

/**
 * Memformat masa standard (HH:MM:SS) kepada format 12 jam (AM/PM).
 * Contoh: 14:30:00 -> 2:30 PM
 * 
 * @param string $time
 * @return string
 */
function format_time(string $time): string {
    if (empty($time)) {
        return '-';
    }
    return date('g:i A', strtotime($time));
}

/**
 * Fungsi memuat naik fail secara selamat (Security Compliant).
 * 
 * @param array $file Elemen $_FILES['nama_input']
 * @param string $target_dir Folder fizikal simpanan fail
 * @param array $allowed_mimes Whitelist bagi MIME type
 * @param int $max_size Had saiz maksimum bait
 * @return array Mengembalikan ['success' => bool, 'filename' => string, 'error' => string]
 */
function upload_file(array $file, string $target_dir, array $allowed_mimes, int $max_size): array {
    // 1. Semak kod ralat PHP $_FILES
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => "Saiz fail melebihi php.ini upload_max_filesize.",
            UPLOAD_ERR_FORM_SIZE  => "Saiz fail melebihi MAX_FILE_SIZE dalam borang HTML.",
            UPLOAD_ERR_PARTIAL    => "Fail hanya dimuat naik sebahagian sahaja.",
            UPLOAD_ERR_NO_FILE    => "Tiada fail dimuat naik.",
            UPLOAD_ERR_NO_TMP_DIR => "Folder temp PHP hilang.",
            UPLOAD_ERR_CANT_WRITE => "Gagal menulis fail ke cakera pelayan.",
            UPLOAD_ERR_EXTENSION  => "Muat naik fail dihentikan oleh extension PHP."
        ];
        return ['success' => false, 'error' => $errors[$file['error']] ?? 'Ralat muat naik tidak diketahui.'];
    }

    // 2. Semak saiz fail
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => "Saiz fail melebihi had maksimum " . ceil($max_size / (1024 * 1024)) . "MB."];
    }

    // 3. Semak MIME type secara sahih menggunakan finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        return ['success' => false, 'error' => "Gagal membaca format fail (finfo_open)."];
    }
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_mimes)) {
        return ['success' => false, 'error' => "Format fail tidak sah ($mime_type). Hanya format gambar/video terpilih dibenarkan."];
    }

    // 4. Pastikan direktori wujud
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // 5. Tentukan extension berasaskan nama fail asal
    $path_info = pathinfo($file['name']);
    $extension = strtolower($path_info['extension'] ?? '');
    
    // Whitelist extension berdasarkan MIME yang sepadan
    $mime_ext_map = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png'  => ['png'],
        'image/webp' => ['webp'],
        'video/mp4'  => ['mp4'],
        'video/webm' => ['webm']
    ];

    $valid_extensions = $mime_ext_map[$mime_type] ?? [];
    if (!in_array($extension, $valid_extensions)) {
        // Gunakan extension pertama dari whitelist MIME jika tiada
        $extension = $valid_extensions[0] ?? 'bin';
    }

    // 6. Namakan semula fail secara rawak & unik (Mencegah Path Traversal & Collision)
    $new_filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $target_filepath = rtrim($target_dir, '/\\') . DIRECTORY_SEPARATOR . $new_filename;

    // 7. Pindahkan fail dari temp ke direktori sasaran
    if (move_uploaded_file($file['tmp_name'], $target_filepath)) {
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'error' => "Gagal memindahkan fail temp ke destinasi akhir pelayan."];
    }
}

/**
 * Tukar status perlawanan daripada 'akan_datang' kepada 'live' secara automatik apabila tarikh & masa perlawanan sudah masuk.
 * 
 * @param mysqli $conn Objek sambungan MySQLi
 * @return int Bilangan perlawanan yang dikemaskini ke 'live'
 */
function auto_update_match_statuses(mysqli $conn): int {
    $now = date('Y-m-d H:i:s');
    $sql = "UPDATE tbl_jadual_perlawanan 
            SET status = 'live' 
            WHERE status = 'akan_datang' 
              AND CONCAT(tarikh, ' ', masa) <= ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $now);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
    return 0;
}
