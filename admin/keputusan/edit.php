<?php
/**
 * Kemas Kini Keputusan & Skor - Admin SukanJTS Sarawak
 * CRUD: Create/Update Keputusan perlawanan
 */

$page_title = "Kemas Kini Keputusan";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan super_admin atau editor sahaja boleh mengakses modul ini
confirm_access(['super_admin', 'editor']);

$error_msg = '';
$success_msg = '';

$jadual_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($jadual_id <= 0) {
    header("Location: index.php");
    exit;
}

// 1. Ambil maklumat perlawanan & jadual
$stmt_fetch = $conn->prepare("SELECT j.*, s.nama_sukan, s.kategori, s.jenis_perlawanan, 
                                     pa.id AS id_a, pa.nama_pasukan AS nama_a, ba.nama_bahagian AS bhg_a,
                                     pb.id AS id_b, pb.nama_pasukan AS nama_b, bb.nama_bahagian AS bhg_b,
                                     v.nama_tempat
                              FROM tbl_jadual_perlawanan j
                              JOIN tbl_sukan s ON j.sukan_id = s.id
                              JOIN tbl_pasukan pa ON j.pasukan_a_id = pa.id
                              JOIN tbl_bahagian ba ON pa.bahagian_id = ba.id
                              LEFT JOIN tbl_pasukan pb ON j.pasukan_b_id = pb.id
                              LEFT JOIN tbl_bahagian bb ON pb.bahagian_id = bb.id
                              JOIN tbl_venue v ON j.venue_id = v.id
                              WHERE j.id = ? LIMIT 1");
$stmt_fetch->bind_param("i", $jadual_id);
$stmt_fetch->execute();
$res = $stmt_fetch->get_result();

if ($res->num_rows !== 1) {
    $stmt_fetch->close();
    $_SESSION['error_msg'] = "Jadual perlawanan tidak dijumpai.";
    header("Location: index.php");
    exit;
}

$match = $res->fetch_assoc();
$stmt_fetch->close();

$display_a = $match['nama_a'] ?: $match['bhg_a'];
$display_b = $match['nama_b'] ?: ($match['bhg_b'] ?? 'TBD');

// 2. Ambil keputusan sedia ada (jika sudah direkodkan)
$stmt_k = $conn->prepare("SELECT * FROM tbl_keputusan WHERE jadual_id = ? LIMIT 1");
$stmt_k->bind_param("i", $jadual_id);
$stmt_k->execute();
$res_k = $stmt_k->get_result();

$keputusan = null;
if ($res_k->num_rows === 1) {
    $keputusan = $res_k->fetch_assoc();
}
$stmt_k->close();

// 3. Proses Borang Kiriman (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skor_a             = isset($_POST['skor_a']) && $_POST['skor_a'] !== '' ? (int)$_POST['skor_a'] : null;
    $skor_b             = isset($_POST['skor_b']) && $_POST['skor_b'] !== '' ? (int)$_POST['skor_b'] : null;
    $pasukan_menang_id = isset($_POST['pasukan_menang_id']) && $_POST['pasukan_menang_id'] !== '' ? (int)$_POST['pasukan_menang_id'] : null;
    $jenis_pingat       = $_POST['jenis_pingat'] ?? 'tiada';
    $status             = $_POST['status'] ?? 'akan_datang';
    $catatan            = trim($_POST['catatan'] ?? '');
    $csrf_token         = $_POST['csrf_token'] ?? '';

    // Validate CSRF
    if (!verify_csrf_token($csrf_token)) {
        $error_msg = "Token keselamatan tidak sah.";
    } else {
        // Tentukan kelayakan input
        // Untuk sukan individu tanpa pasukan B, skor B sentiasa NULL
        if ($match['jenis_perlawanan'] === 'individu' && $match['pasukan_b_id'] === null) {
            $skor_b = null;
        }

        // Mulakan transaksi MySQL untuk menjamin keselamatan kemas kini keputusan + pingat
        $conn->begin_transaction();
        
        try {
            // A. Kemaskini Status Jadual Perlawanan (Auto 'selesai' jika skor/pemenang diisi)
            if (($skor_a !== null || $skor_b !== null || $pasukan_menang_id !== null) && $status !== 'ditangguh') {
                $status = 'selesai';
            }
            $stmt_u_j = $conn->prepare("UPDATE tbl_jadual_perlawanan SET status = ? WHERE id = ?");
            $stmt_u_j->bind_param("si", $status, $jadual_id);
            $stmt_u_j->execute();
            $stmt_u_j->close();

            // B. Masukkan atau Kemaskini Keputusan Perlawanan
            if ($keputusan) {
                // Update
                $stmt_u_k = $conn->prepare("UPDATE tbl_keputusan SET skor_a = ?, skor_b = ?, pasukan_menang_id = ?, jenis_pingat = ?, catatan = ?, direkod_oleh = ? WHERE jadual_id = ?");
                $stmt_u_k->bind_param("iiissii", $skor_a, $skor_b, $pasukan_menang_id, $jenis_pingat, $catatan, $_SESSION['admin_id'], $jadual_id);
                $stmt_u_k->execute();
                $stmt_u_k->close();
                $tindakan = 'update';
            } else {
                // Insert
                $stmt_i_k = $conn->prepare("INSERT INTO tbl_keputusan (jadual_id, skor_a, skor_b, pasukan_menang_id, jenis_pingat, catatan, direkod_oleh) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_i_k->bind_param("iiiissi", $jadual_id, $skor_a, $skor_b, $pasukan_menang_id, $jenis_pingat, $catatan, $_SESSION['admin_id']);
                $stmt_i_k->execute();
                $stmt_i_k->close();
                $tindakan = 'create';
            }

            // C. Kemas Kini Papan Pingat tbl_kedudukan_pingat (Auto-recalculate)
            // Bersihkan jadual cache dan susun semula
            $conn->query("TRUNCATE TABLE tbl_kedudukan_pingat");
            $sync_query = "
                INSERT INTO tbl_kedudukan_pingat (bahagian_id, emas, perak, gangsa)
                SELECT b.id,
                       SUM(CASE WHEN k.jenis_pingat = 'emas' AND p.id = k.pasukan_menang_id THEN 1 ELSE 0 END) AS emas,
                       SUM(CASE 
                           WHEN k.jenis_pingat = 'perak' AND p.id = k.pasukan_menang_id THEN 1 
                           WHEN k.jenis_pingat = 'emas' AND ( (j.pasukan_a_id = p.id AND k.pasukan_menang_id = j.pasukan_b_id) OR (j.pasukan_b_id = p.id AND k.pasukan_menang_id = j.pasukan_a_id) ) THEN 1
                           ELSE 0 
                       END) AS perak,
                       SUM(CASE WHEN k.jenis_pingat = 'gangsa' AND p.id = k.pasukan_menang_id THEN 1 ELSE 0 END) AS gangsa
                FROM tbl_bahagian b
                LEFT JOIN tbl_pasukan p ON p.bahagian_id = b.id
                LEFT JOIN tbl_jadual_perlawanan j ON (j.pasukan_a_id = p.id OR j.pasukan_b_id = p.id)
                LEFT JOIN tbl_keputusan k ON k.jadual_id = j.id
                GROUP BY b.id
                ON DUPLICATE KEY UPDATE 
                    emas = VALUES(emas), 
                    perak = VALUES(perak), 
                    gangsa = VALUES(gangsa)";
            $conn->query($sync_query);

            // Commit transaksi
            $conn->commit();

            // Log Audit
            log_audit($conn, $_SESSION['admin_id'], $tindakan, 'tbl_keputusan', $jadual_id, "Kemas kini keputusan perlawanan ID $jadual_id. Pemenang: " . ($pasukan_menang_id ?: 'Tiada') . ", Pingat: $jenis_pingat");

            $_SESSION['success_msg'] = "Keputusan perlawanan berjaya direkodkan! Pemenang mendapat Pingat Emas dan pasukan kalah secara automatik mendapat Pingat Perak.";
            header("Location: index.php");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $error_msg = "Gagal merekod keputusan: " . $e->getMessage();
        }
    }
}
?>

<div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Keputusan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kemas Kini</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Rekod Keputusan Perlawanan</h3>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-10 col-lg-8">
        <!-- Maklumat Perlawanan (Read-only Card) -->
        <div class="card card-admin p-4 mb-4 bg-light border">
            <h5 class="fw-bold text-navy mb-3"><i class="bi bi-info-circle-fill me-1"></i> Maklumat Kejohanan</h5>
            <div class="row g-3">
                <div class="col-sm-6">
                    <span class="text-muted small d-block">Acara Sukan</span>
                    <strong class="text-dark fs-5"><?php echo sanitize($match['nama_sukan']); ?></strong>
                    <span class="badge bg-secondary ms-1"><?php echo sanitize(ucfirst($match['kategori'])); ?></span>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted small d-block">Venue Pertandingan</span>
                    <strong class="text-dark"><?php echo sanitize($match['nama_tempat']); ?></strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted small d-block">Tarikh / Masa</span>
                    <span class="text-dark fw-semibold"><?php echo format_date($match['tarikh']); ?> (<?php echo format_time($match['masa']); ?>)</span>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted small d-block">Pusingan / Peringkat</span>
                    <span class="badge bg-light text-secondary border" id="pusinganBadge"><?php echo sanitize($match['pusingan'] ?: 'Peringkat Kumpulan'); ?></span>
                </div>
            </div>
        </div>

        <div class="card card-admin p-4">
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    ⚠️ <?php echo sanitize($error_msg); ?>
                </div>
            <?php endif; ?>

            <form action="edit.php?id=<?php echo $jadual_id; ?>" method="POST" class="needs-validation" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Perekodan Skor & Pingat</h5>

                <div class="row g-3 mb-3 align-items-center">
                    <!-- Skor A -->
                    <div class="col-sm-5 text-center">
                        <label for="skor_a" class="form-label fw-bold text-primary"><?php echo sanitize($display_a); ?></label>
                        <input type="number" class="form-control form-control-lg text-center fw-bold fs-4 mx-auto" style="max-width: 120px;" id="skor_a" name="skor_a" value="<?php echo $keputusan ? sanitize($keputusan['skor_a']) : ''; ?>" placeholder="0" min="0" required>
                        <div class="invalid-feedback">Isi skor A.</div>
                    </div>

                    <!-- Separator VS -->
                    <div class="col-sm-2 text-center mt-4">
                        <span class="fs-3 fw-bold text-muted">VS</span>
                    </div>

                    <!-- Skor B -->
                    <div class="col-sm-5 text-center">
                        <?php if ($match['jenis_perlawanan'] === 'individu' && $match['pasukan_b_id'] === null): ?>
                            <label class="form-label fw-bold text-muted">Bukan Berpasukan</label>
                            <input type="text" class="form-control form-control-lg text-center mx-auto" style="max-width: 120px;" value="N/A" readonly disabled>
                        <?php else: ?>
                            <label for="skor_b" class="form-label fw-bold text-primary"><?php echo sanitize($display_b); ?></label>
                            <input type="number" class="form-control form-control-lg text-center fw-bold fs-4 mx-auto" style="max-width: 120px;" id="skor_b" name="skor_b" value="<?php echo $keputusan ? sanitize($keputusan['skor_b']) : ''; ?>" placeholder="0" min="0" required>
                            <div class="invalid-feedback">Isi skor B.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Pemenang & Pingat -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="pasukan_menang_id" class="form-label fw-semibold">Pilih Pemenang Perlawanan</label>
                        <select class="form-select" id="pasukan_menang_id" name="pasukan_menang_id">
                            <option value="">-- Seri / Tiada Pemenang / TBD --</option>
                            <option value="<?php echo $match['pasukan_a_id']; ?>" data-name="<?php echo sanitize($display_a); ?>" <?php echo ($keputusan && $keputusan['pasukan_menang_id'] == $match['pasukan_a_id']) ? 'selected' : ''; ?>>
                                <?php echo sanitize($display_a); ?> (Pasukan A)
                            </option>
                            <?php if ($match['pasukan_b_id'] !== null): ?>
                                <option value="<?php echo $match['pasukan_b_id']; ?>" data-name="<?php echo sanitize($display_b); ?>" <?php echo ($keputusan && $keputusan['pasukan_menang_id'] == $match['pasukan_b_id']) ? 'selected' : ''; ?>>
                                    <?php echo sanitize($display_b); ?> (Pasukan B)
                                </option>
                            <?php endif; ?>
                        </select>
                        <div class="form-text small text-muted">Dipilih secara automatik mengikut keputusan skor di atas.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="jenis_pingat" class="form-label fw-semibold">Anugerah Pingat Kejohanan</label>
                        <select class="form-select" id="jenis_pingat" name="jenis_pingat">
                            <option value="tiada" <?php echo ($keputusan && $keputusan['jenis_pingat'] === 'tiada') ? 'selected' : ''; ?>>Tiada (Peringkat Kumpulan / Awal)</option>
                            <option value="emas" <?php echo ($keputusan && $keputusan['jenis_pingat'] === 'emas') ? 'selected' : ''; ?>>🥇 Pingat Emas (Juara) & Perak (Naib Juara)</option>
                            <option value="perak" <?php echo ($keputusan && $keputusan['jenis_pingat'] === 'perak') ? 'selected' : ''; ?>>🥈 Pingat Perak (Silver)</option>
                            <option value="gangsa" <?php echo ($keputusan && $keputusan['jenis_pingat'] === 'gangsa') ? 'selected' : ''; ?>>🥉 Pingat Gangsa (Bronze)</option>
                        </select>
                        <div class="form-text small text-muted">Bila 'Emas' dipilih, pemenang mendapat Emas & pasukan kalah mendapat Perak secara automatik.</div>
                    </div>
                </div>

                <!-- Kad Live Summary Anugerah Pingat Automatik -->
                <div id="medalPreviewBox" class="alert alert-warning border border-warning-subtle shadow-sm rounded-3 p-3 mb-3 d-none">
                    <div class="fw-bold mb-2 text-dark"><i class="bi bi-award-fill text-warning me-1 fs-5"></i> Agihan Pingat Automatik Perlawanan Ini:</div>
                    <div class="d-flex flex-column gap-1">
                        <div id="goldWinnerBadge" class="fw-semibold text-dark"><span class="badge bg-warning text-dark me-2">🥇 EMAS (Gold)</span> <span id="textGoldWinner" class="text-navy font-bold">-</span></div>
                        <div id="silverWinnerBadge" class="fw-semibold text-dark"><span class="badge bg-secondary text-white me-2">🥈 PERAK (Silver)</span> <span id="textSilverWinner" class="text-secondary font-bold">-</span></div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-semibold">Status Semasa Perlawanan</label>
                        <select class="form-select" id="status" name="status">
                            <option value="akan_datang" <?php echo ($match['status'] === 'akan_datang') ? 'selected' : ''; ?>>Akan Datang</option>
                            <option value="live" <?php echo ($match['status'] === 'live') ? 'selected' : ''; ?>>Sedang Berlangsung (LIVE)</option>
                            <option value="selesai" <?php echo ($match['status'] === 'selesai' || !$keputusan) ? 'selected' : ''; ?>>Selesai (Keputusan Rasmi)</option>
                            <option value="ditangguh" <?php echo ($match['status'] === 'ditangguh') ? 'selected' : ''; ?>>Ditangguh / Batal</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="catatan" class="form-label fw-semibold">Catatan Keputusan</label>
                        <input type="text" class="form-control" id="catatan" name="catatan" value="<?php echo $keputusan ? sanitize($keputusan['catatan']) : ''; ?>" placeholder="Cth: Menang penalti 5-4 / Pecah rekod kejohanan">
                    </div>
                </div>

                <div class="pt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-4">Rekod Keputusan</button>
                    <a href="index.php" class="btn btn-outline-secondary px-4">Batal</a>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const skorA = document.getElementById('skor_a');
        const skorB = document.getElementById('skor_b');
        const menangSelect = document.getElementById('pasukan_menang_id');
        const jenisPingat = document.getElementById('jenis_pingat');
        const statusSelect = document.getElementById('status');
        const pusinganText = document.getElementById('pusinganBadge')?.textContent.toLowerCase() || '';

        const pasukanAId = "<?php echo $match['pasukan_a_id']; ?>";
        const pasukanBId = "<?php echo $match['pasukan_b_id'] ?? ''; ?>";
        const namaA = "<?php echo sanitize(addslashes($display_a)); ?>";
        const namaB = "<?php echo sanitize(addslashes($display_b)); ?>";

        // Auto Pre-select Emas jika Pusingan Akhir / Final
        if (pusinganText.includes('akhir') || pusinganText.includes('final')) {
            if (jenisPingat && jenisPingat.value === 'tiada') {
                jenisPingat.value = 'emas';
            }
        }

        function updateAutomation() {
            const valA = skorA ? parseInt(skorA.value) : null;
            const valB = skorB ? parseInt(skorB.value) : null;

            // Auto status ke selesai jika skor diisi
            if ((!isNaN(valA) && valA !== null) || (!isNaN(valB) && valB !== null)) {
                if (statusSelect && statusSelect.value !== 'selesai') {
                    statusSelect.value = 'selesai';
                }
            }

            // Auto-select pemenang
            if (!isNaN(valA) && !isNaN(valB)) {
                if (valA > valB) {
                    menangSelect.value = pasukanAId;
                } else if (valB > valA) {
                    menangSelect.value = pasukanBId;
                }
            }

            // Kemaskini Visual Preview Box Pingat
            const selectedWinner = menangSelect.value;
            const medalType = jenisPingat.value;
            const previewBox = document.getElementById('medalPreviewBox');
            const textGold = document.getElementById('textGoldWinner');
            const textSilver = document.getElementById('textSilverWinner');

            if (medalType === 'emas' && selectedWinner !== '') {
                previewBox.classList.remove('d-none');
                if (selectedWinner === pasukanAId) {
                    textGold.textContent = namaA + " (Pasukan A)";
                    textSilver.textContent = namaB !== 'TBD' ? namaB + " (Pasukan B)" : "Tiada / TBD";
                } else if (selectedWinner === pasukanBId) {
                    textGold.textContent = namaB + " (Pasukan B)";
                    textSilver.textContent = namaA + " (Pasukan A)";
                }
            } else {
                previewBox.classList.add('d-none');
            }
        }

        if (skorA) skorA.addEventListener('input', updateAutomation);
        if (skorB) skorB.addEventListener('input', updateAutomation);
        if (menangSelect) menangSelect.addEventListener('change', updateAutomation);
        if (jenisPingat) jenisPingat.addEventListener('change', updateAutomation);

        // Jalankan sekali pada masa muat halaman
        updateAutomation();
    });

    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
