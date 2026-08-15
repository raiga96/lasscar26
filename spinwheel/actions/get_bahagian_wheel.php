<?php
/**
 * API: Ambik Senarai Bahagian Dalaman Aktif untuk Roda Spin
 * Modul: Spin Wheel LASSCAR 2028
 * Backend Specialist: Claude
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db.php';

try {
    // 1. Dapatkan rekod status draw semasa
    $draw_query = "SELECT d.id, d.nama_event, d.id_bahagian_menang, d.status_draw, b.nama_bahagian AS nama_pemenang, b.logo_url AS logo_pemenang 
                  FROM tbl_lasscar_draw d
                  LEFT JOIN tbl_bahagian b ON d.id_bahagian_menang = b.id
                  ORDER BY d.id DESC LIMIT 1";
    $draw_res = $conn->query($draw_query);
    $draw_info = $draw_res ? $draw_res->fetch_assoc() : null;

    // Jika tiada rekod draw langsung, bina rekod default
    if (!$draw_info) {
        $conn->query("INSERT INTO tbl_lasscar_draw (nama_event, status_draw) VALUES ('LASSCAR 2028', 'belum_set')");
        $draw_id = $conn->insert_id;
        $draw_info = [
            'id' => $draw_id,
            'nama_event' => 'LASSCAR 2028',
            'id_bahagian_menang' => null,
            'status_draw' => 'belum_set',
            'nama_pemenang' => null,
            'logo_pemenang' => null
        ];
    }

    // 2. Dapatkan senarai 13 bahagian dalaman aktif (ORDER BY id ASC untuk jaminan konsistensi segment)
    $stmt = $conn->prepare("SELECT id, nama_bahagian, singkatan, logo_url FROM tbl_bahagian WHERE jenis = 'dalaman' AND status = 'aktif' ORDER BY id ASC");
    $stmt->execute();
    $result = $stmt->get_result();

    $bahagian_list = [];
    while ($row = $result->fetch_assoc()) {
        $logo_full_path = !empty($row['logo_url']) 
            ? 'https://jts.sarawak.gov.my/lasscar26/assets/uploads/logo-bahagian/' . $row['logo_url']
            : 'https://jts.sarawak.gov.my/lasscar26/assets/uploads/logo-bahagian/default_logo.png';
            
        $bahagian_list[] = [
            'id' => (int)$row['id'],
            'nama_bahagian' => $row['nama_bahagian'],
            'singkatan' => preg_replace('/^LANDAS\s*/i', '', $row['nama_bahagian']),
            'logo_url' => $logo_full_path
        ];
    }
    $stmt->close();

    echo json_json_encode_success([
        'status' => 'success',
        'total' => count($bahagian_list),
        'draw' => [
            'id' => (int)$draw_info['id'],
            'nama_event' => $draw_info['nama_event'],
            'status_draw' => $draw_info['status_draw'],
            'id_bahagian_menang' => $draw_info['id_bahagian_menang'] ? (int)$draw_info['id_bahagian_menang'] : null
        ],
        'items' => $bahagian_list
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengambil data bahagian roda spin: ' . $e->getMessage()
    ]);
}

function json_json_encode_success($data) {
    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
