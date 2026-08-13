<?php
/**
 * Senarai Sukan & Pemenang Pingat Kontinjen - Portal Awam SukanJTS Sarawak
 * Memaparkan acara sukan dipertandingkan, rekod pemenang pingat (Emas/Perak/Gangsa)
 * serta pengiktirafan Juara Keseluruhan Kejohanan mengikut kutipan Pingat Emas.
 */

$page_title = "Acara Sukan & Kedudukan Pingat";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';

// 1. Query Juara Keseluruhan / Carta Kedudukan Pingat Kontinjen
$overall_query = "
    SELECT b.id, b.nama_bahagian, b.logo_url, b.jenis,
           SUM(CASE WHEN k.jenis_pingat = 'emas' THEN 1 ELSE 0 END) AS emas,
           SUM(CASE WHEN k.jenis_pingat = 'perak' THEN 1 ELSE 0 END) AS perak,
           SUM(CASE WHEN k.jenis_pingat = 'gangsa' THEN 1 ELSE 0 END) AS gangsa,
           SUM(CASE WHEN k.jenis_pingat IN ('emas','perak','gangsa') THEN 1 ELSE 0 END) AS jumlah
    FROM tbl_bahagian b
    LEFT JOIN tbl_pasukan p ON p.bahagian_id = b.id
    LEFT JOIN tbl_keputusan k ON k.pasukan_menang_id = p.id
    GROUP BY b.id, b.nama_bahagian, b.logo_url, b.jenis
    ORDER BY emas DESC, perak DESC, gangsa DESC, b.nama_bahagian ASC";
$overall_res = $conn->query($overall_query);

$overall_standings = [];
if ($overall_res && $overall_res->num_rows > 0) {
    while ($row = $overall_res->fetch_assoc()) {
        $overall_standings[] = $row;
    }
}

// Kontinjen yang mendahului Pingat Emas dikira Juara Keseluruhan
$juara_keseluruhan = (!empty($overall_standings) && $overall_standings[0]['emas'] > 0) ? $overall_standings[0] : null;

// 2. Query Pemenang Pingat Mengikut Sukan (Emas, Perak, Gangsa)
$medals_query = "
    SELECT j.sukan_id, k.jenis_pingat, b.id as bahagian_id, b.nama_bahagian, b.logo_url, p.nama_pasukan
    FROM tbl_keputusan k
    JOIN tbl_jadual_perlawanan j ON k.jadual_id = j.id
    JOIN tbl_pasukan p ON k.pasukan_menang_id = p.id
    JOIN tbl_bahagian b ON p.bahagian_id = b.id
    WHERE k.jenis_pingat IN ('emas', 'perak', 'gangsa')
    ORDER BY FIELD(k.jenis_pingat, 'emas', 'perak', 'gangsa')";
$medals_res = $conn->query($medals_query);

$sukan_medals = [];
if ($medals_res && $medals_res->num_rows > 0) {
    while ($m = $medals_res->fetch_assoc()) {
        $s_id = $m['sukan_id'];
        $pingat = $m['jenis_pingat'];
        if (!isset($sukan_medals[$s_id][$pingat])) {
            $sukan_medals[$s_id][$pingat] = [];
        }
        $sukan_medals[$s_id][$pingat][] = $m;
    }
}

// 3. Query Senarai Sukan Aktif
$sukan_query = "
    SELECT s.*, 
           (SELECT COUNT(*) FROM tbl_pasukan WHERE sukan_id = s.id) as total_pasukan
    FROM tbl_sukan s
    WHERE s.status = 'aktif'
    ORDER BY s.nama_sukan ASC";
$sukan_res = $conn->query($sukan_query);

// Fungsi pemetaan ikon sukan
function get_sport_icon($nama_sukan, $db_icon) {
    if (!empty($db_icon) && $db_icon !== 'bi-trophy') {
        return $db_icon;
    }
    $nama = strtolower($nama_sukan);
    if (strpos($nama, 'boling padang') !== false || strpos($nama, 'bowls') !== false) return 'bi-circle-half';
    if (strpos($nama, 'boling') !== false || strpos($nama, 'bowling') !== false) return 'bi-record-circle-fill';
    if (strpos($nama, 'bola sepak') !== false || strpos($nama, 'football') !== false) return 'bi-dribbble';
    if (strpos($nama, 'futsal') !== false) return 'bi-dribbble';
    if (strpos($nama, 'badminton') !== false) return 'bi-activity';
    if (strpos($nama, 'ping pong') !== false || strpos($nama, 'tenis meja') !== false) return 'bi-controller';
    if (strpos($nama, 'karom') !== false || strpos($nama, 'carrom') !== false) return 'bi-grid-3x3-gap-fill';
    if (strpos($nama, 'dart') !== false) return 'bi-bullseye';
    if (strpos($nama, 'petanque') !== false) return 'bi-record-fill';
    if (strpos($nama, 'bola jaring') !== false || strpos($nama, 'netball') !== false) return 'bi-basket3-fill';
    if (strpos($nama, 'pikabol') !== false || strpos($nama, 'pickleball') !== false) return 'bi-square-fill';
    return 'bi-trophy-fill';
}

// Fungsi pemetaan imej visual ilustrasi sukan
function get_sport_image_url($nama_sukan) {
    $nama = strtolower($nama_sukan);
    if (strpos($nama, 'dart') !== false) 
        return 'https://aussiedartsupplies.com.au/cdn/shop/articles/Dart_98c61e6b-2c1e-4296-a849-185e39d7c5bd.jpg?v=1761006666&width=1600';
    if (strpos($nama, 'boling padang') !== false || strpos($nama, 'lawn bowls') !== false) 
        return 'https://images.unsplash.com/photo-1593111774601-dfbce32402c4?auto=format&fit=crop&w=600&q=80';
    if (strpos($nama, 'boling') !== false || strpos($nama, 'bowling') !== false) 
        return 'https://sportsmatik.com/uploads/matik-sports-corner/matik-know-how/bowling_2-compressed_1513402323_45132.jpg';
    if (strpos($nama, 'bola sepak') !== false || strpos($nama, 'football') !== false) 
        return 'https://www.infobae.com/resizer/v2/G2AHJVGT6JFGNNO7AQFPFVKGQA.jpg?auth=58c4000debed60ed638309e89b3e0efa995c5201de178baa0666309a8eca0a6a&smart=true&width=1024&height=512&quality=85';
    if (strpos($nama, 'futsal') !== false) 
        return 'https://img.olympics.com/images/image/private/t_s_pog_staticContent_hero_lg_2x/f_auto/primary/jjzehpncbsvvonxyuhqy';
    if (strpos($nama, 'badminton') !== false) 
        return 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=600&q=80';
    if (strpos($nama, 'ping pong') !== false || strpos($nama, 'tenis meja') !== false || strpos($nama, 'table tennis') !== false) 
        return 'https://images.unsplash.com/photo-1534158914592-062992fbe900?auto=format&fit=crop&w=600&q=80';
    if (strpos($nama, 'karom') !== false || strpos($nama, 'carrom') !== false) 
        return 'https://syncoshop.com/cdn/shop/articles/Choose_right_carrom_board_63adee8a-d00a-4e8c-a287-bebba3f2eeba.jpg?v=1742807999&width=1780';
    if (strpos($nama, 'pentanque') !== false) 
        return 'https://assets.domainecarneros.com/system/uploads/fae/image/asset/1777/Petanque_Game.jpg';
    if (strpos($nama, 'bola jaring') !== false || strpos($nama, 'netball') !== false) 
        return 'https://netball.sport/wp-content/uploads/2023/11/South-Africa-Wales-1920x1080-1.jpg';
    if (strpos($nama, 'pikabol') !== false || strpos($nama, 'pickleball') !== false) 
        return 'https://a57.foxnews.com/static.foxnews.com/foxnews.com/content/uploads/2024/07/1440/810/pickleball-paddle-court.jpg?ve=1&tl=1';
    
    return 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=600&q=80';
}
?>

<!-- Header Utama Page -->
<div class="py-5 text-white text-center mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #04101e 0%, #0a2540 60%, #1e3a5f 100%); border-bottom: 5px solid var(--gold);">
    <div class="container py-3 position-relative z-1">
        <span class="badge bg-gold text-dark fs-6 py-2 px-3 fw-bold rounded-pill mb-3 shadow-sm">
            <i class="bi bi-trophy-fill me-1"></i> KEDUDUKAN PINGAT & ACARA SUKAN
        </span>
        <h1 class="fw-bold display-4 text-white mb-2">Pemenang Pingat Mengikut Acara Sukan</h1>
        <p class="lead text-light col-md-8 mx-auto fs-6 opacity-90 mb-0">
            Paparan pemenang <strong>Pingat Emas (Juara Keseluruhan Sukan)</strong>, <strong>Perak</strong>, dan <strong>Gangsa</strong> bagi setiap sukan yang dipertandingkan.
        </p>
    </div>
</div>

<div class="container mb-5">

    <!-- Section: Highlight Juara Keseluruhan Kejohanan -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fff 100%); border: 2px solid #f59e0b !important;">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold fs-6 shadow-sm">
                            <i class="bi bi-award-fill me-1 text-danger"></i> PENDAHULU CARTA PINGAT
                        </span>
                        <span class="text-muted small fw-semibold">| Pingat Emas Penentu Juara Keseluruhan</span>
                    </div>

                    <h2 class="fw-bold text-dark mb-3 display-6">
                        🏆 Juara Keseluruhan Kejohanan
                    </h2>

                    <?php if ($juara_keseluruhan): 
                        $juara_logo = BASE_URL . 'assets/uploads/logo-bahagian/' . ($juara_keseluruhan['logo_url'] ?: 'default_logo.png');
                    ?>
                        <div class="d-flex align-items-center gap-4 bg-white p-3 rounded-4 shadow-sm border border-warning">
                            <img src="<?php echo $juara_logo; ?>" alt="<?php echo sanitize($juara_keseluruhan['nama_bahagian']); ?>" class="img-thumbnail rounded-circle border-3 border-warning shadow-sm" style="width: 75px; height: 75px; object-fit: cover;">
                            <div>
                                <span class="badge bg-gold text-dark mb-1 fw-bold px-2 py-1">TEMPAT PERTAMA</span>
                                <h3 class="fw-bold text-navy mb-1 fs-4"><?php echo sanitize($juara_keseluruhan['nama_bahagian']); ?></h3>
                                <p class="text-muted small mb-0">Mendahului kutipan pingat keseluruhan LASSCAR 2026</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="p-3 bg-white rounded-3 border text-muted">
                            <i class="bi bi-info-circle me-1"></i> Keputusan rasmi masih dikemas kini oleh urus setia sukan.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-lg-4">
                    <!-- Ringkasan Pingat Top 3 Kontinjen -->
                    <div class="bg-navy text-white p-4 rounded-4 shadow-sm">
                        <h5 class="fw-bold mb-3 text-warning border-bottom border-warning pb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-bar-chart-line-fill"></i> Tiga Kontinjen Teratas
                        </h5>
                        <?php if (!empty($overall_standings)): ?>
                            <div class="d-flex flex-column gap-2">
                                <?php 
                                $top3 = array_slice($overall_standings, 0, 3);
                                $pos = 1;
                                foreach ($top3 as $st):
                                    $med_icon = ($pos === 1) ? '🥇' : (($pos === 2) ? '🥈' : '🥉');
                                ?>
                                    <div class="d-flex align-items-center justify-content-between bg-white bg-opacity-10 p-2 rounded-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fs-5"><?php echo $med_icon; ?></span>
                                            <span class="fw-semibold text-white small text-truncate" style="max-width: 140px;"><?php echo sanitize($st['nama_bahagian']); ?></span>
                                        </div>
                                        <div class="d-flex gap-1 text-center small fw-bold">
                                            <span class="badge bg-warning text-dark px-2"><?php echo $st['emas']; ?> E</span>
                                            <span class="badge bg-secondary px-2"><?php echo $st['perak']; ?> P</span>
                                            <span class="badge bg-danger bg-opacity-75 px-2"><?php echo $st['gangsa']; ?> G</span>
                                        </div>
                                    </div>
                                <?php 
                                    $pos++;
                                endforeach; 
                                ?>
                            </div>
                            <div class="text-end mt-3">
                                <a href="kedudukan-pingat.php" class="btn btn-gold btn-sm fw-bold px-3 rounded-pill">
                                    Lihat Papan Pingat Penuh <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Title: Senarai Acara Sukan -->
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
            <h3 class="fw-bold text-navy mb-1"><i class="bi bi-grid-fill text-gold me-2"></i>Senarai Acara & Pemenang Pingat</h3>
            <p class="text-muted small mb-0">Klik pada sukan untuk maklumat jadual atau semak penganugerahan Emas, Perak & Gangsa.</p>
        </div>
        <span class="badge bg-navy text-white px-3 py-2 rounded-pill fs-6 fw-bold">
            <?php echo $sukan_res ? $sukan_res->num_rows : 0; ?> Acara Sukan
        </span>
    </div>

    <!-- Grid Kad Sukan -->
    <div class="row g-4">
        <?php
        if ($sukan_res && $sukan_res->num_rows > 0):
            while ($row = $sukan_res->fetch_assoc()):
                $s_id = $row['id'];
                $sport_img_url = get_sport_image_url($row['nama_sukan']);
                
                // Badges Kategori
                $kategori_badge = '';
                switch ($row['kategori']) {
                    case 'lelaki': 
                        $kategori_badge = '<span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fw-semibold"><i class="bi bi-gender-male me-1"></i> Lelaki</span>'; 
                        break;
                    case 'wanita': 
                        $kategori_badge = '<span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-semibold"><i class="bi bi-gender-female me-1"></i> Wanita</span>'; 
                        break;
                    case 'campuran': 
                        $kategori_badge = '<span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fw-bold"><i class="bi bi-people me-1"></i> Campuran</span>'; 
                        break;
                }

                $jenis_badge = ($row['jenis_perlawanan'] === 'berpasukan')
                    ? '<span class="badge bg-secondary text-white rounded-pill px-2.5 py-1 fw-semibold"><i class="bi bi-shield-shaded me-1"></i> Berpasukan</span>'
                    : '<span class="badge bg-dark text-white rounded-pill px-2.5 py-1 fw-semibold"><i class="bi bi-person me-1"></i> Individu</span>';

                // Semak Pemenang Pingat Sukan Ini
                $med_emas = $sukan_medals[$s_id]['emas'] ?? [];
                $med_perak = $sukan_medals[$s_id]['perak'] ?? [];
                $med_gangsa = $sukan_medals[$s_id]['gangsa'] ?? [];
                $has_medals = (!empty($med_emas) || !empty($med_perak) || !empty($med_gangsa));
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card card-hover-effect border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Header Imej Visual Sukan -->
                            <div class="position-relative overflow-hidden" style="height: 180px;">
                                <img src="<?php echo $sport_img_url; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo sanitize($row['nama_sukan']); ?>">
                                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.75));"></div>
                                
                                <!-- Tajuk Sukan -->
                                <div class="position-absolute bottom-0 start-0 m-3">
                                    <h4 class="fw-bold text-white mb-0 fs-5 text-shadow"><?php echo sanitize($row['nama_sukan']); ?></h4>
                                </div>

                                <!-- Bilangan Kontinjen -->
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-white text-dark shadow-sm rounded-pill px-3 py-1.5 fw-bold small">
                                        <i class="bi bi-people-fill me-1 text-primary"></i> <?php echo $row['total_pasukan']; ?> Kontinjen
                                    </span>
                                </div>
                            </div>

                            <div class="p-3 pb-2">
                                <div class="d-flex gap-2 flex-wrap mb-3">
                                    <?php echo $kategori_badge; ?>
                                    <?php echo $jenis_badge; ?>
                                </div>

                                <!-- Kotak Kontinjen Pemenang Pingat (Emas, Perak, Gangsa) -->
                                <div class="border rounded-3 p-3 bg-light">
                                    <h6 class="fw-bold text-navy mb-2.5 pb-1 border-bottom d-flex align-items-center justify-content-between small">
                                        <span><i class="bi bi-award-fill text-warning me-1"></i> Kontinjen Pemenang Pingat</span>
                                        <?php if ($has_medals): ?>
                                            <span class="badge bg-success small">Selesai</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary opacity-75 small">Dalam Pertandingan</span>
                                        <?php endif; ?>
                                    </h6>

                                    <!-- 🥇 EMAS / JUARA SUKAN -->
                                    <div class="p-2 mb-1.5 rounded-3 d-flex align-items-center justify-content-between shadow-2xs" style="background-color: #fef9c3; border: 1px solid #fde047;">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fs-5">🥇</span>
                                            <div>
                                                <span class="d-block fw-bold text-dark small" style="font-size: 0.75rem; color: #854d0e !important;">EMAS (JUARA)</span>
                                                <strong class="text-dark small">
                                                    <?php 
                                                    if (!empty($med_emas)) {
                                                        $names = array_map(function($item) { return sanitize($item['nama_bahagian']); }, $med_emas);
                                                        echo implode(', ', $names);
                                                    } else {
                                                        echo '<span class="text-muted fw-normal">Belum direkod</span>';
                                                    }
                                                    ?>
                                                </strong>
                                            </div>
                                        </div>
                                        <?php if (!empty($med_emas)): ?>
                                            <span class="badge bg-warning text-dark fw-bold rounded-pill px-2 py-1 small">Juara</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- 🥈 PERAK -->
                                    <div class="p-2 mb-1.5 rounded-3 d-flex align-items-center justify-content-between" style="background-color: #f1f5f9; border: 1px solid #cbd5e1;">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fs-5">🥈</span>
                                            <div>
                                                <span class="d-block fw-bold text-secondary small" style="font-size: 0.75rem;">PERAK</span>
                                                <strong class="text-dark small">
                                                    <?php 
                                                    if (!empty($med_perak)) {
                                                        $names = array_map(function($item) { return sanitize($item['nama_bahagian']); }, $med_perak);
                                                        echo implode(', ', $names);
                                                    } else {
                                                        echo '<span class="text-muted fw-normal">Belum direkod</span>';
                                                    }
                                                    ?>
                                                </strong>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 🥉 GANGSA -->
                                    <div class="p-2 rounded-3 d-flex align-items-center justify-content-between" style="background-color: #ffedd5; border: 1px solid #fdba74;">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fs-5">🥉</span>
                                            <div>
                                                <span class="d-block fw-bold small" style="font-size: 0.75rem; color: #9a3412;">GANGSA</span>
                                                <strong class="text-dark small">
                                                    <?php 
                                                    if (!empty($med_gangsa)) {
                                                        $names = array_map(function($item) { return sanitize($item['nama_bahagian']); }, $med_gangsa);
                                                        echo implode(', ', $names);
                                                    } else {
                                                        echo '<span class="text-muted fw-normal">Belum direkod</span>';
                                                    }
                                                    ?>
                                                </strong>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Button Jadual & Fixture -->
                        <div class="p-3 pt-0">
                            <div class="pt-2">
                                <a href="jadual.php?sukan_id=<?php echo $row['id']; ?>" class="btn btn-navy btn-sm text-white fw-bold rounded-3 w-100 d-flex align-items-center justify-content-between px-3 py-2 border-0 shadow-sm">
                                    <span><i class="bi bi-calendar-event me-1"></i> Lihat Jadual & Fixture</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
            endwhile;
        else:
            echo "<div class='col-12 text-center text-muted p-5 bg-white rounded-4 shadow-sm'>Tiada acara sukan berdaftar didaftarkan dalam sistem.</div>";
        endif;
        ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


