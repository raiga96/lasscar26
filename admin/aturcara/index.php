<?php
/**
 * Senarai Aturcara Program - Admin SukanJTS Sarawak
 * CRUD: Read & Delete UI
 */

$page_title = "Urus Aturcara";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan super_admin atau editor sahaja boleh mengakses modul ini
confirm_access(['super_admin', 'editor']);

$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
?>

<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h3 class="fw-bold text-dark mb-0">Tentatif & Aturcara Majlis Kejohanan</h3>
    </div>
    <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="create.php" class="btn btn-navy fw-medium">
            <i class="bi bi-plus-lg me-1"></i> Tambah Aturcara Baru
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
                    <th style="width: 150px;">Jenis Majlis</th>
                    <th>Aktiviti / Agenda</th>
                    <th>Masa & Tarikh</th>
                    <th>Pegawai Bertanggungjawab</th>
                    <th style="width: 80px;" class="text-center">Susunan</th>
                    <th style="width: 180px;" class="text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM tbl_aturcara ORDER BY jenis ASC, tarikh ASC, susunan ASC, masa ASC";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $jenis_badge = '';
                        switch ($row['jenis']) {
                            case 'umum': $jenis_badge = '<span class="badge bg-secondary">Umum</span>'; break;
                            case 'pembukaan': $jenis_badge = '<span class="badge bg-primary">Perasmian Pembukaan</span>'; break;
                            case 'penutup': $jenis_badge = '<span class="badge bg-navy border border-secondary text-white">Makan Malam Penutupan</span>'; break;
                        }
                        ?>
                        <tr>
                            <td><?php echo $jenis_badge; ?></td>
                            <td>
                                <strong class="text-dark"><?php echo sanitize($row['aktiviti']); ?></strong>
                            </td>
                            <td>
                                <div class="fw-semibold small"><?php echo format_time($row['masa']); ?></div>
                                <div class="text-muted small"><?php echo format_date($row['tarikh']); ?></div>
                            </td>
                            <td><span class="small"><?php echo sanitize($row['pegawai_bertanggungjawab'] ?: '-'); ?></span></td>
                            <td class="text-center"><code><?php echo sanitize($row['susunan']); ?></code></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info d-flex align-items-center gap-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form action="delete.php" method="POST" onsubmit="return confirm('Adakah anda pasti untuk memadam aturcara \'<?php echo addslashes($row['aktiviti']); ?>\'?');">
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
                    echo "<tr><td colspan='6' class='text-center text-muted p-4'>Tiada aturcara majlis didaftarkan.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
