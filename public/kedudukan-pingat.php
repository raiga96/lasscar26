<?php
/**
 * Papan Kedudukan Pingat (Medal Standing) - Portal Awam SukanJTS Sarawak
 * Memaparkan carta rasmi kutipan pingat emas, perak, dan gangsa Kontinjen secara masa nyata.
 */

$page_title = "Carta Kedudukan Pingat (Medal Standing)";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
?>

<!-- Header -->
<div class="py-4 bg-navy text-white text-center mb-5" style="background-color: var(--navy-blue); border-bottom: 4px solid var(--gold);">
    <div class="container">
        <h2 class="fw-bold mb-1">Carta Kedudukan Pingat Semasa</h2>
        <p class="lead small mb-0">Jumlah pingat terkumpul rasmi yang dimenangi kontinjen bertanding.</p>
    </div>
</div>

<div class="container">
    
    <!-- Info Peringatan Kecil -->
    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
        <span class="fs-4">📢</span>
        <div class="small">
            Kedudukan disusun mengikut jumlah kutipan <strong>Pingat Emas</strong>. Sekiranya seri, jumlah <strong>Pingat Perak</strong>, seterusnya <strong>Pingat Gangsa</strong> diambil kira untuk kedudukan Kontinjen.
        </div>
    </div>

    <!-- Papan Pingat Rasmi -->
    <div class="card card-admin p-4 border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center medal-table table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">Kedudukan</th>
                        <th class="text-start">Nama Kontinjen / Jabatan Bahagian</th>
                        <th style="width: 100px;">🥇 Emas (Gold)</th>
                        <th style="width: 100px;">🥈 Perak (Silver)</th>
                        <th style="width: 100px;">🥉 Gangsa (Bronze)</th>
                        <th style="width: 120px;">Jumlah Pingat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM vw_kedudukan_pingat";
                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        $rank = 1;
                        while ($row = $result->fetch_assoc()) {
                            $logo_path = BASE_URL . 'assets/uploads/logo-bahagian/' . ($row['logo_url'] ?: 'default_logo.png');
                            
                            // Highlight top 3 ranks
                            $rank_display = "<strong>" . $rank . "</strong>";
                            if ($rank === 1) {
                                $rank_display = "<span class='badge bg-warning text-dark fs-6 py-1 px-3 border border-warning'><i class='bi bi-trophy-fill'></i> 1</span>";
                            } elseif ($rank === 2) {
                                $rank_display = "<span class='badge bg-secondary fs-6 py-1 px-3 border border-secondary'><i class='bi bi-trophy-fill'></i> 2</span>";
                            } elseif ($rank === 3) {
                                $rank_display = "<span class='badge bg-danger bg-opacity-75 fs-6 py-1 px-3 border border-danger'><i class='bi bi-trophy-fill'></i> 3</span>";
                            }
                            $rank++;
                            ?>
                            <tr>
                                <td><?php echo $rank_display; ?></td>
                                <td class="text-start">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?php echo $logo_path; ?>" alt="" class="img-thumbnail" style="width: 45px; height: 45px; object-fit: cover;">
                                        <div>
                                            <strong class="text-dark fs-6"><?php echo sanitize($row['nama_bahagian']); ?></strong>
                                            <?php if ($row['jenis'] === 'jemputan'): ?>
                                                <span class="badge bg-light text-muted border small ms-1" style="font-size: 0.65rem;">Jemputan</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-bold text-dark fs-5 bg-warning bg-opacity-10"><?php echo $row['emas']; ?></td>
                                <td class="fw-bold text-dark fs-5 bg-secondary bg-opacity-10"><?php echo $row['perak']; ?></td>
                                <td class="fw-bold text-dark fs-5 bg-danger bg-opacity-10"><?php echo $row['gangsa']; ?></td>
                                <td class="fw-bold text-navy fs-5 bg-light"><?php echo $row['jumlah']; ?></td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center text-muted p-4'>Tiada kontinjen atau pingat direkodkan lagi.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
