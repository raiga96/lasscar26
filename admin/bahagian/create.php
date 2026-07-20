<?php
/**
 * Tambah Kontinjen Baru - Admin SukanJTS Sarawak
 * CRUD: Create
 */

$page_title = "Tambah Kontinjen";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan hanya super_admin boleh mengakses modul ini
confirm_access(['super_admin']);

$error_msg = '';
$success_msg = '';

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
        // Proses Upload Logo
        $logo_filename = 'default_logo.png'; // Lalai jika tiada fail dimuat naik
        
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_res = upload_file(
                $_FILES['logo'], 
                UPLOAD_DIR_LOGO, 
                ALLOWED_IMAGE_MIMES, 
                MAX_IMAGE_SIZE
            );
            
            if ($upload_res['success']) {
                $logo_filename = $upload_res['filename'];
            } else {
                $error_msg = "Muat naik logo gagal: " . $upload_res['error'];
            }
        }

        if (empty($error_msg)) {
            // Prepared statement insert
            $stmt = $conn->prepare("INSERT INTO tbl_bahagian (nama_bahagian, singkatan, jenis, logo_url, status) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssss", $nama_bahagian, $singkatan, $jenis, $logo_filename, $status);
                
                if ($stmt->execute()) {
                    $new_id = $conn->insert_id;
                    
                    // Rekod Audit Log
                    log_audit($conn, $_SESSION['admin_id'], 'create', 'tbl_bahagian', $new_id, "Tambah kontinjen baru: $nama_bahagian ($singkatan)");
                    
                    $_SESSION['success_msg'] = "Kontinjen '$nama_bahagian' berjaya didaftarkan!";
                    header("Location: index.php");
                    exit;
                } else {
                    // Semak jika ralat singkatan bertindih
                    if ($conn->errno === 1062) {
                        $error_msg = "Singkatan atau nama bahagian telah digunakan.";
                    } else {
                        $error_msg = "Gagal menyimpan rekod: " . $stmt->error;
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
                <li class="breadcrumb-item active" aria-current="page">Tambah</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Daftar Kontinjen / Jabatan Jemputan</h3>
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

            <form action="<?php echo sanitize($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="mb-3">
                    <label for="nama_bahagian" class="form-label fw-semibold">Nama Bahagian / Jabatan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_bahagian" name="nama_bahagian" placeholder="Cth: JTS Samarahan / Jabatan Hutan Sarawak" required>
                    <div class="invalid-feedback">Sila masukkan nama bahagian atau jabatan.</div>
                </div>

                <div class="mb-3">
                    <label for="singkatan" class="form-label fw-semibold">Singkatan Kod <span class="text-danger">*</span></label>
                    <input type="text" class="form-control text-uppercase" id="singkatan" name="singkatan" placeholder="Cth: SMR / JHS" maxlength="20" required>
                    <div class="invalid-feedback">Sila masukkan kod singkatan (Max: 20 aksara).</div>
                </div>

                <div class="mb-3">
                    <label for="jenis" class="form-label fw-semibold">Kategori Jenis</label>
                    <select class="form-select" id="jenis" name="jenis">
                        <option value="dalaman">Pejabat Bahagian JTS Sarawak (Dalaman)</option>
                        <option value="jemputan">Jabatan Jemputan Luar (Agensi Luar)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="logo" class="form-label fw-semibold">Logo Bendera Kontinjen</label>
                    <input class="form-control" type="file" id="logo" name="logo" accept="image/*">
                    <div class="form-text small text-muted">Format: PNG, JPG, JPEG, WEBP. Saiz Maksimum: 5MB. **Resolusi Terbaik: Segiempat Sama (1:1 Ratio, Cth: 512x512 px atau 256x256 px)**. Jika dikosongkan, logo default akan digunakan.</div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label fw-semibold">Status Penglibatan</label>
                    <select class="form-select" id="status" name="status">
                        <option value="aktif">Aktif (Mengambil Bahagian)</option>
                        <option value="tidak_aktif">Tidak Aktif</option>
                    </select>
                </div>

                <div class="pt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-4">Simpan Rekod</button>
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
