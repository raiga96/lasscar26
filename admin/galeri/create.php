<?php
/**
 * Muat Naik Media - Admin SukanJTS Sarawak
 * CRUD: Create
 */

$page_title = "Muat Naik Media";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan hanya super_admin atau media boleh mengakses modul ini
confirm_access(['super_admin', 'media']);

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tajuk      = trim($_POST['tajuk'] ?? '');
    $album      = trim($_POST['album'] ?? 'Umum');
    $sukan_id   = isset($_POST['sukan_id']) && $_POST['sukan_id'] !== '' ? (int)$_POST['sukan_id'] : null;
    $submitted_csrf = $_POST['csrf_token'] ?? '';

    // Validate CSRF / post_max_size
    if (empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $error_msg = "Saiz fail yang diupload melebihi had maksimum pelayan. Sila pilih fail imej/video yang lebih kecil.";
    } elseif (!verify_csrf_token($submitted_csrf)) {
        $error_msg = "Token keselamatan tidak sah.";
    } elseif (!isset($_FILES['media_file']) || $_FILES['media_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $error_msg = "Sila pilih fail imej atau video untuk dimuat naik.";
    } else {
        // Tentukan MIME type fail untuk membezakan imej / video
        $tmp_file = $_FILES['media_file']['tmp_name'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        
        if ($finfo) {
            $mime_type = finfo_file($finfo, $tmp_file);
            finfo_close($finfo);
            
            $jenis_fail = '';
            $allowed_mimes = [];
            $max_size = 0;
            
            if (str_starts_with($mime_type, 'image/')) {
                $jenis_fail = 'imej';
                $allowed_mimes = ALLOWED_IMAGE_MIMES;
                $max_size = MAX_IMAGE_SIZE;
            } elseif (str_starts_with($mime_type, 'video/')) {
                $jenis_fail = 'video';
                $allowed_mimes = ALLOWED_VIDEO_MIMES;
                $max_size = MAX_VIDEO_SIZE;
            } else {
                $error_msg = "Format fail tidak sah ($mime_type). Hanya fail imej atau video dibenarkan.";
            }

            if (empty($error_msg)) {
                // Muat naik fail secara selamat
                $upload_res = upload_file($_FILES['media_file'], UPLOAD_DIR_GALERI, $allowed_mimes, $max_size);
                
                if ($upload_res['success']) {
                    $filename = $upload_res['filename'];
                    
                    // Simpan ke database
                    $stmt = $conn->prepare("INSERT INTO tbl_galeri (tajuk, jenis_fail, url_fail, album, sukan_id, upload_oleh) VALUES (?, ?, ?, ?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("ssssii", $tajuk, $jenis_fail, $filename, $album, $sukan_id, $_SESSION['admin_id']);
                        
                        if ($stmt->execute()) {
                            $new_id = $conn->insert_id;
                            
                            // Rekod Audit Log
                            log_audit($conn, $_SESSION['admin_id'], 'create', 'tbl_galeri', $new_id, "Muat naik media baru: $tajuk ($jenis_fail)");
                            
                            $_SESSION['success_msg'] = "Media '$tajuk' berjaya dimuat naik!";
                            header("Location: index.php");
                            exit;
                        } else {
                            // Padam fail yang berjaya diupload jika DB gagal simpan
                            @unlink(UPLOAD_DIR_GALERI . $filename);
                            $error_msg = "Gagal menyimpan rekod media: " . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        @unlink(UPLOAD_DIR_GALERI . $filename);
                        $error_msg = "Ralat penyediaan SQL.";
                    }
                } else {
                    $error_msg = "Gagal memuat naik fail: " . $upload_res['error'];
                }
            }
        } else {
            $error_msg = "Gagal mengesahkan jenis format fail.";
        }
    }
}
?>

<div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Galeri</a></li>
                <li class="breadcrumb-item active" aria-current="page">Muat Naik</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Muat Naik Gambar & Video Kejohanan</h3>
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
                    <label for="tajuk" class="form-label fw-semibold">Tajuk Media <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="tajuk" name="tajuk" placeholder="Cth: Penyerahan piala oleh pengarah / Separuh Akhir Badminton" required>
                    <div class="invalid-feedback">Sila masukkan tajuk media.</div>
                </div>

                <div class="mb-3">
                    <label for="album" class="form-label fw-semibold">Nama Album / Kategori Hari</label>
                    <select class="form-select" id="album" name="album">
                        <option value="Hari 1" selected>Hari Pertama (Hari 1)</option>
                        <option value="Hari 2">Hari Kedua (Hari 2)</option>
                        <option value="Hari 3">Hari Ketiga (Hari 3)</option>
                        <option value="Majlis Pembukaan">Majlis Pembukaan</option>
                        <option value="Majlis Penutupan">Majlis Penutupan / Dinner</option>
                        <option value="Acara Padang">Acara Padang / Bola Sepak</option>
                        <option value="Acara Dewan">Acara Dewan</option>
                        <option value="Umum">Umum</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="sukan_id" class="form-label fw-semibold">Pautkan Ke Acara Sukan (Opsyenal)</label>
                    <select class="form-select" id="sukan_id" name="sukan_id">
                        <option value="">-- Tiada Hubungan Sukan Terperinci --</option>
                        <?php
                        $res_s = $conn->query("SELECT id, nama_sukan, kategori FROM tbl_sukan WHERE status = 'aktif' ORDER BY nama_sukan ASC");
                        if ($res_s && $res_s->num_rows > 0) {
                            while ($row = $res_s->fetch_assoc()) {
                                echo "<option value='" . $row['id'] . "'>" . sanitize($row['nama_sukan']) . " (" . sanitize(ucwords($row['kategori'])) . ")</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="media_file" class="form-label fw-semibold">Pilih Fail Imej / Video <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="media_file" name="media_file" accept="image/*,video/*" required>
                    <div class="form-text small text-muted">
                        Imej dibenarkan: PNG, JPG, JPEG, WEBP. Maksimum saiz: 5MB. **Resolusi Terbaik: Landskap standard 4:3 atau 16:9 (Cth: 1200x800 px atau 1920x1080 px)**.<br>
                        Video dibenarkan: MP4, WEBM. Maksimum saiz: 50MB. **Format Terbaik: Resolusi 720p/1080p (Cth: 1280x720 px atau 1920x1080 px)**.
                    </div>
                    <div class="invalid-feedback">Sila pilih fail media yang sah.</div>
                </div>

                <div class="pt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-4">Muat Naik</button>
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
