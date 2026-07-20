<?php
/**
 * Templat Sidebar Menu Admin - SukanJTS Sarawak
 * Memaparkan pautan navigasi panel pentadbiran mengikut peranan RBAC pengguna.
 */

// Pastikan sesi aktif dan peranan pengguna wujud
$user_role = $_SESSION['admin_role'] ?? 'editor';
$user_nama = $_SESSION['admin_nama'] ?? 'Pentadbir';

// Dapatkan nama fail semasa untuk status 'active' menu
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

/**
 * Semak sama ada menu semasa aktif.
 * 
 * @param string $menu_dir Nama folder modul
 * @param string $menu_file Nama fail modul (default: index.php)
 * @return string Mengembalikan 'active' jika benar
 */
function is_menu_active(string $menu_dir, string $menu_file = 'index.php'): string {
    global $current_page, $current_dir;
    if ($menu_dir === 'dashboard' && $current_page === 'dashboard.php') {
        return 'active';
    }
    if ($current_dir === $menu_dir) {
        return 'active';
    }
    return '';
}
?>
<!-- Sidebar -->
<div id="sidebar-wrapper">
    <div class="sidebar-brand d-flex align-items-center gap-2 px-3 py-3">
        <img src="<?php echo BASE_URL; ?>assets/logo.png" alt="Logo" class="bg-white p-1 rounded-circle" style="height: 32px; width: 32px; object-fit: contain;">
        <span class="fw-bold text-white fs-6">LASSCAR Admin</span>
    </div>
    
    <div class="px-3 py-2 text-muted small bg-black bg-opacity-25 border-bottom border-secondary border-opacity-25">
        <i class="bi bi-person-badge-fill me-1"></i> Peranan: <span class="text-white fw-semibold"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $user_role))); ?></span>
    </div>

    <ul class="sidebar-nav">
        <!-- Dashboard (Semua peranan) -->
        <li class="nav-item">
            <a class="nav-link <?php echo is_menu_active('dashboard'); ?>" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                <i class="bi bi-speedometer2 text-white"></i> Dashboard
            </a>
        </li>

        <!-- MODUL CRUD KONTINJEN (Hanya super_admin) -->
        <?php if ($user_role === 'super_admin'): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo is_menu_active('bahagian'); ?>" href="<?php echo BASE_URL; ?>admin/bahagian/index.php">
                <i class="bi bi-flag-fill text-white"></i> Urus Kontinjen
            </a>
        </li>
        <?php endif; ?>

        <!-- MODUL CRUD ACARA SUKAN (Hanya super_admin) -->
        <?php if ($user_role === 'super_admin'): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo is_menu_active('sukan'); ?>" href="<?php echo BASE_URL; ?>admin/sukan/index.php">
                <i class="bi bi-trophy-fill text-white"></i> Urus Sukan
            </a>
        </li>
        <?php endif; ?>

        <!-- MODUL CRUD PESERTA/PASUKAN (Hanya super_admin) -->
        <?php if ($user_role === 'super_admin'): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo is_menu_active('pasukan'); ?>" href="<?php echo BASE_URL; ?>admin/pasukan/index.php">
                <i class="bi bi-people-fill text-white"></i> Pendaftaran Pasukan
            </a>
        </li>
        <?php endif; ?>

        <!-- MODUL CRUD VENUE (super_admin & editor) -->
        <?php if (in_array($user_role, ['super_admin', 'editor'])): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo is_menu_active('venue'); ?>" href="<?php echo BASE_URL; ?>admin/venue/index.php">
                <i class="bi bi-geo-alt-fill text-white"></i> Urus Venue
            </a>
        </li>
        <?php endif; ?>

        <!-- MODUL CRUD JADUAL PERLAWANAN (super_admin & editor) -->
        <?php if (in_array($user_role, ['super_admin', 'editor'])): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo is_menu_active('jadual'); ?>" href="<?php echo BASE_URL; ?>admin/jadual/index.php">
                <i class="bi bi-calendar-event text-white"></i> Jadual Perlawanan
            </a>
        </li>
        <?php endif; ?>

        <!-- MODUL CRUD KEPUTUSAN & SKOR (super_admin & editor) -->
        <?php if (in_array($user_role, ['super_admin', 'editor'])): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo is_menu_active('keputusan'); ?>" href="<?php echo BASE_URL; ?>admin/keputusan/index.php">
                <i class="bi bi-scoreboard text-white"></i> Keputusan & Skor
            </a>
        </li>
        <?php endif; ?>

        <!-- MODUL CRUD ATURCARA (super_admin & editor) -->
        <?php if (in_array($user_role, ['super_admin', 'editor'])): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo is_menu_active('aturcara'); ?>" href="<?php echo BASE_URL; ?>admin/aturcara/index.php">
                <i class="bi bi-clock-history text-white"></i> Urus Aturcara
            </a>
        </li>
        <?php endif; ?>

        <!-- MODUL CRUD GALERI MEDIA (super_admin & media) -->
        <?php if (in_array($user_role, ['super_admin', 'media'])): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo is_menu_active('galeri'); ?>" href="<?php echo BASE_URL; ?>admin/galeri/index.php">
                <i class="bi bi-images text-white"></i> Galeri Media
            </a>
        </li>
        <?php endif; ?>

        <!-- MODUL CRUD HERO BANNER / JUARA (super_admin & media) -->
        <?php if (in_array($user_role, ['super_admin', 'media'])): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo is_menu_active('hero-banner'); ?>" href="<?php echo BASE_URL; ?>admin/hero-banner/index.php">
                <i class="bi bi-image-fill text-white"></i> Hero Banner
            </a>
        </li>
        <?php endif; ?>

        <!-- MODUL PENGURUSAN PENGGUNA ADMIN (Hanya super_admin) -->
        <?php if ($user_role === 'super_admin'): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo is_menu_active('pengguna'); ?>" href="<?php echo BASE_URL; ?>admin/pengguna/index.php">
                <i class="bi bi-person-gear text-white"></i> Pengguna Admin
            </a>
        </li>
        <?php endif; ?>

        <!-- LOG AUDIT (Hanya super_admin) -->
        <?php if ($user_role === 'super_admin'): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo is_menu_active('audit-log'); ?>" href="<?php echo BASE_URL; ?>admin/audit-log/index.php">
                <i class="bi bi-journal-text text-white"></i> Log Audit
            </a>
        </li>
        <?php endif; ?>
        
        <hr class="mx-3 my-3 text-secondary">

        <!-- LOG KELUAR (Semua peranan) -->
        <li class="nav-item">
            <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>admin/auth/logout.php" onclick="return confirm('Adakah anda pasti untuk keluar dari sistem?');">
                <i class="bi bi-box-arrow-left text-danger"></i> Log Keluar
            </a>
        </li>
    </ul>
</div>

<!-- Page Content Wrapper -->
<div id="page-content-wrapper">
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-admin navbar-light border-bottom">
        <div class="container-fluid p-0">
            <h5 class="m-0 fw-semibold text-secondary">
                <i class="bi bi-person-fill text-primary me-1"></i> Selamat Datang, <?php echo htmlspecialchars($user_nama); ?>
            </h5>
            
            <div class="ms-auto d-flex align-items-center gap-3">
                <a href="<?php echo BASE_URL; ?>public/index.php" target="_blank" class="btn btn-sm btn-outline-primary fw-medium">
                    <i class="bi bi-globe me-1"></i> Lihat Portal Awam
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Area -->
    <div class="admin-content">
