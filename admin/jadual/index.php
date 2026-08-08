<?php
/**
 * Senarai Jadual Perlawanan (Fixtures) - Admin SukanJTS Sarawak
 * Command Center 10 Sukan: Tab Status, Penapis Sukan, Status Toggle & Skor Live
 */

$page_title = "Jadual Perlawanan";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan super_admin atau editor sahaja boleh mengakses modul ini
confirm_access(['super_admin', 'editor']);

// Semak dan kemaskini status perlawanan ke 'live' secara automatik apabila masa sudah masuk
$auto_live_count = function_exists('auto_update_match_statuses') ? auto_update_match_statuses($conn) : 0;

// Kendali Penukaran Status Pantas via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_status') {
    $match_id   = (int)($_POST['match_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (verify_csrf_token($csrf_token) && $match_id > 0 && in_array($new_status, ['akan_datang', 'live', 'selesai', 'ditangguh'])) {
        $stmt_st = $conn->prepare("UPDATE tbl_jadual_perlawanan SET status = ? WHERE id = ?");
        if ($stmt_st) {
            $stmt_st->bind_param("si", $new_status, $match_id);
            $stmt_st->execute();
            $stmt_st->close();
            
            log_audit($conn, $_SESSION['admin_id'], 'update', 'tbl_jadual_perlawanan', $match_id, "Tukar status perlawanan ID $match_id ke $new_status");
            $_SESSION['success_msg'] = "Status perlawanan ID $match_id berjaya ditukar ke '" . strtoupper($new_status) . "'.";
        }
    }
    header("Location: index.php" . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
    exit;
}

$success_msg = $_SESSION['success_msg'] ?? '';
if ($auto_live_count > 0 && empty($success_msg)) {
    $success_msg = "Sebanyak $auto_live_count perlawanan telah bertukar status ke SEDANG BERLANGSUNG (LIVE) secara automatik.";
}
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// Parameter Penapis Status & Sukan
$filter_status = isset($_GET['status']) && in_array($_GET['status'], ['live', 'akan_datang', 'selesai', 'ditangguh']) ? $_GET['status'] : 'all';
$filter_sukan  = isset($_GET['sukan_id']) && (int)$_GET['sukan_id'] > 0 ? (int)$_GET['sukan_id'] : null;

// Kiraan Statistik Perlawanan untuk Top Banner Cards
$count_live = 0; $count_next = 0; $count_past = 0; $count_total = 0;
$res_counts = $conn->query("SELECT status, COUNT(*) as total FROM tbl_jadual_perlawanan GROUP BY status");
if ($res_counts) {
    while ($cnt = $res_counts->fetch_assoc()) {
        $count_total += (int)$cnt['total'];
        if ($cnt['status'] === 'live') $count_live = (int)$cnt['total'];
        elseif ($cnt['status'] === 'akan_datang') $count_next = (int)$cnt['total'];
        elseif ($cnt['status'] === 'selesai') $count_past = (int)$cnt['total'];
    }
}

// Ambil Senarai 10 Sukan untuk Penapis Pills
$sports_list = [];
$res_sports = $conn->query("SELECT id, nama_sukan, ikon FROM tbl_sukan WHERE status = 'aktif' ORDER BY nama_sukan ASC");
if ($res_sports) {
    while ($sp = $res_sports->fetch_assoc()) {
        $sports_list[] = $sp;
    }
}
?>

<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h3 class="fw-bold text-dark mb-0">Pengurusan Jadual Perlawanan (Fixtures)</h3>
        <p class="text-muted small mb-0">Pusat Kawalan 10 Sukan: Urus jadual, penukaran status pantas, dan keputusan perlawanan.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="create.php" class="btn btn-navy fw-medium shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Cipta Jadual Baru
        </a>
    </div>
</div>

<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
        🎉 <?php echo sanitize($success_msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
        ⚠️ <?php echo sanitize($error_msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- ================= 4 KAD STATISTIK RINGKAS ================= -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="index.php?status=live" class="card border-0 shadow-sm rounded-3 p-3 text-decoration-none bg-white border-start border-danger border-4 h-100 style-stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold">Sedang Berlangsung</div>
                    <div class="fs-3 fw-bold text-danger"><?php echo $count_live; ?></div>
                </div>
                <div class="fs-2 text-danger opacity-75"><i class="bi bi-broadcast"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="index.php?status=akan_datang" class="card border-0 shadow-sm rounded-3 p-3 text-decoration-none bg-white border-start border-primary border-4 h-100 style-stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold">Akan Datang</div>
                    <div class="fs-3 fw-bold text-primary"><?php echo $count_next; ?></div>
                </div>
                <div class="fs-2 text-primary opacity-75"><i class="bi bi-calendar-event"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="index.php?status=selesai" class="card border-0 shadow-sm rounded-3 p-3 text-decoration-none bg-white border-start border-success border-4 h-100 style-stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold">Selesai</div>
                    <div class="fs-3 fw-bold text-success"><?php echo $count_past; ?></div>
                </div>
                <div class="fs-2 text-success opacity-75"><i class="bi bi-check-circle-fill"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="index.php?status=all" class="card border-0 shadow-sm rounded-3 p-3 text-decoration-none bg-white border-start border-navy border-4 h-100 style-stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold">Jumlah Perlawanan</div>
                    <div class="fs-3 fw-bold text-navy"><?php echo $count_total; ?></div>
                </div>
                <div class="fs-2 text-navy opacity-75"><i class="bi bi-trophy-fill"></i></div>
            </div>
        </a>
    </div>
</div>

<!-- ================= NAV TABS MENGIKUT STATUS ================= -->
<div class="card card-admin p-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between border-bottom pb-3 mb-3 gap-2">
        <ul class="nav nav-pills gap-1">
            <li class="nav-item">
                <a class="nav-link <?php echo ($filter_status === 'all') ? 'active bg-navy' : 'text-dark border'; ?> fw-semibold small px-3" href="index.php?status=all<?php echo $filter_sukan ? '&sukan_id='.$filter_sukan : ''; ?>">
                    📋 Semua Jadual (<?php echo $count_total; ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($filter_status === 'live') ? 'active bg-danger' : 'text-dark border'; ?> fw-semibold small px-3" href="index.php?status=live<?php echo $filter_sukan ? '&sukan_id='.$filter_sukan : ''; ?>">
                    🔴 LIVE (<?php echo $count_live; ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($filter_status === 'akan_datang') ? 'active bg-primary' : 'text-dark border'; ?> fw-semibold small px-3" href="index.php?status=akan_datang<?php echo $filter_sukan ? '&sukan_id='.$filter_sukan : ''; ?>">
                    📅 Akan Datang (<?php echo $count_next; ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($filter_status === 'selesai') ? 'active bg-success' : 'text-dark border'; ?> fw-semibold small px-3" href="index.php?status=selesai<?php echo $filter_sukan ? '&sukan_id='.$filter_sukan : ''; ?>">
                    🏁 Selesai (<?php echo $count_past; ?>)
                </a>
            </li>
        </ul>

        <?php if ($filter_sukan || $filter_status !== 'all'): ?>
            <a href="index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Reset Penapis
            </a>
        <?php endif; ?>
    </div>

    <!-- ================= PENAPIS PILLS 10 SUKAN ================= -->
    <div class="mb-4">
        <label class="form-label small text-muted fw-bold mb-2"><i class="bi bi-funnel-fill me-1"></i> Tapis Mengikut Sukan:</label>
        <div class="d-flex flex-wrap gap-1">
            <a href="index.php?status=<?php echo $filter_status; ?>" class="btn btn-xs btn-sm <?php echo ($filter_sukan === null) ? 'btn-navy fw-bold' : 'btn-outline-secondary'; ?> rounded-pill px-3">
                Semua Sukan
            </a>
            <?php foreach ($sports_list as $sp): ?>
                <a href="index.php?status=<?php echo $filter_status; ?>&sukan_id=<?php echo $sp['id']; ?>" 
                   class="btn btn-xs btn-sm <?php echo ($filter_sukan === (int)$sp['id']) ? 'btn-navy fw-bold' : 'btn-outline-secondary'; ?> rounded-pill px-3">
                    <i class="bi <?php echo sanitize($sp['ikon'] ?: 'bi-trophy-fill'); ?> me-1"></i> <?php echo sanitize($sp['nama_sukan']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ================= JADUAL PERLAWANAN ================= -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Acara Sukan</th>
                    <th>Perlawanan & Skor</th>
                    <th>Venue</th>
                    <th>Tarikh & Masa</th>
                    <th>Status & Siaran</th>
                    <th style="width: 220px;" class="text-center">Tindakan Pantas</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT j.*, s.nama_sukan, s.kategori, s.jenis_perlawanan, 
                               pa.nama_pasukan AS nama_a, ba.nama_bahagian AS bhg_a, ba.logo_url AS logo_a,
                               pb.nama_pasukan AS nama_b, bb.nama_bahagian AS bhg_b, bb.logo_url AS logo_b,
                               v.nama_tempat,
                               k.skor_a, k.skor_b, k.pasukan_menang_id
                        FROM tbl_jadual_perlawanan j
                        JOIN tbl_sukan s ON j.sukan_id = s.id
                        JOIN tbl_pasukan pa ON j.pasukan_a_id = pa.id
                        JOIN tbl_bahagian ba ON pa.bahagian_id = ba.id
                        LEFT JOIN tbl_pasukan pb ON j.pasukan_b_id = pb.id
                        LEFT JOIN tbl_bahagian bb ON pb.bahagian_id = bb.id
                        JOIN tbl_venue v ON j.venue_id = v.id
                        LEFT JOIN tbl_keputusan k ON k.jadual_id = j.id";
                
                $where = [];
                $params = [];
                $types = "";

                if ($filter_status !== 'all') {
                    $where[] = "j.status = ?";
                    $params[] = $filter_status;
                    $types .= "s";
                }
                if ($filter_sukan !== null) {
                    $where[] = "j.sukan_id = ?";
                    $params[] = $filter_sukan;
                    $types .= "i";
                }

                if (!empty($where)) {
                    $sql .= " WHERE " . implode(" AND ", $where);
                }

                $sql .= " ORDER BY FIELD(j.status, 'live', 'akan_datang', 'selesai', 'ditangguh') ASC, j.tarikh ASC, j.masa ASC";

                $stmt = $conn->prepare($sql);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $display_a = $row['nama_a'] ?: $row['bhg_a'];
                        $display_b = $row['nama_b'] ?: ($row['bhg_b'] ?? 'TBD');
                        
                        $skor_a = ($row['skor_a'] !== null) ? (int)$row['skor_a'] : 0;
                        $skor_b = ($row['skor_b'] !== null) ? (int)$row['skor_b'] : 0;
                        
                        // Formatting Skor
                        $skor_badge = "";
                        if ($row['status'] === 'live') {
                            $skor_badge = "<span class='badge bg-danger fs-6 px-2.5 py-1 shadow-sm animate-pulse'>$skor_a - $skor_b</span>";
                        } elseif ($row['status'] === 'selesai') {
                            $skor_badge = "<span class='badge bg-navy fs-6 px-2.5 py-1'>$skor_a - $skor_b</span>";
                        } else {
                            $skor_badge = "<span class='badge bg-light text-muted border'>VS</span>";
                        }

                        // Formatting Status Badge
                        $status_badge = '';
                        switch ($row['status']) {
                            case 'akan_datang': 
                                $status_badge = '<span class="badge bg-primary">Akan Datang</span>'; 
                                break;
                            case 'live': 
                                $status_badge = '<span class="badge bg-danger text-white animate-pulse"><i class="bi bi-record-fill me-1"></i> LIVE</span>'; 
                                break;
                            case 'selesai': 
                                $status_badge = '<span class="badge bg-success">Selesai</span>'; 
                                break;
                            case 'ditangguh': 
                                $status_badge = '<span class="badge bg-warning text-dark">Ditangguh</span>'; 
                                break;
                        }

                        $yt_link = !empty($row['youtube_url']);
                        ?>
                        <tr>
                            <td>
                                <strong class="text-dark d-block"><?php echo sanitize($row['nama_sukan']); ?></strong>
                                <span class="badge bg-light text-secondary border extra-small"><?php echo sanitize($row['pusingan'] ?: 'Peringkat Kumpulan'); ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-dark"><?php echo sanitize($display_a); ?></span>
                                    <?php echo $skor_badge; ?>
                                    <span class="fw-bold text-dark"><?php echo sanitize($display_b); ?></span>
                                </div>
                            </td>
                            <td><span class="small fw-semibold text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?php echo sanitize($row['nama_tempat']); ?></span></td>
                            <td>
                                <div class="fw-semibold small"><?php echo format_date($row['tarikh']); ?></div>
                                <div class="text-muted small"><i class="bi bi-clock me-1"></i><?php echo format_time($row['masa']); ?></div>
                            </td>
                            <td>
                                <?php echo $status_badge; ?>
                                <?php if ($yt_link): ?>
                                    <div class="mt-1"><span class="badge bg-danger-subtle text-danger border border-danger-subtle small"><i class="bi bi-youtube me-1"></i> YouTube</span></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <!-- Butang Kemas Kini Skor/Pemenang -->
                                    <a href="../keputusan/create.php?jadual_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-gold d-flex align-items-center gap-1 px-2" title="Kemas Kini Skor">
                                        <i class="bi bi-trophy-fill"></i> Skor
                                    </a>

                                    <!-- Dropdown Tukar Status Pantas -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Tukar Status">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li><h6 class="dropdown-header">Tukar Status Pantas</h6></li>
                                            <li>
                                                <form action="index.php<?php echo $filter_status !== 'all' ? '?status='.$filter_status : ''; ?>" method="POST">
                                                    <input type="hidden" name="action" value="quick_status">
                                                    <input type="hidden" name="match_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="new_status" value="live">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <button type="submit" class="dropdown-item text-danger fw-semibold"><i class="bi bi-record-fill me-1"></i> Set ke LIVE</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="index.php<?php echo $filter_status !== 'all' ? '?status='.$filter_status : ''; ?>" method="POST">
                                                    <input type="hidden" name="action" value="quick_status">
                                                    <input type="hidden" name="match_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="new_status" value="selesai">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <button type="submit" class="dropdown-item text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Set ke Selesai</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="index.php<?php echo $filter_status !== 'all' ? '?status='.$filter_status : ''; ?>" method="POST">
                                                    <input type="hidden" name="action" value="quick_status">
                                                    <input type="hidden" name="match_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="new_status" value="akan_datang">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <button type="submit" class="dropdown-item text-primary"><i class="bi bi-calendar-event me-1"></i> Set ke Akan Datang</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Edit -->
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info p-1 px-2" title="Edit Jadual">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <!-- Delete -->
                                    <form action="delete.php" method="POST" onsubmit="return confirm('Adakah anda pasti untuk memadam jadual ini?');">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="Padam">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center text-muted p-5'><i class='bi bi-inbox fs-1 d-block mb-2 text-secondary'></i>Tiada jadual perlawanan ditemui bagi kriteria yang dipilih.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .style-stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .style-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.08) !important;
    }
</style>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
