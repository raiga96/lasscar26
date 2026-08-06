<?php
/**
 * Profil Kontinjen & Jabatan Jemputan - Portal Awam SukanJTS Sarawak
 * Memaparkan kad kontinjen dengan logo dan ringkasan penyertaan sukan & pingat.
 */

$page_title = "Kontinjen & Jabatan Jemputan";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
?>

<!-- Header -->
<div class="py-4 bg-navy text-white text-center mb-5" style="background-color: var(--navy-blue); border-bottom: 4px solid var(--gold);">
    <div class="container">
        <h2 class="fw-bold mb-1">Kontinjen & Jabatan Jemputan</h2>
        <p class="lead small mb-0">Senarai kontinjen rasmi Jabatan Tanah dan Survei Sarawak serta agensi luar jemputan.</p>
    </div>
</div>

<div class="container">
    <!-- Bahagian 1: Pejabat Bahagian JTS Sarawak -->
    <div class="mb-5">
        <h3 class="section-title">Pejabat Bahagian JTS Sarawak</h3>
        <div class="row g-4">
            <?php
            // Ambil kontinjen dalaman
            $query_dalaman = "
                SELECT b.*, 
                       (SELECT COUNT(*) FROM tbl_pasukan WHERE bahagian_id = b.id) as total_sukan,
                       kp.emas, kp.perak, kp.gangsa
                FROM tbl_bahagian b
                LEFT JOIN vw_kedudukan_pingat kp ON kp.bahagian_id = b.id
                WHERE b.jenis = 'dalaman' AND b.status = 'aktif'
                ORDER BY b.nama_bahagian ASC";
            $res_d = $conn->query($query_dalaman);

            if ($res_d && $res_d->num_rows > 0):
                while ($row = $res_d->fetch_assoc()):
                    $logo_path = BASE_URL . 'assets/uploads/logo-bahagian/' . ($row['logo_url'] ?: 'default_logo.png');
                    ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden text-center p-3 bg-white card-hover-effect">
                            <div class="my-3 d-flex align-items-center justify-content-center" style="height: 110px;">
                                <img src="<?php echo $logo_path; ?>" alt="<?php echo sanitize($row['nama_bahagian']); ?>" class="img-fluid p-1" style="max-width: 100px; max-height: 100px; object-fit: contain;">
                            </div>
                            <h5 class="fw-bold text-dark mb-1 fs-6"><?php echo sanitize($row['nama_bahagian']); ?></h5>
                            <code class="text-secondary fw-semibold mb-2 d-block"><?php echo sanitize($row['singkatan']); ?></code>
                            
                            <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill py-1.5 px-3 mb-3 d-inline-block mx-auto small" style="width: fit-content;">
                                <i class="bi bi-trophy-fill me-1"></i> <?php echo $row['total_sukan']; ?> Sukan
                            </div>

                            <!-- Medal Stats -->
                            <div class="d-flex align-items-center justify-content-center gap-2 mt-auto border-top pt-3">
                                <span class="badge bg-light text-dark border py-1.5 px-2 small shadow-sm" title="Emas">🥇 <?php echo $row['emas'] ?: 0; ?></span>
                                <span class="badge bg-light text-dark border py-1.5 px-2 small shadow-sm" title="Perak">🥈 <?php echo $row['perak'] ?: 0; ?></span>
                                <span class="badge bg-light text-dark border py-1.5 px-2 small shadow-sm" title="Gangsa">🥉 <?php echo $row['gangsa'] ?: 0; ?></span>
                            </div>
                        </div>
                    </div>
                <?php 
                endwhile;
            else:
                echo "<div class='col-12 text-center text-muted'>Tiada kontinjen dalaman didaftarkan.</div>";
            endif;
            ?>
        </div>
    </div>

    <!-- Bahagian 2: Jabatan Jemputan Luar -->
    <div>
        <h3 class="section-title">Jabatan & Agensi Jemputan</h3>
        <div class="row g-4">
            <?php
            // Ambil kontinjen jemputan
            $query_jemputan = "
                SELECT b.*, 
                       (SELECT COUNT(*) FROM tbl_pasukan WHERE bahagian_id = b.id) as total_sukan,
                       kp.emas, kp.perak, kp.gangsa
                FROM tbl_bahagian b
                LEFT JOIN vw_kedudukan_pingat kp ON kp.bahagian_id = b.id
                WHERE b.jenis = 'jemputan' AND b.status = 'aktif'
                ORDER BY b.nama_bahagian ASC";
            $res_j = $conn->query($query_jemputan);

            if ($res_j && $res_j->num_rows > 0):
                while ($row = $res_j->fetch_assoc()):
                    $logo_path = BASE_URL . 'assets/uploads/logo-bahagian/' . ($row['logo_url'] ?: 'default_logo.png');
                    ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden text-center p-3 bg-white card-hover-effect">
                            <div class="my-3 d-flex align-items-center justify-content-center" style="height: 110px;">
                                <img src="<?php echo $logo_path; ?>" alt="<?php echo sanitize($row['nama_bahagian']); ?>" class="img-fluid p-1" style="max-width: 100px; max-height: 100px; object-fit: contain;">
                            </div>
                            <h5 class="fw-bold text-dark mb-1 fs-6"><?php echo sanitize($row['nama_bahagian']); ?></h5>
                            <code class="text-secondary fw-semibold mb-2 d-block"><?php echo sanitize($row['singkatan']); ?></code>
                            
                            <div class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill py-1.5 px-3 mb-3 d-inline-block mx-auto small" style="width: fit-content;">
                                <i class="bi bi-trophy-fill me-1"></i> <?php echo $row['total_sukan']; ?> Sukan
                            </div>

                            <!-- Medal Stats -->
                            <div class="d-flex align-items-center justify-content-center gap-2 mt-auto border-top pt-3">
                                <span class="badge bg-light text-dark border py-1.5 px-2 small shadow-sm" title="Emas">🥇 <?php echo $row['emas'] ?: 0; ?></span>
                                <span class="badge bg-light text-dark border py-1.5 px-2 small shadow-sm" title="Perak">🥈 <?php echo $row['perak'] ?: 0; ?></span>
                                <span class="badge bg-light text-dark border py-1.5 px-2 small shadow-sm" title="Gangsa">🥉 <?php echo $row['gangsa'] ?: 0; ?></span>
                            </div>
                        </div>
                    </div>
                <?php 
                endwhile;
            else:
                echo "<div class='col-12 text-center text-muted'>Tiada jabatan jemputan didaftarkan dalam sistem.</div>";
            endif;
            ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
