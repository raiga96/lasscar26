<?php
/**
 * Kemas Kini Hero Banner - Admin SukanJTS Sarawak
 * CRUD: Update
 */

$page_title = "Kemas Kini Hero Banner";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan hanya super_admin atau media boleh mengakses modul ini
confirm_access(['super_admin', 'media']);

$error_msg = '';
$success_msg = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil maklumat banner semasa
$stmt_fetch = $conn->prepare("SELECT * FROM tbl_hero_banner WHERE id = ? LIMIT 1");
$stmt_fetch->bind_param("i", $id);
$stmt_fetch->execute();
$res = $stmt_fetch->get_result();

if ($res->num_rows !== 1) {
    $stmt_fetch->close();
    $_SESSION['error_msg'] = "Banner tidak dijumpai.";
    header("Location: index.php");
    exit;
}

$banner = $res->fetch_assoc();
$stmt_fetch->close();

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
    } else {
        $image_filename = $banner['url_imej']; // Kekalkan imej lama
        
        // Pengendali fail imej baru jika ada (Had saiz: 20MB)
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_res = upload_file($_FILES['banner_image'], UPLOAD_DIR_HERO, ALLOWED_IMAGE_MIMES, MAX_HERO_BANNER_SIZE);
            
            if ($upload_res['success']) {
                // Padam imej banner lama dari cakera
                $old_img = $banner['url_imej'];
                if ($old_img && file_exists(UPLOAD_DIR_HERO . $old_img)) {
                    @unlink(UPLOAD_DIR_HERO . $old_img);
                }
                
                $image_filename = $upload_res['filename'];
            } else {
                $error_msg = "Muat naik imej gagal: " . $upload_res['error'];
            }
        }

        if (empty($error_msg)) {
            // Prepared statement update
            $stmt = $conn->prepare("UPDATE tbl_hero_banner SET tajuk = ?, url_imej = ?, bahagian_juara_id = ?, status_aktif = ?, susunan = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ssisii", $tajuk, $image_filename, $bahagian_juara_id, $status_aktif, $susunan, $id);
                
                if ($stmt->execute()) {
                    // Rekod Audit Log
                    log_audit($conn, $_SESSION['admin_id'], 'update', 'tbl_hero_banner', $id, "Kemas kini banner utama: $tajuk");
                    
                    $_SESSION['success_msg'] = "Hero banner '$tajuk' berjaya dikemas kini!";
                    header("Location: index.php");
                    exit;
                } else {
                    $error_msg = "Gagal mengemas kini rekod banner: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error_msg = "Ralat penyediaan SQL.";
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
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Hero Banner</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Kemas Kini Banner Hero Utama</h3>
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

            <form action="edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="mb-3">
                    <label for="tajuk" class="form-label fw-semibold">Tajuk Banner / Ucapan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="tajuk" name="tajuk" value="<?php echo sanitize($banner['tajuk']); ?>">
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
                                $selected = ($row['id'] == $banner['bahagian_juara_id']) ? 'selected' : '';
                                echo "<option value='" . $row['id'] . "' $selected>" . sanitize($row['nama_bahagian']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold d-block">Imej Banner Semasa</label>
                    <img src="<?php echo BASE_URL; ?>assets/uploads/hero/<?php echo sanitize($banner['url_imej']); ?>" class="img-thumbnail mb-2" style="width: 240px; height: 120px; object-fit: cover;" alt="">
                    <label for="banner_image" class="form-label fw-semibold">Tukar Imej Banner (Opsyenal)</label>
                    <input class="form-control" type="file" id="banner_image" name="banner_image" accept="image/*">
                    <div class="form-text small text-muted">Biarkan kosong jika tidak mahu menukar imej sedia ada. Format: PNG, JPG, JPEG, WEBP. **Saiz Maksimum: 20MB**. **Resolusi Terbaik: Landskap Lebar (16:5 Ratio, Cth: 1920x600 px atau 1200x380 px)**.</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="status_aktif" class="form-label fw-semibold">Status Paparan</label>
                        <select class="form-select" id="status_aktif" name="status_aktif">
                            <option value="tidak_aktif" <?php echo ($banner['status_aktif'] === 'tidak_aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                            <option value="aktif" <?php echo ($banner['status_aktif'] === 'aktif') ? 'selected' : ''; ?>>Aktif (Paparkan di Slider)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="susunan" class="form-label fw-semibold">Susunan Keutamaan</label>
                        <input type="number" class="form-control" id="susunan" name="susunan" value="<?php echo sanitize($banner['susunan']); ?>">
                    </div>
                </div>

                <div class="pt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-4">Kemas Kini</button>
                    <a href="index.php" class="btn btn-outline-secondary px-4">Batal</a>
                </div>
            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
