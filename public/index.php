<?php
/**
 * Laman Utama Portal Awam (Landing Page) - SukanJTS Sarawak
 * Memaparkan Hero Banner Dinamik (dengan Mod Juara Keseluruhan), Perutusan, Live Match, dan Top Standings.
 */

$page_title = "Laman Utama Kejohanan";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';

// A. Semak sama ada kejohanan tamat (Semua jadual berstatus 'selesai' ATAU 'ditangguh')
$res_check = $conn->query("SELECT COUNT(*) as total FROM tbl_jadual_perlawanan WHERE status IN ('akan_datang', 'live')");
$active_matches = $res_check ? $res_check->fetch_assoc()['total'] : 0;

$res_total = $conn->query("SELECT COUNT(*) as total FROM tbl_jadual_perlawanan");
$total_matches = $res_total ? $res_total->fetch_assoc()['total'] : 0;

// Kejohanan dikira selesai jika total matches > 0 dan tiada perlawanan aktif
$is_kejohanan_selesai = ($total_matches > 0 && $active_matches === 0);

// B. Ambil Kontinjen Juara Keseluruhan (Paling banyak Emas, diikuti Perak, Gangsa)
$juara_keseluruhan = null;
if ($is_kejohanan_selesai) {
    $res_juara = $conn->query("SELECT * FROM vw_kedudukan_pingat LIMIT 1");
    if ($res_juara && $res_juara->num_rows > 0) {
        $juara_keseluruhan = $res_juara->fetch_assoc();
    }
}

// C. Ambil banner aktif dari pangkalan data
$banners = [];
$res_banners = $conn->query("SELECT hb.*, b.nama_bahagian 
                             FROM tbl_hero_banner hb 
                             LEFT JOIN tbl_bahagian b ON hb.bahagian_juara_id = b.id 
                             WHERE hb.status_aktif = 'aktif' 
                             ORDER BY hb.susunan ASC");
if ($res_banners && $res_banners->num_rows > 0) {
    while ($row = $res_banners->fetch_assoc()) {
        $banners[] = $row;
    }
}
?>

<!-- ================= HERO CAROUSEL / JUARA BANNER ================= -->
<?php if ($is_kejohanan_selesai && $juara_keseluruhan): ?>
    <!-- MOD JUARA KESELURUHAN (Dinamik Automatik) -->
    <div class="py-5 text-white" style="background: linear-gradient(135deg, #020617 0%, #0a2540 60%, #1e3a5f 100%); border-bottom: 5px solid var(--gold);">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <span class="badge bg-warning text-dark fw-bold mb-3 px-3 py-2 fs-6 shadow-sm"><i class="bi bi-trophy-fill me-1"></i> KEPUTUSAN RASMI KEJOHANAN</span>
                    <h1 class="fw-bold display-4 text-white">Tahniah Kontinjen Juara!</h1>
                    <p class="fs-5 text-slate-300 mb-4 opacity-90">
                        Kejohanan Sukan Inter-Bahagian JTS Sarawak 2026 telah tamat dengan jayanya. Tahniah disampaikan kepada kontinjen juara keseluruhan atas pencapaian cemerlang!
                    </p>
                    
                    <!-- Kad Keputusan Juara (Glassmorphism) -->
                    <div class="champion-badge-card p-4 d-flex align-items-center gap-4 rounded-4 shadow-lg border border-warning border-opacity-50">
                        <div class="bg-white p-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 90px; height: 90px;">
                            <img src="<?php echo BASE_URL; ?>assets/uploads/logo-bahagian/<?php echo sanitize($juara_keseluruhan['logo_url'] ?: 'default_logo.png'); ?>" alt="" class="img-fluid" style="max-width: 75px; max-height: 75px; object-fit: contain;">
                        </div>
                        <div>
                            <h3 class="fw-bold text-white mb-2"><?php echo sanitize($juara_keseluruhan['nama_bahagian']); ?></h3>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="text-center bg-white bg-opacity-10 rounded-3 px-3 py-1.5 border border-white border-opacity-10">
                                    <span class="d-block extra-small text-gold fw-semibold">Emas</span>
                                    <strong class="fs-5 text-warning">🥇 <?php echo $juara_keseluruhan['emas']; ?></strong>
                                </div>
                                <div class="text-center bg-white bg-opacity-10 rounded-3 px-3 py-1.5 border border-white border-opacity-10">
                                    <span class="d-block extra-small text-light fw-semibold">Perak</span>
                                    <strong class="fs-5 text-secondary">🥈 <?php echo $juara_keseluruhan['perak']; ?></strong>
                                </div>
                                <div class="text-center bg-white bg-opacity-10 rounded-3 px-3 py-1.5 border border-white border-opacity-10">
                                    <span class="d-block extra-small text-light fw-semibold">Gangsa</span>
                                    <strong class="fs-5 text-warning-subtle">🥉 <?php echo $juara_keseluruhan['gangsa']; ?></strong>
                                </div>
                                <div class="text-center bg-gold text-navy rounded-3 px-3 py-1.5 fw-bold shadow-sm">
                                    <span class="d-block extra-small opacity-75">Jumlah</span>
                                    <strong class="fs-5"><?php echo $juara_keseluruhan['jumlah']; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-5 text-center">
                    <?php 
                    $juara_banner_img = null;
                    foreach ($banners as $b) {
                        if ($b['bahagian_juara_id'] == $juara_keseluruhan['bahagian_id']) {
                            $juara_banner_img = $b['url_imej'];
                            break;
                        }
                    }
                    if ($juara_banner_img):
                    ?>
                        <img src="<?php echo BASE_URL; ?>assets/uploads/hero/<?php echo sanitize($juara_banner_img); ?>" class="img-fluid rounded-4 shadow-lg border border-gold" style="max-height: 350px; object-fit: cover;" alt="Banner Juara">
                    <?php else: ?>
                        <div class="p-5 rounded-4 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-20 shadow-lg">
                            <span class="display-1 d-block text-warning mb-2" style="font-size: 6rem;">🏆</span>
                            <div class="h3 fw-bold text-white mb-0">JUARA KESELURUHAN</div>
                            <div class="text-gold fw-bold mt-1"><?php echo TOURNAMENT_TITLE; ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- MOD SLIDER BANNER / HERO HEBAT -->
    <?php if (count($banners) > 0): ?>
        <div id="heroCarousel" class="carousel slide hero-carousel shadow" data-bs-ride="carousel" data-bs-interval="4000">
            <?php if (count($banners) > 1): ?>
                <div class="carousel-indicators mb-3">
                    <?php foreach ($banners as $index => $banner): ?>
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                                class="<?php echo $index === 0 ? 'active' : ''; ?>" 
                                aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                aria-label="Slaid <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="carousel-inner">
                <?php foreach ($banners as $index => $banner): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>" data-bs-interval="4000">
                        <img src="<?php echo BASE_URL; ?>assets/uploads/hero/<?php echo $banner['url_imej']; ?>" class="d-block w-100" alt="<?php echo sanitize($banner['tajuk']); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($banners) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon p-3 bg-dark bg-opacity-50 rounded-circle" aria-hidden="true"></span>
                    <span class="visually-hidden">Sebelumnya</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon p-3 bg-dark bg-opacity-50 rounded-circle" aria-hidden="true"></span>
                    <span class="visually-hidden">Seterusnya</span>
                </button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Default Premium Hero Section -->
        <div class="py-5 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #04101e 0%, #0a2540 50%, #11345d 100%); border-bottom: 5px solid var(--gold);">
            <div class="container text-center py-5 position-relative z-1">
                <span class="badge bg-gold text-dark fs-6 py-2 px-3 fw-bold rounded-pill mb-3 shadow-sm"><i class="bi bi-fire me-1"></i> PORTAL RASMI SUKAN</span>
                <h1 class="fw-bold display-3 text-white tracking-tight mb-3"><?php echo TOURNAMENT_TITLE; ?></h1>
                <p class="fs-5 text-slate-300 col-md-8 mx-auto fw-medium opacity-90 mb-4"><?php echo TOURNAMENT_THEME; ?></p>
                
                <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap mt-2">
                    <span class="badge bg-white bg-opacity-10 backdrop-blur text-white border border-white border-opacity-20 fs-6 py-2.5 px-4 rounded-3 fw-semibold">
                        <i class="bi bi-calendar3 text-gold me-2"></i> <?php echo TOURNAMENT_DATE; ?>
                    </span>
                    <span class="badge bg-white bg-opacity-10 backdrop-blur text-white border border-white border-opacity-20 fs-6 py-2.5 px-4 rounded-3 fw-semibold">
                        <i class="bi bi-geo-alt-fill text-gold me-2"></i> <?php echo TOURNAMENT_LOCATION; ?>
                    </span>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- ================= PERUTUSAN PENGARAH / PENGERUSI ================= -->
<div class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-md-3 text-center">
                <div class="p-3 bg-light rounded-4 border shadow-sm d-inline-block">
                    <img src="../assets/dls.png" alt="" style="width: 250px; height: 520px; object-fit: contain;">
                </div>
                <h6 class="fw-bold text-dark mt-3 mb-0 fs-6"><?php echo CHAIRMAN_NAME; ?></h6>
                <p class="text-muted small mb-0"><?php echo CHAIRMAN_ROLE; ?></p>
            </div>
            <div class="col-md-9 border-start border-4 border-primary ps-md-4">
                <h4 class="fw-bold text-navy mb-2">Kata Alu-aluan Pengarah JTS</h4>
                <p class="fs-6 text-secondary mb-0 leading-relaxed" style="text-align: justify;">
                    "<?php echo CHAIRMAN_WELCOME_MESSAGE; ?>"
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ================= MATCH CENTER (LIVE / PERLAWANAN SEMASA) ================= -->
<div class="py-5" style="background-color: #f1f5f9;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-navy mb-1"><i class="bi bi-broadcast me-2 text-danger"></i>Match Center</h3>
                <p class="text-muted small mb-0">Perlawanan terkini dan keputusan perlawanan sukan secara masa-nyata.</p>
            </div>
            <a href="keputusan.php" class="btn btn-navy rounded-3 fw-semibold px-3 py-2 shadow-sm">
                Lihat Semua Skor <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        
        <div class="row g-4">
            <?php
            // Kemaskini status perlawanan ke 'live' secara automatik apabila masa sudah masuk
            if (function_exists('auto_update_match_statuses')) {
                auto_update_match_statuses($conn);
            }

            $query_matches = "
                SELECT j.id, j.status, j.tarikh, j.masa, j.pusingan, j.youtube_url,
                       s.nama_sukan, s.kategori, s.jenis_perlawanan,
                       pa.nama_pasukan AS nama_a, ba.nama_bahagian AS bhg_a, ba.logo_url AS logo_a,
                       pb.nama_pasukan AS nama_b, bb.nama_bahagian AS bhg_b, bb.logo_url AS logo_b,
                       k.skor_a, k.skor_b
                FROM tbl_jadual_perlawanan j
                JOIN tbl_sukan s ON j.sukan_id = s.id
                JOIN tbl_pasukan pa ON j.pasukan_a_id = pa.id
                JOIN tbl_bahagian ba ON pa.bahagian_id = ba.id
                LEFT JOIN tbl_pasukan pb ON j.pasukan_b_id = pb.id
                LEFT JOIN tbl_bahagian bb ON pb.bahagian_id = bb.id
                LEFT JOIN tbl_keputusan k ON k.jadual_id = j.id
                ORDER BY FIELD(j.status, 'live', 'akan_datang', 'selesai', 'ditangguh') ASC, j.tarikh ASC, j.masa ASC
                LIMIT 3";
            $res_m = $conn->query($query_matches);

            if ($res_m && $res_m->num_rows > 0):
                while ($row = $res_m->fetch_assoc()):
                    $display_a = $row['nama_a'] ?: $row['bhg_a'];
                    $display_b = $row['nama_b'] ?: ($row['bhg_b'] ?? 'TBD');
                    
                    $logo_a = BASE_URL . 'assets/uploads/logo-bahagian/' . (!empty($row['logo_a']) ? $row['logo_a'] : 'default_logo.png');
                    $logo_b = BASE_URL . 'assets/uploads/logo-bahagian/' . (!empty($row['logo_b']) ? $row['logo_b'] : 'default_logo.png');

                    $status_badge = '';
                    if ($row['status'] === 'live') {
                        $status_badge = '<span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-bold shadow-sm animate-pulse"><i class="bi bi-record-fill me-1"></i> LIVE</span>';
                    } elseif ($row['status'] === 'selesai') {
                        $status_badge = '<span class="badge bg-secondary text-white rounded-pill px-3 py-1 fw-semibold">Tamat</span>';
                    } else {
                        $status_badge = '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 fw-semibold">Akan Datang</span>';
                    }
                    ?>
                    <div class="col-md-4">
                        <div class="card card-hover-effect border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-center position-relative">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                <span class="badge bg-light text-dark border small fw-semibold"><?php echo sanitize($row['nama_sukan']); ?></span>
                                <?php echo $status_badge; ?>
                            </div>

                            <div class="small text-muted mb-2 fw-semibold"><?php echo sanitize($row['pusingan'] ?: 'Peringkat Kumpulan'); ?></div>
                            
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
                                
                                <!-- Skor / VS -->
                                <div class="px-2 text-center flex-shrink-0">
                                    <?php if ($row['status'] === 'akan_datang'): ?>
                                        <span class="fs-6 fw-bold text-muted d-block"><?php echo format_time($row['masa']); ?></span>
                                        <span class="badge bg-light text-secondary border extra-small mt-1">VS</span>
                                    <?php else: ?>
                                        <?php 
                                        $skor_a_val = ($row['skor_a'] !== null) ? (int)$row['skor_a'] : 0;
                                        $skor_b_val = ($row['skor_b'] !== null) ? (int)$row['skor_b'] : 0;
                                        ?>
                                        <div class="fs-2 fw-bold text-navy px-3 py-1 bg-light rounded-3 border shadow-sm d-inline-block" style="letter-spacing: 0.05em;">
                                            <span class="text-navy"><?php echo $skor_a_val; ?></span>
                                            <span class="text-muted opacity-50 mx-1.5">-</span>
                                            <span class="text-navy"><?php echo $skor_b_val; ?></span>
                                        </div>
                                    <?php endif; ?>
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
                            
                            <!-- Tarikh & Masa -->
                            <div class="small text-muted pt-2 border-top mt-2">
                                <i class="bi bi-calendar-event me-1"></i> <?php echo format_date($row['tarikh']); ?>
                            </div>

                            <?php 
                            $yt_embed = !empty($row['youtube_url']) ? get_youtube_embed_url($row['youtube_url']) : null;
                            if ($yt_embed): 
                            ?>
                                <div class="mt-3 pt-2">
                                    <button type="button" class="btn btn-danger btn-sm w-100 fw-bold rounded-pill shadow-sm animate-pulse"
                                            onclick="openYoutubeModal('<?php echo $yt_embed; ?>', '<?php echo sanitize(addslashes($display_a . ' vs ' . $display_b)); ?>')">
                                        <i class="bi bi-youtube me-1"></i> 📺 YouTube Live
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                endwhile;
            else: 
            ?>
                <div class="col-12 text-center text-muted p-4">Tiada maklumat perlawanan masa nyata didaftarkan.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ================= MEDAL STANDINGS (TOP 5) ================= -->
<div class="py-5 bg-white">
    <div class="container">
        <div class="row g-4">
            <!-- Papan Pingat Teratas -->
            <div class="col-lg-7">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fw-bold text-navy mb-0"><i class="bi bi-trophy-fill me-2 text-warning"></i>Kedudukan Pingat Teratas</h3>
                    <a href="kedudukan-pingat.php" class="btn btn-sm btn-outline-navy fw-semibold rounded-3">Papar Penuh</a>
                </div>
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table medal-table align-middle text-center mb-0 bg-white">
                            <thead class="table-dark bg-navy text-white">
                                <tr>
                                    <th style="width: 60px;">Ked.</th>
                                    <th class="text-start ps-3">Kontinjen / Bahagian</th>
                                    <th style="width: 60px;">🥇 E</th>
                                    <th style="width: 60px;">🥈 P</th>
                                    <th style="width: 60px;">🥉 G</th>
                                    <th style="width: 80px;">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $res_medal = $conn->query("SELECT * FROM vw_kedudukan_pingat LIMIT 5");
                                if ($res_medal && $res_medal->num_rows > 0) {
                                    $rank = 1;
                                    while ($row = $res_medal->fetch_assoc()) {
                                        $logo = BASE_URL . 'assets/uploads/logo-bahagian/' . ($row['logo_url'] ?: 'default_logo.png');
                                        ?>
                                        <tr>
                                            <td><strong><?php echo $rank++; ?></strong></td>
                                            <td class="text-start ps-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?php echo $logo; ?>" alt="" style="width: 32px; height: 32px; object-fit: contain;">
                                                    <span class="fw-semibold text-dark"><?php echo sanitize($row['nama_bahagian']); ?></span>
                                                </div>
                                            </td>
                                            <td class="fw-bold text-dark"><?php echo $row['emas']; ?></td>
                                            <td class="fw-bold text-dark"><?php echo $row['perak']; ?></td>
                                            <td class="fw-bold text-dark"><?php echo $row['gangsa']; ?></td>
                                            <td class="fw-bold text-navy"><?php echo $row['jumlah']; ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center text-muted p-4'>Tiada data kutipan pingat.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Bahagian/Jabatan Mengambil Bahagian (Statistik & Highlight) -->
            <div class="col-lg-5">
                <h3 class="fw-bold text-navy mb-3"><i class="bi bi-people-fill me-2 text-primary"></i>Kategori Penyertaan</h3>
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
                    <p class="small text-muted mb-4" style="text-align: justify;">
                        Kejohanan ini mengumpulkan kontinjen rasmi Pejabat Bahagian Jabatan Tanah dan Survei seluruh negeri Sarawak bersama agensi luar dan jabatan kerajaan yang dijemput.
                    </p>
                    
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center justify-content-between bg-white p-3 rounded-3 shadow-sm border-start border-primary border-4">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Pejabat Bahagian JTS</h6>
                                <span class="small text-muted">Ibu Pejabat & 11 Pejabat Bahagian</span>
                            </div>
                            <span class="badge bg-primary fs-5 px-3 py-2 rounded-3">12</span>
                        </div>
                        
                        <div class="d-flex align-items-center justify-content-between bg-white p-3 rounded-3 shadow-sm border-start border-secondary border-4">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Jabatan Jemputan Luar</h6>
                                <span class="small text-muted">Agensi luar, PBT, agensi kerajaan</span>
                            </div>
                            <?php
                            $res_j = $conn->query("SELECT COUNT(*) as total FROM tbl_bahagian WHERE jenis = 'jemputan' AND status = 'aktif'");
                            $total_jemputan = $res_j ? $res_j->fetch_assoc()['total'] : 0;
                            ?>
                            <span class="badge bg-secondary fs-5 px-3 py-2 rounded-3"><?php echo $total_jemputan; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= GALERI HIGHLIGHTS ================= -->
<div class="py-5 bg-light border-top">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-navy mb-1"><i class="bi bi-images me-2 text-info"></i>Imbasan Foto Kejohanan</h3>
                <p class="text-muted small mb-0">Koleksi gambar menarik aksi dan kejayaan para atlet.</p>
            </div>
            <a href="galeri.php" class="btn btn-sm btn-navy fw-semibold rounded-3 px-3 py-2">Lihat Galeri Penuh</a>
        </div>
        
        <div class="row g-3">
            <?php
            $query_g = "SELECT * FROM tbl_galeri WHERE jenis_fail = 'imej' ORDER BY dicipta_pada DESC LIMIT 4";
            $res_g = $conn->query($query_g);

            if ($res_g && $res_g->num_rows > 0):
                while ($row = $res_g->fetch_assoc()):
                    if (!empty($row['is_gdrive'])) {
                        $img_url = "https://lh3.googleusercontent.com/d/" . $row['gdrive_file_id'] . "=w800";
                        $gdrive_link = $row['gdrive_view_url'] ?: "https://drive.google.com/file/d/" . $row['gdrive_file_id'] . "/view";
                    } else {
                        $img_url = BASE_URL . 'assets/uploads/galeri/' . $row['url_fail'];
                        $gdrive_link = BASE_URL . 'public/galeri.php';
                    }
                    ?>
                    <div class="col-6 col-md-3">
                        <a href="<?php echo sanitize($gdrive_link); ?>" target="_blank" class="text-decoration-none">
                            <div class="gallery-grid-item position-relative bg-dark rounded-4 overflow-hidden shadow-sm card-hover-effect" style="height: 220px; cursor: pointer;">
                                <img src="<?php echo sanitize($img_url); ?>" loading="lazy" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo sanitize($row['tajuk']); ?>"
                                     onerror="this.onerror=null; this.src='<?php echo BASE_URL . 'public/gdrive-image.php?id=' . ($row['gdrive_file_id'] ?? ''); ?>'">
                                <div class="position-absolute bottom-0 start-0 m-3 z-1">
                                    <span class="badge bg-navy text-white rounded-pill px-3 py-1 fs-7 shadow-sm"><?php echo sanitize($row['album'] ?: 'Umum'); ?></span>
                                </div>
                                <div class="position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-40 d-flex align-items-center justify-content-center opacity-0 hover-overlay-show" style="transition: opacity 0.2s;">
                                    <span class="text-white fw-medium small bg-dark bg-opacity-75 px-3 py-1 rounded-pill"><i class="bi bi-box-arrow-up-right me-1"></i> Buka Foto</span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php 
                endwhile;
            else: 
            ?>
                <div class="col-12 text-center text-muted p-4">Tiada media galeri untuk dipaparkan.</div>
            <?php endif; ?>
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

// Inisialisasi Auto-cycle Hero Banner Carousel
document.addEventListener("DOMContentLoaded", function() {
    const heroCarousel = document.getElementById('heroCarousel');
    if (heroCarousel && typeof bootstrap !== 'undefined') {
        new bootstrap.Carousel(heroCarousel, {
            interval: 4000,
            ride: 'carousel',
            wrap: true,
            touch: true
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
