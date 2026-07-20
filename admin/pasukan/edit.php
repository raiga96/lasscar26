<?php
/**
 * Edit Nama Pasukan - Admin SukanJTS Sarawak
 * CRUD: Update
 */

$page_title = "Kemas Kini Pasukan";
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

// Ambil maklumat pasukan semasa
$stmt_fetch = $conn->prepare("SELECT p.*, b.nama_bahagian, s.nama_sukan 
                              FROM tbl_pasukan p
                              JOIN tbl_bahagian b ON p.bahagian_id = b.id
                              JOIN tbl_sukan s ON p.sukan_id = s.id
                              WHERE p.id = ? LIMIT 1");
$stmt_fetch->bind_param("i", $id);
$stmt_fetch->execute();
$res = $stmt_fetch->get_result();

if ($res->num_rows !== 1) {
    $stmt_fetch->close();
    $_SESSION['error_msg'] = "Pendaftaran pasukan tidak dijumpai.";
    header("Location: index.php");
    exit;
}

$pasukan = $res->fetch_assoc();
$stmt_fetch->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pasukan = trim($_POST['nama_pasukan'] ?? '');
    $csrf_token   = $_POST['csrf_token'] ?? '';

    // Validate CSRF
    if (!verify_csrf_token($csrf_token)) {
        $error_msg = "Token keselamatan tamat tempoh. Sila hantar borang semula.";
    } else {
        $nama_pasukan_val = empty($nama_pasukan) ? null : $nama_pasukan;

        // Prepared statement update
        $stmt = $conn->prepare("UPDATE tbl_pasukan SET nama_pasukan = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $nama_pasukan_val, $id);
            
            if ($stmt->execute()) {
                // Rekod Audit Log
                log_audit($conn, $_SESSION['admin_id'], 'update', 'tbl_pasukan', $id, "Kemaskini nama pasukan ID $id: " . ($nama_pasukan ?: 'Kekal Nama Bahagian') . " (Sukan: " . $pasukan['nama_sukan'] . ")");
                
                $_SESSION['success_msg'] = "Nama pasukan bagi kontinjen '" . $pasukan['nama_bahagian'] . "' berjaya dikemas kini!";
                header("Location: index.php");
                exit;
            } else {
                $error_msg = "Gagal mengemas kini rekod: " . $stmt->error;
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
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Pasukan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kemas Kini</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Kemas Kini Pasukan</h3>
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
                    <label class="form-label fw-semibold d-block">Kontinjen Asal</label>
                    <input type="text" class="form-control" value="<?php echo sanitize($pasukan['nama_bahagian']); ?>" readonly disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold d-block">Acara Sukan</label>
                    <input type="text" class="form-control" value="<?php echo sanitize($pasukan['nama_sukan']); ?>" readonly disabled>
                </div>

                <div class="mb-3">
                    <label for="nama_pasukan" class="form-label fw-semibold">Nama Pasukan Khusus</label>
                    <input type="text" class="form-control" id="nama_pasukan" name="nama_pasukan" value="<?php echo sanitize($pasukan['nama_pasukan'] ?? ''); ?>" placeholder="Cth: Kuching Tigers / Betong A">
                    <div class="form-text small text-muted">Biarkan kosong untuk kembali menggunakan nama rasmi Bahagian.</div>
                </div>

                <div class="pt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-4">Kemas Kini Nama</button>
                    <a href="index.php" class="btn btn-outline-secondary px-4">Batal</a>
                </div>
            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
