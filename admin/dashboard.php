<?php
/**
 * Papan Pemuka Pentadbir (Admin Dashboard) - SukanJTS Sarawak
 * Memaparkan statistik kejohanan semasa dan ringkasan audit log.
 */

$page_title = "Papan Pemuka Dashboard";

// Panggil layout header (auth-check secara automatik dipanggil di sini)
require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
require_once __DIR__ . '/../includes/db.php';

$user_role = $_SESSION['admin_role'];

// 1. Ambil Statistik
// Statistik Sukan
$res_sukan = $conn->query("SELECT COUNT(*) as total FROM tbl_sukan WHERE status = 'aktif'");
$total_sukan = $res_sukan ? $res_sukan->fetch_assoc()['total'] : 0;

// Statistik Live Matches
$res_live = $conn->query("SELECT COUNT(*) as total FROM tbl_jadual_perlawanan WHERE status = 'live'");
$total_live = $res_live ? $res_live->fetch_assoc()['total'] : 0;

// Statistik Kontinjen/Bahagian
$res_bahagian = $conn->query("SELECT COUNT(*) as total FROM tbl_bahagian WHERE status = 'aktif'");
$total_bahagian = $res_bahagian ? $res_bahagian->fetch_assoc()['total'] : 0;

// Statistik Media Galeri
$res_media = $conn->query("SELECT COUNT(*) as total FROM tbl_galeri");
$total_media = $res_media ? $res_media->fetch_assoc()['total'] : 0;
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold text-dark">Selamat Datang ke Panel Pengurusan SukanJTS</h2>
        <p class="text-muted">Gunakan panel ini untuk mengemas kini semua maklumat kejohanan secara masa nyata.</p>
    </div>
</div>

<!-- Grid Kad Statistik -->
<div class="row g-3 mb-4">
    <!-- Kad Kontinjen -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-admin p-3 border-start border-primary border-4 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted small text-uppercase mb-1">Jumlah Kontinjen</h6>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo $total_bahagian; ?></h3>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                    <i class="bi bi-flag-fill fs-3"></i>
                </div>
            </div>
            <?php if ($user_role === 'super_admin'): ?>
                <div class="mt-2 pt-2 border-top">
                    <a href="bahagian/index.php" class="text-decoration-none small text-primary fw-medium">Urus Kontinjen →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Kad Sukan -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-admin p-3 border-start border-success border-4 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted small text-uppercase mb-1">Acara Sukan Aktif</h6>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo $total_sukan; ?></h3>
                </div>
                <div class="bg-success bg-opacity-10 text-success rounded p-3">
                    <i class="bi bi-trophy-fill fs-3"></i>
                </div>
            </div>
            <?php if ($user_role === 'super_admin'): ?>
                <div class="mt-2 pt-2 border-top">
                    <a href="sukan/index.php" class="text-decoration-none small text-success fw-medium">Urus Sukan →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Kad Live Match -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-admin p-3 border-start border-danger border-4 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted small text-uppercase mb-1">Perlawanan LIVE</h6>
                    <h3 class="fw-bold mb-0 text-danger d-flex align-items-center gap-2">
                        <?php echo $total_live; ?>
                        <?php if ($total_live > 0): ?>
                            <span class="badge badge-live p-1 rounded-circle" style="width: 10px; height: 10px;"></span>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                    <i class="bi bi-broadcast fs-3"></i>
                </div>
            </div>
            <?php if (in_array($user_role, ['super_admin', 'editor'])): ?>
                <div class="mt-2 pt-2 border-top">
                    <a href="keputusan/index.php" class="text-decoration-none small text-danger fw-medium">Kemas kini Skor →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Kad Galeri -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-admin p-3 border-start border-warning border-4 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted small text-uppercase mb-1">Galeri Media</h6>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo $total_media; ?></h3>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                    <i class="bi bi-images fs-3"></i>
                </div>
            </div>
            <?php if (in_array($user_role, ['super_admin', 'media'])): ?>
                <div class="mt-2 pt-2 border-top">
                    <a href="galeri/index.php" class="text-decoration-none small text-warning fw-medium text-dark">Urus Media →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Log Audit Terkini (Hanya untuk Super Admin) -->
    <?php if ($user_role === 'super_admin'): ?>
    <div class="col-12 col-lg-8">
        <div class="card card-admin p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark m-0">Log Audit Sistem Terkini</h5>
                <a href="audit-log/index.php" class="btn btn-sm btn-navy fw-medium">Papar Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tarikh/Masa</th>
                            <th>Pengguna</th>
                            <th>Tindakan</th>
                            <th>Jadual</th>
                            <th>Butiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT l.*, p.nama_penuh FROM tbl_audit_log l 
                                  LEFT JOIN tbl_pengguna p ON l.pengguna_id = p.id 
                                  ORDER BY l.dicipta_pada DESC LIMIT 5";
                        $result = $conn->query($query);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $tindakan_badge = '';
                                switch ($row['tindakan']) {
                                    case 'create': $tindakan_badge = '<span class="badge bg-success">CREATE</span>'; break;
                                    case 'update': $tindakan_badge = '<span class="badge bg-info text-dark">UPDATE</span>'; break;
                                    case 'delete': $tindakan_badge = '<span class="badge bg-danger">DELETE</span>'; break;
                                    case 'login':  $tindakan_badge = '<span class="badge bg-primary">LOGIN</span>'; break;
                                    case 'logout': $tindakan_badge = '<span class="badge bg-secondary">LOGOUT</span>'; break;
                                }
                                echo "<tr>";
                                echo "<td>" . sanitize($row['dicipta_pada']) . "</td>";
                                echo "<td class='fw-semibold'>" . sanitize($row['nama_penuh'] ?? 'Sistem') . "</td>";
                                echo "<td>" . $tindakan_badge . "</td>";
                                echo "<td><code>" . sanitize($row['jadual_disentuh']) . "</code></td>";
                                echo "<td>" . sanitize($row['butiran']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center text-muted'>Tiada log aktiviti direkodkan.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pintasan Pantas (Semua Peranan) -->
    <div class="col-12 <?php echo ($user_role === 'super_admin') ? 'col-lg-4' : 'col-lg-12'; ?>">
        <div class="card card-admin p-4 h-100">
            <h5 class="fw-bold text-dark mb-3">Tindakan Pantas</h5>
            <div class="d-grid gap-2">
                <?php if ($user_role === 'super_admin'): ?>
                    <a href="bahagian/create.php" class="btn btn-outline-primary text-start p-3 d-flex align-items-center gap-3">
                        <i class="bi bi-flag fs-4"></i>
                        <div>
                            <div class="fw-semibold">Tambah Kontinjen</div>
                            <div class="small text-muted">Daftar bahagian atau jabatan jemputan</div>
                        </div>
                    </a>
                    <a href="sukan/create.php" class="btn btn-outline-success text-start p-3 d-flex align-items-center gap-3">
                        <i class="bi bi-trophy fs-4"></i>
                        <div>
                            <div class="fw-semibold">Tambah Acara Sukan</div>
                            <div class="small text-muted">Daftar sukan & kategori kejohanan</div>
                        </div>
                    </a>
                <?php endif; ?>
                
                <?php if (in_array($user_role, ['super_admin', 'editor'])): ?>
                    <a href="jadual/create.php" class="btn btn-outline-danger text-start p-3 d-flex align-items-center gap-3">
                        <i class="bi bi-calendar-plus fs-4"></i>
                        <div>
                            <div class="fw-semibold">Cipta Jadual Fixture</div>
                            <div class="small text-muted">Bina padanan pasukan & venue perlawanan</div>
                        </div>
                    </a>
                <?php endif; ?>

                <?php if (in_array($user_role, ['super_admin', 'media'])): ?>
                    <a href="galeri/create.php" class="btn btn-outline-warning text-start p-3 d-flex align-items-center gap-3 text-dark">
                        <i class="bi bi-image fs-4"></i>
                        <div>
                            <div class="fw-semibold">Muat Naik Media</div>
                            <div class="small text-muted">Tambah gambar & video kejohanan</div>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Panggil layout footer
require_once __DIR__ . '/../includes/admin-footer.php';
?>
