<?php
/**
 * Templat Footer Portal Awam - SukanJTS Sarawak
 * Memaparkan maklumat penutup sistem, pautan pantas, dan memuatkan script JS.
 */
?>
<footer class="footer-custom py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            <!-- Info Korporat -->
            <div class="col-lg-5">
                <h5 class="text-white mb-3"><img src="<?php echo BASE_URL; ?>assets/logo.png" alt="Logo" class="bg-white p-1 rounded-circle me-1" style="height: 28px; width: 28px; object-fit: contain;"> LASSCAR 2026</h5>
                <p class="small text-white opacity-75 mb-3" style="text-align: justify;">
                    Landas Sport Carnival (LASSCAR) 2026 merupakan portal berpusat rasmi bagi pengedaran keputusan skor perlawanan secara langsung, jadual fixture terkini, serta carta kedudukan pingat bagi kejohanan sukan anjuran Jabatan Tanah dan Survei Sarawak. Portal ini dibina berteraskan standard integriti dan ketelusan tinggi.
                </p>
                <div class="small text-white opacity-50">
                    &copy; <?php echo date('Y'); ?> Jabatan Tanah dan Survei Sarawak. Hak Cipta Terpelihara.
                </div>
            </div>
            
            <!-- Pautan Pantas -->
            <div class="col-sm-6 col-lg-3 offset-lg-1">
                <h5 class="text-white mb-3">Pautan Pantas</h5>
                <ul class="list-unstyled d-flex flex-column gap-2 small">
                    <li><a href="<?php echo BASE_URL; ?>public/index.php" class="text-decoration-none text-white opacity-75">Laman Utama</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/bahagian.php" class="text-decoration-none text-white opacity-75">Profil Kontinjen</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/jadual.php" class="text-decoration-none text-white opacity-75">Jadual & Fixtures</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/keputusan.php" class="text-decoration-none text-white opacity-75">Match Center Live</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/kedudukan-pingat.php" class="text-decoration-none text-white opacity-75">Papan Kutipan Pingat</a></li>
                </ul>
            </div>
            
            <!-- Lokasi & Kejohanan -->
            <div class="col-sm-6 col-lg-3">
                <h5 class="text-white mb-3">Hubungi Urus Setia</h5>
                <p class="small text-white opacity-75 mb-1"><i class="bi bi-geo-alt-fill text-gold me-1"></i> <?php echo TOURNAMENT_LOCATION; ?></p>
                <p class="small text-white opacity-75 mb-1"><i class="bi bi-calendar-event-fill text-gold me-1"></i> Tarikh: <?php echo TOURNAMENT_DATE; ?></p>
                <p class="small text-white opacity-75 mb-0"><i class="bi bi-envelope-fill text-gold me-1"></i> sukan.jts@sarawak.gov.my</p>
                
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>admin/auth/login.php" class="btn btn-sm btn-outline-secondary py-1 text-white border-secondary">
                        <i class="bi bi-shield-lock"></i> Log Masuk Staf
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- JS Custom Utama -->
<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>

</body>
</html>
<?php
// Tutup sambungan pangkalan data secara global jika objek masih dibuka
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
