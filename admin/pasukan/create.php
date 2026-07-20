<?php
/**
 * Daftarkan Pasukan Baru - Admin SukanJTS Sarawak
 * CRUD: Create
 */

$page_title = "Daftar Pasukan";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan hanya super_admin boleh mengakses modul ini
confirm_access(['super_admin']);

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bahagian_id  = (int)($_POST['bahagian_id'] ?? 0);
    $sukan_id     = (int)($_POST['sukan_id'] ?? 0);
    $nama_pasukan = trim($_POST['nama_pasukan'] ?? '');
    $csrf_token   = $_POST['csrf_token'] ?? '';

    // Validate CSRF
    if (!verify_csrf_token($csrf_token)) {
        $error_msg = "Token keselamatan tamat tempoh. Sila hantar borang semula.";
    } elseif ($bahagian_id <= 0 || $sukan_id <= 0) {
        $error_msg = "Sila pilih kontinjen dan acara sukan.";
    } else {
        // Set nama_pasukan kepada NULL jika kosong
        $nama_pasukan_val = empty($nama_pasukan) ? null : $nama_pasukan;

        // Prepared statement insert
        $stmt = $conn->prepare("INSERT INTO tbl_pasukan (bahagian_id, sukan_id, nama_pasukan) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iis", $bahagian_id, $sukan_id, $nama_pasukan_val);
            
            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                
                // Dapatkan nama sukan & bahagian untuk log audit
                $res_b = $conn->query("SELECT nama_bahagian FROM tbl_bahagian WHERE id = $bahagian_id");
                $res_s = $conn->query("SELECT nama_sukan FROM tbl_sukan WHERE id = $sukan_id");
                $n_b = $res_b ? $res_b->fetch_assoc()['nama_bahagian'] : '';
                $n_s = $res_s ? $res_s->fetch_assoc()['nama_sukan'] : '';

                log_audit($conn, $_SESSION['admin_id'], 'create', 'tbl_pasukan', $new_id, "Daftar pasukan: Kontinjen $n_b untuk sukan $n_s. Nama Pasukan: " . ($nama_pasukan ?: 'Sama Kontinjen'));
                
                $_SESSION['success_msg'] = "Pasukan '$n_b' berjaya didaftarkan untuk sukan '$n_s'!";
                header("Location: index.php");
                exit;
            } else {
                if ($conn->errno === 1062) {
                    $error_msg = "Kontinjen ini telah pun didaftarkan untuk sukan tersebut.";
                } else {
                    $error_msg = "Gagal mendaftar pasukan: " . $stmt->error;
                }
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
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Pasukan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Daftar</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Pendaftaran Kontinjen Ke Acara Sukan</h3>
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
                    <label for="bahagian_id" class="form-label fw-semibold">Pilih Kontinjen (Bahagian) <span class="text-danger">*</span></label>
                    <select class="form-select" id="bahagian_id" name="bahagian_id" required>
                        <option value="">-- Sila Pilih Kontinjen --</option>
                        <?php
                        $res_b = $conn->query("SELECT id, nama_bahagian, jenis FROM tbl_bahagian WHERE status = 'aktif' ORDER BY jenis ASC, nama_bahagian ASC");
                        if ($res_b && $res_b->num_rows > 0) {
                            $current_type = '';
                            while ($row = $res_b->fetch_assoc()) {
                                $type_label = ($row['jenis'] === 'dalaman') ? 'Pejabat Bahagian JTS' : 'Jabatan Jemputan';
                                if ($current_type !== $row['jenis']) {
                                    if ($current_type !== '') echo "</optgroup>";
                                    $current_type = $row['jenis'];
                                    echo "<optgroup label='" . $type_label . "'>";
                                }
                                echo "<option value='" . $row['id'] . "'>" . sanitize($row['nama_bahagian']) . "</option>";
                            }
                            echo "</optgroup>";
                        }
                        ?>
                    </select>
                    <div class="invalid-feedback">Sila pilih kontinjen.</div>
                </div>

                <div class="mb-3">
                    <label for="sukan_id" class="form-label fw-semibold">Pilih Acara Sukan <span class="text-danger">*</span></label>
                    <select class="form-select" id="sukan_id" name="sukan_id" required>
                        <option value="">-- Sila Pilih Sukan --</option>
                        <?php
                        $res_s = $conn->query("SELECT id, nama_sukan, kategori, jenis_perlawanan FROM tbl_sukan WHERE status = 'aktif' ORDER BY nama_sukan ASC");
                        if ($res_s && $res_s->num_rows > 0) {
                            while ($row = $res_s->fetch_assoc()) {
                                $detail = ucwords($row['kategori']) . " | " . ucwords($row['jenis_perlawanan']);
                                echo "<option value='" . $row['id'] . "'>" . sanitize($row['nama_sukan']) . " (" . $detail . ")</option>";
                            }
                        }
                        ?>
                    </select>
                    <div class="invalid-feedback">Sila pilih sukan.</div>
                </div>

                <div class="mb-3">
                    <label for="nama_pasukan" class="form-label fw-semibold">Nama Pasukan Khas (Opsyenal)</label>
                    <input type="text" class="form-control" id="nama_pasukan" name="nama_pasukan" placeholder="Cth: Kuching Tigers / Betong A">
                    <div class="form-text small text-muted">Biarkan kosong untuk menggunakan nama rasmi Bahagian (default). Sesuai jika terdapat sub-kontinjen.</div>
                </div>

                <div class="pt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-4">Daftar Pasukan</button>
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
