<?php
/**
 * Kemas Kini Venue - Admin SukanJTS Sarawak
 * CRUD: Update
 */

$page_title = "Kemas Kini Venue";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan super_admin atau editor sahaja boleh mengakses modul ini
confirm_access(['super_admin', 'editor']);

$error_msg = '';
$success_msg = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil maklumat venue semasa
$stmt_fetch = $conn->prepare("SELECT * FROM tbl_venue WHERE id = ? LIMIT 1");
$stmt_fetch->bind_param("i", $id);
$stmt_fetch->execute();
$res = $stmt_fetch->get_result();

if ($res->num_rows !== 1) {
    $stmt_fetch->close();
    $_SESSION['error_msg'] = "Venue tidak dijumpai.";
    header("Location: index.php");
    exit;
}

$venue = $res->fetch_assoc();
$stmt_fetch->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_tempat = trim($_POST['nama_tempat'] ?? '');
    $alamat      = trim($_POST['alamat'] ?? '');
    $latitude    = trim($_POST['latitude'] ?? '');
    $longitude   = trim($_POST['longitude'] ?? '');
    $kapasiti    = trim($_POST['kapasiti'] ?? '');
    $catatan     = trim($_POST['catatan'] ?? '');
    $csrf_token  = $_POST['csrf_token'] ?? '';

    // Validate CSRF
    if (!verify_csrf_token($csrf_token)) {
        $error_msg = "Token keselamatan tamat tempoh. Sila hantar borang semula.";
    } elseif (empty($nama_tempat)) {
        $error_msg = "Sila isi nama tempat.";
    } else {
        // Set optional fields to null if empty
        $alamat_val = empty($alamat) ? null : $alamat;
        $latitude_val = ($latitude === '') ? null : (float)$latitude;
        $longitude_val = ($longitude === '') ? null : (float)$longitude;
        $kapasiti_val = ($kapasiti === '') ? null : (int)$kapasiti;
        $catatan_val = empty($catatan) ? null : $catatan;

        // Prepared statement update
        $stmt = $conn->prepare("UPDATE tbl_venue SET nama_tempat = ?, alamat = ?, latitude = ?, longitude = ?, kapasiti = ?, catatan = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssddisi", $nama_tempat, $alamat_val, $latitude_val, $longitude_val, $kapasiti_val, $catatan_val, $id);
            
            if ($stmt->execute()) {
                // Rekod Audit Log
                log_audit($conn, $_SESSION['admin_id'], 'update', 'tbl_venue', $id, "Kemas kini venue: $nama_tempat");
                
                $_SESSION['success_msg'] = "Venue '$nama_tempat' berjaya dikemas kini!";
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
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Venue</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Kemas Kini Venue Pertandingan</h3>
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
                    <label for="nama_tempat" class="form-label fw-semibold">Nama Tempat / Venue <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_tempat" name="nama_tempat" value="<?php echo sanitize($venue['nama_tempat']); ?>" required>
                    <div class="invalid-feedback">Sila masukkan nama tempat atau venue.</div>
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label fw-semibold">Alamat Lokasi</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="2"><?php echo sanitize($venue['alamat']); ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="latitude" class="form-label fw-semibold">Latitude</label>
                        <input type="number" step="any" class="form-control" id="latitude" name="latitude" value="<?php echo ($venue['latitude'] !== null) ? sanitize($venue['latitude']) : ''; ?>">
                    </div>
                    <div class="col-6">
                        <label for="longitude" class="form-label fw-semibold">Longitude</label>
                        <input type="number" step="any" class="form-control" id="longitude" name="longitude" value="<?php echo ($venue['longitude'] !== null) ? sanitize($venue['longitude']) : ''; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="kapasiti" class="form-label fw-semibold">Kapasiti Penonton (Orang)</label>
                    <input type="number" class="form-control" id="kapasiti" name="kapasiti" value="<?php echo ($venue['kapasiti'] !== null) ? sanitize($venue['kapasiti']) : ''; ?>" min="0">
                </div>

                <div class="mb-3">
                    <label for="catatan" class="form-label fw-semibold">Catatan / Maklumat Tambahan</label>
                    <textarea class="form-control" id="catatan" name="catatan" rows="2"><?php echo sanitize($venue['catatan']); ?></textarea>
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
