<?php
/**
 * Spin Wheel Reveal (Majlis Pengumuman Penganjur LASSCAR 2028)
 * Path: spinwheel/index.php
 * Visual UI/UX: ChatGPT | Backend Logic: Claude
 */

require_once __DIR__ . '/../includes/db.php';
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spin Wheel Reveal Penganjur LASSCAR 2028 — JTS Sarawak</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Custom Spin Wheel CSS -->
    <link rel="stylesheet" href="assets/css/wheel.css">
</head>
<body>

    <!-- Butang Toggle Panel Admin (Atas Kanan Tersembunyi) -->
    <button class="admin-toggle-btn" id="adminToggleBtn">
        <i class="bi bi-gear-fill me-1"></i> Panel Setup (Admin)
    </button>

    <!-- Top Bar Admin Control (Secara Tersembunyi) -->
    <div class="admin-control-bar py-3 px-4 d-none" id="adminBar">
        <div class="container-fluid">
            <div class="row align-items-center g-3">
                <div class="col-md-5">
                    <label for="selectPemenang" class="form-label text-warning small fw-bold mb-1">
                        <i class="bi bi-shield-lock-fill me-1"></i> KUNCI PENGANJUR LASSCAR 2028 (PREDETERMINED)
                    </label>
                    <select class="form-select form-select-sm bg-dark text-white border-secondary" id="selectPemenang">
                        <option value="">-- Memuatkan Bahagian... --</option>
                    </select>
                </div>
                <div class="col-md-7 d-flex gap-2 align-items-end">
                    <button class="btn btn-warning btn-sm fw-bold px-3" id="btnLockWinner">
                        <i class="bi bi-key-fill me-1"></i> Kunci Pilihan Penganjur
                    </button>
                    <button class="btn btn-outline-danger btn-sm px-3" id="btnResetWheel">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Set Semula
                    </button>
                    <span class="text-muted extra-small ms-auto align-self-center">
                        <i class="bi bi-info-circle me-1"></i> Tetapan ini tersembunyi semasa majlis.
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Header Main Stage Studio -->
    <header class="spin-header text-center py-4 px-3 position-relative z-1">
        <div class="container">
            <div class="d-inline-flex align-items-center gap-2 brand-badge px-3 py-1.5 rounded-pill mb-2 shadow-sm">
                <i class="bi bi-trophy-fill text-warning"></i>
                <span class="fw-bold small">MAJLIS PENGUMUMAN PENGANJUR RASMI</span>
            </div>
            <h1 class="main-title display-4 text-uppercase mb-1">LASSCAR 2028</h1>
            <p class="text-slate-300 fs-6 col-md-8 mx-auto opacity-75 mb-0">Landas Sport Carnival — Jabatan Tanah dan Survei Sarawak</p>
        </div>
    </header>

    <!-- Main Stage Wheel Arena -->
    <main class="container my-3 position-relative z-1">
        <div class="row align-items-center justify-content-center">
            <div class="col-12 text-center">

                <!-- Wheel Container Stage -->
                <div class="wheel-stage my-2">
                    
                    <!-- Ring Luar Glowing Gold -->
                    <div class="wheel-outer-ring"></div>

                    <!-- Penunjuk Roda (Top Pointer Arrow) -->
                    <div class="wheel-pointer">
                        <div class="pointer-arrow"></div>
                    </div>

                    <!-- Canvas Roda Spin -->
                    <canvas id="wheelCanvas"></canvas>

                    <!-- Hub Tengah Roda -->
                    <div class="wheel-center-hub">
                        <img src="../assets/uploads/logo-bahagian/default_logo.png" style="width: 50px; height: 50px; object-fit: contain;" alt="JTS Logo">
                    </div>
                </div>

                <!-- Status & Action Controls -->
                <div class="mt-4 mb-3">
                    <div class="d-inline-flex align-items-center gap-2 status-pill mb-3">
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill" id="statusBadge">CHECKING...</span>
                        <span class="text-slate-200 small fw-medium" id="statusText">Memuatkan status roda spin...</span>
                    </div>

                    <div>
                        <button class="btn btn-spin-action shadow-lg" id="btnSpin" disabled>
                            <i class="bi bi-play-circle-fill me-2"></i> SPIN WHEEL REVEAL
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer Copyright -->
    <footer class="text-center py-3 text-slate-400 small opacity-60">
        &copy; 2026 Jabatan Tanah dan Survei Sarawak — Modul Spin Wheel LASSCAR 2028
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Canvas Confetti JS -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

    <!-- Custom Spin Wheel JS Engine -->
    <script src="assets/js/wheel.js"></script>
</body>
</html>
