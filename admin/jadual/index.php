<?php
/**
 * Senarai Jadual Perlawanan (Fixtures) - Admin SukanJTS Sarawak
 * CRUD: Read & Delete UI
 */

$page_title = "Jadual Perlawanan";
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
        <h3 class="fw-bold text-dark mb-0">Pengurusan Jadual Perlawanan (Fixtures)</h3>
    </div>
    <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="create.php" class="btn btn-navy fw-medium">
            <i class="bi bi-plus-lg me-1"></i> Cipta Jadual Baru
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
                    <th>Sukan</th>
                    <th>Perlawanan (Pasukan A vs B)</th>
                    <th>Venue</th>
                    <th>Tarikh & Masa</th>
                    <th>Pusingan</th>
                    <th>Status</th>
                    <th style="width: 180px;" class="text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT j.*, s.nama_sukan, s.kategori, s.jenis_perlawanan, 
                                 pa.nama_pasukan AS nama_a, ba.nama_bahagian AS bhg_a,
                                 pb.nama_pasukan AS nama_b, bb.nama_bahagian AS bhg_b,
                                 v.nama_tempat
                          FROM tbl_jadual_perlawanan j
                          JOIN tbl_sukan s ON j.sukan_id = s.id
                          JOIN tbl_pasukan pa ON j.pasukan_a_id = pa.id
                          JOIN tbl_bahagian ba ON pa.bahagian_id = ba.id
                          LEFT JOIN tbl_pasukan pb ON j.pasukan_b_id = pb.id
                          LEFT JOIN tbl_bahagian bb ON pb.bahagian_id = bb.id
                          JOIN tbl_venue v ON j.venue_id = v.id
                          ORDER BY j.tarikh ASC, j.masa ASC";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $display_a = $row['nama_a'] ?: $row['bhg_a'];
                        
                        if ($row['jenis_perlawanan'] === 'individu' && $row['pasukan_b_id'] === null) {
                            $matchup = "<strong class='text-dark'>" . sanitize($display_a) . "</strong> <span class='badge bg-light text-muted border'>Individu</span>";
                        } else {
                            $display_b = $row['nama_b'] ?: ($row['bhg_b'] ?? 'TBD');
                            $matchup = "<strong class='text-dark'>" . sanitize($display_a) . "</strong> <span class='text-muted small px-1'>vs</span> <strong class='text-dark'>" . sanitize($display_b) . "</strong>";
                        }

                        $status_badge = '';
                        switch ($row['status']) {
                            case 'akan_datang': 
                                $status_badge = '<span class="badge bg-primary">Akan Datang</span>'; 
                                break;
                            case 'live': 
                                $status_badge = '<span class="badge badge-live">LIVE</span>'; 
                                break;
                            case 'selesai': 
                                $status_badge = '<span class="badge bg-success">Selesai</span>'; 
                                break;
                            case 'ditangguh': 
                                $status_badge = '<span class="badge bg-warning text-dark">Ditangguh</span>'; 
                                break;
                        }
                        ?>
                        <tr>
                            <td>
                                <strong class="text-dark"><?php echo sanitize($row['nama_sukan']); ?></strong>
                                <div class="small text-muted"><?php echo sanitize(ucfirst($row['kategori'])); ?></div>
                            </td>
                            <td><?php echo $matchup; ?></td>
                            <td><span class="small fw-semibold"><?php echo sanitize($row['nama_tempat']); ?></span></td>
                            <td>
                                <div class="fw-semibold small"><?php echo format_date($row['tarikh']); ?></div>
                                <div class="text-muted small"><?php echo format_time($row['masa']); ?></div>
                            </td>
                            <td><span class="badge bg-light text-secondary border"><?php echo sanitize($row['pusingan'] ?: 'Peringkat Awal'); ?></span></td>
                            <td><?php echo $status_badge; ?></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info d-flex align-items-center gap-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form action="delete.php" method="POST" onsubmit="return confirm('Adakah anda pasti untuk memadam perlawanan ini? Semua keputusan berkaitan juga akan dipadam.');">
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
                    echo "<tr><td colspan='7' class='text-center text-muted p-4'>Tiada jadual perlawanan ditemui.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
