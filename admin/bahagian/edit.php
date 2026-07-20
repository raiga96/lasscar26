<?php
/**
 * Kemas Kini Kontinjen - Admin SukanJTS Sarawak
 * CRUD: Update
 */

$page_title = "Kemas Kini Kontinjen";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan hanya super_admin boleh mengakses modul ini
confirm_access(['super_admin']);

$error_msg = '';
$success_msg = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil maklumat kontinjen semasa
$stmt_fetch = $conn->prepare("SELECT * FROM tbl_bahagian WHERE id = ? LIMIT 1");
$stmt_fetch->bind_param("i", $id);
$stmt_fetch->execute();
$res = $stmt_fetch->get_result();

if ($res->num_rows !== 1) {
    $stmt_fetch->close();
    $_SESSION['error_msg'] = "Kontinjen tidak dijumpai.";
    header("Location: index.php");
    exit;
}

$kontinjen = $res->fetch_assoc();
$stmt_fetch->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_bahagian = trim($_POST['nama_bahagian'] ?? '');
    $singkatan     = strtoupper(trim($_POST['singkatan'] ?? ''));
    $jenis         = $_POST['jenis'] ?? 'dalaman';
    $status        = $_POST['status'] ?? 'aktif';
    $submitted_csrf    = $_POST['csrf_token'] ?? '';

    // Validate CSRF / post_max_size
    if (empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $error_msg = "Saiz fail yang diupload melebihi had maksimum pelayan. Sila pilih fail logo yang lebih kecil.";
    } elseif (!verify_csrf_token($submitted_csrf)) {
        $error_msg = "Token keselamatan tamat tempoh. Sila hantar borang semula.";
    } elseif (empty($nama_bahagian) || empty($singkatan)) {
        $error_msg = "Sila isi semua medan yang wajib.";
    } else {
        $logo_filename = $kontinjen['logo_url']; // Lalai kekalkan logo lama
        
        // Pengendali fail logo baru
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_res = upload_file(
                $_FILES['logo'], 
                UPLOAD_DIR_LOGO, 
                ALLOWED_IMAGE_MIMES, 
                MAX_IMAGE_SIZE
            );
            
            if ($upload_res['success']) {
                // Padam logo lama jika bukan default_logo.png
                $old_logo = $kontinjen['logo_url'];
                if ($old_logo && $old_logo !== 'default_logo.png' && file_exists(UPLOAD_DIR_LOGO . $old_logo)) {
                    @unlink(UPLOAD_DIR_LOGO . $old_logo);
                }
                
                $logo_filename = $upload_res['filename'];
            } else {
                $error_msg = "Muat naik logo gagal: " . $upload_res['error'];
            }
        }

        if (empty($error_msg)) {
            // Prepared statement update
            $stmt = $conn->prepare("UPDATE tbl_bahagian SET nama_bahagian = ?, singkatan = ?, jenis = ?, logo_url = ?, status = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("sssssi", $nama_bahagian, $singkatan, $jenis, $logo_filename, $status, $id);
                
                if ($stmt->execute()) {
                    // Rekod Audit Log
                    log_audit($conn, $_SESSION['admin_id'], 'update', 'tbl_bahagian', $id, "Kemas kini kontinjen: $nama_bahagian ($singkatan)");
                    
                    $_SESSION['success_msg'] = "Kontinjen '$nama_bahagian' berjaya dikemas kini!";
                    header("Location: index.php");
                    exit;
                } else {
                    if ($conn->errno === 1062) {
                        $error_msg = "Singkatan atau nama bahagian telah digunakan.";
                    } else {
                        $error_msg = "Gagal mengemaskini rekod: " . $stmt->error;
                    }
                }
                $stmt->close();
            } else {
                $error_msg = "Ralat sistem penyediaan SQL.";
            }
        }
    }
}
?>

<div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Kontinjen</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Kemas Kini Kontinjen</h3>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card card-admin p-4">
            <!-- Mesej ralat diuruskan oleh SweetAlert2 secara popup di penghujung fail -->

            <form action="edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="mb-3">
                    <label for="nama_bahagian" class="form-label fw-semibold">Nama Bahagian / Jabatan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_bahagian" name="nama_bahagian" value="<?php echo sanitize($kontinjen['nama_bahagian']); ?>" required>
                    <div class="invalid-feedback">Sila masukkan nama bahagian atau jabatan.</div>
                </div>

                <div class="mb-3">
                    <label for="singkatan" class="form-label fw-semibold">Singkatan Kod <span class="text-danger">*</span></label>
                    <input type="text" class="form-control text-uppercase" id="singkatan" name="singkatan" value="<?php echo sanitize($kontinjen['singkatan']); ?>" maxlength="20" required>
                    <div class="invalid-feedback">Sila masukkan kod singkatan (Max: 20 aksara).</div>
                </div>

                <div class="mb-3">
                    <label for="jenis" class="form-label fw-semibold">Kategori Jenis</label>
                    <select class="form-select" id="jenis" name="jenis">
                        <option value="dalaman" <?php echo ($kontinjen['jenis'] === 'dalaman') ? 'selected' : ''; ?>>Pejabat Bahagian JTS Sarawak (Dalaman)</option>
                        <option value="jemputan" <?php echo ($kontinjen['jenis'] === 'jemputan') ? 'selected' : ''; ?>>Jabatan Jemputan Luar (Agensi Luar)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold d-block">Logo Semasa</label>
                    <img src="<?php echo BASE_URL; ?>assets/uploads/logo-bahagian/<?php echo sanitize($kontinjen['logo_url'] ?: 'default_logo.png'); ?>" alt="Logo Semasa" class="img-thumbnail mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                    <input class="form-control" type="file" id="logo" name="logo" accept="image/*">
                    <div class="form-text small text-muted">Format: PNG, JPG, JPEG, WEBP. Saiz Maksimum: 5MB. **Resolusi Terbaik: Segiempat Sama (1:1 Ratio, Cth: 512x512 px atau 256x256 px)**. Biarkan kosong jika tiada pertukaran logo.</div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label fw-semibold">Status Penglibatan</label>
                    <select class="form-select" id="status" name="status">
                        <option value="aktif" <?php echo ($kontinjen['status'] === 'aktif') ? 'selected' : ''; ?>>Aktif (Mengambil Bahagian)</option>
                        <option value="tidak_aktif" <?php echo ($kontinjen['status'] === 'tidak_aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                    </select>
                </div>

                <div class="pt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-4">Kemas Kini</button>
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
<?php if (!empty($error_msg)): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            title: 'Ralat!',
            text: <?php echo json_encode($swal_error ?: $error_msg); ?>,
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    });
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
