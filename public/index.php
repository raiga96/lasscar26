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
    // Ambil baris pertama dari view kedudukan pingat
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
    <div class="py-5 text-white" style="background: linear-gradient(135deg, #061727 0%, #0a2540 100%); border-bottom: 5px solid var(--gold);">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <span class="badge bg-warning text-dark fw-bold mb-3 px-3 py-2 fs-6">🏆 KEPUTUSAN RASMI KEJOHANAN</span>
                    <h1 class="fw-bold display-4 text-white">Tahniah Kontinjen Juara!</h1>
                    <p class="fs-5 text-muted mb-4">
                        Sukan Inter-Bahagian JTS Sarawak 2026 telah berakhir secara rasminya. Ucapan sekalung tahniah diucapkan kepada kontinjen juara keseluruhan atas kutipan pingat emas terbanyak!
                    </p>
                    
                    <!-- Kad Keputusan Juara -->
                    <div class="champion-badge-card p-4 d-flex align-items-center gap-4">
                        <img src="<?php echo BASE_URL; ?>assets/uploads/logo-bahagian/<?php echo sanitize($juara_keseluruhan['logo_url'] ?: 'default_logo.png'); ?>" alt="" class="img-fluid rounded bg-white p-2" style="width: 100px; height: 100px; object-fit: cover;">
                        <div>
                            <h3 class="fw-bold text-white mb-2"><?php echo sanitize($juara_keseluruhan['nama_bahagian']); ?></h3>
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-center bg-white bg-opacity-10 rounded px-3 py-1">
                                    <span class="d-block small text-muted">Emas</span>
                                    <strong class="fs-5 text-warning">🥇 <?php echo $juara_keseluruhan['emas']; ?></strong>
                                </div>
                                <div class="text-center bg-white bg-opacity-10 rounded px-3 py-1">
                                    <span class="d-block small text-muted">Perak</span>
                                    <strong class="fs-5 text-secondary">🥈 <?php echo $juara_keseluruhan['perak']; ?></strong>
                                </div>
                                <div class="text-center bg-white bg-opacity-10 rounded px-3 py-1">
                                    <span class="d-block small text-muted">Gangsa</span>
                                    <strong class="fs-5 text-light">🥉 <?php echo $juara_keseluruhan['gangsa']; ?></strong>
                                </div>
                                <div class="text-center bg-white bg-opacity-10 rounded px-3 py-1">
                                    <span class="d-block small text-muted">Jumlah</span>
                                    <strong class="fs-5 text-white"><?php echo $juara_keseluruhan['jumlah']; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-5 text-center">
                    <!-- Preview Banner Juara Utama jika ada upload dari Admin -->
                    <?php 
                    // Cari banner yang di-tag juara
                    $juara_banner_img = null;
                    foreach ($banners as $b) {
                        if ($b['bahagian_juara_id'] == $juara_keseluruhan['bahagian_id']) {
                            $juara_banner_img = $b['url_imej'];
                            break;
                        }
                    }
                    if ($juara_banner_img):
                    ?>
                        <img src="<?php echo BASE_URL; ?>assets/uploads/hero/<?php echo sanitize($juara_banner_img); ?>" class="img-fluid rounded-4 shadow-lg border border-secondary border-opacity-50" style="max-height: 350px; object-fit: cover;" alt="Banner Juara">
                    <?php else: ?>
                        <span class="display-1 d-block text-warning mb-2" style="font-size: 7rem;">🏆</span>
                        <div class="h3 fw-bold text-white mb-0">Juara Keseluruhan</div>
                        <div class="text-gold fw-bold"><?php echo TOURNAMENT_TITLE; ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- MOD BIASA / SLIDER BANNER AKTIF -->
    <?php if (count($banners) > 0): ?>
        <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
            <div class="carousel-inner h-100">
                <?php foreach ($banners as $index => $banner): ?>
                    <div class="carousel-item h-100 <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo BASE_URL; ?>assets/uploads/hero/<?php echo $banner['url_imej']; ?>" class="d-block w-100 h-100" alt="">
                        <div class="carousel-caption d-none d-md-flex align-items-center h-100 justify-content-center">
                            <div class="hero-overlay-glass">
                                <h1><?php echo sanitize($banner['tajuk']); ?></h1>
                                <p class="mb-0 mt-2"><?php echo TOURNAMENT_TITLE; ?></p>
                                <span class="badge bg-gold text-dark mt-3 fw-bold"><?php echo TOURNAMENT_DATE; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($banners) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Sebelumnya</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Seterusnya</span>
                </button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Default Hero Static jika tiada banner dimuat naik -->
        <div class="py-5 text-white" style="background: linear-gradient(135deg, #0a2540 0%, #1e3a5f 100%); border-bottom: 5px solid var(--gold);">
            <div class="container text-center py-5">
                <span class="fs-1 d-block mb-3">🏆</span>
                <h1 class="fw-bold display-4"><?php echo TOURNAMENT_TITLE; ?></h1>
                <p class="fs-5 text-muted col-md-8 mx-auto"><?php echo TOURNAMENT_THEME; ?></p>
                <div class="mt-4">
                    <span class="badge bg-gold text-dark fs-6 py-2 px-3 fw-semibold"><i class="bi bi-calendar3 me-1"></i> <?php echo TOURNAMENT_DATE; ?></span>
                    <span class="badge bg-white bg-opacity-10 text-white fs-6 py-2 px-3 ms-2 fw-semibold"><i class="bi bi-geo-alt me-1"></i> <?php echo TOURNAMENT_LOCATION; ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- ================= PERUTUSAN PENGARAH / PENGERUSI ================= -->
<div class="py-5 bg-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-md-3 text-center">
                <div class="bg-light p-3 rounded-circle d-inline-block border">
                    <span style="font-size: 5rem;">👤</span>
                </div>
                <h6 class="fw-bold text-dark mt-3 mb-0"><?php echo CHAIRMAN_NAME; ?></h6>
                <p class="text-muted small mb-0"><?php echo CHAIRMAN_ROLE; ?></p>
            </div>
            <div class="col-md-9 border-start border-3 border-primary ps-md-5">
                <h4 class="fw-bold text-navy mb-3">Kata Alu-aluan Pengarah</h4>
                <p class="fs-6 text-secondary mb-0" style="text-align: justify; line-height: 1.8;">
                    "<?php echo CHAIRMAN_WELCOME_MESSAGE; ?>"
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ================= MATCH CENTER (LIVE / PERLAWANAN SEMASA) ================= -->
<div class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="section-title mb-0">Match Center (Perlawanan Terkini)</h3>
            <a href="keputusan.php" class="btn btn-sm btn-navy fw-medium">Lihat Semua Skor</a>
        </div>
        
        <div class="row g-3">
            <?php
            // Ambil 3 perlawanan paling aktif (utamakan LIVE, kemudian selesai yang terbaharu)
            $query_matches = "
                SELECT j.id, j.status, j.tarikh, j.masa, j.pusingan,
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
                    
                    $logo_a = BASE_URL . 'assets/uploads/logo-bahagian/' . ($row['logo_a'] ?: 'default_logo.png');
                    $logo_b = BASE_URL . 'assets/uploads/logo-bahagian/' . ($row['logo_b'] ?: 'default_logo.png');

                    $status_badge = '';
                    if ($row['status'] === 'live') {
                        $status_badge = '<span class="badge badge-live-blink mb-2">LIVE</span>';
                    } elseif ($row['status'] === 'selesai') {
                        $status_badge = '<span class="badge bg-success mb-2">Tamat</span>';
                    } else {
                        $status_badge = '<span class="badge bg-primary mb-2">Akan Datang</span>';
                    }
                    ?>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-3 p-4 bg-white h-100 text-center">
                            <?php echo $status_badge; ?>
                            <div class="small text-muted mb-2"><?php echo sanitize($row['nama_sukan']); ?> | <?php echo sanitize($row['pusingan'] ?: 'Peringkat Kumpulan'); ?></div>
                            
                            <div class="d-flex align-items-center justify-content-center gap-3 my-3">
                                <!-- Pasukan A -->
                                <div class="text-center" style="width: 100px;">
                                    <img src="<?php echo $logo_a; ?>" alt="" class="img-fluid rounded mb-2 border p-1 bg-white" style="width: 50px; height: 50px; object-fit: cover;">
                                    <span class="d-block small fw-bold text-dark text-truncate"><?php echo sanitize($display_a); ?></span>
                                </div>
                                
                                <!-- Skor / VS -->
                                <div class="px-2">
                                    <?php if ($row['status'] === 'akan_datang'): ?>
                                        <span class="fs-6 fw-bold text-muted"><?php echo format_time($row['masa']); ?></span>
                                    <?php else: ?>
                                        <span class="fs-3 fw-bold text-navy">
                                            <?php echo ($row['skor_a'] !== null) ? $row['skor_a'] : '0'; ?>
                                            <?php if ($row['pasukan_b_id'] !== null): ?>
                                                -
                                                <?php echo ($row['skor_b'] !== null) ? $row['skor_b'] : '0'; ?>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Pasukan B -->
                                <?php if ($row['pasukan_b_id'] !== null): ?>
                                    <div class="text-center" style="width: 100px;">
                                        <img src="<?php echo $logo_b; ?>" alt="" class="img-fluid rounded mb-2 border p-1 bg-white" style="width: 50px; height: 50px; object-fit: cover;">
                                        <span class="d-block small fw-bold text-dark text-truncate"><?php echo sanitize($display_b); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="small text-muted pt-2 border-top mt-2">
                                <i class="bi bi-clock me-1"></i> <?php echo format_date($row['tarikh']); ?>
                            </div>
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
                    <h3 class="section-title mb-0">Pungutan Pingat</h3>
                    <a href="kedudukan-pingat.php" class="btn btn-sm btn-navy fw-medium">Papar Penuh</a>
                </div>
                <div class="table-responsive">
                    <table class="table medal-table table-striped align-middle text-center mb-0 bg-white">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Ked.</th>
                                <th class="text-start">Kontinjen / Bahagian</th>
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
                                        <td class="text-start">
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="<?php echo $logo; ?>" alt="" class="img-thumbnail" style="width: 30px; height: 30px; object-fit: cover;">
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
            
            <!-- Bahagian/Jabatan Mengambil Bahagian (Statistik & Highlight) -->
            <div class="col-lg-5">
                <h3 class="section-title mb-3">Kategori Penglibatan</h3>
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-light">
                    <p class="small text-muted" style="text-align: justify;">
                        Kejohanan kali ini mengumpulkan kontinjen rasmi Pejabat Bahagian Jabatan Tanah dan Survei seluruh negeri Sarawak bersama agensi luaran dan jabatan kerajaan yang dijemput bertanding.
                    </p>
                    
                    <div class="d-flex flex-column gap-3 mt-3">
                        <div class="d-flex align-items-center justify-content-between bg-white p-3 rounded shadow-sm border-start border-primary border-4">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Pejabat Bahagian JTS</h6>
                                <span class="small text-muted">Ibu Pejabat & 11 Pejabat Bahagian</span>
                            </div>
                            <span class="badge bg-primary fs-5">12</span>
                        </div>
                        
                        <div class="d-flex align-items-center justify-content-between bg-white p-3 rounded shadow-sm border-start border-secondary border-4">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Jabatan Jemputan Luar</h6>
                                <span class="small text-muted">Agensi luar, PBT, agensi kerajaan</span>
                            </div>
                            <?php
                            $res_j = $conn->query("SELECT COUNT(*) as total FROM tbl_bahagian WHERE jenis = 'jemputan' AND status = 'aktif'");
                            $total_jemputan = $res_j ? $res_j->fetch_assoc()['total'] : 0;
                            ?>
                            <span class="badge bg-secondary fs-5"><?php echo $total_jemputan; ?></span>
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
            <h3 class="section-title mb-0">Imbasan Foto Kejohanan</h3>
            <a href="galeri.php" class="btn btn-sm btn-navy fw-medium">Lihat Galeri Penuh</a>
        </div>
        
        <div class="row g-3">
            <?php
            // Ambil 4 foto rawak dari galeri
            $query_g = "SELECT * FROM tbl_galeri WHERE jenis_fail = 'imej' ORDER BY RAND() LIMIT 4";
            $res_g = $conn->query($query_g);

            if ($res_g && $res_g->num_rows > 0):
                while ($row = $res_g->fetch_assoc()):
                    $img_url = BASE_URL . 'assets/uploads/galeri/' . $row['url_fail'];
                    ?>
                    <div class="col-6 col-md-3">
                        <div class="gallery-grid-item position-relative bg-dark" style="height: 200px;" 
                             data-type="imej" data-url="<?php echo $img_url; ?>" data-title="<?php echo sanitize($row['tajuk']); ?>" data-album="<?php echo sanitize($row['album']); ?>">
                            <img src="<?php echo $img_url; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo sanitize($row['tajuk']); ?>">
                            <div class="position-absolute bottom-0 start-0 m-2">
                                <span class="badge bg-dark bg-opacity-75 text-white"><?php echo sanitize($row['album'] ?: 'Hari 1'); ?></span>
                            </div>
                        </div>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
