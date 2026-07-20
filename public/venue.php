<?php
/**
 * Senarai Tempat Pertandingan (Venue) - Portal Awam SukanJTS Sarawak
 * Memaparkan lokasi pertandingan berserta integrasi peta interaktif Leaflet/OpenStreetMap.
 */

$page_title = "Lokasi & Venue Pertandingan";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
?>

<!-- Import Leaflet CSS & JS untuk peta interaktif tanpa kunci API -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- Header -->
<div class="py-4 bg-navy text-white text-center mb-5" style="background-color: var(--navy-blue); border-bottom: 4px solid var(--gold);">
    <div class="container">
        <h2 class="fw-bold mb-1">Tempat & Lokasi Pertandingan (Venue)</h2>
        <p class="lead small mb-0">Lokasi rasmi kejohanan sukan, alamat lengkap dan panduan peta perjalanan.</p>
    </div>
</div>

<div class="container">
    <div class="row g-4">
        <?php
        $query = "SELECT * FROM tbl_venue ORDER BY nama_tempat ASC";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $venue_id = $row['id'];
                $has_coords = ($row['latitude'] !== null && $row['longitude'] !== null);
                ?>
                <div class="col-12 col-lg-6">
                    <div class="card card-admin p-4 border shadow-sm h-100 d-flex flex-column justify-content-between">
                        <div>
                            <h4 class="fw-bold text-navy mb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo sanitize($row['nama_tempat']); ?></h4>
                            <p class="text-dark small mb-3"><strong>Alamat:</strong> <?php echo sanitize($row['alamat'] ?: 'Tiada alamat rasmi'); ?></p>
                            
                            <div class="row g-2 mb-3 small text-secondary">
                                <div class="col-6">
                                    <i class="bi bi-people-fill text-primary me-1"></i> Kapasiti: <strong><?php echo $row['kapasiti'] !== null ? sanitize($row['kapasiti']) . ' orang' : 'N/A'; ?></strong>
                                </div>
                                <div class="col-6">
                                    <i class="bi bi-info-circle-fill text-primary me-1"></i> Catatan: <strong><?php echo sanitize($row['catatan'] ?: '-'); ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Map Area -->
                        <?php if ($has_coords): ?>
                            <div class="mb-3">
                                <div id="map_<?php echo $venue_id; ?>" style="height: 250px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);"></div>
                            </div>
                            
                            <!-- Leaflet Map Inisialisasi Script -->
                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    // Inisialisasi peta Leaflet berpusat di koordinat venue
                                    const lat = <?php echo $row['latitude']; ?>;
                                    const lng = <?php echo $row['longitude']; ?>;
                                    const map = L.map('map_<?php echo $venue_id; ?>').setView([lat, lng], 15);

                                    // Gunakan OpenStreetMap tiles
                                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                        attribution: '&copy; OpenStreetMap contributors'
                                    }).addTo(map);

                                    // Tambah marker di venue
                                    L.marker([lat, lng]).addTo(map)
                                        .bindPopup('<strong><?php echo addslashes(sanitize($row['nama_tempat'])); ?></strong><br><?php echo addslashes(sanitize($row['alamat'] ?: '')); ?>')
                                        .openPopup();
                                    
                                    // Selesaikan isu render Leaflet dalam tab/flexbox
                                    setTimeout(function(){ map.invalidateSize(); }, 200);
                                });
                            </script>
                        <?php else: ?>
                            <div class="mb-3 d-flex align-items-center justify-content-center bg-light text-muted border rounded" style="height: 250px;">
                                <div class="text-center">
                                    <i class="bi bi-map-fill fs-2"></i>
                                    <div class="small">Tiada maklumat koordinat GPS disediakan.</div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Pautan Google Maps -->
                        <?php if ($has_coords): ?>
                            <div class="text-end border-top pt-2">
                                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($row['latitude'] . ',' . $row['longitude']); ?>" target="_blank" class="btn btn-sm btn-outline-primary fw-medium small">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka Peta Google (Google Maps)
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
            endwhile;
        else:
            echo "<div class='col-12 text-center text-muted p-5'>Tiada venue didaftarkan sepadan dalam sistem.</div>";
        endif;
        ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
