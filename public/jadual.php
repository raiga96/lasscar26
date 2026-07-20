<?php
/**
 * Jadual & Fixture Kejohanan - Portal Awam SukanJTS Sarawak
 * Menyediakan tapisan pencarian dinamik mengikut jenis sukan, tarikh, atau carian teks kontinjen.
 */

$page_title = "Jadual & Fixtures";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';

// Ambil parameter filter
$filter_sukan = isset($_GET['sukan_id']) && $_GET['sukan_id'] !== '' ? (int)$_GET['sukan_id'] : null;
$filter_tarikh = isset($_GET['tarikh']) && $_GET['tarikh'] !== '' ? $_GET['tarikh'] : null;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Bina Query Penapisan Dinamik (Prepared Statement)
$sql = "SELECT j.*, s.nama_sukan, s.kategori, s.jenis_perlawanan, 
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
        WHERE 1=1";

$params = [];
$types = "";

if ($filter_sukan !== null) {
    $sql .= " AND j.sukan_id = ?";
    $params[] = $filter_sukan;
    $types .= "i";
}

if ($filter_tarikh !== null) {
    $sql .= " AND j.tarikh = ?";
    $params[] = $filter_tarikh;
    $types .= "s";
}

if ($search_query !== '') {
    $sql .= " AND (ba.nama_bahagian LIKE ? OR bb.nama_bahagian LIKE ? OR pa.nama_pasukan LIKE ? OR pb.nama_pasukan LIKE ? OR j.pusingan LIKE ?)";
    $like_search = "%" . $search_query . "%";
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
    $types .= "sssss";
}

$sql .= " ORDER BY j.tarikh ASC, j.masa ASC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = null;
}
?>

<!-- Header -->
<div class="py-4 bg-navy text-white text-center mb-5" style="background-color: var(--navy-blue); border-bottom: 4px solid var(--gold);">
    <div class="container">
        <h2 class="fw-bold mb-1">Jadual & Fixtures Kejohanan</h2>
        <p class="lead small mb-0">Senarai penuh atur masa perlawanan dan lokasi pertandingan.</p>
    </div>
</div>

<div class="container">
    
    <!-- Borang Penapisan & Carian -->
    <div class="card card-admin p-4 mb-4 border shadow-sm">
        <form action="jadual.php" method="GET" class="row g-3 align-items-end">
            <!-- Tapis Sukan -->
            <div class="col-12 col-md-4">
                <label for="sukan_id" class="form-label small fw-semibold text-secondary">Acara Sukan</label>
                <select class="form-select" id="sukan_id" name="sukan_id">
                    <option value="">-- Semua Sukan --</option>
                    <?php
                    $res_s = $conn->query("SELECT id, nama_sukan, kategori FROM tbl_sukan WHERE status = 'aktif' ORDER BY nama_sukan ASC");
                    if ($res_s && $res_s->num_rows > 0) {
                        while ($row = $res_s->fetch_assoc()) {
                            $selected = ($filter_sukan === (int)$row['id']) ? 'selected' : '';
                            echo "<option value='" . $row['id'] . "' $selected>" . sanitize($row['nama_sukan']) . " (" . sanitize(ucwords($row['kategori'])) . ")</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <!-- Tapis Tarikh -->
            <div class="col-12 col-md-3">
                <label for="tarikh" class="form-label small fw-semibold text-secondary">Tarikh</label>
                <input type="date" class="form-control" id="tarikh" name="tarikh" value="<?php echo sanitize($filter_tarikh ?? ''); ?>">
            </div>

            <!-- Carian Kontinjen -->
            <div class="col-12 col-md-3">
                <label for="search" class="form-label small fw-semibold text-secondary">Cari Kontinjen / Pusingan</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Cth: Kuching / Akhir" value="<?php echo sanitize($search_query); ?>">
            </div>

            <!-- Butang Carian -->
            <div class="col-12 col-md-2 d-grid">
                <button type="submit" class="btn btn-navy"><i class="bi bi-filter"></i> Tapis & Cari</button>
            </div>
        </form>
        
        <?php if ($filter_sukan !== null || $filter_tarikh !== null || $search_query !== ''): ?>
            <div class="mt-3 text-start">
                <a href="jadual.php" class="text-decoration-none small text-danger"><i class="bi bi-x-circle"></i> Set Semula Tapisan</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Paparan Jadual -->
    <div class="card card-admin p-4 border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Sukan & Kategori</th>
                        <th>Perlawanan (Pasukan A vs Pasukan B)</th>
                        <th>Tempat / Venue</th>
                        <th>Tarikh & Masa</th>
                        <th>Pusingan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
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
                                case 'akan_datang': $status_badge = '<span class="badge bg-primary">Akan Datang</span>'; break;
                                case 'live': $status_badge = '<span class="badge badge-live-blink">LIVE</span>'; break;
                                case 'selesai': $status_badge = '<span class="badge bg-success">Selesai</span>'; break;
                                case 'ditangguh': $status_badge = '<span class="badge bg-warning text-dark">Ditangguh</span>'; break;
                            }
                            ?>
                            <tr>
                                <td>
                                    <strong class="text-dark"><?php echo sanitize($row['nama_sukan']); ?></strong>
                                    <div class="small text-muted"><?php echo sanitize(ucfirst($row['kategori'])); ?></div>
                                </td>
                                <td><?php echo $matchup; ?></td>
                                <td><span class="small fw-semibold text-navy"><i class="bi bi-geo-alt-fill"></i> <?php echo sanitize($row['nama_tempat']); ?></span></td>
                                <td>
                                    <div class="fw-semibold small"><?php echo format_date($row['tarikh']); ?></div>
                                    <div class="text-muted small"><?php echo format_time($row['masa']); ?></div>
                                </td>
                                <td><span class="badge bg-light text-secondary border"><?php echo sanitize($row['pusingan'] ?: 'Peringkat Kumpulan'); ?></span></td>
                                <td><?php echo $status_badge; ?></td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center text-muted p-4'>Tiada jadual perlawanan ditemui sepadan dengan tapisan anda.</td></tr>";
                    }
                    if ($stmt) $stmt->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
