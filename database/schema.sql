-- ============================================
-- Sistem Pengurusan Pertandingan Sukan JTS Sarawak
-- Database Schema v1.0
-- Engine: MySQL / MariaDB | Storage: InnoDB | Charset: utf8mb4
-- ============================================

CREATE DATABASE IF NOT EXISTS sistem_sukan_jts
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sistem_sukan_jts;

-- ------------------------------------------------
-- 1. Pengguna Admin
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_pengguna (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_penuh VARCHAR(150) NOT NULL,
    emel VARCHAR(150) NOT NULL UNIQUE,
    kata_laluan VARCHAR(255) NOT NULL,           -- disimpan via password_hash()
    peranan ENUM('super_admin','editor','media') NOT NULL DEFAULT 'editor',
    status ENUM('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif',
    percubaan_gagal TINYINT UNSIGNED DEFAULT 0,
    dikunci_sehingga DATETIME NULL,
    dicipta_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dikemaskini_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------
-- 2. Bahagian & Jabatan Jemputan
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_bahagian (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_bahagian VARCHAR(150) NOT NULL,
    singkatan VARCHAR(20) NULL,
    jenis ENUM('dalaman','jemputan') NOT NULL DEFAULT 'dalaman',
    logo_url VARCHAR(255) NULL,
    status ENUM('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif',
    dicipta_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------
-- 3. Jenis Sukan
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_sukan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_sukan VARCHAR(100) NOT NULL,
    kategori ENUM('lelaki','wanita','campuran') NOT NULL DEFAULT 'campuran',
    jenis_perlawanan ENUM('individu','berpasukan') NOT NULL DEFAULT 'berpasukan',
    ikon VARCHAR(100) NULL,                       -- cth: nama class bootstrap-icons
    keterangan TEXT NULL,
    status ENUM('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif',
    dicipta_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------
-- 4. Pasukan/Peserta (Bahagian x Sukan)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_pasukan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bahagian_id INT UNSIGNED NOT NULL,
    sukan_id INT UNSIGNED NOT NULL,
    nama_pasukan VARCHAR(150) NULL,               -- opsyenal jika beza dari nama bahagian
    dicipta_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bahagian_id) REFERENCES tbl_bahagian(id) ON DELETE CASCADE,
    FOREIGN KEY (sukan_id) REFERENCES tbl_sukan(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_bahagian_sukan (bahagian_id, sukan_id)
) ENGINE=InnoDB;

-- ------------------------------------------------
-- 5. Tempat Pertandingan (Venue)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_venue (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_tempat VARCHAR(150) NOT NULL,
    alamat TEXT NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    kapasiti INT UNSIGNED NULL,
    catatan TEXT NULL,
    dicipta_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------
-- 6. Jadual Perlawanan (Fixtures)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_jadual_perlawanan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sukan_id INT UNSIGNED NOT NULL,
    pasukan_a_id INT UNSIGNED NOT NULL,
    pasukan_b_id INT UNSIGNED NULL,               -- NULL jika sukan individu (cth: larian)
    venue_id INT UNSIGNED NOT NULL,
    tarikh DATE NOT NULL,
    masa TIME NOT NULL,
    pusingan VARCHAR(50) NULL,                     -- cth: 'Suku Akhir', 'Separuh Akhir'
    status ENUM('akan_datang','live','selesai','ditangguh') NOT NULL DEFAULT 'akan_datang',
    dicipta_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dikemaskini_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sukan_id) REFERENCES tbl_sukan(id) ON DELETE CASCADE,
    FOREIGN KEY (pasukan_a_id) REFERENCES tbl_pasukan(id) ON DELETE CASCADE,
    FOREIGN KEY (pasukan_b_id) REFERENCES tbl_pasukan(id) ON DELETE SET NULL,
    FOREIGN KEY (venue_id) REFERENCES tbl_venue(id) ON DELETE RESTRICT,
    INDEX idx_status (status),
    INDEX idx_tarikh (tarikh)
) ENGINE=InnoDB;

-- ------------------------------------------------
-- 7. Keputusan Perlawanan
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_keputusan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jadual_id INT UNSIGNED NOT NULL UNIQUE,
    skor_a INT UNSIGNED NULL,
    skor_b INT UNSIGNED NULL,
    pasukan_menang_id INT UNSIGNED NULL,
    jenis_pingat ENUM('emas','perak','gangsa','tiada') NOT NULL DEFAULT 'tiada',
    catatan TEXT NULL,
    direkod_oleh INT UNSIGNED NULL,
    dicipta_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dikemaskini_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (jadual_id) REFERENCES tbl_jadual_perlawanan(id) ON DELETE CASCADE,
    FOREIGN KEY (pasukan_menang_id) REFERENCES tbl_pasukan(id) ON DELETE SET NULL,
    FOREIGN KEY (direkod_oleh) REFERENCES tbl_pengguna(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------
-- 8. Kedudukan Pingat (opsyenal — boleh dikira live via VIEW)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_kedudukan_pingat (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bahagian_id INT UNSIGNED NOT NULL UNIQUE,
    emas INT UNSIGNED NOT NULL DEFAULT 0,
    perak INT UNSIGNED NOT NULL DEFAULT 0,
    gangsa INT UNSIGNED NOT NULL DEFAULT 0,
    jumlah INT UNSIGNED GENERATED ALWAYS AS (emas + perak + gangsa) STORED,
    dikemaskini_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bahagian_id) REFERENCES tbl_bahagian(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Alternatif: VIEW pengiraan automatik masa-nyata (disyorkan sebagai sumber utama)
CREATE OR REPLACE VIEW vw_kedudukan_pingat AS
SELECT
    b.id AS bahagian_id,
    b.nama_bahagian,
    b.singkatan,
    b.logo_url,
    b.jenis,
    SUM(CASE WHEN k.jenis_pingat = 'emas' THEN 1 ELSE 0 END) AS emas,
    SUM(CASE WHEN k.jenis_pingat = 'perak' THEN 1 ELSE 0 END) AS perak,
    SUM(CASE WHEN k.jenis_pingat = 'gangsa' THEN 1 ELSE 0 END) AS gangsa,
    SUM(CASE WHEN k.jenis_pingat IN ('emas','perak','gangsa') THEN 1 ELSE 0 END) AS jumlah
FROM tbl_bahagian b
LEFT JOIN tbl_pasukan p ON p.bahagian_id = b.id
LEFT JOIN tbl_keputusan k ON k.pasukan_menang_id = p.id
GROUP BY b.id, b.nama_bahagian, b.singkatan, b.logo_url, b.jenis
ORDER BY emas DESC, perak DESC, gangsa DESC, b.nama_bahagian ASC;

-- ------------------------------------------------
-- 9. Aturcara (Pembukaan / Penutup / Umum)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_aturcara (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jenis ENUM('umum','pembukaan','penutup') NOT NULL DEFAULT 'umum',
    tarikh DATE NOT NULL,
    masa TIME NOT NULL,
    aktiviti VARCHAR(255) NOT NULL,
    pegawai_bertanggungjawab VARCHAR(150) NULL,
    susunan INT UNSIGNED NOT NULL DEFAULT 0,       -- untuk order by
    dicipta_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------
-- 10. Galeri Media
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_galeri (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tajuk VARCHAR(200) NULL,
    jenis_fail ENUM('imej','video') NOT NULL DEFAULT 'imej',
    url_fail VARCHAR(255) NOT NULL,
    gdrive_file_id VARCHAR(100) NULL,
    gdrive_folder_id VARCHAR(100) NULL,
    gdrive_thumbnail_url TEXT NULL,
    gdrive_view_url TEXT NULL,
    gdrive_modified_time DATETIME NULL,
    is_gdrive TINYINT(1) NOT NULL DEFAULT 0,
    album VARCHAR(100) NULL,                        -- cth: 'Hari 1', 'Badminton', 'Majlis Penutup'
    sukan_id INT UNSIGNED NULL,
    upload_oleh INT UNSIGNED NULL,
    dicipta_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sukan_id) REFERENCES tbl_sukan(id) ON DELETE SET NULL,
    FOREIGN KEY (upload_oleh) REFERENCES tbl_pengguna(id) ON DELETE SET NULL,
    INDEX idx_album (album),
    UNIQUE INDEX idx_gdrive_file_id (gdrive_file_id)
) ENGINE=InnoDB;

-- ------------------------------------------------
-- 11. Hero Banner (Juara)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_hero_banner (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tajuk VARCHAR(200) NOT NULL,
    url_imej VARCHAR(255) NOT NULL,
    bahagian_juara_id INT UNSIGNED NULL,
    status_aktif ENUM('aktif','tidak_aktif') NOT NULL DEFAULT 'tidak_aktif',
    susunan INT UNSIGNED NOT NULL DEFAULT 0,
    dicipta_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bahagian_juara_id) REFERENCES tbl_bahagian(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------
-- 12. Log Audit
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_audit_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pengguna_id INT UNSIGNED NULL,
    tindakan ENUM('create','update','delete','login','logout') NOT NULL,
    jadual_disentuh VARCHAR(100) NOT NULL,
    rekod_id INT UNSIGNED NULL,
    butiran TEXT NULL,
    ip_address VARCHAR(45) NULL,
    dicipta_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengguna_id) REFERENCES tbl_pengguna(id) ON DELETE SET NULL
) ENGINE=InnoDB;
