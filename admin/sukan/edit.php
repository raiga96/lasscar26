<?php
/**
 * Kemas Kini Acara Sukan - Admin SukanJTS Sarawak
 * CRUD: Update
 */

$page_title = "Kemas Kini Sukan";
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

// Ambil maklumat sukan semasa
$stmt_fetch = $conn->prepare("SELECT * FROM tbl_sukan WHERE id = ? LIMIT 1");
$stmt_fetch->bind_param("i", $id);
$stmt_fetch->execute();
$res = $stmt_fetch->get_result();

if ($res->num_rows !== 1) {
    $stmt_fetch->close();
    $_SESSION['error_msg'] = "Sukan tidak dijumpai.";
    header("Location: index.php");
    exit;
}

$sukan = $res->fetch_assoc();
$stmt_fetch->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_sukan      = trim($_POST['nama_sukan'] ?? '');
    $kategori        = $_POST['kategori'] ?? 'campuran';
    $jenis_perlawanan = $_POST['jenis_perlawanan'] ?? 'berpasukan';
    $ikon            = trim($_POST['ikon'] ?? 'bi-trophy');
    $keterangan      = trim($_POST['keterangan'] ?? '');
    $status          = $_POST['status'] ?? 'aktif';
    $csrf_token      = $_POST['csrf_token'] ?? '';

    // Validate CSRF
    if (!verify_csrf_token($csrf_token)) {
        $error_msg = "Token keselamatan tamat tempoh. Sila hantar borang semula.";
    } elseif (empty($nama_sukan)) {
        $error_msg = "Sila isi nama sukan.";
    } else {
        // Prepared statement update
        $stmt = $conn->prepare("UPDATE tbl_sukan SET nama_sukan = ?, kategori = ?, jenis_perlawanan = ?, ikon = ?, keterangan = ?, status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssssssi", $nama_sukan, $kategori, $jenis_perlawanan, $ikon, $keterangan, $status, $id);
            
            if ($stmt->execute()) {
                // Rekod Audit Log
                log_audit($conn, $_SESSION['admin_id'], 'update', 'tbl_sukan', $id, "Kemas kini sukan: $nama_sukan (Kategori: $kategori)");
                
                $_SESSION['success_msg'] = "Acara sukan '$nama_sukan' berjaya dikemas kini!";
                header("Location: index.php");
                exit;
            } else {
                $error_msg = "Gagal mengemaskini rekod: " . $stmt->error;
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
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Sukan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Kemas Kini Acara Sukan</h3>
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
                    <label for="nama_sukan" class="form-label fw-semibold">Nama Acara Sukan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_sukan" name="nama_sukan" value="<?php echo sanitize($sukan['nama_sukan']); ?>" required>
                    <div class="invalid-feedback">Sila masukkan nama acara sukan.</div>
                </div>

                <div class="mb-3">
                    <label for="kategori" class="form-label fw-semibold">Kategori Peserta</label>
                    <select class="form-select" id="kategori" name="kategori">
                        <option value="campuran" <?php echo ($sukan['kategori'] === 'campuran') ? 'selected' : ''; ?>>Campuran (Lelaki & Wanita)</option>
                        <option value="lelaki" <?php echo ($sukan['kategori'] === 'lelaki') ? 'selected' : ''; ?>>Lelaki Sahaja</option>
                        <option value="wanita" <?php echo ($sukan['kategori'] === 'wanita') ? 'selected' : ''; ?>>Wanita Sahaja</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="jenis_perlawanan" class="form-label fw-semibold">Jenis Perlawanan</label>
                    <select class="form-select" id="jenis_perlawanan" name="jenis_perlawanan">
                        <option value="berpasukan" <?php echo ($sukan['jenis_perlawanan'] === 'berpasukan') ? 'selected' : ''; ?>>Berpasukan (Kontinjen Berlawan)</option>
                        <option value="individu" <?php echo ($sukan['jenis_perlawanan'] === 'individu') ? 'selected' : ''; ?>>Individu (Peserta Perseorangan)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="ikon" class="form-label fw-semibold">Ikon Acara (Bootstrap Icons)</label>
                    <select class="form-select" id="ikon" name="ikon">
                        <option value="bi-trophy" <?php echo ($sukan['ikon'] === 'bi-trophy') ? 'selected' : ''; ?>>🏆 Piala (bi-trophy)</option>
                        <option value="bi-dribbble" <?php echo ($sukan['ikon'] === 'bi-dribbble') ? 'selected' : ''; ?>>⚽ Bola Sepak (bi-dribbble)</option>
                        <option value="bi-lightning-fill" <?php echo ($sukan['ikon'] === 'bi-lightning-fill') ? 'selected' : ''; ?>>🏸 Badminton (bi-lightning-fill)</option>
                        <option value="bi-grid-3x3-gap-fill" <?php echo ($sukan['ikon'] === 'bi-grid-3x3-gap-fill') ? 'selected' : ''; ?>>♟️ Catur (bi-grid-3x3-gap-fill)</option>
                        <option value="bi-bullseye" <?php echo ($sukan['ikon'] === 'bi-bullseye') ? 'selected' : ''; ?>>🎯 Dart (bi-bullseye)</option>
                        <option value="bi-basketball" <?php echo ($sukan['ikon'] === 'bi-basketball') ? 'selected' : ''; ?>>🏀 Bola Jaring/Netball (bi-basketball)</option>
                        <option value="bi-circle-fill" <?php echo ($sukan['ikon'] === 'bi-circle-fill') ? 'selected' : ''; ?>>⚫ Karom/Bulatan (bi-circle-fill)</option>
                        <option value="bi-activity" <?php echo ($sukan['ikon'] === 'bi-activity') ? 'selected' : ''; ?>>🏃 Larian (bi-activity)</option>
                        <option value="bi-star-fill" <?php echo ($sukan['ikon'] === 'bi-star-fill') ? 'selected' : ''; ?>>⭐ Bintang (bi-star-fill)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label fw-semibold">Keterangan Ringkas</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?php echo sanitize($sukan['keterangan']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label fw-semibold">Status Acara</label>
                    <select class="form-select" id="status" name="status">
                        <option value="aktif" <?php echo ($sukan['status'] === 'aktif') ? 'selected' : ''; ?>>Aktif (Akan Dipertandingkan)</option>
                        <option value="tidak_aktif" <?php echo ($sukan['status'] === 'tidak_aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                    </select>
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
