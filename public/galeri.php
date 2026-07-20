<?php
/**
 * Galeri Media Kejohanan - Portal Awam SukanJTS Sarawak
 * Memaparkan imej & video perlawanan dengan penapis album serta paparan lightbox premium.
 */

$page_title = "Galeri Gambar & Video";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';

// Ambil parameter tapisan album
$filter_album = isset($_GET['album']) && $_GET['album'] !== '' ? $_GET['album'] : null;

// Bina query dinamik
$sql = "SELECT g.*, s.nama_sukan FROM tbl_galeri g 
        LEFT JOIN tbl_sukan s ON g.sukan_id = s.id";
$params = [];
$types = "";

if ($filter_album !== null) {
    $sql .= " WHERE g.album = ?";
    $params[] = $filter_album;
    $types .= "s";
}
$sql .= " ORDER BY g.dicipta_pada DESC";

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

// Ambil senarai album unik untuk butang penapis
$album_res = $conn->query("SELECT DISTINCT album FROM tbl_galeri WHERE album IS NOT NULL AND album != '' ORDER BY album ASC");
?>

<!-- Header -->
<div class="py-4 bg-navy text-white text-center mb-5" style="background-color: var(--navy-blue); border-bottom: 4px solid var(--gold);">
    <div class="container">
        <h2 class="fw-bold mb-1">Galeri Media LASSCAR 2026</h2>
        <p class="lead small mb-0">Himpunan imej dan video kejohanan yang dirakam sepanjang pertandingan berlangsung.</p>
    </div>
</div>

<div class="container">
    
    <!-- Butang Penapis Album -->
    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
        <a href="galeri.php" class="btn btn-sm <?php echo ($filter_album === null) ? 'btn-navy' : 'btn-outline-secondary'; ?> px-3 fw-medium">
            Tunjukkan Semua
        </a>
        <?php if ($album_res && $album_res->num_rows > 0): ?>
            <?php while ($alb = $album_res->fetch_assoc()): ?>
                <a href="galeri.php?album=<?php echo urlencode($alb['album']); ?>" class="btn btn-sm <?php echo ($filter_album === $alb['album']) ? 'btn-navy' : 'btn-outline-secondary'; ?> px-3 fw-medium">
                    <?php echo sanitize($alb['album']); ?>
                </a>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <!-- Grid Media -->
    <div class="row g-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php 
                $file_url = BASE_URL . 'assets/uploads/galeri/' . $row['url_fail'];
                ?>
                <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                    <!-- Grid Item (Clickable for Lightbox) -->
                    <div class="gallery-grid-item position-relative bg-dark shadow-sm rounded-3 overflow-hidden" 
                         style="height: 220px;"
                         data-type="<?php echo $row['jenis_fail']; ?>"
                         data-url="<?php echo $file_url; ?>"
                         data-title="<?php echo sanitize($row['tajuk']); ?>"
                         data-album="<?php echo sanitize($row['album']); ?>">
                         
                        <?php if ($row['jenis_fail'] === 'imej'): ?>
                            <img src="<?php echo $file_url; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo sanitize($row['tajuk']); ?>">
                        <?php else: ?>
                            <video class="w-100 h-100" style="object-fit: cover;" preload="metadata">
                                <source src="<?php echo $file_url; ?>" type="video/mp4">
                            </video>
                            <div class="position-absolute top-50 start-50 translate-middle text-white bg-dark bg-opacity-75 rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="bi bi-play-fill fs-4"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Album & Sukan Tag Overlay -->
                        <div class="position-absolute bottom-0 start-0 m-2 z-1">
                            <span class="badge bg-navy text-white small"><?php echo sanitize($row['album'] ?: 'Umum'); ?></span>
                            <?php if ($row['nama_sukan']): ?>
                                <span class="badge bg-gold text-dark small"><?php echo sanitize($row['nama_sukan']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Hover Overlay Effect -->
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-40 d-flex align-items-center justify-content-center opacity-0 hover-overlay-show" style="transition: opacity 0.2s; pointer-events: none;">
                            <span class="text-white fw-medium small"><i class="bi bi-zoom-in me-1"></i> Papar Media</span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted py-5">
                <i class="bi bi-images fs-1 d-block mb-2"></i>
                Tiada fail media ditemui bagi kategori album yang dipilih.
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- CSS Tambahan Khusus untuk Hover Overlay Galeri -->
<style>
    .gallery-grid-item:hover .hover-overlay-show {
        opacity: 1 !important;
    }
</style>

<?php 
if ($stmt) $stmt->close();
require_once __DIR__ . '/../includes/footer.php'; 
?>
