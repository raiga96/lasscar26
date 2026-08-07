<?php
/**
 * Enjin Penyegerakan Automatik Google Drive (Claude Specialist - Backend)
 * Mengesan imej baru, dikemaskini, dan dipadam dari Google Drive (termasuk 10 Sukan & Subfolder).
 */

if (!defined('GDRIVE_SERVICE_ACCOUNT_FILE')) {
    require_once __DIR__ . '/../config/config.php';
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/gdrive.php';

/**
 * Jalankan penyegerakan automatik Google Drive ke pangkalan data
 * 
 * @param mysqli $conn Objek sambungan MySQLi
 * @param string|null $folder_id ID Folder Google Drive (lalai dari config)
 * @param bool $force Paksa penyegerakan tanpa menghiraukan TTL
 * @return array Status penyegerakan
 */
function sync_gdrive_gallery($conn, $folder_id = null, $force = false) {
    if (!$folder_id) {
        $folder_id = GDRIVE_FOLDER_ID;
    }

    $lock_file = __DIR__ . '/../config/gdrive_last_sync.txt';
    $now = time();

    // Semak TTL jika tidak dipaksa
    if (!$force && file_exists($lock_file)) {
        $last_sync = (int)@file_get_contents($lock_file);
        if (($now - $last_sync) < GDRIVE_SYNC_TTL) {
            return [
                'success' => true,
                'synced'  => false,
                'reason'  => 'ttl_active',
                'message' => 'Penyegerakan masih dalam tempoh bertenang (TTL).'
            ];
        }
    }

    // Ambil senarai fail dari Google Drive API v3 (Imbasan Rekursif Subfolder & Sukan)
    $drive_files = fetch_gdrive_folder_files($folder_id);
    if ($drive_files === false || !empty($GLOBALS['gdrive_last_error'])) {
        $err_msg = $GLOBALS['gdrive_last_error'] ?? 'Gagal mengambil fail dari Google Drive. Pastikan Service Account JSON & Folder ID adalah betul.';
        return [
            'success' => false,
            'synced'  => false,
            'reason'  => 'api_error',
            'message' => $err_msg
        ];
    }

    // Kemaskini penanda masa penyegerakan
    @file_put_contents($lock_file, (string)$now);

    // Ambil senarai sukan aktif untuk pemadanan sukan_id automatik
    $sukan_list = [];
    $sukan_res = $conn->query("SELECT id, nama_sukan FROM tbl_sukan WHERE status = 'aktif'");
    if ($sukan_res) {
        while ($s_row = $sukan_res->fetch_assoc()) {
            $sukan_list[$s_row['id']] = mb_strtolower(trim($s_row['nama_sukan']));
        }
    }

    // 1. Ambil semua rekod GDrive sedia ada dalam database
    $existing_records = [];
    $res = $conn->query("SELECT id, gdrive_file_id, gdrive_modified_time FROM tbl_galeri WHERE is_gdrive = 1");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $existing_records[$row['gdrive_file_id']] = $row;
        }
    }

    $seen_gdrive_ids = [];
    $added_count   = 0;
    $updated_count = 0;

    // Prepared statements untuk INSERT & UPDATE
    $stmt_insert = $conn->prepare("
        INSERT INTO tbl_galeri 
        (tajuk, jenis_fail, url_fail, gdrive_file_id, gdrive_folder_id, gdrive_thumbnail_url, gdrive_view_url, gdrive_modified_time, is_gdrive, album, sukan_id, dicipta_pada) 
        VALUES (?, 'imej', '', ?, ?, ?, ?, ?, 1, ?, ?, ?)
    ");

    $stmt_update = $conn->prepare("
        UPDATE tbl_galeri 
        SET tajuk = ?, gdrive_thumbnail_url = ?, gdrive_view_url = ?, gdrive_modified_time = ?, album = ?, sukan_id = ? 
        WHERE gdrive_file_id = ?
    ");

    foreach ($drive_files as $file) {
        $file_id       = $file['id'];
        $raw_name      = pathinfo($file['name'], PATHINFO_FILENAME);
        $sub_detail    = !empty($file['subfolder_detail']) ? " ({$file['subfolder_detail']})" : "";
        $file_name     = $raw_name . $sub_detail;
        
        $thumb_url     = isset($file['thumbnailLink']) ? str_replace('=s220', '=w1000-h800', $file['thumbnailLink']) : '';
        $view_url      = $file['webViewLink'] ?? "https://drive.google.com/file/d/{$file_id}/view";
        $modified_time = isset($file['modifiedTime']) ? date('Y-m-d H:i:s', strtotime($file['modifiedTime'])) : date('Y-m-d H:i:s');
        $created_time  = isset($file['createdTime'])  ? date('Y-m-d H:i:s', strtotime($file['createdTime']))  : date('Y-m-d H:i:s');
        
        $album_name    = !empty($file['album_name']) ? $file['album_name'] : 'Google Drive';
        $item_folder_id= $file['folder_id'] ?? $folder_id;

        // Pemadanan automatik sukan_id berdasarkan nama folder / album
        $matched_sukan_id = null;
        $search_term = mb_strtolower($album_name . ' ' . ($file['folder_name'] ?? ''));
        foreach ($sukan_list as $s_id => $s_name) {
            if (strpos($search_term, $s_name) !== false || strpos($s_name, $search_term) !== false) {
                $matched_sukan_id = $s_id;
                break;
            }
        }

        $seen_gdrive_ids[] = $file_id;

        if (!isset($existing_records[$file_id])) {
            // REKOD BARU -> INSERT
            if ($stmt_insert) {
                $stmt_insert->bind_param("sssssssis", $file_name, $file_id, $item_folder_id, $thumb_url, $view_url, $modified_time, $album_name, $matched_sukan_id, $created_time);
                $stmt_insert->execute();
                $added_count++;
            }
        } else {
            // REKOD SEDIA ADA -> UPDATE
            $db_rec = $existing_records[$file_id];
            if ($db_rec['gdrive_modified_time'] !== $modified_time) {
                if ($stmt_update) {
                    $stmt_update->bind_param("sssssis", $file_name, $thumb_url, $view_url, $modified_time, $album_name, $matched_sukan_id, $file_id);
                    $stmt_update->execute();
                    $updated_count++;
                }
            }
        }
    }

    if ($stmt_insert) $stmt_insert->close();
    if ($stmt_update) $stmt_update->close();

    // 2. KESAN FAIL YANG DIPADAM DARI GOOGLE DRIVE -> DELETE FROM DB
    $deleted_count = 0;
    foreach ($existing_records as $g_id => $rec) {
        if (!in_array($g_id, $seen_gdrive_ids)) {
            $stmt_del = $conn->prepare("DELETE FROM tbl_galeri WHERE gdrive_file_id = ?");
            if ($stmt_del) {
                $stmt_del->bind_param("s", $g_id);
                $stmt_del->execute();
                $stmt_del->close();
                $deleted_count++;
            }
        }
    }

    return [
        'success' => true,
        'synced'  => true,
        'added'   => $added_count,
        'updated' => $updated_count,
        'deleted' => $deleted_count,
        'total'   => count($drive_files),
        'message' => "Penyegerakan automatik rekursif berjaya! ({$added_count} baru, {$updated_count} dikemaskini, {$deleted_count} dipadam)."
    ];
}
