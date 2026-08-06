<?php
/**
 * Senarai Hero Banner - Admin SukanJTS Sarawak
 * CRUD: Read & Delete UI
 */

$page_title = "Hero Banner";
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
        <h3 class="fw-bold text-dark mb-1">Pengurusan Banner Utama (Hero Image)</h3>
        <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i> Spesifikasi cadangan imej: <strong>1920 x 800 piksel (Nisbah 16:9)</strong> untuk paparan sempurna.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="create.php" class="btn btn-navy fw-medium">
            <i class="bi bi-plus-lg me-1"></i> Tambah Banner Baru
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
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 150px;">Preview Banner</th>
                    <th>Tajuk Pengumuman / Tema</th>
                    <th>Pemenang Juara Utama</th>
                    <th>Status Aktif</th>
                    <th style="width: 80px;" class="text-center">Susunan</th>
                    <th style="width: 180px;" class="text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT hb.*, b.nama_bahagian 
                          FROM tbl_hero_banner hb
                          LEFT JOIN tbl_bahagian b ON hb.bahagian_juara_id = b.id
                          ORDER BY hb.susunan ASC, hb.dicipta_pada DESC";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $image_path = BASE_URL . 'assets/uploads/hero/' . $row['url_imej'];
                        
                        $status_badge = ($row['status_aktif'] === 'aktif') 
                            ? '<span class="badge bg-success">Aktif</span>' 
                            : '<span class="badge bg-secondary">Tidak Aktif</span>';
                        ?>
                        <tr>
                            <td>
                                <img src="<?php echo sanitize($image_path); ?>" class="img-thumbnail" style="width: 120px; height: 60px; object-fit: cover;" alt="">
                            </td>
                            <td>
                                <strong class="text-dark"><?php echo sanitize($row['tajuk']); ?></strong>
                            </td>
                            <td>
                                <?php if ($row['bahagian_juara_id']): ?>
                                    <span class="badge bg-gold text-dark fs-6"><i class="bi bi-trophy-fill me-1"></i> <?php echo sanitize($row['nama_bahagian']); ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">- Tiada -</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $status_badge; ?></td>
                            <td class="text-center"><code><?php echo sanitize($row['susunan']); ?></code></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info d-flex align-items-center gap-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form action="delete.php" method="POST" onsubmit="return confirm('Adakah anda pasti untuk memadam banner ini? Fail banner fizikal akan dipadam dari pelayan.');">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" title="Padam">
                                            <i class="bi bi-trash"></i> Padam
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center text-muted p-4'>Tiada banner iklan didaftarkan.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
