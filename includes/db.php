<?php
/**
 * Modul Sambungan Pangkalan Data (MySQLi Object-Oriented)
 * Dipanggil secara global merentasi sistem.
 */

// Panggil fail konfigurasi jika belum dipanggil
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/config.php';
}

// Matikan laporan ralat MySQLi mentah ke paparan untuk mengelakkan pendedahan laluan/kredensial
mysqli_report(MYSQLI_REPORT_OFF);

// Mulakan sambungan MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Semak status sambungan
if ($conn->connect_error) {
    // Catat ke log ralat
    error_log("Sambungan Database Gagal: " . $conn->connect_error);
    
    // Tunjukkan halaman ralat yang premium dan mesra pengguna
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="ms">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ralat Sambungan Sistem</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background-color: #f3f4f6;
                color: #1f2937;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
            }
            .card {
                border: none;
                border-radius: 12px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                max-width: 450px;
                width: 100%;
                padding: 2rem;
                text-align: center;
            }
            .icon {
                font-size: 3.5rem;
                color: #ef4444;
                margin-bottom: 1rem;
            }
            h1 {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
            }
            p {
                font-size: 0.9rem;
                color: #6b7280;
                line-height: 1.5;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">⚠️</div>
            <h1>Gangguan Sistem Sementara</h1>
            <p>Sistem pengurusan sukan sedang mengalami gangguan sambungan pangkalan data secara teknikal. Sila hubungi Pentadbir Sistem JTS Sarawak.</p>
            <hr>
            <p class="text-muted small mb-0">Ralat: Pangkalan data tidak dapat dicapai. Sila pastikan pelayan MySQL aktif dan skrip pemasangan dijalankan.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Set charset sambungan ke utf8mb4 agar dapat memproses logo, teks, dan simbol sukan dengan baik
$conn->set_charset("utf8mb4");
