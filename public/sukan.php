<?php
/**
 * Senarai Sukan Dipertandingkan - Portal Awam SukanJTS Sarawak
 * Memaparkan kad-kad sukan berserta ikon dan statistik pasukan berdaftar.
 */

$page_title = "Acara Sukan Kejohanan";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
?>

<!-- Header -->
<div class="py-4 bg-navy text-white text-center mb-5" style="background-color: var(--navy-blue); border-bottom: 4px solid var(--gold);">
    <div class="container">
        <h2 class="fw-bold mb-1">Acara Sukan & Kategori Pertandingan</h2>
        <p class="lead small mb-0">Senarai penuh sukan yang dipertandingkan dalam kejohanan LASSCAR 2026.</p>
    </div>
</div>

<div class="container">
    <div class="row g-4">
        <?php
        // Ambil semua sukan aktif
        $query = "
            SELECT s.*, 
                   (SELECT COUNT(*) FROM tbl_pasukan WHERE sukan_id = s.id) as total_pasukan
            FROM tbl_sukan s
            WHERE s.status = 'aktif'
            ORDER BY s.nama_sukan ASC";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $ikon_class = $row['ikon'] ?: 'bi-trophy';
                
                $kategori_label = '';
                switch ($row['kategori']) {
                    case 'lelaki': $kategori_label = '<span class="badge bg-info text-dark">Lelaki</span>'; break;
                    case 'wanita': $kategori_label = '<span class="badge bg-danger bg-opacity-75">Wanita</span>'; break;
                    case 'campuran': $kategori_label = '<span class="badge bg-warning text-dark">Campuran</span>'; break;
                }

                $jenis_label = ($row['jenis_perlawanan'] === 'berpasukan')
                    ? '<span class="badge bg-secondary">Berpasukan</span>'
                    : '<span class="badge bg-dark">Individu</span>';
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card sport-card h-100 p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="sport-icon-wrapper">
                                    <i class="bi <?php echo sanitize($ikon_class); ?> fs-3"></i>
                                </div>
                                <span class="badge bg-primary rounded-pill px-3 py-1 fw-semibold small">
                                    <i class="bi bi-people-fill"></i> <?php echo $row['total_pasukan']; ?> Kontinjen
                                </span>
                            </div>
                            
                            <h4 class="fw-bold text-dark mb-2"><?php echo sanitize($row['nama_sukan']); ?></h4>
                            
                            <div class="d-flex gap-2 mb-3">
                                <?php echo $kategori_label; ?>
                                <?php echo $jenis_label; ?>
                            </div>
                            
                            <p class="text-secondary small mb-0" style="text-align: justify;">
                                <?php echo sanitize($row['keterangan'] ?: 'Tiada keterangan lanjut berkaitan acara sukan ini.'); ?>
                            </p>
                        </div>
                        
                        <!-- Mini Link to Fixtures filtered by this sport -->
                        <div class="mt-4 pt-3 border-top">
                            <a href="jadual.php?sukan_id=<?php echo $row['id']; ?>" class="text-decoration-none small text-navy fw-bold d-flex align-items-center gap-1">
                                <i class="bi bi-calendar-event"></i> Lihat Jadual & Fixture →
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
            endwhile;
        else:
            echo "<div class='col-12 text-center text-muted p-5'>Tiada acara sukan berdaftar didaftarkan dalam sistem.</div>";
        endif;
        ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
