<?php
/**
 * Urus Pendaftaran Pasukan Mengikut Sukan - Admin SukanJTS Sarawak
 * CRUD: Read, Quick Add (Single & Bulk), Delete UI
 */

$page_title = "Pendaftaran Pasukan Mengikut Sukan";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan hanya super_admin/editor boleh mengakses modul ini
confirm_access(['super_admin', 'editor']);

// Proses Pendaftaran (Single atau Bulk / Semua Kontinjen)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $_SESSION['error_msg'] = "Token keselamatan tidak sah. Sila cuba lagi.";
    } else {
        $action = $_POST['action'];
        $sukan_id = (int)($_POST['sukan_id'] ?? 0);

        if ($sukan_id <= 0) {
            $_SESSION['error_msg'] = "Sukan tidak sah.";
        } elseif ($action === 'bulk_register') {
            // Claude Backend: Daftarkan semua bahagian aktif yang belum mendaftar sukan ini
            $stmt_b = $conn->prepare("
                SELECT id, nama_bahagian FROM tbl_bahagian 
                WHERE status = 'aktif' AND id NOT IN (
                    SELECT bahagian_id FROM tbl_pasukan WHERE sukan_id = ?
                )
            ");
            $stmt_b->bind_param("i", $sukan_id);
            $stmt_b->execute();
            $unregistered = $stmt_b->get_result();
            
            $inserted_count = 0;
            if ($unregistered && $unregistered->num_rows > 0) {
                $stmt_inst = $conn->prepare("INSERT INTO tbl_pasukan (bahagian_id, sukan_id) VALUES (?, ?)");
                while ($b_row = $unregistered->fetch_assoc()) {
                    $b_id = $b_row['id'];
                    $stmt_inst->bind_param("ii", $b_id, $sukan_id);
                    if ($stmt_inst->execute()) {
                        $inserted_count++;
                    }
                }
                $stmt_inst->close();
            }
            $stmt_b->close();

            if ($inserted_count > 0) {
                log_audit($conn, $_SESSION['admin_id'], 'create', 'tbl_pasukan', null, "Pendaftaran Pukal: $inserted_count bahagian didaftarkan untuk sukan ID $sukan_id.");
                $_SESSION['success_msg'] = "Berjaya mendaftarkan $inserted_count bahagian secara pukal ke sukan ini!";
            } else {
                $_SESSION['error_msg'] = "Semua bahagian telah pun mendaftar untuk sukan ini.";
            }
            header("Location: index.php?sukan_id=" . $sukan_id);
            exit;

        } elseif ($action === 'single_register') {
            // Claude Backend: Daftarkan satu bahagian khusus
            $bahagian_id = (int)($_POST['bahagian_id'] ?? 0);
            $nama_pasukan = trim($_POST['nama_pasukan'] ?? '');
            $nama_pasukan_val = empty($nama_pasukan) ? null : $nama_pasukan;

            if ($bahagian_id <= 0) {
                $_SESSION['error_msg'] = "Sila pilih bahagian.";
            } else {
                $stmt_ins = $conn->prepare("INSERT INTO tbl_pasukan (bahagian_id, sukan_id, nama_pasukan) VALUES (?, ?, ?)");
                $stmt_ins->bind_param("iis", $bahagian_id, $sukan_id, $nama_pasukan_val);
                if ($stmt_ins->execute()) {
                    log_audit($conn, $_SESSION['admin_id'], 'create', 'tbl_pasukan', $conn->insert_id, "Daftar pasukan: Bahagian ID $bahagian_id ke Sukan ID $sukan_id.");
                    $_SESSION['success_msg'] = "Bahagian berjaya didaftarkan untuk sukan ini!";
                } else {
                    if ($conn->errno === 1062) {
                        $_SESSION['error_msg'] = "Bahagian ini sudah mendaftar untuk sukan berkenaan.";
                    } else {
                        $_SESSION['error_msg'] = "Gagal mendaftar pasukan: " . $stmt_ins->error;
                    }
                }
                $stmt_ins->close();
            }
            header("Location: index.php?sukan_id=" . $sukan_id);
            exit;
        }
    }
}

$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// Dapatkan senarai semua sukan & bilangan pasukan berdaftar
$query_sukan = "
    SELECT s.*, 
           COUNT(p.id) as total_pasukan
    FROM tbl_sukan s
    LEFT JOIN tbl_pasukan p ON p.sukan_id = s.id
    WHERE s.status = 'aktif'
    GROUP BY s.id
    ORDER BY s.nama_sukan ASC";
$res_sukan = $conn->query($query_sukan);

// Ambil semua bahagian aktif untuk modal/drop down pendaftaran
$res_all_bahagian = $conn->query("SELECT id, nama_bahagian, jenis FROM tbl_bahagian WHERE status = 'aktif' ORDER BY jenis ASC, nama_bahagian ASC");
$bahagian_list = [];
if ($res_all_bahagian && $res_all_bahagian->num_rows > 0) {
    while ($b = $res_all_bahagian->fetch_assoc()) {
        $bahagian_list[] = $b;
    }
}

$selected_sukan_id = (int)($_GET['sukan_id'] ?? 0);
?>

<div class="row mb-3 align-items-center">
    <div class="col-sm-8">
        <h3 class="fw-bold text-dark mb-1">Pendaftaran Pasukan Mengikut Acara Sukan</h3>
        <p class="text-muted small mb-0">Urus penyertaan kontinjen/bahagian bagi setiap jenis sukan yang didaftarkan.</p>
    </div>
</div>

<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        🎉 <?php echo sanitize($success_msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        ⚠️ <?php echo sanitize($error_msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <?php if ($res_sukan && $res_sukan->num_rows > 0): ?>
        <?php while ($sukan = $res_sukan->fetch_assoc()): 
            $s_id = $sukan['id'];
            $is_expanded = ($selected_sukan_id === (int)$s_id);
            
            // Ambil senarai pasukan yang mendaftar untuk sukan ini
            $stmt_teams = $conn->prepare("
                SELECT p.*, b.nama_bahagian, b.singkatan, b.logo_url, b.jenis 
                FROM tbl_pasukan p
                JOIN tbl_bahagian b ON p.bahagian_id = b.id
                WHERE p.sukan_id = ?
                ORDER BY b.nama_bahagian ASC
            ");
            $stmt_teams->bind_param("i", $s_id);
            $stmt_teams->execute();
            $teams_res = $stmt_teams->get_result();

            // Senarai ID bahagian yang sudah berdaftar untuk sukan ini
            $registered_b_ids = [];
            $teams_data = [];
            if ($teams_res && $teams_res->num_rows > 0) {
                while ($t = $teams_res->fetch_assoc()) {
                    $teams_data[] = $t;
                    $registered_b_ids[] = $t['bahagian_id'];
                }
            }
            $stmt_teams->close();
        ?>
        <div class="col-12">
            <div class="card card-admin border-0 shadow-sm rounded-4 overflow-hidden">
                <!-- Header Sukan Card -->
                <div class="card-header bg-white p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-3 fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="<?php echo !empty($sukan['ikon']) ? sanitize($sukan['ikon']) : 'bi bi-trophy-fill'; ?>"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <?php echo sanitize($sukan['nama_sukan']); ?>
                                <span class="badge bg-navy bg-opacity-10 text-navy border border-navy border-opacity-25 rounded-pill fs-7 fw-semibold">
                                    <?php echo sanitize(ucfirst($sukan['kategori'])); ?> • <?php echo sanitize(ucfirst($sukan['jenis_perlawanan'])); ?>
                                </span>
                            </h5>
                            <small class="text-muted">Jumlah Kontinjen Mendaftar: <strong class="text-dark"><?php echo count($teams_data); ?> Pasukan</strong></small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <!-- Action: Bulk Register All Unregistered -->
                        <form action="index.php" method="POST" onsubmit="return confirm('Adakah anda pasti untuk mendaftarkan SEMUA bahagian yang belum berdaftar ke sukan <?php echo addslashes($sukan['nama_sukan']); ?>?');" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="action" value="bulk_register">
                            <input type="hidden" name="sukan_id" value="<?php echo $s_id; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-success rounded-3 fw-semibold">
                                <i class="bi bi-check-all me-1"></i> Daftar Semua Bahagian
                            </button>
                        </form>

                        <!-- Action: Modal Single Register -->
                        <button type="button" class="btn btn-sm btn-navy rounded-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalRegisterSingle<?php echo $s_id; ?>">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Satu-Satu
                        </button>
                    </div>
                </div>

                <!-- Body: Senarai Pasukan Berdaftar -->
                <div class="card-body p-0">
                    <?php if (count($teams_data) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light fs-7">
                                    <tr>
                                        <th class="ps-3" style="width: 50px;">#</th>
                                        <th>Kontinjen / Bahagian</th>
                                        <th>Nama Khas Pasukan</th>
                                        <th>Jenis Kontinjen</th>
                                        <th class="text-center" style="width: 120px;">Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teams_data as $idx => $team): 
                                        $logo_path = BASE_URL . 'assets/uploads/logo-bahagian/' . ($team['logo_url'] ?: 'default_logo.png');
                                        $team_name_display = $team['nama_pasukan'] ?: $team['nama_bahagian'];
                                    ?>
                                    <tr>
                                        <td class="ps-3 text-muted fw-semibold"><?php echo $idx + 1; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="<?php echo sanitize($logo_path); ?>" alt="" class="rounded" style="width: 32px; height: 32px; object-fit: contain;">
                                                <strong class="text-dark"><?php echo sanitize($team['nama_bahagian']); ?></strong>
                                                <?php if ($team['singkatan']): ?>
                                                    <code class="small text-secondary">(<?php echo sanitize($team['singkatan']); ?>)</code>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark"><?php echo sanitize($team_name_display); ?></span>
                                            <?php if (empty($team['nama_pasukan'])): ?>
                                                <span class="badge bg-light text-muted border small ms-1">Default Nama Bahagian</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($team['jenis'] === 'dalaman'): ?>
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill">JTS Dalaman</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill">Agensi Jemputan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="edit.php?id=<?php echo $team['id']; ?>" class="btn btn-sm btn-light border text-dark" title="Edit Nama">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="delete.php" method="POST" onsubmit="return confirm('Batal pendaftaran <?php echo addslashes($team['nama_bahagian']); ?> untuk sukan ini?');" class="d-inline">
                                                    <input type="hidden" name="id" value="<?php echo $team['id']; ?>">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <button type="submit" class="btn btn-sm btn-light border text-danger" title="Batal Pendaftaran">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada kontinjen yang mendaftar untuk sukan ini. Klik <strong>"Daftar Semua Bahagian"</strong> atau <strong>"Tambah Satu-Satu"</strong> di atas.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Satu-Satu untuk Sukan Ini -->
        <div class="modal fade" id="modalRegisterSingle<?php echo $s_id; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header bg-navy text-white rounded-top-4">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle me-1"></i> Daftarkan Bahagian ke <?php echo sanitize($sukan['nama_sukan']); ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="index.php" method="POST">
                        <div class="modal-body p-4">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="action" value="single_register">
                            <input type="hidden" name="sukan_id" value="<?php echo $s_id; ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Kontinjen / Bahagian <span class="text-danger">*</span></label>
                                <select name="bahagian_id" class="form-select" required>
                                    <option value="">-- Sila Pilih Bahagian --</option>
                                    <?php 
                                    $has_available = false;
                                    foreach ($bahagian_list as $b_item): 
                                        $already = in_array($b_item['id'], $registered_b_ids);
                                        if (!$already) $has_available = true;
                                    ?>
                                        <option value="<?php echo $b_item['id']; ?>" <?php echo $already ? 'disabled' : ''; ?>>
                                            <?php echo sanitize($b_item['nama_bahagian']); ?> 
                                            <?php echo $already ? '(Sudah Mendaftar)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!$has_available): ?>
                                    <div class="form-text text-success mt-1">🎉 Semua bahagian telah mendaftar untuk sukan ini!</div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Pasukan Khas (Opsyenal)</label>
                                <input type="text" name="nama_pasukan" class="form-control" placeholder="Cth: Kuching Tigers / Betong A">
                                <div class="form-text small text-muted">Kosongkan jika mahu guna nama rasmi Bahagian.</div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light rounded-bottom-4">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-navy" <?php echo !$has_available ? 'disabled' : ''; ?>>Daftarkan Pasukan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12 text-center p-5 bg-white rounded-4 shadow-sm">
            <i class="bi bi-trophy fs-1 text-muted d-block mb-3"></i>
            <h5>Tiada Acara Sukan Wujud</h5>
            <p class="text-muted">Sila daftarkan acara sukan terlebih dahulu di modul Urus Sukan.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

