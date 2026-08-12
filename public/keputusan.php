<?php
/**
 * Pusat Perlawanan (Match Center) - Portal Awam SukanJTS Sarawak
 * Mempamerkan perlawanan dalam 3 kategori tab: Sedang Berlangsung (LIVE), Akan Datang, dan Selesai.
 */

$page_title = "Pusat Perlawanan (Match Center)";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';

// Kemaskini status perlawanan ke 'live' secara automatik apabila masa sudah masuk
if (function_exists('auto_update_match_statuses')) {
    auto_update_match_statuses($conn);
}

// Ambil parameter filter sukan & tab jika ada
$filter_sukan = isset($_GET['sukan_id']) && (int)$_GET['sukan_id'] > 0 ? (int)$_GET['sukan_id'] : null;
$req_tab      = isset($_GET['tab']) ? trim($_GET['tab']) : '';

// Dapatkan senarai sukan aktif untuk filter UI
$sukan_list = [];
$res_sukan = $conn->query("SELECT id, nama_sukan, ikon FROM tbl_sukan WHERE status = 'aktif' ORDER BY nama_sukan ASC");
if ($res_sukan && $res_sukan->num_rows > 0) {
    while ($s_row = $res_sukan->fetch_assoc()) {
        $sukan_list[] = $s_row;
    }
}

// Ambil semua perlawanan dan asingkan mengikut status di server
$query = "SELECT j.*, s.nama_sukan, s.kategori, s.jenis_perlawanan, 
                 pa.nama_pasukan AS nama_a, ba.nama_bahagian AS bhg_a, ba.logo_url AS logo_a,
                 pb.nama_pasukan AS nama_b, bb.nama_bahagian AS bhg_b, bb.logo_url AS logo_b,
                 v.nama_tempat,
                 k.skor_a, k.skor_b, k.jenis_pingat, k.catatan,
                 pw.nama_pasukan AS nama_w, bw.nama_bahagian AS bhg_w
          FROM tbl_jadual_perlawanan j
          JOIN tbl_sukan s ON j.sukan_id = s.id
          JOIN tbl_pasukan pa ON j.pasukan_a_id = pa.id
          JOIN tbl_bahagian ba ON pa.bahagian_id = ba.id
          LEFT JOIN tbl_pasukan pb ON j.pasukan_b_id = pb.id
          LEFT JOIN tbl_bahagian bb ON pb.bahagian_id = bb.id
          LEFT JOIN tbl_venue v ON j.venue_id = v.id
          LEFT JOIN tbl_keputusan k ON k.jadual_id = j.id
          LEFT JOIN tbl_pasukan pw ON k.pasukan_menang_id = pw.id
          LEFT JOIN tbl_bahagian bw ON pw.bahagian_id = bw.id
          ORDER BY j.tarikh ASC, j.masa ASC";
$result = $conn->query($query);

$matches_live = [];
$matches_next = [];
$matches_past = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] === 'selesai' || $row['skor_a'] !== null || $row['pasukan_menang_id'] !== null) {
            // Tapis mengikut sukan jika penapis diaktifkan
            if ($filter_sukan !== null && (int)$row['sukan_id'] !== $filter_sukan) {
                continue;
            }
            $matches_past[] = $row;
        } elseif ($row['status'] === 'live') {
            $matches_live[] = $row;
        } else {
            // Tapis mengikut sukan jika penapis diaktifkan
            if ($filter_sukan !== null && (int)$row['sukan_id'] !== $filter_sukan) {
                continue;
            }
            $matches_next[] = $row;
        }
    }
}

// Susun perlawanan selesai: paling terkini (tarikh DESC, masa DESC) di paling atas
usort($matches_past, function($a, $b) {
    $time_a = strtotime($a['tarikh'] . ' ' . $a['masa']);
    $time_b = strtotime($b['tarikh'] . ' ' . $b['masa']);
    return $time_b - $time_a;
});

// Tentukan tab mana yang patut aktif mengikut konteks
$active_tab = 'live';
if ($req_tab === 'next') {
    $active_tab = 'next';
} elseif ($req_tab === 'past' || ($filter_sukan !== null && $req_tab === '')) {
    $active_tab = ($req_tab === 'next') ? 'next' : ($filter_sukan !== null && $req_tab === 'next' ? 'next' : ($req_tab ? $req_tab : 'past'));
}
if ($filter_sukan !== null && !empty($req_tab)) {
    $active_tab = $req_tab;
} elseif ($filter_sukan !== null && empty($req_tab)) {
    $active_tab = 'next'; // Default ke perlawanan seterusnya jika ditapis dari tab seterusnya
}
?>

<!-- Header -->
<div class="py-4 bg-navy text-white text-center mb-5" style="background-color: var(--navy-blue); border-bottom: 4px solid var(--gold);">
    <div class="container">
        <h2 class="fw-bold mb-1">Match Center (Pusat Perlawanan)</h2>
        <p class="lead small mb-0">Skor perlawanan terkini secara langsung, fixture seterusnya dan rekod pemenang.</p>
    </div>
</div>

<div class="container">
    
    <!-- Tab Navigation Bootstrap -->
    <ul class="nav nav-pills justify-content-center mb-4 gap-2" id="matchTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link btn-outline-primary fw-semibold px-4 position-relative <?php echo ($active_tab === 'live') ? 'active' : ''; ?>" id="live-tab" data-bs-toggle="tab" data-bs-target="#live-content" type="button" role="tab" aria-controls="live-content" aria-selected="<?php echo ($active_tab === 'live') ? 'true' : 'false'; ?>">
                🔴 Sedang Berlangsung (LIVE)
                <?php if (count($matches_live) > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo count($matches_live); ?>
                    </span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link btn-outline-primary fw-semibold px-4 <?php echo ($active_tab === 'next') ? 'active' : ''; ?>" id="next-tab" data-bs-toggle="tab" data-bs-target="#next-content" type="button" role="tab" aria-controls="next-content" aria-selected="false">
                📅 Perlawanan Seterusnya
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link btn-outline-primary fw-semibold px-4 <?php echo ($active_tab === 'past') ? 'active' : ''; ?>" id="past-tab" data-bs-toggle="tab" data-bs-target="#past-content" type="button" role="tab" aria-controls="past-content" aria-selected="<?php echo ($active_tab === 'past') ? 'true' : 'false'; ?>">
                🏁 Perlawanan Selesai
                <?php if ($filter_sukan !== null): ?>
                    <span class="badge bg-warning text-dark ms-1">Telah Ditapis</span>
                <?php endif; ?>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="matchTabContent">
        <!-- ================= TAB 1: LIVE MATCHES ================= -->
        <div class="tab-pane fade <?php echo ($active_tab === 'live') ? 'show active' : ''; ?>" id="live-content" role="tabpanel" aria-labelledby="live-tab">
            <div class="row g-4 justify-content-center">
                <?php if (count($matches_live) > 0): ?>
                    <?php foreach ($matches_live as $row): ?>
                        <?php 
                        $display_a = $row['nama_a'] ?: $row['bhg_a'];
                        $display_b = $row['nama_b'] ?: ($row['bhg_b'] ?? 'TBD');
                        $logo_a = BASE_URL . 'assets/uploads/logo-bahagian/' . (!empty($row['logo_a']) ? $row['logo_a'] : 'default_logo.png');
                        $logo_b = BASE_URL . 'assets/uploads/logo-bahagian/' . (!empty($row['logo_b']) ? $row['logo_b'] : 'default_logo.png');
                        
                        $skor_a_val = ($row['skor_a'] !== null) ? (int)$row['skor_a'] : 0;
                        $skor_b_val = ($row['skor_b'] !== null) ? (int)$row['skor_b'] : 0;
                        ?>
                        <div class="col-md-6 col-lg-5">
                            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-center position-relative style-live-card">
                                <!-- Header Card -->
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                    <span class="badge bg-light text-dark border small fw-semibold px-3 py-1.5"><?php echo sanitize($row['nama_sukan']); ?></span>
                                    <span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-bold shadow-sm animate-pulse">
                                        <i class="bi bi-record-fill me-1"></i> LIVE
                                    </span>
                                </div>

                                <!-- Pusingan -->
                                <div class="small text-muted mb-3 fw-semibold"><?php echo sanitize($row['pusingan'] ?: 'Peringkat Kumpulan'); ?></div>

                                <!-- Pasukan & Skor -->
                                <div class="d-flex align-items-center justify-content-between my-3 px-1 gap-2">
                                    <!-- Pasukan A -->
                                    <div class="text-center flex-fill" style="flex: 1; min-width: 0;">
                                        <div class="d-flex align-items-center justify-content-center mx-auto mb-2" style="height: 60px;">
                                            <img src="<?php echo $logo_a; ?>" alt="" class="img-fluid" style="max-width: 55px; max-height: 55px; object-fit: contain;">
                                        </div>
                                        <span class="d-block fw-bold text-dark fs-6 lh-sm" title="<?php echo sanitize($display_a); ?>">
                                            <?php echo sanitize($display_a); ?>
                                        </span>
                                    </div>

                                    <!-- Skor Tengah (0 - 0) -->
                                    <div class="px-2 text-center flex-shrink-0">
                                        <div class="fs-2 fw-bold text-navy px-3 py-1 bg-light rounded-3 border shadow-sm d-inline-block" style="letter-spacing: 0.05em;">
                                            <span class="text-navy"><?php echo $skor_a_val; ?></span>
                                            <span class="text-muted opacity-50 mx-1.5">-</span>
                                            <span class="text-navy"><?php echo $skor_b_val; ?></span>
                                        </div>
                                    </div>

                                    <!-- Pasukan B -->
                                    <div class="text-center flex-fill" style="flex: 1; min-width: 0;">
                                        <div class="d-flex align-items-center justify-content-center mx-auto mb-2" style="height: 60px;">
                                            <img src="<?php echo $logo_b; ?>" alt="" class="img-fluid" style="max-width: 55px; max-height: 55px; object-fit: contain;">
                                        </div>
                                        <span class="d-block fw-bold text-dark fs-6 lh-sm" title="<?php echo sanitize($display_b); ?>">
                                            <?php echo sanitize($display_b); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Venue & Tarikh -->
                                <div class="text-muted small pt-3 border-top mt-3 d-flex align-items-center justify-content-center gap-3">
                                    <span><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo sanitize($row['nama_tempat'] ?: 'Stadium Perpaduan'); ?></span>
                                    <span><i class="bi bi-calendar-event me-1"></i> <?php echo format_date($row['tarikh']); ?></span>
                                </div>

                                <?php 
                                $yt_embed = !empty($row['youtube_url']) ? get_youtube_embed_url($row['youtube_url']) : null;
                                if ($yt_embed): 
                                ?>
                                    <div class="mt-3 pt-2">
                                        <button type="button" class="btn btn-danger btn-sm w-100 fw-bold rounded-pill shadow-sm animate-pulse"
                                                onclick="openYoutubeModal('<?php echo $yt_embed; ?>', '<?php echo sanitize(addslashes($display_a . ' vs ' . $display_b)); ?>')">
                                            <i class="bi bi-youtube me-1"></i> 📺 Tonton Siaran Langsung (YouTube Live)
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 text-muted">
                        <span class="fs-1 d-block mb-2">⚽</span>
                        Tiada perlawanan yang sedang berlangsung secara langsung (LIVE) pada masa ini.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ================= TAB 2: UPCOMING MATCHES ================= -->
        <div class="tab-pane fade <?php echo ($active_tab === 'next') ? 'show active' : ''; ?>" id="next-content" role="tabpanel" aria-labelledby="next-tab">
            <!-- Penapis Pills Sukan UI/UX -->
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <label class="form-label small text-muted fw-bold mb-0"><i class="bi bi-funnel-fill text-primary me-1"></i> Tapis Mengikut Acara Sukan:</label>
                    <?php if ($filter_sukan !== null): ?>
                        <a href="keputusan.php?tab=next" class="btn btn-xs btn-sm btn-outline-secondary rounded-pill">
                            <i class="bi bi-x-circle me-1"></i> Reset Penapis
                        </a>
                    <?php endif; ?>
                </div>
                <div class="d-flex flex-wrap gap-1.5 mt-2">
                    <a href="keputusan.php?tab=next" class="btn btn-sm <?php echo ($filter_sukan === null) ? 'btn-navy fw-bold shadow-sm' : 'btn-outline-secondary'; ?> rounded-pill px-3">
                        🏆 Semua Sukan
                    </a>
                    <?php foreach ($sukan_list as $sp): ?>
                        <a href="keputusan.php?tab=next&sukan_id=<?php echo $sp['id']; ?>" 
                           class="btn btn-sm <?php echo ($filter_sukan === (int)$sp['id']) ? 'btn-navy fw-bold shadow-sm' : 'btn-outline-secondary'; ?> rounded-pill px-3">
                            <i class="bi <?php echo sanitize($sp['ikon'] ?: 'bi-trophy-fill'); ?> me-1"></i> <?php echo sanitize($sp['nama_sukan']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="row g-4">
                <?php if (count($matches_next) > 0): ?>
                    <?php foreach ($matches_next as $row): ?>
                        <?php 
                        $display_a = $row['nama_a'] ?: $row['bhg_a'];
                        $display_b = $row['nama_b'] ?: ($row['bhg_b'] ?? 'TBD');
                        $logo_a = BASE_URL . 'assets/uploads/logo-bahagian/' . (!empty($row['logo_a']) ? $row['logo_a'] : 'default_logo.png');
                        $logo_b = BASE_URL . 'assets/uploads/logo-bahagian/' . (!empty($row['logo_b']) ? $row['logo_b'] : 'default_logo.png');
                        
                        $is_postponed = ($row['status'] === 'ditangguh');
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 text-center d-flex flex-column justify-content-between">
                                <div>
                                    <?php if ($is_postponed): ?>
                                        <span class="badge bg-warning text-dark mb-2">Ditangguh / Batal</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary mb-2">Akan Datang</span>
                                    <?php endif; ?>
                                    
                                    <div class="small text-muted mb-2"><?php echo sanitize($row['nama_sukan']); ?> (<?php echo sanitize(ucfirst($row['kategori'])); ?>)</div>
                                    
                                    <div class="d-flex align-items-center justify-content-center gap-3 my-3">
                                        <div class="text-center" style="width: 80px;">
                                            <img src="<?php echo $logo_a; ?>" alt="" class="img-fluid rounded border mb-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <span class="d-block small fw-bold text-dark text-truncate"><?php echo sanitize($display_a); ?></span>
                                        </div>
                                        
                                        <div class="fw-bold text-muted small">VS</div>
                                        
                                        <?php if ($row['pasukan_b_id'] !== null): ?>
                                            <div class="text-center" style="width: 80px;">
                                                <img src="<?php echo $logo_b; ?>" alt="" class="img-fluid rounded border mb-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                <span class="d-block small fw-bold text-dark text-truncate"><?php echo sanitize($display_b); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="small text-muted pt-2 border-top mt-2">
                                    <div class="fw-semibold text-navy"><i class="bi bi-geo-alt-fill me-1"></i> <?php echo sanitize($row['nama_tempat']); ?></div>
                                    <div><i class="bi bi-calendar-event me-1"></i> <?php echo format_date($row['tarikh']); ?> (<?php echo format_time($row['masa']); ?>)</div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 text-muted">Tiada jadual perlawanan akan datang didaftarkan.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ================= TAB 3: PAST MATCHES ================= -->
        <div class="tab-pane fade <?php echo ($active_tab === 'past') ? 'show active' : ''; ?>" id="past-content" role="tabpanel" aria-labelledby="past-tab">
            <!-- Penapis Pills Sukan UI/UX -->
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <label class="form-label small text-muted fw-bold mb-0"><i class="bi bi-funnel-fill text-primary me-1"></i> Tapis Mengikut Acara Sukan:</label>
                    <?php if ($filter_sukan !== null): ?>
                        <a href="keputusan.php?tab=past" class="btn btn-xs btn-sm btn-outline-secondary rounded-pill">
                            <i class="bi bi-x-circle me-1"></i> Reset Penapis
                        </a>
                    <?php endif; ?>
                </div>
                <div class="d-flex flex-wrap gap-1.5 mt-2">
                    <a href="keputusan.php?tab=past" class="btn btn-sm <?php echo ($filter_sukan === null) ? 'btn-navy fw-bold shadow-sm' : 'btn-outline-secondary'; ?> rounded-pill px-3">
                        🏆 Semua Sukan
                    </a>
                    <?php foreach ($sukan_list as $sp): ?>
                        <a href="keputusan.php?tab=past&sukan_id=<?php echo $sp['id']; ?>" 
                           class="btn btn-sm <?php echo ($filter_sukan === (int)$sp['id']) ? 'btn-navy fw-bold shadow-sm' : 'btn-outline-secondary'; ?> rounded-pill px-3">
                            <i class="bi <?php echo sanitize($sp['ikon'] ?: 'bi-trophy-fill'); ?> me-1"></i> <?php echo sanitize($sp['nama_sukan']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="row g-4">
                <?php if (count($matches_past) > 0): ?>
                    <?php foreach ($matches_past as $row): ?>
                        <?php 
                        $display_a = $row['nama_a'] ?: $row['bhg_a'];
                        $display_b = $row['nama_b'] ?: ($row['bhg_b'] ?? 'TBD');
                        
                        $logo_a = BASE_URL . 'assets/uploads/logo-bahagian/' . (!empty($row['logo_a']) ? $row['logo_a'] : 'default_logo.png');
                        $logo_b = BASE_URL . 'assets/uploads/logo-bahagian/' . (!empty($row['logo_b']) ? $row['logo_b'] : 'default_logo.png');

                        $pemenang_name = ($row['bhg_w']) ? ($row['nama_w'] ?: $row['bhg_w']) : null;
                        
                        $pingat_badge = '';
                        switch ($row['jenis_pingat']) {
                            case 'emas': $pingat_badge = '<span class="badge bg-warning text-dark"><i class="bi bi-award-fill"></i> EMAS</span>'; break;
                            case 'perak': $pingat_badge = '<span class="badge bg-secondary"><i class="bi bi-award-fill"></i> PERAK</span>'; break;
                            case 'gangsa': $pingat_badge = '<span class="badge bg-danger"><i class="bi bi-award-fill"></i> GANGSA</span>'; break;
                        }
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 text-center d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-success">Tamat</span>
                                        <?php echo $pingat_badge; ?>
                                    </div>
                                    
                                    <div class="small text-muted mb-2"><?php echo sanitize($row['nama_sukan']); ?> | <?php echo sanitize($row['pusingan'] ?: 'Selesai'); ?></div>
                                    
                                    <div class="d-flex align-items-center justify-content-center gap-3 my-2">
                                        <!-- Pasukan A -->
                                        <div class="text-center" style="width: 90px;">
                                            <img src="<?php echo $logo_a; ?>" alt="" class="img-fluid rounded border mb-2" style="width: 45px; height: 45px; object-fit: cover;">
                                            <span class="d-block small fw-bold text-dark text-truncate <?php echo ($pemenang_name === $display_a) ? 'text-primary' : ''; ?>"><?php echo sanitize($display_a); ?></span>
                                        </div>
                                        
                                        <!-- Skor Akhir -->
                                        <div>
                                            <span class="fs-4 fw-bold text-navy px-2 py-1 bg-light rounded border">
                                                <?php echo $row['skor_a'] !== null ? $row['skor_a'] : '0'; ?>
                                                <?php if ($row['pasukan_b_id'] !== null): ?>
                                                    -
                                                    <?php echo $row['skor_b'] !== null ? $row['skor_b'] : '0'; ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>

                                        <!-- Pasukan B -->
                                        <?php if ($row['pasukan_b_id'] !== null): ?>
                                            <div class="text-center" style="width: 90px;">
                                                <img src="<?php echo $logo_b; ?>" alt="" class="img-fluid rounded border mb-2" style="width: 45px; height: 45px; object-fit: cover;">
                                                <span class="d-block small fw-bold text-dark text-truncate <?php echo ($pemenang_name === $display_b) ? 'text-primary' : ''; ?>"><?php echo sanitize($display_b); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Pemenang -->
                                    <div class="my-2 py-1 bg-light rounded text-center small border">
                                        <?php if ($pemenang_name): ?>
                                            Pemenang: <strong class="text-primary"><i class="bi bi-trophy-fill text-warning me-1"></i> <?php echo sanitize($pemenang_name); ?></strong>
                                        <?php else: ?>
                                            Keputusan: <strong class="text-muted">Seri / Tiada Pemenang</strong>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="small text-muted pt-2 border-top mt-2">
                                    <?php if ($row['catatan']): ?>
                                        <div class="text-dark small mb-1"><em>*<?php echo sanitize($row['catatan']); ?></em></div>
                                    <?php endif; ?>
                                    <div><i class="bi bi-calendar-check me-1"></i> <?php echo format_date($row['tarikh']); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 text-muted">Tiada rekod perlawanan yang selesai ditemui.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- Modal YouTube Live Player -->
<div class="modal fade" id="youtubeLiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white fs-6" id="youtubeModalTitle"><i class="bi bi-youtube text-danger me-2"></i> Siaran Langsung</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="closeYoutubeModal()"></button>
            </div>
            <div class="modal-body p-2">
                <div class="ratio ratio-16x9 rounded-3 overflow-hidden">
                    <iframe id="youtubeIframe" src="" title="YouTube Live Stream" allowfullscreen allow="autoplay; encrypted-media"></iframe>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-outline-light px-4 ms-auto" data-bs-dismiss="modal" onclick="closeYoutubeModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function openYoutubeModal(embedUrl, title) {
    document.getElementById('youtubeModalTitle').innerHTML = '<i class="bi bi-youtube text-danger me-2"></i> ' + title;
    document.getElementById('youtubeIframe').src = embedUrl;
    const modal = new bootstrap.Modal(document.getElementById('youtubeLiveModal'));
    modal.show();
}

function closeYoutubeModal() {
    document.getElementById('youtubeIframe').src = '';
}

document.getElementById('youtubeLiveModal')?.addEventListener('hidden.bs.modal', function () {
    closeYoutubeModal();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
