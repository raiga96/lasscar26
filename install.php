<?php
/**
 * Skrip Pemasangan Pangkalan Data - SukanJTS Sarawak
 * Boleh dijalankan melalui web (browser) atau CLI.
 */

// Panggil fail konfigurasi
require_once __DIR__ . '/config/config.php';

echo "=== SukanJTS Sarawak Database Installer ===\n";
if (php_sapi_name() !== 'cli') {
    echo "<pre>";
}

// 1. Sambung ke MySQL Server (tanpa memilih DB dahulu untuk cipta DB jika tiada)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) {
    die("Penyambungan ke MySQL Server gagal: " . $conn->connect_error . "\n");
}

echo "1. Menyambung ke MySQL Server... Berjaya!\n";

// Cipta Database jika belum ada
$db_create_query = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($db_create_query) === TRUE) {
    echo "2. Pangkalan data '" . DB_NAME . "' sedia digunakan.\n";
} else {
    die("Ralat semasa mencipta pangkalan data: " . $conn->error . "\n");
}

// Pilih database
$conn->select_db(DB_NAME);

// 2. Baca fail schema.sql dan jalankan
$schema_file = __DIR__ . '/database/schema.sql';
if (!file_exists($schema_file)) {
    die("Fail schema.sql tidak dijumpai di: " . $schema_file . "\n");
}

$schema_sql = file_get_contents($schema_file);

echo "3. Membaca schema.sql dan mencipta jadual...\n";
if ($conn->multi_query($schema_sql)) {
    // Kosongkan keputusan multi_query untuk mengelakkan ralat out-of-sync
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "   Jadual, view, dan indeks berjaya dicipta!\n";
} else {
    die("Ralat semasa menjalankan schema.sql: " . $conn->error . "\n");
}

// 3. Masukkan Seed Data Pentadbir (Akaun Utama)
echo "4. Memasukkan seed data pentadbir lalai...\n";
$admin_nama = "Administrator JTS";
$admin_emel = "admin@jts.sarawak.gov.my";
$admin_pass = password_hash("Admin@JTS2026", PASSWORD_BCRYPT);
$admin_role = "super_admin";

// Semak sama ada pengguna sudah ada
$stmt = $conn->prepare("SELECT id FROM tbl_pengguna WHERE emel = ?");
$stmt->bind_param("s", $admin_emel);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt_insert = $conn->prepare("INSERT INTO tbl_pengguna (nama_penuh, emel, kata_laluan, peranan, status) VALUES (?, ?, ?, ?, 'aktif')");
    $stmt_insert->bind_param("ssss", $admin_nama, $admin_emel, $admin_pass, $admin_role);
    if ($stmt_insert->execute()) {
        echo "   Akaun pentadbir utama berjaya dicipta!\n";
        echo "   E-mel: " . $admin_emel . "\n";
        echo "   Kata Laluan: Admin@JTS2026\n";
    } else {
        echo "   Ralat memasukkan akaun pentadbir: " . $stmt_insert->error . "\n";
    }
    $stmt_insert->close();
} else {
    echo "   Akaun pentadbir utama sudah wujud. Langkau.\n";
}
$stmt->close();

// 4. Masukkan Seed Pejabat Bahagian (Kontinjen Dalaman JTS)
echo "5. Memasukkan seed data Pejabat Bahagian (JTS)...\n";
$bahagian_jts = [
    ['Ibu Pejabat JTS', 'IPJ', 'dalaman'],
    ['JTS Kuching', 'KCH', 'dalaman'],
    ['JTS Samarahan', 'SMR', 'dalaman'],
    ['JTS Sri Aman', 'SRA', 'dalaman'],
    ['JTS Betong', 'BTG', 'dalaman'],
    ['JTS Sarikei', 'SRK', 'dalaman'],
    ['JTS Sibu', 'SBU', 'dalaman'],
    ['JTS Mukah', 'MKH', 'dalaman'],
    ['JTS Kapit', 'KPT', 'dalaman'],
    ['JTS Bintulu', 'BTU', 'dalaman'],
    ['JTS Miri', 'MRI', 'dalaman'],
    ['JTS Limbang', 'LBG', 'dalaman']
];

$stmt_b = $conn->prepare("SELECT id FROM tbl_bahagian WHERE singkatan = ?");
$stmt_b_insert = $conn->prepare("INSERT INTO tbl_bahagian (nama_bahagian, singkatan, jenis, logo_url, status) VALUES (?, ?, ?, 'default_logo.png', 'aktif')");

foreach ($bahagian_jts as $b) {
    $stmt_b->bind_param("s", $b[1]);
    $stmt_b->execute();
    $res_b = $stmt_b->get_result();
    if ($res_b->num_rows === 0) {
        $stmt_b_insert->bind_param("sss", $b[0], $b[1], $b[2]);
        $stmt_b_insert->execute();
    }
}
$stmt_b->close();
$stmt_b_insert->close();
echo "   Data Bahagian JTS sedia.\n";

// 5. Masukkan Seed Jabatan Jemputan
echo "6. Memasukkan seed data Jabatan Jemputan...\n";
$bahagian_jemputan = [
    ['Jabatan Kerja Raya Sarawak', 'JKR', 'jemputan'],
    ['Pihak Berkuasa Tempatan', 'PBT', 'jemputan'],
    ['Lembaga Air Kuching', 'LAK', 'jemputan'],
    ['Jabatan Hutan Sarawak', 'JHS', 'jemputan']
];

$stmt_bi_insert = $conn->prepare("INSERT INTO tbl_bahagian (nama_bahagian, singkatan, jenis, logo_url, status) VALUES (?, ?, ?, 'default_logo.png', 'aktif')");
foreach ($bahagian_jemputan as $b) {
    $stmt_b = $conn->prepare("SELECT id FROM tbl_bahagian WHERE singkatan = ?");
    $stmt_b->bind_param("s", $b[1]);
    $stmt_b->execute();
    $res_b = $stmt_b->get_result();
    if ($res_b->num_rows === 0) {
        $stmt_bi_insert->bind_param("sss", $b[0], $b[1], $b[2]);
        $stmt_bi_insert->execute();
    }
    $stmt_b->close();
}
$stmt_bi_insert->close();
echo "   Data Jabatan Jemputan sedia.\n";

// 6. Masukkan Seed Acara Sukan
echo "7. Memasukkan seed data Acara Sukan...\n";
$sukan_list = [
    ['Bola Sepak', 'lelaki', 'berpasukan', 'bi-dribbble', 'Pertandingan bola sepak 9-sebelah padang terbuka.'],
    ['Badminton', 'campuran', 'berpasukan', 'bi-lightning-fill', 'Acara badminton bergu campuran dan bergu lelaki.'],
    ['Catur', 'campuran', 'individu', 'bi-grid-3x3-gap-fill', 'Pertandingan catur klasik intelek.'],
    ['Dart', 'campuran', 'individu', 'bi-bullseye', 'Pertandingan ketepatan balingan dart.'],
    ['Netball', 'wanita', 'berpasukan', 'bi-basketball', 'Acara bola jaring wanita antarabangsa.'],
    ['Karom', 'campuran', 'individu', 'bi-circle-fill', 'Pertandingan karom beregu terbuka.']
];

$stmt_s = $conn->prepare("SELECT id FROM tbl_sukan WHERE nama_sukan = ?");
$stmt_s_insert = $conn->prepare("INSERT INTO tbl_sukan (nama_sukan, kategori, jenis_perlawanan, ikon, keterangan, status) VALUES (?, ?, ?, ?, ?, 'aktif')");

foreach ($sukan_list as $s) {
    $stmt_s->bind_param("s", $s[0]);
    $stmt_s->execute();
    $res_s = $stmt_s->get_result();
    if ($res_s->num_rows === 0) {
        $stmt_s_insert->bind_param("sssss", $s[0], $s[1], $s[2], $s[3], $s[4]);
        $stmt_s_insert->execute();
    }
}
$stmt_s->close();
$stmt_s_insert->close();
echo "   Data Acara Sukan sedia.\n";

// 7. Masukkan Seed Tempat/Venue
echo "8. Memasukkan seed data Venue...\n";
$venue_list = [
    ['Stadium Perpaduan Petra Jaya', 'Jalan Stadium, Petra Jaya, 93050 Kuching, Sarawak', '1.5833330', '110.3500000', 5000, 'Gelanggang utama badminton dan majlis penutup.'],
    ['Padang Bola Sepak JTS', 'Jalan Simpang Tiga, 93300 Kuching, Sarawak', '1.5305560', '110.3563890', 1000, 'Padang rasmi bagi perlawanan bola sepak.'],
    ['Dewan Serbaguna Ibu Pejabat JTS', 'Menara Tanah & Survei, Simpang Tiga, 93300 Kuching, Sarawak', '1.5310000', '110.3570000', 400, 'Lokasi perlawanan Catur, Dart dan Karom, serta Majlis Pembukaan.']
];

$stmt_v = $conn->prepare("SELECT id FROM tbl_venue WHERE nama_tempat = ?");
$stmt_v_insert = $conn->prepare("INSERT INTO tbl_venue (nama_tempat, alamat, latitude, longitude, kapasiti, catatan) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($venue_list as $v) {
    $stmt_v->bind_param("s", $v[0]);
    $stmt_v->execute();
    $res_v = $stmt_v->get_result();
    if ($res_v->num_rows === 0) {
        $stmt_v_insert->bind_param("ssddis", $v[0], $v[1], $v[2], $v[3], $v[4], $v[5]);
        $stmt_v_insert->execute();
    }
}
$stmt_v->close();
$stmt_v_insert->close();
echo "   Data Venue sedia.\n";

// 8. Cipta Direktori Upload Aset
echo "9. Mencipta direktori fizikal upload...\n";
$dirs = [UPLOAD_DIR_LOGO, UPLOAD_DIR_GALERI, UPLOAD_DIR_HERO];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "   Direktori '" . basename($dir) . "' dicipta.\n";
        } else {
            echo "   Gagal mencipta direktori '" . $dir . "'\n";
        }
    } else {
        echo "   Direktori '" . basename($dir) . "' sedia ada.\n";
    }
}

// Cipta fail logo_default.png placeholder jika belum ada
$default_logo_path = UPLOAD_DIR_LOGO . 'default_logo.png';
if (!file_exists($default_logo_path)) {
    // Buat imej kosong dengan teks "JTS Logo"
    $im = imagecreatetruecolor(150, 150);
    $bg = imagecolorallocate($im, 10, 37, 64); // Navy blue
    $text_color = imagecolorallocate($im, 255, 215, 0); // Gold
    imagefill($im, 0, 0, $bg);
    imagestring($im, 4, 30, 65, "JTS KONTINJEN", $text_color);
    imagepng($im, $default_logo_path);
    imagedestroy($im);
    echo "   Imej 'default_logo.png' dicipta.\n";
}

echo "\n*** PEMASANGAN BERJAYA! SISTEM SUKANJTS SEDIA DIGUNAKAN. ***\n";
if (php_sapi_name() !== 'cli') {
    echo "</pre>";
    echo "<br><a href='" . BASE_URL . "public/index.php'>Buka Paparan Awam (Landing Page)</a>";
    echo " | <a href='" . BASE_URL . "admin/auth/login.php'>Log Masuk Pentadbir</a>";
}
$conn->close();
