<?php
/**
 * Tambah Pentadbir Baru - Admin SukanJTS Sarawak
 * CRUD: Create
 */

$page_title = "Tambah Pentadbir";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan hanya super_admin boleh mengakses modul ini
confirm_access(['super_admin']);

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_penuh  = trim($_POST['nama_penuh'] ?? '');
    $emel        = strtolower(trim($_POST['emel'] ?? ''));
    $kata_laluan = $_POST['kata_laluan'] ?? '';
    $peranan     = $_POST['peranan'] ?? 'editor';
    $status      = $_POST['status'] ?? 'aktif';
    $csrf_token  = $_POST['csrf_token'] ?? '';

    // Validate CSRF
    if (!verify_csrf_token($csrf_token)) {
        $error_msg = "Token keselamatan tidak sah.";
    } elseif (empty($nama_penuh) || empty($emel) || empty($kata_laluan)) {
        $error_msg = "Sila isi semua medan yang wajib.";
    } elseif (!filter_var($emel, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Sila masukkan format e-mel yang sah.";
    } elseif (strlen($kata_laluan) < 8) {
        $error_msg = "Kata laluan mestilah mengandungi sekurang-kurangnya 8 aksara.";
    } else {
        // Hashing password
        $hashed_pass = password_hash($kata_laluan, PASSWORD_BCRYPT);

        // Prepared statement insert
        $stmt = $conn->prepare("INSERT INTO tbl_pengguna (nama_penuh, emel, kata_laluan, peranan, status) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssss", $nama_penuh, $emel, $hashed_pass, $peranan, $status);
            
            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                
                // Rekod Audit Log
                log_audit($conn, $_SESSION['admin_id'], 'create', 'tbl_pengguna', $new_id, "Daftar pengguna admin baru: $nama_penuh ($emel, Peranan: $peranan)");
                
                $_SESSION['success_msg'] = "Pengguna pentadbir '$nama_penuh' berjaya didaftarkan!";
                header("Location: index.php");
                exit;
            } else {
                if ($conn->errno === 1062) {
                    $error_msg = "E-mel ini telah pun digunakan oleh akaun pentadbir lain.";
                } else {
                    $error_msg = "Gagal menyimpan rekod: " . $stmt->error;
                }
            }
            $stmt->close();
        } else {
            $error_msg = "Ralat sistem penyediaan SQL.";
        }
    }
}
?>

<div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Pentadbir</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Daftar Pentadbir Sistem Baru</h3>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card card-admin p-4">
            
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    ⚠️ <?php echo sanitize($error_msg); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo sanitize($_SERVER['PHP_SELF']); ?>" method="POST" class="needs-validation" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="mb-3">
                    <label for="nama_penuh" class="form-label fw-semibold">Nama Penuh Pentadbir <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_penuh" name="nama_penuh" placeholder="Cth: Ahmad bin Bakar" required>
                    <div class="invalid-feedback">Sila masukkan nama penuh.</div>
                </div>

                <div class="mb-3">
                    <label for="emel" class="form-label fw-semibold">E-mel Rasmi JTS <span class="text-danger">*</span></label>
                    <input type="email" class="form-control text-lowercase" id="emel" name="emel" placeholder="Cth: ahmad@jts.sarawak.gov.my" required>
                    <div class="invalid-feedback">Sila masukkan e-mel rasmi yang sah.</div>
                </div>

                <div class="mb-3">
                    <label for="kata_laluan" class="form-label fw-semibold">Kata Laluan Masuk <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="kata_laluan" name="kata_laluan" placeholder="••••••••" minlength="8" required>
                    <div class="form-text small text-muted">Mestilah sekurang-kurangnya 8 aksara.</div>
                    <div class="invalid-feedback">Sila masukkan kata laluan sekurang-kurangnya 8 aksara.</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="peranan" class="form-label fw-semibold">Peranan RBAC</label>
                        <select class="form-select" id="peranan" name="peranan">
                            <option value="editor" selected>Editor Modul (Jadual & Skor)</option>
                            <option value="media">Editor Media (Galeri & Banner)</option>
                            <option value="super_admin">Super Admin (Akses Penuh)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="status" class="form-label fw-semibold">Status Keaktifan</label>
                        <select class="form-select" id="status" name="status">
                            <option value="aktif" selected>Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif (Kunci Sesi)</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-4">Daftar Pengguna</button>
                    <a href="index.php" class="btn btn-outline-secondary px-4">Batal</a>
                </div>
            </form>

        </div>
    </div>
</div>

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

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
