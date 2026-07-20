<?php
/**
 * Halaman Log Audit Sistem - Admin SukanJTS Sarawak
 * Memaparkan rekod transaksi append-only untuk pemantauan aktiviti pentadbir.
 */

$page_title = "Log Audit Sistem";
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/db.php';

// Pastikan hanya super_admin boleh mengakses modul ini
confirm_access(['super_admin']);

// Konfigurasi Pagination Ringkas
$limit = 50; // Papar 50 rekod sehelai
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Dapatkan jumlah rekod keseluruhan
$total_res = $conn->query("SELECT COUNT(*) as total FROM tbl_audit_log");
$total_records = $total_res ? $total_res->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_records / $limit);
?>

<div class="row mb-3 align-items-center">
    <div class="col-12">
        <h3 class="fw-bold text-dark mb-0">Audit Trail / Log Transaksi Sistem</h3>
        <p class="text-muted small mb-0">Memantau semua aktiviti pendaftaran, kemas kini, pemadaman, serta kemasukan/keluar sistem oleh pengguna pentadbir (Append-only).</p>
    </div>
</div>

<div class="card card-admin p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small">
            <thead class="table-light">
                <tr>
                    <th style="width: 80px;">ID Log</th>
                    <th style="width: 160px;">Tarikh & Masa</th>
                    <th>Pengguna Pentadbir</th>
                    <th>Tindakan</th>
                    <th>Jadual Disentuh</th>
                    <th>ID Rekod</th>
                    <th>Butiran Aktiviti</th>
                    <th>Alamat IP</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT l.*, p.nama_penuh, p.emel 
                                        FROM tbl_audit_log l 
                                        LEFT JOIN tbl_pengguna p ON l.pengguna_id = p.id 
                                        ORDER BY l.dicipta_pada DESC LIMIT ? OFFSET ?");
                if ($stmt) {
                    $stmt->bind_param("ii", $limit, $offset);
                    $stmt->execute();
                    $result = $stmt->get_result();

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
                            ?>
                            <tr>
                                <td><code>#<?php echo sanitize($row['id']); ?></code></td>
                                <td><span class="fw-semibold text-dark"><?php echo sanitize($row['dicipta_pada']); ?></span></td>
                                <td>
                                    <?php if ($row['pengguna_id']): ?>
                                        <strong class="text-dark"><?php echo sanitize($row['nama_penuh']); ?></strong>
                                        <div class="text-muted small"><?php echo sanitize($row['emel']); ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">Sistem / Awam</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $tindakan_badge; ?></td>
                                <td><code><?php echo sanitize($row['jadual_disentuh']); ?></code></td>
                                <td><code><?php echo $row['rekod_id'] !== null ? sanitize($row['rekod_id']) : '-'; ?></code></td>
                                <td class="text-dark fw-medium"><?php echo sanitize($row['butiran'] ?: '-'); ?></td>
                                <td><code><?php echo sanitize($row['ip_address'] ?: '127.0.0.1'); ?></code></td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center text-muted p-4'>Tiada log aktiviti direkodkan dalam sistem.</td></tr>";
                    }
                    $stmt->close();
                } else {
                    echo "<tr><td colspan='8' class='text-center text-danger p-4'>Gagal mendapatkan log audit dari pangkalan data.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination UI -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">Sebelumnya</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Seterusnya</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
