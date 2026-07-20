<?php
/**
 * Tambah Acara Sukan Baru - Admin SukanJTS Sarawak
 * CRUD: Create
 */

$page_title = "Tambah Sukan";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan hanya super_admin boleh mengakses modul ini
confirm_access(['super_admin']);

$error_msg = '';
$success_msg = '';

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
        // Prepared statement insert
        $stmt = $conn->prepare("INSERT INTO tbl_sukan (nama_sukan, kategori, jenis_perlawanan, ikon, keterangan, status) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssss", $nama_sukan, $kategori, $jenis_perlawanan, $ikon, $keterangan, $status);
            
            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                
                // Rekod Audit Log
                log_audit($conn, $_SESSION['admin_id'], 'create', 'tbl_sukan', $new_id, "Tambah sukan baru: $nama_sukan (Kategori: $kategori)");
                
                $_SESSION['success_msg'] = "Sukan '$nama_sukan' berjaya didaftarkan!";
                header("Location: index.php");
                exit;
            } else {
                $error_msg = "Gagal menyimpan rekod: " . $stmt->error;
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
                <li class="breadcrumb-item active" aria-current="page">Tambah</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-0">Daftar Acara Sukan Baru</h3>
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
                    <label for="nama_sukan" class="form-label fw-semibold">Nama Acara Sukan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_sukan" name="nama_sukan" placeholder="Cth: Badminton / Bola Sepak / Catur" required>
                    <div class="invalid-feedback">Sila masukkan nama acara sukan.</div>
                </div>

                <div class="mb-3">
                    <label for="kategori" class="form-label fw-semibold">Kategori Peserta</label>
                    <select class="form-select" id="kategori" name="kategori">
                        <option value="campuran">Campuran (Lelaki & Wanita)</option>
                        <option value="lelaki">Lelaki Sahaja</option>
                        <option value="wanita">Wanita Sahaja</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="jenis_perlawanan" class="form-label fw-semibold">Jenis Perlawanan</label>
                    <select class="form-select" id="jenis_perlawanan" name="jenis_perlawanan">
                        <option value="berpasukan">Berpasukan (Kontinjen Berlawan)</option>
                        <option value="individu">Individu (Peserta Perseorangan)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="ikon" class="form-label fw-semibold">Ikon Acara (Bootstrap Icons)</label>
                    <select class="form-select" id="ikon" name="ikon">
                        <option value="bi-trophy">🏆 Piala (bi-trophy)</option>
                        <option value="bi-dribbble">⚽ Bola Sepak (bi-dribbble)</option>
                        <option value="bi-lightning-fill">🏸 Badminton (bi-lightning-fill)</option>
                        <option value="bi-grid-3x3-gap-fill">♟️ Catur (bi-grid-3x3-gap-fill)</option>
                        <option value="bi-bullseye">🎯 Dart (bi-bullseye)</option>
                        <option value="bi-basketball">🏀 Bola Jaring/Netball (bi-basketball)</option>
                        <option value="bi-circle-fill">⚫ Karom/Bulatan (bi-circle-fill)</option>
                        <option value="bi-activity">🏃 Larian (bi-activity)</option>
                        <option value="bi-star-fill">⭐ Bintang (bi-star-fill)</option>
                    </select>
                    <div class="form-text small text-muted">Ikon ini akan dipaparkan di portal awam.</div>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label fw-semibold">Keterangan Ringkas</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Masukkan ringkasan peraturan, jumlah pemain, dsb."></textarea>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label fw-semibold">Status Acara</label>
                    <select class="form-select" id="status" name="status">
                        <option value="aktif">Aktif (Akan Dipertandingkan)</option>
                        <option value="tidak_aktif">Tidak Aktif</option>
                    </select>
                </div>

                <div class="pt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-4">Simpan Rekod</button>
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
