<?php
/**
 * Galeri Media Kejohanan - Portal Awam SukanJTS Sarawak
 * Memaparkan imej & video perlawanan dengan penapis album serta penyegerakan automatik Google Drive.
 */

$page_title = "Galeri Gambar & Video";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/gdrive_sync.php';

// Jalankan penyegerakan automatik Google Drive (dikawal oleh TTL 3 minit)
$gdrive_sync_status = sync_gdrive_gallery($conn);

// Ambil parameter tapisan album
$filter_album = isset($_GET['album']) && $_GET['album'] !== '' ? trim($_GET['album']) : null;

// Bina query dinamik (Sokong carian tak peka huruf besar/kecil & nama sukan)
$sql = "SELECT g.*, s.nama_sukan FROM tbl_galeri g 
        LEFT JOIN tbl_sukan s ON g.sukan_id = s.id";
$params = [];
$types = "";

if ($filter_album !== null) {
    $sql .= " WHERE (LOWER(g.album) = LOWER(?) OR LOWER(s.nama_sukan) = LOWER(?) OR LOWER(g.album) LIKE LOWER(?))";
    $like_param = '%' . $filter_album . '%';
    $params = [$filter_album, $filter_album, $like_param];
    $types = "sss";
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

// Ambil senarai 10 Sukan & Bilangan Gambar Google Drive
$sports_folders = [];
$s_res = $conn->query("
    SELECT s.id, s.nama_sukan, s.ikon,
           COUNT(g.id) AS total_media
    FROM tbl_sukan s
    LEFT JOIN tbl_galeri g ON (g.sukan_id = s.id OR LOWER(g.album) = LOWER(s.nama_sukan) OR LOWER(g.album) LIKE CONCAT('%', LOWER(s.nama_sukan), '%'))
    WHERE s.status = 'aktif'
    GROUP BY s.id, s.nama_sukan, s.ikon
    ORDER BY s.nama_sukan ASC
");
if ($s_res) {
    while ($s_row = $s_res->fetch_assoc()) {
        $sports_folders[] = $s_row;
    }
}
?>

<!-- Header -->
<div class="py-4 bg-navy text-white text-center mb-4" style="background-color: var(--navy-blue); border-bottom: 4px solid var(--gold);">
    <div class="container">
        <h2 class="fw-bold mb-1">Galeri Media LASSCAR 2026</h2>
        <p class="lead small mb-0">Himpunan imej dan video kejohanan yang disegera secara automatik dari Google Drive & jurugambar rasmi.</p>
    </div>
</div>

<div class="container mb-5">
    
    <?php if (!file_exists(GDRIVE_SERVICE_ACCOUNT_FILE)): ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-3 p-3 mb-4 text-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 text-warning d-block mb-1"></i>
            <strong>Kredensial Google Drive Belum Dihubungkan!</strong><br>
            <span class="small text-muted">Folder dan gambar akan muncul secara automatik selepas fail <code>config/gdrive-service-account.json</code> diletakkan dan folder Google Drive dikongsi (*Share*) dengan Service Account.</span>
        </div>
    <?php endif; ?>

    <!-- Section Folder Sukan Google Drive (10 Folder Sukan) -->
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-folder-fill text-gold me-2"></i> Folder Sukan Google Drive (10 Sukan)
            </h5>
            <?php if ($filter_album !== null): ?>
                <a href="galeri.php" class="btn btn-sm btn-outline-navy fw-medium">
                    <i class="bi bi-x-circle me-1"></i> Kosongkan Penapis
                </a>
            <?php endif; ?>
        </div>

        <div class="row g-2">
            <!-- Butang Folder Semua -->
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <a href="galeri.php" class="card text-decoration-none border-0 shadow-sm rounded-3 p-2 text-center h-100 style-folder-card <?php echo ($filter_album === null) ? 'bg-navy text-white' : 'bg-white text-dark'; ?>">
                    <div class="fs-3 mb-1"><i class="bi bi-images"></i></div>
                    <div class="fw-bold small text-truncate">Semua Media</div>
                    <div class="small opacity-75" style="font-size: 0.75rem;">Tunjukkan Semua</div>
                </a>
            </div>
            
            <!-- 10 Folder Sukan -->
            <?php foreach ($sports_folders as $sf): ?>
                <?php 
                $is_selected = ($filter_album !== null && (strcasecmp($filter_album, $sf['nama_sukan']) === 0 || stripos($sf['nama_sukan'], $filter_album) !== false));
                ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a href="galeri.php?album=<?php echo urlencode($sf['nama_sukan']); ?>" 
                       class="card text-decoration-none border-0 shadow-sm rounded-3 p-2 text-center h-100 style-folder-card <?php echo $is_selected ? 'bg-navy text-white' : 'bg-white text-dark'; ?>">
                        <div class="fs-3 mb-1 text-gold">
                            <i class="bi <?php echo sanitize($sf['ikon'] ?: 'bi-folder-fill'); ?>"></i>
                        </div>
                        <div class="fw-bold small text-truncate" title="<?php echo sanitize($sf['nama_sukan']); ?>">
                            <?php echo sanitize($sf['nama_sukan']); ?>
                        </div>
                        <div class="small opacity-75" style="font-size: 0.75rem;">
                            <?php echo $sf['total_media']; ?> Gambar
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Grid Media -->
    <div class="row g-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php 
                if (!empty($row['is_gdrive'])) {
                    $thumb_url = !empty($row['gdrive_thumbnail_url']) ? $row['gdrive_thumbnail_url'] : BASE_URL . 'public/gdrive-image.php?id=' . $row['gdrive_file_id'];
                    $full_url  = !empty($row['gdrive_thumbnail_url']) ? $row['gdrive_thumbnail_url'] : BASE_URL . 'public/gdrive-image.php?id=' . $row['gdrive_file_id'];
                    $gdrive_link = $row['gdrive_view_url'] ?: "https://drive.google.com/file/d/" . $row['gdrive_file_id'] . "/view";
                } else {
                    $thumb_url = BASE_URL . 'assets/uploads/galeri/' . $row['url_fail'];
                    $full_url  = $thumb_url;
                    $gdrive_link = '';
                }
                ?>
                <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                    <!-- Grid Item (Clickable for Lightbox) -->
                    <div class="gallery-grid-item position-relative bg-dark shadow-sm rounded-4 overflow-hidden style-card-hover" 
                         style="height: 220px; cursor: pointer;"
                         onclick="openGalleryModal('<?php echo $row['jenis_fail']; ?>', '<?php echo sanitize($full_url); ?>', '<?php echo sanitize($row['tajuk']); ?>', '<?php echo sanitize($row['album']); ?>', '<?php echo sanitize($gdrive_link); ?>')">
                         
                        <?php if ($row['jenis_fail'] === 'imej'): ?>
                            <img src="<?php echo sanitize($thumb_url); ?>" loading="lazy" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo sanitize($row['tajuk']); ?>"
                                 onerror="this.src='<?php echo BASE_URL . 'public/gdrive-image.php?id=' . ($row['gdrive_file_id'] ?? ''); ?>'">
                        <?php else: ?>
                            <video class="w-100 h-100" style="object-fit: cover;" preload="metadata">
                                <source src="<?php echo sanitize($thumb_url); ?>" type="video/mp4">
                            </video>
                            <div class="position-absolute top-50 start-50 translate-middle text-white bg-dark bg-opacity-75 rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="bi bi-play-fill fs-4"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Album & Tag Overlay -->
                        <div class="position-absolute bottom-0 start-0 m-2 z-1">
                            <span class="badge bg-navy text-white small shadow-sm"><?php echo sanitize($row['album'] ?: 'Umum'); ?></span>
                            <?php if (!empty($row['is_gdrive'])): ?>
                                <span class="badge bg-primary text-white small shadow-sm"><i class="bi bi-google"></i> Drive</span>
                            <?php endif; ?>
                            <?php if (!empty($row['nama_sukan'])): ?>
                                <span class="badge bg-gold text-dark small shadow-sm"><?php echo sanitize($row['nama_sukan']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Hover Overlay Effect -->
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-40 d-flex align-items-center justify-content-center opacity-0 hover-overlay-show" style="transition: opacity 0.2s;">
                            <span class="text-white fw-medium small bg-dark bg-opacity-75 px-3 py-1 rounded-pill"><i class="bi bi-zoom-in me-1"></i> Papar Media</span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted py-5">
                <i class="bi bi-images fs-1 d-block mb-2 text-navy"></i>
                Tiada fail media ditemui bagi kategori album yang dipilih.
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal Lightbox Galeri -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold id-modal-title" id="galleryModalTitle">Papar Media</h5>
                    <span class="badge bg-gold text-dark small mt-1" id="galleryModalAlbum">Album</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3">
                <div id="galleryModalMediaContainer" class="d-flex align-items-center justify-content-center" style="min-height: 350px; max-height: 70vh;">
                    <!-- Content injected via JS -->
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                <a id="galleryModalDriveBtn" href="#" target="_blank" class="btn btn-sm btn-outline-light d-none">
                    <i class="bi bi-google me-1"></i> Buka di Google Drive
                </a>
                <button type="button" class="btn btn-sm btn-secondary px-4 ms-auto" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    .gallery-grid-item:hover .hover-overlay-show {
        opacity: 1 !important;
    }
    .style-card-hover, .style-folder-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .style-card-hover:hover, .style-folder-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.12) !important;
    }
</style>

<script>
function openGalleryModal(type, url, title, album, gdriveLink) {
    document.getElementById('galleryModalTitle').innerText = title || 'Media LASSCAR 2026';
    document.getElementById('galleryModalAlbum').innerText = album || 'Umum';
    
    const container = document.getElementById('galleryModalMediaContainer');
    if (type === 'imej') {
        container.innerHTML = `<img src="${url}" class="img-fluid rounded-3" style="max-height: 65vh; object-fit: contain;" alt="${title}">`;
    } else {
        container.innerHTML = `<video controls autoplay class="w-100 rounded-3" style="max-height: 65vh;"><source src="${url}" type="video/mp4"></video>`;
    }
    
    const driveBtn = document.getElementById('galleryModalDriveBtn');
    if (gdriveLink && gdriveLink.length > 5) {
        driveBtn.href = gdriveLink;
        driveBtn.classList.remove('d-none');
    } else {
        driveBtn.classList.add('d-none');
    }
    
    const modal = new bootstrap.Modal(document.getElementById('galleryModal'));
    modal.show();
}
</script>

<?php 
if ($stmt) $stmt->close();
require_once __DIR__ . '/../includes/footer.php'; 
?>
