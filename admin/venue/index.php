<?php
/**
 * Senarai Tempat Pertandingan (Venue) - Admin SukanJTS Sarawak
 * CRUD: Read & Delete UI
 */

$page_title = "Urus Venue";
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
        <h3 class="fw-bold text-dark mb-0">Pengurusan Lokasi / Venue Pertandingan</h3>
    </div>
    <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="create.php" class="btn btn-navy fw-medium">
            <i class="bi bi-plus-lg me-1"></i> Tambah Venue Baru
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
                    <th>Nama Tempat / Venue</th>
                    <th>Alamat</th>
                    <th>Koordinat (Lat / Long)</th>
                    <th>Kapasiti Penonton</th>
                    <th>Catatan</th>
                    <th style="width: 180px;" class="text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM tbl_venue ORDER BY nama_tempat ASC";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $coords = ($row['latitude'] !== null && $row['longitude'] !== null) 
                            ? sanitize($row['latitude'] . ', ' . $row['longitude'])
                            : 'Tiada';
                        ?>
                        <tr>
                            <td>
                                <strong class="text-dark"><?php echo sanitize($row['nama_tempat']); ?></strong>
                            </td>
                            <td class="small text-muted" style="max-width: 250px;"><?php echo sanitize($row['alamat'] ?: '-'); ?></td>
                            <td>
                                <code><?php echo $coords; ?></code>
                                <?php if ($coords !== 'Tiada'): ?>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($row['latitude'] . ',' . $row['longitude']); ?>" target="_blank" class="ms-1 text-decoration-none small" title="Peta Google"><i class="bi bi-box-arrow-up-right"></i> Map</a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['kapasiti'] !== null ? sanitize($row['kapasiti']) . ' orang' : '-'; ?></td>
                            <td class="small text-muted"><?php echo sanitize($row['catatan'] ?: '-'); ?></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info d-flex align-items-center gap-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form action="delete.php" method="POST" onsubmit="return confirm('Adakah anda pasti untuk memadam venue \'<?php echo addslashes($row['nama_tempat']); ?>\'? Tindakan ini akan gagal jika terdapat jadual perlawanan yang merujuk kepada venue ini.');">
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
                    echo "<tr><td colspan='6' class='text-center text-muted p-4'>Tiada venue pertandingan didaftarkan.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
