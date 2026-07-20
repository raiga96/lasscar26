<?php
/**
 * Kemas Kini Jadual - Admin SukanJTS Sarawak
 * CRUD: Update
 */

$page_title = "Kemas Kini Jadual";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan super_admin atau editor sahaja boleh mengakses modul ini
confirm_access(['super_admin', 'editor']);

$error_msg = '';
$success_msg = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil maklumat jadual semasa
$stmt_fetch = $conn->prepare("SELECT * FROM tbl_jadual_perlawanan WHERE id = ? LIMIT 1");
$stmt_fetch->bind_param("i", $id);
$stmt_fetch->execute();
$res = $stmt_fetch->get_result();

if ($res->num_rows !== 1) {
    $stmt_fetch->close();
    $_SESSION['error_msg'] = "Jadual perlawanan tidak dijumpai.";
    header("Location: index.php");
    exit;
}

$jadual = $res->fetch_assoc();
$stmt_fetch->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sukan_id     = (int)($_POST['sukan_id'] ?? 0);
    $pasukan_a_id = (int)($_POST['pasukan_a_id'] ?? 0);
    $pasukan_b_id = isset($_POST['pasukan_b_id']) && $_POST['pasukan_b_id'] !== '' ? (int)$_POST['pasukan_b_id'] : null;
    $venue_id     = (int)($_POST['venue_id'] ?? 0);
    $tarikh       = $_POST['tarikh'] ?? '';
    $masa         = $_POST['masa'] ?? '';
    $pusingan     = trim($_POST['pusingan'] ?? '');
    $status       = $_POST['status'] ?? 'akan_datang';
    $csrf_token   = $_POST['csrf_token'] ?? '';

    // Validate CSRF
    if (!verify_csrf_token($csrf_token)) {
        $error_msg = "Token keselamatan tamat tempoh. Sila hantar borang semula.";
    } elseif ($sukan_id <= 0 || $pasukan_a_id <= 0 || $venue_id <= 0 || empty($tarikh) || empty($masa)) {
        $error_msg = "Sila isi semua medan yang wajib.";
    } elseif ($pasukan_a_id === $pasukan_b_id && $pasukan_b_id !== null) {
        $error_msg = "Pasukan A dan Pasukan B tidak boleh kontinjen yang sama.";
    } else {
        // Prepared statement update
        $stmt = $conn->prepare("UPDATE tbl_jadual_perlawanan SET sukan_id = ?, pasukan_a_id = ?, pasukan_b_id = ?, venue_id = ?, tarikh = ?, masa = ?, pusingan = ?, status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("iiiissssi", $sukan_id, $pasukan_a_id, $pasukan_b_id, $venue_id, $tarikh, $masa, $pusingan, $status, $id);
            
            if ($stmt->execute()) {
                // Dapatkan info sukan untuk log
                $res_s = $conn->query("SELECT nama_sukan FROM tbl_sukan WHERE id = $sukan_id");
                $s_name = $res_s ? $res_s->fetch_assoc()['nama_sukan'] : '';

                log_audit($conn, $_SESSION['admin_id'], 'update', 'tbl_jadual_perlawanan', $id, "Kemas kini jadual perlawanan ID $id bagi sukan $s_name");
                
                $_SESSION['success_msg'] = "Jadual perlawanan sukan '$s_name' berjaya dikemas kini!";
                header("Location: index.php");
                exit;
            } else {
                $error_msg = "Gagal mengemas kini jadual: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_msg = "Ralat sistem penyediaan SQL.";
        }
    }
}
?>

<div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Jadual</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kemas Kini</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Kemas Kini Jadual Perlawanan</h3>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="card card-admin p-4">
            
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    ⚠️ <?php echo sanitize($error_msg); ?>
                </div>
            <?php endif; ?>

            <form action="edit.php?id=<?php echo $id; ?>" method="POST" class="needs-validation" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="row g-3 mb-3">
                    <!-- Sukan -->
                    <div class="col-md-6">
                        <label for="sukan_id" class="form-label fw-semibold">Pilih Acara Sukan <span class="text-danger">*</span></label>
                        <select class="form-select" id="sukan_id" name="sukan_id" onchange="filterTeamsBySport()" required>
                            <option value="">-- Sila Pilih Sukan --</option>
                            <?php
                            $res_s = $conn->query("SELECT id, nama_sukan, kategori, jenis_perlawanan FROM tbl_sukan WHERE status = 'aktif' ORDER BY nama_sukan ASC");
                            if ($res_s && $res_s->num_rows > 0) {
                                while ($row = $res_s->fetch_assoc()) {
                                    $selected = ($row['id'] == $jadual['sukan_id']) ? 'selected' : '';
                                    echo "<option value='" . $row['id'] . "' data-jenis='" . $row['jenis_perlawanan'] . "' $selected>" . sanitize($row['nama_sukan']) . " (" . sanitize(ucwords($row['kategori'])) . ")</option>";
                                }
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">Sila pilih acara sukan.</div>
                    </div>
                    
                    <!-- Venue -->
                    <div class="col-md-6">
                        <label for="venue_id" class="form-label fw-semibold">Pilih Venue Pertandingan <span class="text-danger">*</span></label>
                        <select class="form-select" id="venue_id" name="venue_id" required>
                            <option value="">-- Sila Pilih Venue --</option>
                            <?php
                            $res_v = $conn->query("SELECT id, nama_tempat FROM tbl_venue ORDER BY nama_tempat ASC");
                            if ($res_v && $res_v->num_rows > 0) {
                                while ($row = $res_v->fetch_assoc()) {
                                    $selected = ($row['id'] == $jadual['venue_id']) ? 'selected' : '';
                                    echo "<option value='" . $row['id'] . "' $selected>" . sanitize($row['nama_tempat']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">Sila pilih venue.</div>
                    </div>
                </div>

                <!-- Matchup (Pasukan A & B) -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="pasukan_a_id" class="form-label fw-semibold">Pasukan / Kontinjen A <span class="text-danger">*</span></label>
                        <select class="form-select" id="pasukan_a_id" name="pasukan_a_id" required>
                            <option value="">-- Pilih Sukan Terlebih Dahulu --</option>
                            <?php
                            $res_p = $conn->query("SELECT p.id, p.sukan_id, b.nama_bahagian, p.nama_pasukan 
                                                   FROM tbl_pasukan p 
                                                   JOIN tbl_bahagian b ON p.bahagian_id = b.id
                                                   ORDER BY b.nama_bahagian ASC");
                            if ($res_p && $res_p->num_rows > 0) {
                                while ($row = $res_p->fetch_assoc()) {
                                    $team_name = $row['nama_pasukan'] ?: $row['nama_bahagian'];
                                    $selected = ($row['id'] == $jadual['pasukan_a_id']) ? 'selected' : '';
                                    echo "<option value='" . $row['id'] . "' data-sukan-id='" . $row['sukan_id'] . "' class='d-none' $selected>" . sanitize($team_name) . "</option>";
                                }
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">Sila pilih Pasukan A.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="pasukan_b_id" class="form-label fw-semibold">Pasukan / Kontinjen B <span id="req_b_asterisk"></span></label>
                        <select class="form-select" id="pasukan_b_id" name="pasukan_b_id">
                            <option value="">-- Pilih Sukan Terlebih Dahulu --</option>
                            <?php
                            if ($res_p && $res_p->num_rows > 0) {
                                $res_p->data_seek(0);
                                while ($row = $res_p->fetch_assoc()) {
                                    $team_name = $row['nama_pasukan'] ?: $row['nama_bahagian'];
                                    $selected = ($row['id'] == $jadual['pasukan_b_id']) ? 'selected' : '';
                                    echo "<option value='" . $row['id'] . "' data-sukan-id='" . $row['sukan_id'] . "' class='d-none' $selected>" . sanitize($team_name) . "</option>";
                                }
                            }
                            ?>
                        </select>
                        <div class="form-text small text-muted" id="pasukan_b_help">Boleh dikosongkan untuk sukan berjenis individu.</div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Tarikh -->
                    <div class="col-md-4">
                        <label for="tarikh" class="form-label fw-semibold">Tarikh Perlawanan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tarikh" name="tarikh" value="<?php echo sanitize($jadual['tarikh']); ?>" required>
                        <div class="invalid-feedback">Sila pilih tarikh.</div>
                    </div>

                    <!-- Masa -->
                    <div class="col-md-4">
                        <label for="masa" class="form-label fw-semibold">Masa Perlawanan <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="masa" name="masa" value="<?php echo sanitize($jadual['masa']); ?>" required>
                        <div class="invalid-feedback">Sila masukkan masa.</div>
                    </div>

                    <!-- Pusingan -->
                    <div class="col-md-4">
                        <label for="pusingan" class="form-label fw-semibold">Pusingan / Peringkat</label>
                        <input type="text" class="form-control" id="pusingan" name="pusingan" value="<?php echo sanitize($jadual['pusingan'] ?? ''); ?>" placeholder="Cth: Peringkat Kumpulan, Suku Akhir, Akhir">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="status" class="form-label fw-semibold">Status Perlawanan</label>
                    <select class="form-select" id="status" name="status">
                        <option value="akan_datang" <?php echo ($jadual['status'] === 'akan_datang') ? 'selected' : ''; ?>>Akan Datang</option>
                        <option value="live" <?php echo ($jadual['status'] === 'live') ? 'selected' : ''; ?>>Sedang Berlangsung (LIVE)</option>
                        <option value="selesai" <?php echo ($jadual['status'] === 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                        <option value="ditangguh" <?php echo ($jadual['status'] === 'ditangguh') ? 'selected' : ''; ?>>Ditangguh / Batal</option>
                    </select>
                </div>

                <div class="pt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-4">Kemas Kini Jadual</button>
                    <a href="index.php" class="btn btn-outline-secondary px-4">Batal</a>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    function filterTeamsBySport() {
        const sukanSelect = document.getElementById('sukan_id');
        const aSelect = document.getElementById('pasukan_a_id');
        const bSelect = document.getElementById('pasukan_b_id');
        const reqAsterisk = document.getElementById('req_b_asterisk');
        const helpText = document.getElementById('pasukan_b_help');
        
        const selectedOption = sukanSelect.options[sukanSelect.selectedIndex];
        const selectedSukanId = sukanSelect.value;
        
        if (!selectedSukanId) {
            aSelect.disabled = true;
            bSelect.disabled = true;
            aSelect.value = '';
            bSelect.value = '';
            reqAsterisk.innerHTML = '';
            return;
        }

        const isIndividu = selectedOption.getAttribute('data-jenis') === 'individu';
        
        // Tetapkan keperluan pasukan B
        if (isIndividu) {
            bSelect.required = false;
            reqAsterisk.innerHTML = '';
            helpText.innerHTML = "Boleh dikosongkan untuk sukan berjenis individu.";
        } else {
            bSelect.required = true;
            reqAsterisk.innerHTML = ' <span class="text-danger">*</span>';
            helpText.innerHTML = "Wajib dipilih bagi sukan berpasukan (A vs B).";
        }

        // Aktifkan pilihan
        aSelect.disabled = false;
        bSelect.disabled = false;

        // Tapis opsi Pasukan A
        for (let i = 0; i < aSelect.options.length; i++) {
            const opt = aSelect.options[i];
            if (opt.value === '') continue;
            
            if (opt.getAttribute('data-sukan-id') === selectedSukanId) {
                opt.classList.remove('d-none');
            } else {
                opt.classList.add('d-none');
            }
        }

        // Tapis opsi Pasukan B
        for (let i = 0; i < bSelect.options.length; i++) {
            const opt = bSelect.options[i];
            
            if (opt.value === '') {
                opt.innerHTML = isIndividu ? '-- Tiada (Sukan Individu) --' : '-- Pilih Pasukan B --';
                continue;
            }
            
            if (opt.getAttribute('data-sukan-id') === selectedSukanId) {
                opt.classList.remove('d-none');
            } else {
                opt.classList.add('d-none');
            }
        }
    }

    // Panggil fungsi penapisan semasa halaman dimuatkan untuk menetapkan keadaan awal
    document.addEventListener("DOMContentLoaded", function() {
        filterTeamsBySport();
        // Kembalikan nilai terpilih asal
        document.getElementById('pasukan_a_id').value = "<?php echo $jadual['pasukan_a_id']; ?>";
        document.getElementById('pasukan_b_id').value = "<?php echo ($jadual['pasukan_b_id'] !== null) ? $jadual['pasukan_b_id'] : ''; ?>";
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
