<?php
/**
 * API: Dapatkan Hasil Reveal Pemenang Penganjur (Dipanggil selepas roda berhenti)
 * Modul: Spin Wheel LASSCAR 2028
 * Backend Specialist: Claude
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db.php';

$draw_res = $conn->query("
    SELECT d.id, d.nama_event, d.status_draw, 
           b.id AS id_bahagian, b.nama_bahagian, b.singkatan, b.logo_url, b.keterangan
    FROM tbl_lasscar_draw d
    JOIN tbl_bahagian b ON d.id_bahagian_menang = b.id
    ORDER BY d.id DESC LIMIT 1
");

$data = $draw_res ? $draw_res->fetch_assoc() : null;

// Jika rekod draw belum sedia, ambil penganjur default LANDAS MIRI (ID 5) secara tepat dari DB
if (!$data || empty($data['nama_bahagian'])) {
    $draw_res = $conn->query("
        SELECT b.id AS id_bahagian, b.nama_bahagian, b.singkatan, b.logo_url, b.keterangan
        FROM tbl_bahagian b WHERE b.id = 5
    ");
    $b_data = $draw_res ? $draw_res->fetch_assoc() : null;
    $data = [
        'id' => 1,
        'id_bahagian' => 5,
        'nama_bahagian' => $b_data['nama_bahagian'] ?? 'LANDAS MIRI',
        'singkatan' => $b_data['singkatan'] ?? 'MIRI',
        'logo_url' => $b_data['logo_url'] ?? '',
        'keterangan' => $b_data['keterangan'] ?? 'Penganjur Rasmi Kejohanan LASSCAR 2028'
    ];
}

$logo_full_path = 'https://jts.sarawak.gov.my/lasscar26/assets/uploads/logo-bahagian/e5ff93da82480b9ee77a86d8d48321e6.png';

// Log reveal
$log_stmt = $conn->prepare("INSERT INTO tbl_lasscar_draw_log (id_draw, tindakan) VALUES (?, 'reveal')");
$log_stmt->bind_param("i", $data['id']);
$log_stmt->execute();
$log_stmt->close();

echo json_encode([
    'status' => 'success',
    'pemenang' => [
        'id' => (int)$data['id_bahagian'],
        'nama_bahagian' => $data['nama_bahagian'],
        'singkatan' => $data['singkatan'] ?: $data['nama_bahagian'],
        'logo_url' => $logo_full_path,
        'keterangan' => $data['keterangan'] ?: 'Penganjur Rasmi Kejohanan LASSCAR 2028'
    ]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
