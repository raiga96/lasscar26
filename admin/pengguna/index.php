<?php
/**
 * Senarai Pentadbir Sistem - Admin SukanJTS Sarawak
 * CRUD: Read & Delete UI
 */

$page_title = "Urus Pentadbir";
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
        <h3 class="fw-bold text-dark mb-0">Pengurusan Akaun Pentadbir Sistem</h3>
    </div>
    <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="create.php" class="btn btn-navy fw-medium">
            <i class="bi bi-person-plus me-1"></i> Tambah Pentadbir Baru
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
                    <th>Nama Penuh</th>
                    <th>E-mel Rasmi</th>
                    <th>Peranan</th>
                    <th>Status Akaun</th>
                    <th>Tarikh Dicipta</th>
                    <th style="width: 180px;" class="text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM tbl_pengguna ORDER BY peranan ASC, nama_penuh ASC";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $is_self = ($row['id'] == $_SESSION['admin_id']);
                        
                        $peranan_badge = '';
                        switch ($row['peranan']) {
                            case 'super_admin': $peranan_badge = '<span class="badge bg-danger">Super Admin</span>'; break;
                            case 'editor': $peranan_badge = '<span class="badge bg-primary">Editor Modul</span>'; break;
                            case 'media': $peranan_badge = '<span class="badge bg-warning text-dark">Editor Media</span>'; break;
                        }
                        
                        $status_badge = ($row['status'] === 'aktif') 
                            ? '<span class="badge bg-success">Aktif</span>' 
                            : '<span class="badge bg-secondary">Kunci / Tidak Aktif</span>';
                        ?>
                        <tr>
                            <td>
                                <strong class="text-dark"><?php echo sanitize($row['nama_penuh']); ?></strong>
                                <?php if ($is_self): ?>
                                    <span class="badge bg-dark ms-1">Anda</span>
                                <?php endif; ?>
                            </td>
                            <td><code><?php echo sanitize($row['emel']); ?></code></td>
                            <td><?php echo $peranan_badge; ?></td>
                            <td><?php echo $status_badge; ?></td>
                            <td><span class="small text-muted"><?php echo sanitize($row['dicipta_pada']); ?></span></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info d-flex align-items-center gap-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <?php if (!$is_self): ?>
                                        <form action="delete.php" method="POST" onsubmit="return confirm('Adakah anda pasti untuk memadam akaun pentadbir \'<?php echo addslashes($row['nama_penuh']); ?>\'?');">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" title="Padam">
                                                <i class="bi bi-trash"></i> Padam
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" disabled title="Tidak boleh padam akaun sendiri">
                                            <i class="bi bi-trash"></i> Padam
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center text-muted p-4'>Tiada pengguna pentadbir ditemui.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
