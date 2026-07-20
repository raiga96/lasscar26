<?php
/**
 * Tentatif & Aturcara Program - Portal Awam SukanJTS Sarawak
 * Memaparkan susunan aturcara bagi Perasmian Pembukaan, Penutupan/Dinner dan Aturcara Kejohanan Umum.
 */

$page_title = "Aturcara & Tentatif Majlis";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';

// Ambil aturcara mengikut jenis
$res_umum = $conn->query("SELECT * FROM tbl_aturcara WHERE jenis = 'umum' ORDER BY tarikh ASC, susunan ASC, masa ASC");
$res_buka = $conn->query("SELECT * FROM tbl_aturcara WHERE jenis = 'pembukaan' ORDER BY tarikh ASC, susunan ASC, masa ASC");
$res_tutup = $conn->query("SELECT * FROM tbl_aturcara WHERE jenis = 'penutup' ORDER BY tarikh ASC, susunan ASC, masa ASC");
?>

<!-- Header -->
<div class="py-4 bg-navy text-white text-center mb-5" style="background-color: var(--navy-blue); border-bottom: 4px solid var(--gold);">
    <div class="container">
        <h2 class="fw-bold mb-1">Jadual Aturcara & Tentatif Majlis</h2>
        <p class="lead small mb-0">Atur cara rasmi bagi majlis perasmian pembukaan, makan malam, dan penutupan kejohanan.</p>
    </div>
</div>

<div class="container">
    <div class="row g-4">
        
        <!-- Majlis Perasmian Pembukaan -->
        <div class="col-lg-6">
            <div class="card card-admin p-4 border shadow-sm h-100">
                <h4 class="fw-bold text-navy mb-4 border-bottom pb-2"><i class="bi bi-door-open-fill text-primary"></i> Majlis Perasmian Pembukaan</h4>
                
                <?php if ($res_buka && $res_buka->num_rows > 0): ?>
                    <div class="position-relative ps-4 border-start border-primary border-2 ms-2">
                        <?php while ($row = $res_buka->fetch_assoc()): ?>
                            <div class="position-relative mb-4">
                                <!-- Dot Penunjuk Timeline -->
                                <span class="position-absolute start-0 translate-middle-x bg-primary rounded-circle border border-white" style="left: -25px; width: 14px; height: 14px;"></span>
                                
                                <div class="fw-bold text-dark fs-5"><?php echo format_time($row['masa']); ?></div>
                                <div class="text-muted small mb-1"><i class="bi bi-calendar3"></i> <?php echo format_date($row['tarikh']); ?></div>
                                <h5 class="fw-bold text-navy mb-1"><?php echo sanitize($row['aktiviti']); ?></h5>
                                <?php if ($row['pegawai_bertanggungjawab']): ?>
                                    <div class="small text-secondary"><i class="bi bi-person-fill text-muted me-1"></i> Penanggungjawab: <strong><?php echo sanitize($row['pegawai_bertanggungjawab']); ?></strong></div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-5">Tiada tentatif majlis pembukaan didaftarkan.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Majlis Makan Malam & Penutupan -->
        <div class="col-lg-6">
            <div class="card card-admin p-4 border shadow-sm h-100">
                <h4 class="fw-bold text-navy mb-4 border-bottom pb-2"><i class="bi bi-moon-stars-fill text-warning"></i> Majlis Makan Malam & Penutupan</h4>
                
                <?php if ($res_tutup && $res_tutup->num_rows > 0): ?>
                    <div class="position-relative ps-4 border-start border-warning border-2 ms-2">
                        <?php while ($row = $res_tutup->fetch_assoc()): ?>
                            <div class="position-relative mb-4">
                                <!-- Dot Penunjuk Timeline -->
                                <span class="position-absolute start-0 translate-middle-x bg-warning rounded-circle border border-white" style="left: -25px; width: 14px; height: 14px;"></span>
                                
                                <div class="fw-bold text-dark fs-5"><?php echo format_time($row['masa']); ?></div>
                                <div class="text-muted small mb-1"><i class="bi bi-calendar3"></i> <?php echo format_date($row['tarikh']); ?></div>
                                <h5 class="fw-bold text-navy mb-1"><?php echo sanitize($row['aktiviti']); ?></h5>
                                <?php if ($row['pegawai_bertanggungjawab']): ?>
                                    <div class="small text-secondary"><i class="bi bi-person-fill text-muted me-1"></i> Penanggungjawab: <strong><?php echo sanitize($row['pegawai_bertanggungjawab']); ?></strong></div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-5">Tiada tentatif majlis penutupan didaftarkan.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tentatif Sukan & Aturcara Umum -->
        <div class="col-12 mt-5">
            <div class="card card-admin p-4 border shadow-sm">
                <h4 class="fw-bold text-navy mb-4 border-bottom pb-2"><i class="bi bi-calendar-check-fill text-success"></i> Aturcara Umum Kejohanan (Tentatif Harian)</h4>
                
                <?php if ($res_umum && $res_umum->num_rows > 0): ?>
                    <div class="row g-4">
                        <?php 
                        $current_date = '';
                        while ($row = $res_umum->fetch_assoc()): 
                            if ($current_date !== $row['tarikh']) {
                                $current_date = $row['tarikh'];
                                echo "<div class='col-12 mt-4'><h5 class='fw-bold text-primary mb-3'><i class='bi bi-calendar3-week'></i> " . format_date($current_date) . "</h5></div>";
                            }
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="p-3 bg-light rounded border border-start border-4 border-success h-100">
                                    <div class="fw-bold text-dark mb-1"><i class="bi bi-clock me-1"></i> <?php echo format_time($row['masa']); ?></div>
                                    <h6 class="fw-bold text-navy mb-2"><?php echo sanitize($row['aktiviti']); ?></h6>
                                    <?php if ($row['pegawai_bertanggungjawab']): ?>
                                        <div class="small text-muted"><i class="bi bi-person me-1"></i> Pegawai: <?php echo sanitize($row['pegawai_bertanggungjawab']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-5">Tiada aturcara sukan harian didaftarkan lagi.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
