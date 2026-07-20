<?php
/**
 * Kemas Kini Pentadbir - Admin SukanJTS Sarawak
 * CRUD: Update
 */

$page_title = "Kemas Kini Pentadbir";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan hanya super_admin boleh mengakses modul ini
confirm_access(['super_admin']);

$error_msg = '';
$success_msg = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$is_self = ($id == $_SESSION['admin_id']);

// Ambil maklumat pengguna semasa
$stmt_fetch = $conn->prepare("SELECT * FROM tbl_pengguna WHERE id = ? LIMIT 1");
$stmt_fetch->bind_param("i", $id);
$stmt_fetch->execute();
$res = $stmt_fetch->get_result();

if ($res->num_rows !== 1) {
    $stmt_fetch->close();
    $_SESSION['error_msg'] = "Pengguna tidak dijumpai.";
    header("Location: index.php");
    exit;
}

$user_data = $res->fetch_assoc();
$stmt_fetch->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_penuh  = trim($_POST['nama_penuh'] ?? '');
    $emel        = strtolower(trim($_POST['emel'] ?? ''));
    $kata_laluan = $_POST['kata_laluan'] ?? '';
    
    // Cegah penukaran status/peranan diri sendiri
    if ($is_self) {
        $peranan = $user_data['peranan'];
        $status  = $user_data['status'];
    } else {
        $peranan = $_POST['peranan'] ?? 'editor';
        $status  = $_POST['status'] ?? 'aktif';
    }
    
    $csrf_token  = $_POST['csrf_token'] ?? '';

    // Validate CSRF
    if (!verify_csrf_token($csrf_token)) {
        $error_msg = "Token keselamatan tidak sah.";
    } elseif (empty($nama_penuh) || empty($emel)) {
        $error_msg = "Sila isi semua medan yang wajib.";
    } elseif (!filter_var($emel, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Sila masukkan format e-mel yang sah.";
    } elseif (!empty($kata_laluan) && strlen($kata_laluan) < 8) {
        $error_msg = "Kata laluan baharu mestilah sekurang-kurangnya 8 aksara.";
    } else {
        
        $sql = "UPDATE tbl_pengguna SET nama_penuh = ?, emel = ?, peranan = ?, status = ?";
        
        // Tambah kata laluan ke query jika diisi
        if (!empty($kata_laluan)) {
            $sql .= ", kata_laluan = ?";
        }
        $sql .= " WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($kata_laluan)) {
                $hashed_pass = password_hash($kata_laluan, PASSWORD_BCRYPT);
                $stmt->bind_param("sssssi", $nama_penuh, $emel, $peranan, $status, $hashed_pass, $id);
            } else {
                $stmt->bind_param("ssssii", $nama_penuh, $emel, $peranan, $status, $id);
            }
            
            if ($stmt->execute()) {
                // Kemas kini nama sesi jika mengemas kini profil diri sendiri
                if ($is_self) {
                    $_SESSION['admin_nama'] = $nama_penuh;
                }

                // Rekod Audit Log
                log_audit($conn, $_SESSION['admin_id'], 'update', 'tbl_pengguna', $id, "Kemas kini pengguna admin ID $id: $nama_penuh ($emel)");
                
                $_SESSION['success_msg'] = "Pengguna pentadbir '$nama_penuh' berjaya dikemas kini!";
                header("Location: index.php");
                exit;
            } else {
                if ($conn->errno === 1062) {
                    $error_msg = "E-mel ini telah pun digunakan oleh akaun pentadbir lain.";
                } else {
                    $error_msg = "Gagal mengemas kini rekod: " . $stmt->error;
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
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Kemas Kini Maklumat Pentadbir</h3>
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

            <form action="edit.php?id=<?php echo $id; ?>" method="POST" class="needs-validation" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="mb-3">
                    <label for="nama_penuh" class="form-label fw-semibold">Nama Penuh Pentadbir <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_penuh" name="nama_penuh" value="<?php echo sanitize($user_data['nama_penuh']); ?>" required>
                    <div class="invalid-feedback">Sila masukkan nama penuh.</div>
                </div>

                <div class="mb-3">
                    <label for="emel" class="form-label fw-semibold">E-mel Rasmi JTS <span class="text-danger">*</span></label>
                    <input type="email" class="form-control text-lowercase" id="emel" name="emel" value="<?php echo sanitize($user_data['emel']); ?>" required>
                    <div class="invalid-feedback">Sila masukkan e-mel rasmi yang sah.</div>
                </div>

                <div class="mb-3">
                    <label for="kata_laluan" class="form-label fw-semibold">Kata Laluan Baru (Opsyenal)</label>
                    <input type="password" class="form-control" id="kata_laluan" name="kata_laluan" placeholder="••••••••" minlength="8">
                    <div class="form-text small text-muted">Biarkan kosong jika tidak mahu menukar kata laluan semasa.</div>
                    <div class="invalid-feedback">Sila masukkan kata laluan sekurang-kurangnya 8 aksara.</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="peranan" class="form-label fw-semibold">Peranan RBAC</label>
                        <?php if ($is_self): ?>
                            <input type="text" class="form-control" value="<?php echo sanitize(ucwords(str_replace('_', ' ', $user_data['peranan']))); ?>" readonly disabled>
                            <div class="form-text small text-muted text-warning">Anda tidak boleh menukar peranan akaun anda sendiri.</div>
                        <?php else: ?>
                            <select class="form-select" id="peranan" name="peranan">
                                <option value="editor" <?php echo ($user_data['peranan'] === 'editor') ? 'selected' : ''; ?>>Editor Modul (Jadual & Skor)</option>
                                <option value="media" <?php echo ($user_data['peranan'] === 'media') ? 'selected' : ''; ?>>Editor Media (Galeri & Banner)</option>
                                <option value="super_admin" <?php echo ($user_data['peranan'] === 'super_admin') ? 'selected' : ''; ?>>Super Admin (Akses Penuh)</option>
                            </select>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-6">
                        <label for="status" class="form-label fw-semibold">Status Keaktifan</label>
                        <?php if ($is_self): ?>
                            <input type="text" class="form-control" value="<?php echo sanitize(ucfirst($user_data['status'])); ?>" readonly disabled>
                            <div class="form-text small text-muted text-warning">Anda tidak boleh menyahaktifkan sesi diri sendiri.</div>
                        <?php else: ?>
                            <select class="form-select" id="status" name="status">
                                <option value="aktif" <?php echo ($user_data['status'] === 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="tidak_aktif" <?php echo ($user_data['status'] === 'tidak_aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="pt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-4">Kemas Kini</button>
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
