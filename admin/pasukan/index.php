<?php
/**
 * Urus Pendaftaran Pasukan - Admin SukanJTS Sarawak
 * CRUD: Read & Delete UI
 */

$page_title = "Pendaftaran Pasukan";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan hanya super_admin boleh mengakses modul ini
confirm_access(['super_admin']);

$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
?>

<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h3 class="fw-bold text-dark mb-0">Pendaftaran Kontinjen Ke Acara Sukan</h3>
    </div>
    <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="create.php" class="btn btn-navy fw-medium">
            <i class="bi bi-plus-lg me-1"></i> Daftarkan Pasukan Baru
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
                    <th>Kontinjen (Bahagian)</th>
                    <th>Acara Sukan</th>
                    <th>Nama Pasukan Perlawanan</th>
                    <th>Dicipta Pada</th>
                    <th style="width: 180px;" class="text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT p.*, b.nama_bahagian, b.logo_url, s.nama_sukan, s.kategori 
                          FROM tbl_pasukan p
                          JOIN tbl_bahagian b ON p.bahagian_id = b.id
                          JOIN tbl_sukan s ON p.sukan_id = s.id
                          ORDER BY s.nama_sukan ASC, b.nama_bahagian ASC";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $logo_path = BASE_URL . 'assets/uploads/logo-bahagian/' . ($row['logo_url'] ?: 'default_logo.png');
                        $display_team_name = $row['nama_pasukan'] ?: $row['nama_bahagian'];
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?php echo sanitize($logo_path); ?>" alt="" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                    <strong class="text-dark"><?php echo sanitize($row['nama_bahagian']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <strong class="text-primary"><?php echo sanitize($row['nama_sukan']); ?></strong> 
                                <span class="small text-muted">(<?php echo sanitize(ucfirst($row['kategori'])); ?>)</span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark"><?php echo sanitize($display_team_name); ?></span>
                                <?php if (empty($row['nama_pasukan'])): ?>
                                    <span class="badge bg-light text-muted small border">Sama Bahagian</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="small text-muted"><?php echo sanitize($row['dicipta_pada']); ?></span></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info d-flex align-items-center gap-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i> Nama
                                    </a>
                                    <form action="delete.php" method="POST" onsubmit="return confirm('Adakah anda pasti untuk membatalkan pendaftaran pasukan \'<?php echo addslashes($display_team_name); ?>\' bagi sukan \'<?php echo addslashes($row['nama_sukan']); ?>\'?');">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" title="Batal Pendaftaran">
                                            <i class="bi bi-x-circle"></i> Batal
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center text-muted p-4'>Tiada pendaftaran pasukan dalam sistem. Sila daftarkan pasukan baharu.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
