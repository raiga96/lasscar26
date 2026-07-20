<?php
/**
 * Senarai Kontinjen & Bahagian - Admin SukanJTS Sarawak
 * CRUD: Read & Delete UI
 */

$page_title = "Urus Kontinjen";
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
        <h3 class="fw-bold text-dark mb-0">Pengurusan Kontinjen & Jabatan Jemputan</h3>
    </div>
    <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="create.php" class="btn btn-navy fw-medium">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kontinjen Baru
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
                    <th style="width: 80px;">Logo</th>
                    <th>Nama Bahagian / Jabatan</th>
                    <th>Singkatan</th>
                    <th>Jenis</th>
                    <th>Status</th>
                    <th style="width: 180px;" class="text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM tbl_bahagian ORDER BY jenis ASC, nama_bahagian ASC";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $logo_filename = $row['logo_url'] ?: 'default_logo.png';
                        $logo_path = BASE_URL . 'assets/uploads/logo-bahagian/' . $logo_filename;
                        
                        $jenis_badge = ($row['jenis'] === 'dalaman') 
                            ? '<span class="badge bg-primary">JTS Sarawak</span>' 
                            : '<span class="badge bg-secondary">Jemputan</span>';
                            
                        $status_badge = ($row['status'] === 'aktif') 
                            ? '<span class="badge bg-success">Aktif</span>' 
                            : '<span class="badge bg-danger">Tidak Aktif</span>';
                        ?>
                        <tr>
                            <td>
                                <img src="<?php echo sanitize($logo_path); ?>" alt="Logo <?php echo sanitize($row['nama_bahagian']); ?>" class="img-thumbnail rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td>
                                <strong class="text-dark"><?php echo sanitize($row['nama_bahagian']); ?></strong>
                            </td>
                            <td><code><?php echo sanitize($row['singkatan']); ?></code></td>
                            <td><?php echo $jenis_badge; ?></td>
                            <td><?php echo $status_badge; ?></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info d-flex align-items-center gap-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <!-- Form Delete POST -->
                                    <form action="delete.php" method="POST" onsubmit="return confirm('Adakah anda pasti untuk memadam kontinjen \'<?php echo addslashes($row['nama_bahagian']); ?>\'? Tindakan ini akan memadam semua pendaftaran pasukan yang berkaitan.');">
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
                    echo "<tr><td colspan='6' class='text-center text-muted p-4'>Tiada kontinjen berdaftar dalam sistem.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
