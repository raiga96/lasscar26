<?php
/**
 * Urus Galeri Media - Admin SukanJTS Sarawak
 * CRUD: Read & Delete UI
 */

$page_title = "Galeri Media";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan hanya super_admin atau media boleh mengakses modul ini
confirm_access(['super_admin', 'media']);

$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
?>

<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h3 class="fw-bold text-dark mb-0">Pengurusan Galeri Media Kejohanan</h3>
    </div>
    <div class="col-sm-6 text-sm-end mt-2 mt-sm-0 d-flex gap-2 justify-content-sm-end">
        <button id="btn-sync-gdrive" class="btn btn-outline-navy fw-medium" onclick="syncGoogleDrive()">
            <i class="bi bi-google me-1"></i> Segerak Google Drive
        </button>
        <a href="create.php" class="btn btn-navy fw-medium">
            <i class="bi bi-upload me-1"></i> Muat Naik Manual
        </a>
    </div>
</div>

<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        🎉 <?php echo sanitize($success_msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        ⚠️ <?php echo sanitize($error_msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card card-admin p-4">
    <div class="row g-3">
        <?php
        $query = "SELECT g.*, s.nama_sukan, u.nama_penuh AS nama_uploader 
                  FROM tbl_galeri g
                  LEFT JOIN tbl_sukan s ON g.sukan_id = s.id
                  LEFT JOIN tbl_pengguna u ON g.upload_oleh = u.id
                  ORDER BY g.dicipta_pada DESC";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if (!empty($row['is_gdrive'])) {
                    $file_url = "https://lh3.googleusercontent.com/d/" . $row['gdrive_file_id'] . "=w800";
                } else {
                    $file_url = BASE_URL . 'assets/uploads/galeri/' . $row['url_fail'];
                }
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm border rounded-3 overflow-hidden">
                        
                        <!-- Media Preview -->
                        <div class="position-relative bg-dark" style="height: 180px;">
                            <?php if ($row['jenis_fail'] === 'imej'): ?>
                                <img src="<?php echo sanitize($file_url); ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo sanitize($row['tajuk']); ?>">
                            <?php else: ?>
                                <video class="w-100 h-100" style="object-fit: cover;" preload="metadata">
                                    <source src="<?php echo sanitize($file_url); ?>" type="video/mp4">
                                    Format tidak disokong.
                                </video>
                                <div class="position-absolute top-50 start-50 translate-middle text-white bg-dark bg-opacity-75 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; pointer-events: none;">
                                    <i class="bi bi-play-fill fs-3"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Badge Album/Sukan -->
                            <div class="position-absolute bottom-0 start-0 m-2">
                                <span class="badge bg-navy text-white"><?php echo sanitize($row['album'] ?: 'Umum'); ?></span>
                                <?php if (!empty($row['is_gdrive'])): ?>
                                    <span class="badge bg-primary text-white"><i class="bi bi-google"></i> Drive</span>
                                <?php endif; ?>
                                <?php if ($row['nama_sukan']): ?>
                                    <span class="badge bg-gold text-dark"><?php echo sanitize($row['nama_sukan']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold text-dark text-truncate mb-1" title="<?php echo sanitize($row['tajuk']); ?>"><?php echo sanitize($row['tajuk'] ?: 'Media Tanpa Tajuk'); ?></h6>
                                <p class="text-muted small mb-0">Sumber: <strong><?php echo !empty($row['is_gdrive']) ? 'Google Drive Auto-Sync' : sanitize($row['nama_uploader'] ?: 'Sistem'); ?></strong></p>
                                <p class="text-muted small mb-0"><i class="bi bi-calendar3 me-1"></i> <?php echo format_date(date('Y-m-d', strtotime($row['dicipta_pada']))); ?></p>
                            </div>

                            <!-- Delete Form POST -->
                            <div class="mt-3 pt-2 border-top">
                                <form action="delete.php" method="POST" onsubmit="return confirm('Adakah anda pasti untuk memadam fail media ini?');">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-1">
                                        <i class="bi bi-trash"></i> Padam Rekod
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
                <?php
            }
        } else {
            echo "<div class='col-12 text-center text-muted p-5'>Tiada fail media dimuat naik dalam galeri.</div>";
        }
        ?>
    </div>
</div>

<script>
function syncGoogleDrive() {
    const btn = document.getElementById('btn-sync-gdrive');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyegegrak...';

    const formData = new FormData();
    formData.append('csrf_token', '<?php echo $csrf_token; ?>');

    fetch('gdrive-sync.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (data.success) {
            alert('🎉 ' + data.message);
            location.reload();
        } else {
            alert('⚠️ Ralat: ' + data.message);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('⚠️ Ralat sambungan ke pelayan.');
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
