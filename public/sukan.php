<?php
/**
 * Senarai Sukan Dipertandingkan - Portal Awam SukanJTS Sarawak
 * Memaparkan kad-kad sukan berserta ikon rasmi, lencana kategori moden & statistik penyertaan.
 */

$page_title = "Acara Sukan Kejohanan";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
?>

<!-- Header -->
<div class="py-5 text-white text-center mb-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, #04101e 0%, #0a2540 60%, #1e3a5f 100%); border-bottom: 5px solid var(--gold);">
    <div class="container py-3 position-relative z-1">
        <span class="badge bg-gold text-dark fs-6 py-2 px-3 fw-bold rounded-pill mb-3 shadow-sm">
            <i class="bi bi-trophy-fill me-1"></i> ACARA RASMI KEJOHANAN
        </span>
        <h1 class="fw-bold display-4 text-white mb-2">Acara Sukan & Kategori Pertandingan</h1>
        <p class="lead text-light col-md-8 mx-auto fs-6 opacity-90 mb-0">Senarai sukan dipertandingkan dalam kejohanan LASSCAR 2026 beserta statistik penyertaan kontinjen.</p>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4">
        <?php
        // Ambil semua sukan aktif & pengiraan statistik pasukan
        $query = "
            SELECT s.*, 
                   (SELECT COUNT(*) FROM tbl_pasukan WHERE sukan_id = s.id) as total_pasukan
            FROM tbl_sukan s
            WHERE s.status = 'aktif'
            ORDER BY s.nama_sukan ASC";
        $result = $conn->query($query);

        // Fungsi bantuan pemetaan ikon pintar mengikut nama sukan jika ikon ikon_class kosong
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

        // Fungsi pemetaan imej visual ilustrasi sukan yang tepat & khusus
        function get_sport_image_url($nama_sukan) {
            $nama = strtolower($nama_sukan);
            // 1. Dart
            if (strpos($nama, 'dart') !== false) 
                return 'https://images.unsplash.com/photo-1618688339178-57e4e970a049?auto=format&fit=crop&w=600&q=80';
            // 2. Boling Padang / Lawn Bowls
            if (strpos($nama, 'boling padang') !== false || strpos($nama, 'lawn bowls') !== false) 
                return 'https://images.unsplash.com/photo-1593111774601-dfbce32402c4?auto=format&fit=crop&w=600&q=80';
            // 3. Tenpin Boling / Bowling
            if (strpos($nama, 'boling') !== false || strpos($nama, 'bowling') !== false) 
                return 'https://images.unsplash.com/photo-1545232979-fbfd14860b73?auto=format&fit=crop&w=600&q=80';
            // 4. Bola Sepak
            if (strpos($nama, 'bola sepak') !== false || strpos($nama, 'football') !== false) 
                return 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=600&q=80';
            // 5. Futsal
            if (strpos($nama, 'futsal') !== false) 
                return 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=600&q=80';
            // 6. Badminton
            if (strpos($nama, 'badminton') !== false) 
                return 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=600&q=80';
            // 7. Ping Pong / Tenis Meja
            if (strpos($nama, 'ping pong') !== false || strpos($nama, 'tenis meja') !== false || strpos($nama, 'table tennis') !== false) 
                return 'https://images.unsplash.com/photo-1534158914592-062992fbe900?auto=format&fit=crop&w=600&q=80';
            // 8. Karom / Carrom
            if (strpos($nama, 'karom') !== false || strpos($nama, 'carrom') !== false) 
                return 'https://images.unsplash.com/photo-1610890716171-6b1bb98ffd09?auto=format&fit=crop&w=600&q=80';
            // 9. Petanque
            if (strpos($nama, 'petanque') !== false) 
                return 'https://images.unsplash.com/photo-1563299796-17596ed6b017?auto=format&fit=crop&w=600&q=80';
            // 10. Bola Jaring / Netball
            if (strpos($nama, 'bola jaring') !== false || strpos($nama, 'netball') !== false) 
                return 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=600&q=80';
            // 11. Pikabol / Pickleball
            if (strpos($nama, 'pikabol') !== false || strpos($nama, 'pickleball') !== false) 
                return 'https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?auto=format&fit=crop&w=600&q=80';
            
            return 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=600&q=80';
        }

        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $sport_img_url = get_sport_image_url($row['nama_sukan']);
                
                $kategori_badge = '';
                switch ($row['kategori']) {
                    case 'lelaki': 
                        $kategori_badge = '<span class="badge bg-primary text-white rounded-pill px-3 py-1.5 fw-semibold"><i class="bi bi-gender-male me-1"></i> Lelaki</span>'; 
                        break;
                    case 'wanita': 
                        $kategori_badge = '<span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-semibold"><i class="bi bi-gender-female me-1"></i> Wanita</span>'; 
                        break;
                    case 'campuran': 
                        $kategori_badge = '<span class="badge bg-warning text-dark rounded-pill px-3 py-1.5 fw-bold"><i class="bi bi-people me-1"></i> Campuran</span>'; 
                        break;
                }

                $jenis_badge = ($row['jenis_perlawanan'] === 'berpasukan')
                    ? '<span class="badge bg-secondary text-white rounded-pill px-3 py-1.5 fw-semibold"><i class="bi bi-shield-shaded me-1"></i> Berpasukan</span>'
                    : '<span class="badge bg-dark text-white rounded-pill px-3 py-1.5 fw-semibold"><i class="bi bi-person me-1"></i> Individu</span>';
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card card-hover-effect border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Header Imej Ilustrasi Sukan (Tanpa Ikon Overlay) -->
                            <div class="position-relative overflow-hidden" style="height: 190px;">
                                <img src="<?php echo $sport_img_url; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo sanitize($row['nama_sukan']); ?>">
                                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.65));"></div>
                                
                                <!-- Tajuk Sukan Overlaid -->
                                <div class="position-absolute bottom-0 start-0 m-3">
                                    <h4 class="fw-bold text-white mb-0 fs-5 text-shadow"><?php echo sanitize($row['nama_sukan']); ?></h4>
                                </div>

                                <!-- Lencana Kontinjen -->
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-white text-dark shadow-sm rounded-pill px-3 py-1.5 fw-bold small">
                                        <i class="bi bi-people-fill me-1 text-primary"></i> <?php echo $row['total_pasukan']; ?> Kontinjen
                                    </span>
                                </div>
                            </div>

                            <div class="p-4">
                                <div class="d-flex gap-2 flex-wrap mb-3">
                                    <?php echo $kategori_badge; ?>
                                    <?php echo $jenis_badge; ?>
                                </div>
                                
                                <p class="text-dark small mb-0 leading-relaxed" style="text-align: justify; opacity: 0.85;">
                                    <?php echo sanitize($row['keterangan'] ?: 'Acara sukan rasmi yang dipertandingkan antara pejabat bahagian dan jabatan jemputan.'); ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Pautan Pantas ke Jadual & Keputusan -->
                        <div class="p-4 pt-0">
                            <div class="pt-3 border-top">
                                <a href="jadual.php?sukan_id=<?php echo $row['id']; ?>" class="btn btn-navy btn-sm text-white fw-bold rounded-3 w-100 d-flex align-items-center justify-content-between px-3 py-2 border-0 shadow-sm">
                                    <span><i class="bi bi-calendar-event me-1"></i> Jadual & Fixture</span>
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

