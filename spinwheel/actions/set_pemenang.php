<?php
/**
 * API: Kunci Bahagian Pemenang (Predetermined Winner)
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

$id_bahagian = isset($_POST['id_bahagian']) ? (int)$_POST['id_bahagian'] : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : 'set';

if ($action === 'reset') {
    // Reset draw status
    $stmt = $conn->prepare("UPDATE tbl_lasscar_draw SET status_draw = 'belum_set', id_bahagian_menang = NULL ORDER BY id DESC LIMIT 1");
    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Status roda spin telah diset semula. Sila pilih pemenang baharu.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mereset status roda spin.']);
    }
    exit;
}

if ($id_bahagian <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Sila pilih bahagian pemenang yang sah.']);
    exit;
}

// Pastikan bahagian wujud dan jenis = 'dalaman'
$stmt_check = $conn->prepare("SELECT id, nama_bahagian FROM tbl_bahagian WHERE id = ? AND jenis = 'dalaman' AND status = 'aktif'");
$stmt_check->bind_param("i", $id_bahagian);
$stmt_check->execute();
$res_check = $stmt_check->get_result();

if ($res_check->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Bahagian yang dipilih tidak sah atau bukan bahagian dalaman aktif.']);
    exit;
}

$row_b = $res_check->fetch_assoc();
$nama_bahagian = $row_b['nama_bahagian'];
$stmt_check->close();

// Kemaskini rekod draw terkini kepada status 'sedia'
$stmt_upd = $conn->prepare("UPDATE tbl_lasscar_draw SET id_bahagian_menang = ?, status_draw = 'sedia' ORDER BY id DESC LIMIT 1");
$stmt_upd->bind_param("i", $id_bahagian);

if ($stmt_upd->execute()) {
    $stmt_upd->close();
    
    // Log tindakan
    $draw_id_res = $conn->query("SELECT id FROM tbl_lasscar_draw ORDER BY id DESC LIMIT 1");
    $draw_row = $draw_id_res->fetch_assoc();
    $draw_id = $draw_row['id'];

    $stmt_log = $conn->prepare("INSERT INTO tbl_lasscar_draw_log (id_draw, tindakan) VALUES (?, 'set_pemenang')");
    $stmt_log->bind_param("i", $draw_id);
    $stmt_log->execute();
    $stmt_log->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Bahagian "' . $nama_bahagian . '" telah berjaya dikunci sebagai penganjur LASSCAR 2028!',
        'id_bahagian' => $id_bahagian,
        'nama_bahagian' => $nama_bahagian
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan tetapan pemenang penganjur.']);
}
