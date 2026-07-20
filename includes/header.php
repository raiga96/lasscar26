<?php
/**
 * Templat Header Portal Awam - SukanJTS Sarawak
 * Memaparkan navbar navigasi utama yang responsif.
 */

// Panggil fail konfigurasi global
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

// Tentukan nama fail semasa untuk menetapkan status 'active' pada pautan navigasi
$current_page = basename($_SERVER['PHP_SELF']);

/**
 * Menentukan sama ada pautan navbar aktif.
 * 
 * @param string $page_name Nama fail halaman
 * @return string 'active' jika aktif
 */
function is_active_link(string $page_name): string {
    global $current_page;
    return ($current_page === $page_name) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal rasmi kejohanan sukan tahunan Jabatan Tanah dan Survei Sarawak. Keputusan skor secara langsung, fixtures, dan kutipan pingat kontinjen.">
    <title>LASSCAR 2026</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Helaian Gaya Custom -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>

<!-- Navbar Awam -->
<nav class="navbar navbar-expand-xl navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>public/index.php">
            <img src="<?php echo BASE_URL; ?>assets/logo.png" alt="Logo" class="d-inline-block align-top bg-white p-1 rounded" style="height: 45px; width: auto; object-fit: contain; max-width: 60px;">
            <div>
                <span class="fs-5 d-block fw-bold"><?php echo APP_NAME; ?></span>
                <span class="small text-muted d-block" style="font-size: 0.65rem; font-weight: 500; letter-spacing: 0.05em; color: var(--gold) !important;">LANDAS SPORT CARNIVAL</span>
            </div>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_link('index.php'); ?>" href="<?php echo BASE_URL; ?>public/index.php">Utama</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_link('bahagian.php'); ?>" href="<?php echo BASE_URL; ?>public/bahagian.php">Kontinjen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_link('sukan.php'); ?>" href="<?php echo BASE_URL; ?>public/sukan.php">Sukan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_link('jadual.php'); ?>" href="<?php echo BASE_URL; ?>public/jadual.php">Jadual</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_link('keputusan.php'); ?>" href="<?php echo BASE_URL; ?>public/keputusan.php">Perlawanan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_link('kedudukan-pingat.php'); ?>" href="<?php echo BASE_URL; ?>public/kedudukan-pingat.php">Pingat</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_link('venue.php'); ?>" href="<?php echo BASE_URL; ?>public/venue.php">Venue</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_link('aturcara.php'); ?>" href="<?php echo BASE_URL; ?>public/aturcara.php">Aturcara</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_link('galeri.php'); ?>" href="<?php echo BASE_URL; ?>public/galeri.php">Galeri</a>
                </li>
            </ul>
            <div class="ms-xl-3 mt-2 mt-xl-0">
                <a href="<?php echo BASE_URL; ?>admin/auth/login.php" class="btn btn-gold btn-sm d-flex align-items-center gap-1 justify-content-center">
                    <i class="bi bi-shield-lock-fill"></i> Urus Setia
                </a>
            </div>
        </div>
    </div>
</nav>
