<?php
/**
 * Tambah Hero Banner Baru - Admin SukanJTS Sarawak
 * CRUD: Create
 */

$page_title = "Tambah Hero Banner";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan hanya super_admin atau media boleh mengakses modul ini
confirm_access(['super_admin', 'media']);

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tajuk             = trim($_POST['tajuk'] ?? '');
    $bahagian_juara_id = isset($_POST['bahagian_juara_id']) && $_POST['bahagian_juara_id'] !== '' ? (int)$_POST['bahagian_juara_id'] : null;
    $status_aktif      = $_POST['status_aktif'] ?? 'tidak_aktif';
    $susunan           = (int)($_POST['susunan'] ?? 0);
    $submitted_csrf    = $_POST['csrf_token'] ?? '';

    // Validate CSRF / post_max_size
    if (empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $error_msg = "Saiz fail yang diupload melebihi had maksimum pelayan. Sila pilih fail imej yang lebih kecil.";
    } elseif (!verify_csrf_token($submitted_csrf)) {
        $error_msg = "Token keselamatan tidak sah.";
    } elseif (empty($tajuk)) {
        $error_msg = "Sila isi tajuk pengumuman.";
    } elseif (!isset($_FILES['banner_image']) || $_FILES['banner_image']['error'] === UPLOAD_ERR_NO_FILE) {
        $error_msg = "Sila pilih imej banner untuk dimuat naik.";
    } else {
        // Muat naik imej banner
        $upload_res = upload_file($_FILES['banner_image'], UPLOAD_DIR_HERO, ALLOWED_IMAGE_MIMES, MAX_IMAGE_SIZE);
        
        if ($upload_res['success']) {
            $image_filename = $upload_res['filename'];
            
            // Simpan ke database
            $stmt = $conn->prepare("INSERT INTO tbl_hero_banner (tajuk, url_imej, bahagian_juara_id, status_aktif, susunan) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssisi", $tajuk, $image_filename, $bahagian_juara_id, $status_aktif, $susunan);
                
                if ($stmt->execute()) {
                    $new_id = $conn->insert_id;
                    
                    // Rekod Audit Log
                    log_audit($conn, $_SESSION['admin_id'], 'create', 'tbl_hero_banner', $new_id, "Tambah banner utama baru: $tajuk");
                    
                    $_SESSION['success_msg'] = "Hero banner '$tajuk' berjaya didaftarkan!";
                    header("Location: index.php");
                    exit;
                } else {
                    @unlink(UPLOAD_DIR_HERO . $image_filename);
                    $error_msg = "Gagal menyimpan rekod banner: " . $stmt->error;
                }
                $stmt->close();
            } else {
                @unlink(UPLOAD_DIR_HERO . $image_filename);
                $error_msg = "Ralat penyediaan SQL.";
            }
        } else {
            $error_msg = "Muat naik imej gagal: " . $upload_res['error'];
        }
    }
}
?>

<div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Hero Banner</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Daftar Banner Hero Utama Baru</h3>
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
                    <label for="tajuk" class="form-label fw-semibold">Tajuk Banner / Ucapan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="tajuk" name="tajuk" placeholder="Cth: Selamat Datang Atlet JTS / Tahniah Kontinjen Juara!" required>
                    <div class="invalid-feedback">Sila masukkan tajuk banner.</div>
                </div>

                <div class="mb-3">
                    <label for="bahagian_juara_id" class="form-label fw-semibold">Kontinjen Juara Keseluruhan (Opsyenal)</label>
                    <select class="form-select" id="bahagian_juara_id" name="bahagian_juara_id">
                        <option value="">-- Tiada Pengisytiharan Juara --</option>
                        <?php
                        $res_b = $conn->query("SELECT id, nama_bahagian FROM tbl_bahagian WHERE status = 'aktif' ORDER BY nama_bahagian ASC");
                        if ($res_b && $res_b->num_rows > 0) {
                            while ($row = $res_b->fetch_assoc()) {
                                echo "<option value='" . $row['id'] . "'>" . sanitize($row['nama_bahagian']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <div class="form-text small text-muted">Pilih kontinjen ini HANYA jika perlawanan tamat dan ingin memaparkan Juara Keseluruhan di landing page.</div>
                </div>

                <div class="mb-3">
                    <label for="banner_image" class="form-label fw-semibold">Muat Naik Imej Banner <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="banner_image" name="banner_image" accept="image/*" required>
                    <div class="form-text small text-muted">Format: PNG, JPG, JPEG, WEBP. Saiz Maksimum: 5MB. **Resolusi Terbaik: Landskap Lebar (16:5 Ratio, Cth: 1920x600 px atau 1200x380 px)**.</div>
                    <div class="invalid-feedback">Sila pilih fail imej yang sah.</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="status_aktif" class="form-label fw-semibold">Status Paparan</label>
                        <select class="form-select" id="status_aktif" name="status_aktif">
                            <option value="tidak_aktif">Tidak Aktif</option>
                            <option value="aktif">Aktif (Paparkan di Slider)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="susunan" class="form-label fw-semibold">Susunan Keutamaan</label>
                        <input type="number" class="form-control" id="susunan" name="susunan" value="0" min="0">
                    </div>
                </div>

                <div class="pt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-4">Simpan Banner</button>
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
