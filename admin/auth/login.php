<?php
/**
 * Halaman Log Masuk Pentadbir - SukanJTS Sarawak
 * Melindungi daripada serangan brute-force, CSRF, SQLi, dan session fixation.
 */

// Mulakan sesi jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah log masuk, redirect terus ke dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

// Panggil dependencies
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';

$error_msg = '';

// Proses Kiriman Borang (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emel = trim($_POST['emel'] ?? '');
    $kata_laluan = $_POST['kata_laluan'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // 1. Pengesahan CSRF
    if (!verify_csrf_token($csrf_token)) {
        $error_msg = "Token keselamatan tidak sah. Sila hantar borang semula.";
    } elseif (empty($emel) || empty($kata_laluan)) {
        $error_msg = "Sila isi e-mel dan kata laluan anda.";
    } else {
        // 2. Semak status kunci akaun (brute-force rate limiting)
        $lock_status = check_login_attempts($conn, $emel);
        if ($lock_status['status'] === true) {
            $error_msg = $lock_status['message'];
        } else {
            // 3. Query pengguna menggunakan Prepared Statements
            $stmt = $conn->prepare("SELECT id, nama_penuh, kata_laluan, peranan, status FROM tbl_pengguna WHERE emel = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $emel);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();

                    // Semak status keaktifan akaun
                    if ($user['status'] !== 'aktif') {
                        $error_msg = "Akaun anda telah dinyahaktifkan. Sila hubungi Pentadbir Utama.";
                    }
                    // Sahkan kata laluan
                    elseif (password_verify($kata_laluan, $user['kata_laluan'])) {
                        // Reset percubaan gagal
                        reset_failed_login($conn, $emel);

                        // Set Sesi Pengguna
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_nama'] = $user['nama_penuh'];
                        $_SESSION['admin_role'] = $user['peranan'];
                        $_SESSION['last_activity'] = time();

                        // Cegah Session Fixation dengan menjana ID sesi baru
                        session_regenerate_id(true);

                        // Rekod log audit login
                        log_audit($conn, $user['id'], 'login', 'tbl_pengguna', $user['id'], "Pengguna log masuk berjaya");

                        // Redirect ke Dashboard
                        header("Location: ../dashboard.php");
                        exit;
                    } else {
                        // Kata laluan salah, rekod kegagalan
                        record_failed_login($conn, $emel);
                        $error_msg = "E-mel atau kata laluan tidak sah.";
                    }
                } else {
                    // E-mel tidak wujud, paparkan mesej am (jangan dedah e-mel tiada untuk elak email enumeration)
                    $error_msg = "E-mel atau kata laluan tidak sah.";
                }
                $stmt->close();
            } else {
                $error_msg = "Masalah teknikal berlaku. Sila cuba lagi kemudian.";
            }
        }
    }
}

// Dapatkan ralat sesi dari auth-check jika ada
if (empty($error_msg) && isset($_SESSION['login_error'])) {
    $error_msg = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk Pentadbir - SukanJTS Sarawak</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-blue: #0a2540;
            --gold: #ffd700;
            --dark-grey: #1f2937;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #061727 0%, #0a2540 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }
        .card-header-navy {
            background-color: var(--navy-blue);
            color: #ffffff;
            padding: 2.5rem 1.5rem 1.5rem 1.5rem;
            text-align: center;
            position: relative;
            border-bottom: 4px solid var(--gold);
        }
        .card-header-navy h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .card-header-navy p {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 0;
        }
        .btn-navy {
            background-color: var(--navy-blue);
            color: #ffffff;
            border: none;
            font-weight: 600;
            padding: 0.75rem;
            width: 100%;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .btn-navy:hover {
            background-color: #061727;
            color: #ffffff;
            transform: translateY(-1px);
        }
        .form-control:focus {
            border-color: var(--navy-blue);
            box-shadow: 0 0 0 0.25rem rgba(10, 37, 64, 0.15);
        }
        .logo-img {
            max-width: 70px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="card-header-navy">
        <!-- Logo JTS (Guna default_logo) -->
        <div class="text-center">
            <span style="font-size: 3rem;">🏆</span>
        </div>
        <h2>Panel Pentadbir</h2>
        <p>Sistem Pengurusan Sukan JTS Sarawak</p>
    </div>
    <div class="card-body p-4">
        
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger d-flex align-items-center small" role="alert">
                <div>
                    ⚠️ <?php echo sanitize($error_msg); ?>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?php echo sanitize($_SERVER['PHP_SELF']); ?>" method="POST" class="needs-validation" novalidate>
            <!-- CSRF Token -->
            <?php csrf_field(); ?>

            <div class="mb-3">
                <label for="emel" class="form-label small fw-semibold">E-mel Rasmi</label>
                <input type="email" class="form-control form-control-lg text-lowercase" id="emel" name="emel" placeholder="contoh@jts.sarawak.gov.my" required>
                <div class="invalid-feedback">Sila masukkan e-mel yang sah.</div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="kata_laluan" class="form-label small fw-semibold mb-0">Kata Laluan</label>
                </div>
                <input type="password" class="form-control form-control-lg" id="kata_laluan" name="kata_laluan" placeholder="••••••••" required>
                <div class="invalid-feedback">Sila masukkan kata laluan anda.</div>
            </div>

            <button type="submit" class="btn btn-navy btn-lg mb-3">Log Masuk</button>
            
            <div class="text-center">
                <a href="../../public/index.php" class="text-decoration-none small text-muted">← Kembali ke Landing Page Awam</a>
            </div>
        </form>

    </div>
</div>

<!-- Bootstrap 5 JavaScript & Form Validation -->
<script>
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>
</body>
</html>
