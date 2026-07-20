<?php
/**
 * Tambah Aturcara Baru - Admin SukanJTS Sarawak
 * CRUD: Create
 */

$page_title = "Tambah Aturcara";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan super_admin atau editor sahaja boleh mengakses modul ini
confirm_access(['super_admin', 'editor']);

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis                    = $_POST['jenis'] ?? 'umum';
    $tarikh                   = $_POST['tarikh'] ?? '';
    $masa                     = $_POST['masa'] ?? '';
    $aktiviti                 = trim($_POST['aktiviti'] ?? '');
    $pegawai_bertanggungjawab = trim($_POST['pegawai_bertanggungjawab'] ?? '');
    $susunan                  = (int)($_POST['susunan'] ?? 0);
    $csrf_token               = $_POST['csrf_token'] ?? '';

    // Validate CSRF
    if (!verify_csrf_token($csrf_token)) {
        $error_msg = "Token keselamatan tamat tempoh. Sila hantar borang semula.";
    } elseif (empty($tarikh) || empty($masa) || empty($aktiviti)) {
        $error_msg = "Sila isi semua medan yang wajib.";
    } else {
        $pegawai_val = empty($pegawai_bertanggungjawab) ? null : $pegawai_bertanggungjawab;

        // Prepared statement insert
        $stmt = $conn->prepare("INSERT INTO tbl_aturcara (jenis, tarikh, masa, aktiviti, pegawai_bertanggungjawab, susunan) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssi", $jenis, $tarikh, $masa, $aktiviti, $pegawai_val, $susunan);
            
            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                
                // Rekod Audit Log
                log_audit($conn, $_SESSION['admin_id'], 'create', 'tbl_aturcara', $new_id, "Tambah aturcara baru: $aktiviti (Jenis: $jenis)");
                
                $_SESSION['success_msg'] = "Aturcara '$aktiviti' berjaya didaftarkan!";
                header("Location: index.php");
                exit;
            } else {
                $error_msg = "Gagal menyimpan rekod: " . $stmt->error;
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
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Aturcara</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Daftar Agenda Aturcara Baru</h3>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card card-admin p-4">
            
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    ⚠️ <?php echo sanitize($error_msg); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo sanitize($_SERVER['PHP_SELF']); ?>" method="POST" class="needs-validation" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="mb-3">
                    <label for="jenis" class="form-label fw-semibold">Jenis Majlis / Program <span class="text-danger">*</span></label>
                    <select class="form-select" id="jenis" name="jenis" required>
                        <option value="umum" selected>Umum (Kejohanan Sepanjang Hari)</option>
                        <option value="pembukaan">Majlis Perasmian Pembukaan</option>
                        <option value="penutup">Majlis Makan Malam & Penutupan</option>
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="tarikh" class="form-label fw-semibold">Tarikh Kejadian <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tarikh" name="tarikh" required>
                        <div class="invalid-feedback">Sila pilih tarikh.</div>
                    </div>
                    <div class="col-6">
                        <label for="masa" class="form-label fw-semibold">Masa Mula <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="masa" name="masa" required>
                        <div class="invalid-feedback">Sila masukkan masa.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="aktiviti" class="form-label fw-semibold">Aktiviti / Acara Program <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="aktiviti" name="aktiviti" placeholder="Cth: Ketibaan dif-dif kehormat / Bacaan ikrar atlet" required>
                    <div class="invalid-feedback">Sila masukkan nama aktiviti.</div>
                </div>

                <div class="mb-3">
                    <label for="pegawai_bertanggungjawab" class="form-label fw-semibold">Pegawai Bertanggungjawab</label>
                    <input type="text" class="form-control" id="pegawai_bertanggungjawab" name="pegawai_bertanggungjawab" placeholder="Nama / Jawatan pegawai">
                </div>

                <div class="mb-3">
                    <label for="susunan" class="form-label fw-semibold">Susunan Keutamaan (Sorting Order)</label>
                    <input type="number" class="form-control" id="susunan" name="susunan" value="0" min="0">
                    <div class="form-text small text-muted">Nombor lebih kecil dipaparkan dahulu pada hari yang sama.</div>
                </div>

                <div class="pt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-4">Simpan Agenda</button>
                    <a href="index.php" class="btn btn-outline-secondary px-4">Batal</a>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
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
