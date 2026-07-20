<?php
/**
 * Senarai Keputusan Perlawanan - Admin SukanJTS Sarawak
 * CRUD: Read UI & Link to edit results
 */

$page_title = "Urus Keputusan";
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
    <div class="col-sm-12">
        <h3 class="fw-bold text-dark mb-0">Pengurusan Keputusan & Skor Perlawanan</h3>
        <p class="text-muted small mb-0">Klik pada "Kemaskini" untuk merekodkan keputusan perlawanan, menentukan pemenang, dan mengurniakan pingat.</p>
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
                    <th>Perlawanan & Skor</th>
                    <th>Status</th>
                    <th>Pemenang</th>
                    <th>Pingat</th>
                    <th>Perekod</th>
                    <th style="width: 150px;" class="text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT j.id AS jadual_id, j.status AS j_status, j.pusingan,
                                 s.nama_sukan, s.kategori, s.jenis_perlawanan,
                                 pa.nama_pasukan AS nama_a, ba.nama_bahagian AS bhg_a,
                                 pb.nama_pasukan AS nama_b, bb.nama_bahagian AS bhg_b,
                                 k.skor_a, k.skor_b, k.jenis_pingat, k.catatan,
                                 pw.nama_pasukan AS nama_w, bw.nama_bahagian AS bhg_w,
                                 u.nama_penuh AS nama_perekod
                          FROM tbl_jadual_perlawanan j
                          JOIN tbl_sukan s ON j.sukan_id = s.id
                          JOIN tbl_pasukan pa ON j.pasukan_a_id = pa.id
                          JOIN tbl_bahagian ba ON pa.bahagian_id = ba.id
                          LEFT JOIN tbl_pasukan pb ON j.pasukan_b_id = pb.id
                          LEFT JOIN tbl_bahagian bb ON pb.bahagian_id = bb.id
                          LEFT JOIN tbl_keputusan k ON k.jadual_id = j.id
                          LEFT JOIN tbl_pasukan pw ON k.pasukan_menang_id = pw.id
                          LEFT JOIN tbl_bahagian bw ON pw.bahagian_id = bw.id
                          LEFT JOIN tbl_pengguna u ON k.direkod_oleh = u.id
                          ORDER BY j.status DESC, j.tarikh ASC, j.masa ASC";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $display_a = $row['nama_a'] ?: $row['bhg_a'];
                        $display_b = $row['nama_b'] ?: ($row['bhg_b'] ?? 'TBD');
                        
                        $score_display = '';
                        if ($row['j_status'] === 'akan_datang') {
                            $score_display = "<span class='text-muted small'>Akan Berlangsung</span>";
                        } else {
                            $sk_a = ($row['skor_a'] !== null) ? $row['skor_a'] : '0';
                            $sk_b = ($row['skor_b'] !== null) ? $row['skor_b'] : '0';
                            
                            if ($row['jenis_perlawanan'] === 'individu' && $row['pasukan_b_id'] === null) {
                                $score_display = "<span class='badge bg-light text-dark border p-2 fs-6'>" . sanitize($sk_a) . "</span>";
                            } else {
                                $score_display = "<span class='badge bg-light text-dark border p-2 fs-6'>" . sanitize($sk_a) . " - " . sanitize($sk_b) . "</span>";
                            }
                        }

                        $status_badge = '';
                        switch ($row['j_status']) {
                            case 'akan_datang': $status_badge = '<span class="badge bg-primary">Akan Datang</span>'; break;
                            case 'live': $status_badge = '<span class="badge badge-live">LIVE</span>'; break;
                            case 'selesai': $status_badge = '<span class="badge bg-success">Selesai</span>'; break;
                            case 'ditangguh': $status_badge = '<span class="badge bg-warning text-dark">Ditangguh</span>'; break;
                        }

                        $pemenang_display = ($row['bhg_w']) ? sanitize($row['nama_w'] ?: $row['bhg_w']) : '-';
                        if ($row['jenis_pingat'] === 'tiada' && $row['j_status'] === 'selesai' && !$row['bhg_w']) {
                            $pemenang_display = '<span class="text-muted small">Seri / Tiada</span>';
                        }

                        $pingat_badge = '-';
                        switch ($row['jenis_pingat']) {
                            case 'emas': $pingat_badge = '<span class="badge bg-warning text-dark"><i class="bi bi-award-fill"></i> EMAS</span>'; break;
                            case 'perak': $pingat_badge = '<span class="badge bg-secondary"><i class="bi bi-award-fill"></i> PERAK</span>'; break;
                            case 'gangsa': $pingat_badge = '<span class="badge bg-danger"><i class="bi bi-award-fill"></i> GANGSA</span>'; break;
                            case 'tiada': if ($row['j_status'] === 'selesai') $pingat_badge = '<span class="badge bg-light text-muted border">Tiada</span>'; break;
                        }
                        ?>
                        <tr>
                            <td>
                                <strong class="text-dark"><?php echo sanitize($row['nama_sukan']); ?></strong>
                                <div class="small text-muted"><?php echo sanitize($row['pusingan'] ?: 'Peringkat Kumpulan'); ?></div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div style="min-width: 150px;">
                                        <?php if ($row['jenis_perlawanan'] === 'individu' && $row['pasukan_b_id'] === null): ?>
                                            <span class="fw-semibold text-dark"><?php echo sanitize($display_a); ?></span>
                                        <?php else: ?>
                                            <span class="fw-semibold text-dark"><?php echo sanitize($display_a); ?></span>
                                            <span class="text-muted small px-1">vs</span>
                                            <span class="fw-semibold text-dark"><?php echo sanitize($display_b); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div><?php echo $score_display; ?></div>
                                </div>
                            </td>
                            <td><?php echo $status_badge; ?></td>
                            <td><strong class="text-dark"><?php echo $pemenang_display; ?></strong></td>
                            <td><?php echo $pingat_badge; ?></td>
                            <td><span class="small text-muted"><?php echo sanitize($row['nama_perekod'] ?: '-'); ?></span></td>
                            <td class="text-center">
                                <a href="edit.php?id=<?php echo $row['jadual_id']; ?>" class="btn btn-sm btn-navy d-flex align-items-center justify-content-center gap-1">
                                    <i class="bi bi-pencil-square"></i> Kemaskini
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center text-muted p-4'>Tiada keputusan perlawanan didaftarkan dalam sistem.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
