<?php
/**
 * Modul Keselamatan Sesi & RBAC - SukanJTS Sarawak
 * Wajib disertakan di bahagian paling atas setiap fail dalam direktori admin/.
 */

// Aktifkan Output Buffering bagi mengelakkan ralat "headers already sent" ketika redirect
ob_start();

// Mulakan sesi jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Panggil fail konfigurasi jika belum dipanggil
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}

// 1. Semak jika pengguna belum log masuk
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Sila log masuk untuk mengakses panel pentadbiran.";
    header("Location: " . BASE_URL . "admin/auth/login.php");
    exit;
}

// 2. Semak status tamat sesi (Session Timeout)
$current_time = time();
if (isset($_SESSION['last_activity']) && ($current_time - $_SESSION['last_activity']) > SESSION_LIFETIME) {
    // Sesi telah tamat, musnahkan sesi
    session_unset();
    session_destroy();
    
    // Mulakan semula sesi baru untuk hantar mesej ralat
    session_start();
    $_SESSION['login_error'] = "Sesi anda telah tamat kerana tidak aktif. Sila log masuk semula.";
    header("Location: " . BASE_URL . "admin/auth/login.php");
    exit;
}

// Kemas kini masa aktiviti terakhir
$_SESSION['last_activity'] = $current_time;

/**
 * Memeriksa sama ada peranan admin semasa dibenarkan mengakses halaman tertentu.
 * Jika tidak dibenarkan, paparkan halaman ralat premium "Akses Dihalang".
 * 
 * @param array $allowed_roles Senarai peranan yang dibenarkan (cth: ['super_admin', 'editor'])
 * @return void
 */
function confirm_access(array $allowed_roles): void {
    if (!isset($_SESSION['admin_role']) || !in_array($_SESSION['admin_role'], $allowed_roles)) {
        http_response_code(403);
        ?>
        <!DOCTYPE html>
        <html lang="ms">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Akses Dihalang - LASSCAR 2026</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
            <style>
                body {
                    font-family: 'Poppins', sans-serif;
                    background-color: #f3f4f6;
                    color: #1f2937;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                }
                .card {
                    border: none;
                    border-radius: 12px;
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                    background: #ffffff;
                    max-width: 500px;
                    width: 100%;
                    padding: 2.5rem 2rem;
                    text-align: center;
                }
                .icon {
                    font-size: 4rem;
                    color: #d97706; /* Orange/Gold accent */
                    margin-bottom: 1rem;
                }
                h1 {
                    font-size: 1.6rem;
                    font-weight: 600;
                    margin-bottom: 0.5rem;
                    color: #111827;
                }
                p {
                    font-size: 0.95rem;
                    color: #4b5563;
                    line-height: 1.6;
                    margin-bottom: 1.5rem;
                }
                .btn-navy {
                    background-color: #0a2540;
                    color: #ffffff;
                    padding: 0.6rem 1.5rem;
                    border-radius: 6px;
                    font-weight: 600;
                    text-decoration: none;
                    transition: background 0.2s;
                }
                .btn-navy:hover {
                    background-color: #061727;
                    color: #ffffff;
                }
            </style>
        </head>
        <body>
            <div class="card">
                <div class="icon">🔒</div>
                <h1>Akses Dihalang (403 Forbidden)</h1>
                <p>Maaf, peranan akaun anda (<strong><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $_SESSION['admin_role']))); ?></strong>) tidak mempunyai kebenaran untuk mengakses modul ini.</p>
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn-navy">Kembali ke Dashboard</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
