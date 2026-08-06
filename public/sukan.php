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
            <i class="bi bi-trophy-fill me-1"></i> ACARA RASMI kejohanan
        </span>
        <h1 class="fw-bold display-4 text-white mb-2">Acara Sukan & Kategori Pertandingan</h1>
        <p class="lead text-slate-300 col-md-8 mx-auto fs-6 opacity-90 mb-0">Senarai sukan dipertandingkan dalam kejohanan LASSCAR 2026 beserta statistik penyertaan kontinjen.</p>
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

        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $ikon_class = get_sport_icon($row['nama_sukan'], $row['ikon']);
                
                $kategori_badge = '';
                switch ($row['kategori']) {
                    case 'lelaki': 
                        $kategori_badge = '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1.5 fw-semibold"><i class="bi bi-gender-male me-1"></i> Lelaki</span>'; 
                        break;
                    case 'wanita': 
                        $kategori_badge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1.5 fw-semibold"><i class="bi bi-gender-female me-1"></i> Wanita</span>'; 
                        break;
                    case 'campuran': 
                        $kategori_badge = '<span class="badge bg-warning bg-opacity-15 text-dark border border-warning border-opacity-50 rounded-pill px-3 py-1.5 fw-bold"><i class="bi bi-people me-1"></i> Campuran</span>'; 
                        break;
                }

                $jenis_badge = ($row['jenis_perlawanan'] === 'berpasukan')
                    ? '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1.5 fw-semibold"><i class="bi bi-shield-shaded me-1"></i> Berpasukan</span>'
                    : '<span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 rounded-pill px-3 py-1.5 fw-semibold"><i class="bi bi-person me-1"></i> Individu</span>';
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card card-hover-effect border-0 shadow-sm rounded-4 p-4 bg-white h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="bg-navy text-white rounded-3 p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                                    <i class="bi <?php echo sanitize($ikon_class); ?> fs-3 text-gold"></i>
                                </div>
                                <span class="badge bg-navy bg-opacity-10 text-navy border border-navy border-opacity-25 rounded-pill px-3 py-2 fw-bold small">
                                    <i class="bi bi-people-fill me-1"></i> <?php echo $row['total_pasukan']; ?> Kontinjen
                                </span>
                            </div>
                            
                            <h4 class="fw-bold text-dark mb-2 fs-5"><?php echo sanitize($row['nama_sukan']); ?></h4>
                            
                            <div class="d-flex gap-2 flex-wrap mb-3">
                                <?php echo $kategori_badge; ?>
                                <?php echo $jenis_badge; ?>
                            </div>
                            
                            <p class="text-secondary small mb-0 leading-relaxed" style="text-align: justify;">
                                <?php echo sanitize($row['keterangan'] ?: 'Acara sukan rasmi yang dipertandingkan antara pejabat bahagian dan jabatan jemputan.'); ?>
                            </p>
                        </div>
                        
                        <!-- Pautan Pantas ke Jadual & Keputusan -->
                        <div class="mt-4 pt-3 border-top">
                            <a href="jadual.php?sukan_id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm text-navy fw-bold rounded-3 w-100 d-flex align-items-center justify-content-between px-3 py-2 border">
                                <span><i class="bi bi-calendar-event me-1"></i> Jadual & Fixture</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
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

