<?php
/**
 * API: Kira Sudut Putaran Roda Spin (Server-Determined Rotation)
 * Modul: Spin Wheel LASSCAR 2028
 * Backend Specialist: Claude
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Kaedah permintaan tidak dibenarkan.']);
    exit;
}

// 1. Dapatkan maklumat draw terkini
$draw_res = $conn->query("SELECT id, id_bahagian_menang, status_draw FROM tbl_lasscar_draw ORDER BY id DESC LIMIT 1");
$draw = $draw_res ? $draw_res->fetch_assoc() : null;

// Jika belum diset, hardcode penganjur default (cth: ID 4 - LANDAS SIBU atau ID 1 - LANDAS HQ)
if (!$draw || empty($draw['id_bahagian_menang'])) {
    $id_pemenang_default = 4; // LANDAS SIBU
    $conn->query("UPDATE tbl_lasscar_draw SET id_bahagian_menang = $id_pemenang_default, status_draw = 'sedia' ORDER BY id DESC LIMIT 1");
    $draw = [
        'id' => 1,
        'id_bahagian_menang' => $id_pemenang_default,
        'status_draw' => 'sedia'
    ];
}

$id_pemenang = (int)$draw['id_bahagian_menang'];

// 2. Dapatkan senarai 13 bahagian dalaman aktif (ORDER BY id ASC - Wajib selari dengan frontend)
$stmt = $conn->prepare("SELECT id FROM tbl_bahagian WHERE jenis = 'dalaman' AND status = 'aktif' ORDER BY id ASC");
$stmt->execute();
$res = $stmt->get_result();

$segment_index = -1;
$index_counter = 0;

while ($row = $res->fetch_assoc()) {
    if ((int)$row['id'] === $id_pemenang) {
        $segment_index = $index_counter;
        break;
    }
    $index_counter++;
}
$stmt->close();

$total_segments = $res->num_rows;

if ($segment_index === -1 || $total_segments === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Bahagian pemenang tidak wujud dalam senarai jejari roda spin.']);
    exit;
}

// 3. Kira sudut rotation server-side
// Formula sudut: 
// Setiap jejari = 360 / N
// Jejari bermula di sudut 0 (atas/kanan) mengikut susunan ikut jam.
// Penunjuk (pointer) berada di ATAS (270 darjah / -90 darjah).
$segment_angle = 360.0 / $total_segments;

// Center angle bagi segment index K
$segment_center_angle = ($segment_index * $segment_angle) + ($segment_angle / 2.0);

// Untuk mendarat di penunjuk ATAS (270°), putaran target = 270 - segment_center_angle
$target_stop_angle = (270.0 - $segment_center_angle + 360.0) % 360.0;

// Tambah 6 hingga 9 pusingan lengkap (360 * 6 = 2160)
$num_full_spins = rand(6, 9);

// Jarak mikro offset rawak (-4° hingga +4°) untuk tampak semulajadi
$micro_offset = (rand(-40, 40) / 10.0);

$final_rotation_degrees = ($num_full_spins * 360.0) + $target_stop_angle + $micro_offset;
$duration_ms = 7500; // 7.5 saat pusingan penuh yang mendebarkan

// 4. Kemaskini status draw kepada 'selesai' untuk kunci keputusan
$upd_stmt = $conn->prepare("UPDATE tbl_lasscar_draw SET status_draw = 'selesai' WHERE id = ?");
$upd_stmt->bind_param("i", $draw['id']);
$upd_stmt->execute();
$upd_stmt->close();

// Log spin
$log_stmt = $conn->prepare("INSERT INTO tbl_lasscar_draw_log (id_draw, tindakan) VALUES (?, 'mula_spin')");
$log_stmt->bind_param("i", $draw['id']);
$log_stmt->execute();
$log_stmt->close();

// Pulangkan HANYA final_rotation_degrees & duration_ms ke frontend (Tanpa membocorkan id pemenang)
echo json_encode([
    'status' => 'success',
    'final_rotation_degrees' => round($final_rotation_degrees, 2),
    'duration_ms' => $duration_ms
]);
