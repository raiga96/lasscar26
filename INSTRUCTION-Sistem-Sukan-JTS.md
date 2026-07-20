# Dokumen Panduan Pembangunan
## Sistem Pengurusan Pertandingan Sukan — JTS Sarawak

**Versi:** 1.0 | **Tarikh:** 20 Julai 2026
**Rujukan:** PRD-Sistem-Sukan-JTS-Sarawak.md

---

## 1. INSTRUCTION (Arahan Pembangunan)

Dokumen ini adalah arahan rasmi untuk developer/AI assistant yang membina Sistem Pengurusan Pertandingan Sukan bagi Jabatan Tanah dan Survei Sarawak (JTS Sarawak).

### 1.1 Peranan
Bertindak sebagai **senior full-stack developer** yang mahir PHP prosedural/OOP ringkas, MySQLi, Bootstrap, dan JavaScript vanilla, dengan fokus kuat kepada keselamatan aplikasi web.

### 1.2 Cara Bekerja
1. Sentiasa rujuk **Seksyen 5 (Schema)** di bawah sebelum menulis sebarang query — jangan reka nama jadual/medan baharu tanpa kemas kini schema dahulu.
2. Setiap fungsi CRUD mesti ditulis mengikut corak dalam **Seksyen 4 (Rules)** — terutamanya penggunaan MySQLi prepared statements.
3. Bina modul mengikut susunan keutamaan: **Database → Auth Admin → CRUD Data Asas (Bahagian/Sukan/Venue) → CRUD Jadual & Keputusan → Front-End Awam → Galeri/Hero Banner → QA/Security Testing.**
4. Setiap kali satu modul siap, sediakan ringkasan fail yang dihasilkan/diubah dan cara ia diuji.
5. Jika ada keperluan yang tidak jelas dalam PRD, buat andaian munasabah dan nyatakan andaian tersebut secara ringkas — jangan berhenti bertanya melainkan benar-benar kritikal.
6. Elakkan menulis kod yang tidak lengkap ("TODO" tanpa isi) untuk fungsi keselamatan (login, validation, file upload).
7. Gunakan Bahasa Melayu untuk label/UI yang dilihat pengguna akhir; kod, nama pembolehubah, dan komen teknikal boleh dalam Bahasa Inggeris untuk kekal standard industri (boleh diselaraskan ikut keperluan).

---

## 2. GOAL (Matlamat)

### 2.1 Matlamat Utama
Membina sistem web yang **selamat, responsif, dan mudah diselenggara** yang membolehkan:
- Orang awam/peserta melihat maklumat pertandingan sukan secara masa nyata tanpa log masuk.
- Urus setia sukan mengemas kini jadual, keputusan, kedudukan pingat, aturcara, dan galeri melalui panel admin CRUD penuh.

### 2.2 Matlamat Terukur (Definition of Done)
- [ ] Semua 16 modul awam (rujuk PRD Seksyen 4A) berfungsi penuh dan responsif di mobile/tablet/desktop.
- [ ] Semua 13 modul admin (rujuk PRD Seksyen 4B) menyokong Create, Read, Update, Delete.
- [ ] Kedudukan pingat dikira automatik apabila keputusan direkod (tiada pengiraan manual).
- [ ] Semua query pangkalan data menggunakan MySQLi prepared statements — sifar SQL string concatenation terus daripada input pengguna.
- [ ] Semua kata laluan admin disimpan menggunakan `password_hash()` / disahkan dengan `password_verify()`.
- [ ] Sistem lulus ujian asas OWASP Top 10 (SQLi, XSS, CSRF, broken auth, insecure file upload).
- [ ] Semua fail upload media disahkan jenis & saiz sebelum disimpan.

### 2.3 Bukan Matlamat (Out of Scope)
- Aplikasi mobile native.
- Sistem pendaftaran peserta secara awam/online (Fasa 2).
- Integrasi pembayaran atau e-dagang.
- Live video streaming.

---

## 3. ARCHITECTURE (Seni Bina Sistem)

### 3.1 Gambaran Keseluruhan
Seni bina **3-tier klasik**, dibina sebagai **monolith PHP prosedural bercorak MVC ringkas** (bukan framework penuh seperti Laravel, kecuali dinyatakan sebaliknya oleh klien):

```
┌─────────────────────────────────────────────┐
│  PRESENTATION LAYER (Browser)                │
│  HTML5 + Bootstrap 5 + Vanilla JS            │
└───────────────────┬───────────────────────────┘
                    │ HTTPS
┌───────────────────▼───────────────────────────┐
│  APPLICATION LAYER (PHP 8.x)                  │
│  ┌─────────────┐  ┌──────────────┐            │
│  │ public/      │  │ admin/        │            │
│  │ (awam)       │  │ (CRUD panel)  │            │
│  └─────────────┘  └──────────────┘            │
│  includes/ (db.php, functions.php, auth.php)  │
└───────────────────┬───────────────────────────┘
                    │ MySQLi (Prepared Statements)
┌───────────────────▼───────────────────────────┐
│  DATA LAYER — MySQL / MariaDB                 │
└─────────────────────────────────────────────────┘
```

### 3.2 Struktur Folder (Rujukan Wajib)

```
sistem-sukan-jts/
├── admin/
│   ├── auth/               login.php, logout.php
│   ├── bahagian/            index.php, create.php, edit.php, delete.php
│   ├── sukan/                (sama pola CRUD)
│   ├── pasukan/
│   ├── jadual/
│   ├── keputusan/
│   ├── venue/
│   ├── aturcara/
│   ├── galeri/
│   ├── hero-banner/
│   ├── pengguna/             (urus admin — hanya super_admin)
│   ├── audit-log/
│   └── dashboard.php
├── public/
│   ├── index.php             (landing / hero)
│   ├── bahagian.php
│   ├── sukan.php
│   ├── jadual.php
│   ├── keputusan.php         (tab live / selesai / akan datang)
│   ├── kedudukan-pingat.php
│   ├── venue.php
│   ├── aturcara.php
│   └── galeri.php
├── includes/
│   ├── db.php                 sambungan MySQLi
│   ├── functions.php          fungsi umum (format tarikh, dsb.)
│   ├── auth-check.php          semak sesi admin + peranan
│   ├── csrf.php                jana & sahkan token CSRF
│   ├── header.php / footer.php (awam)
│   └── admin-header.php / admin-sidebar.php
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   └── uploads/
│       ├── galeri/
│       ├── hero/
│       └── logo-bahagian/
├── config/
│   └── config.php              (kredensial DB, tetapan sesi)
└── database/
    └── schema.sql               (rujuk Seksyen 5)
```

### 3.3 Aliran Data (Contoh: Kemaskini Keputusan → Kedudukan Pingat)

```
Admin hantar borang skor (admin/keputusan/edit.php)
        │
        ▼
Validate input (PHP) + CSRF check
        │
        ▼
MySQLi prepared statement → UPDATE tbl_keputusan
        │
        ▼
Trigger/fungsi kira semula tbl_kedudukan_pingat
   (boleh guna SQL query agregat live, ATAU kemaskini rekod)
        │
        ▼
Log ke tbl_audit_log
        │
        ▼
Paparan awam (kedudukan-pingat.php) auto-papar data terkini
   (query terus dari DB — bukan cache statik)
```

### 3.4 Prinsip Seni Bina
- **Separation of concerns**: logik DB (`includes/db.php`) berasingan daripada logik paparan (fail `.php` di `public/`/`admin/`).
- **Single source of truth**: kedudukan pingat sentiasa dikira/disahkan dari `tbl_keputusan` — elak data pingat "terpisah" tanpa pengesahan.
- **Stateless front-end**: JavaScript hanya untuk UX (validation, tab switching, modal galeri) — logik data kekal di server (PHP).
- **Least privilege**: setiap peranan admin (`super_admin`, `editor`, `media`) hanya nampak menu yang dibenarkan (kawal di `auth-check.php`).

---

## 4. RULES (Peraturan Pembangunan — Wajib Patuh)

### 4.1 Peraturan Pangkalan Data
1. **WAJIB** guna MySQLi dengan **Prepared Statements** (`mysqli_prepare` + `bind_param`) untuk SEMUA query yang mengandungi input pengguna. Tiada pengecualian.
2. **DILARANG SAMA SEKALI** guna PDO (mengikut keperluan klien).
3. **DILARANG** membina query dengan concatenation string terus dari `$_GET`/`$_POST`/`$_REQUEST`.
4. Setiap sambungan DB guna satu fail pusat `includes/db.php` — jangan buka sambungan baharu berselerak di fail lain.
5. Guna `mysqli_real_escape_string()` sebagai lapisan tambahan HANYA jika prepared statement benar-benar tidak praktikal (jarang berlaku) — bukan pengganti kepada prepared statement.

### 4.2 Peraturan Keselamatan
6. Kata laluan: **WAJIB** `password_hash(PASSWORD_BCRYPT)` semasa simpan, `password_verify()` semasa log masuk.
7. **CSRF token** wajib pada setiap borang admin (create/update/delete) — sahkan token sebelum proses.
8. **Sanitize output**: guna `htmlspecialchars()` pada semua data pengguna yang dipaparkan ke HTML (elak XSS).
9. **Validation dua lapis**: JavaScript (UX sahaja) DAN PHP (keselamatan sebenar) — jangan percaya validation JS sahaja.
10. **File upload** (galeri/hero/logo):
    - Semak MIME type sebenar (`finfo_file`), bukan sekadar sambungan nama fail.
    - Whitelist: `.jpg, .jpeg, .png, .webp` (imej), `.mp4, .webm` (video).
    - Had saiz maksimum (cadangan: 5MB imej, 50MB video).
    - Namakan semula fail secara rawak/unik (`uniqid()` / `bin2hex(random_bytes())`) — elak overwrite & path traversal.
11. **Session**: `session_regenerate_id(true)` selepas login berjaya; tamat sesi selepas tempoh tidak aktif (cadangan 30 minit untuk admin).
12. **RBAC**: setiap fail dalam `admin/` mesti panggil `auth-check.php` di baris pertama, dan semak peranan yang dibenarkan untuk modul berkenaan.
13. **Rate limiting log masuk**: kunci akaun/IP sementara selepas 5 percubaan gagal berturut-turut.
14. Semua sambungan pengeluaran (production) **WAJIB HTTPS**.

### 4.3 Peraturan Kod
15. Konsisten guna **snake_case** untuk nama fail dan medan database, **camelCase** untuk pembolehubah/fungsi JavaScript.
16. Setiap fail CRUD ikut pola nama seragam: `index.php` (senarai), `create.php`, `edit.php`, `delete.php` (proses backend sahaja, guna POST, bukan link GET terus untuk delete).
17. Elak "magic numbers" — guna konstanta/nama status jelas (`'akan_datang'`, `'live'`, `'selesai'`) bukan `1`, `2`, `3`.
18. Setiap query yang boleh gagal mesti disemak (`if (!$result) { ... }`) dan berikan mesej ralat mesra pengguna (bukan dedah ralat MySQL mentah kepada pengguna).
19. Komen kod pada setiap fungsi utama — tujuan, parameter, pulangan.

### 4.4 Peraturan UI/UX
20. Guna komponen Bootstrap 5 sedia ada dahulu sebelum tulis CSS custom — elak "reinvent the wheel".
21. Semua jadual data admin mesti ada paparan responsif (guna `.table-responsive`).
22. Status "LIVE" mesti mempunyai penanda visual jelas (badge merah/animasi) berbeza daripada "Selesai"/"Akan Datang".
23. Semua imej (termasuk galeri) wajib ada atribut `alt`.

### 4.5 Peraturan Audit & Log
24. Setiap tindakan Create/Update/Delete di admin panel **WAJIB** direkod ke `tbl_audit_log` (siapa, bila, apa, IP).
25. Jangan padam rekod audit log walau apa jua sebab (append-only).

---

## 5. SCHEMA (Struktur Pangkalan Data)

### 5.1 Skrip SQL Penuh

```sql
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
CREATE TABLE tbl_pengguna (
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
CREATE TABLE tbl_bahagian (
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
CREATE TABLE tbl_sukan (
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
CREATE TABLE tbl_pasukan (
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
CREATE TABLE tbl_venue (
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
CREATE TABLE tbl_jadual_perlawanan (
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
CREATE TABLE tbl_keputusan (
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
CREATE TABLE tbl_kedudukan_pingat (
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
    SUM(CASE WHEN k.jenis_pingat = 'emas' THEN 1 ELSE 0 END) AS emas,
    SUM(CASE WHEN k.jenis_pingat = 'perak' THEN 1 ELSE 0 END) AS perak,
    SUM(CASE WHEN k.jenis_pingat = 'gangsa' THEN 1 ELSE 0 END) AS gangsa,
    SUM(CASE WHEN k.jenis_pingat IN ('emas','perak','gangsa') THEN 1 ELSE 0 END) AS jumlah
FROM tbl_bahagian b
LEFT JOIN tbl_pasukan p ON p.bahagian_id = b.id
LEFT JOIN tbl_keputusan k ON k.pasukan_menang_id = p.id
GROUP BY b.id, b.nama_bahagian
ORDER BY emas DESC, perak DESC, gangsa DESC;

-- ------------------------------------------------
-- 9. Aturcara (Pembukaan / Penutup / Umum)
-- ------------------------------------------------
CREATE TABLE tbl_aturcara (
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
CREATE TABLE tbl_galeri (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tajuk VARCHAR(200) NULL,
    jenis_fail ENUM('imej','video') NOT NULL DEFAULT 'imej',
    url_fail VARCHAR(255) NOT NULL,
    album VARCHAR(100) NULL,                        -- cth: 'Hari 1', 'Badminton', 'Majlis Penutup'
    sukan_id INT UNSIGNED NULL,
    upload_oleh INT UNSIGNED NULL,
    dicipta_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sukan_id) REFERENCES tbl_sukan(id) ON DELETE SET NULL,
    FOREIGN KEY (upload_oleh) REFERENCES tbl_pengguna(id) ON DELETE SET NULL,
    INDEX idx_album (album)
) ENGINE=InnoDB;

-- ------------------------------------------------
-- 11. Hero Banner (Juara)
-- ------------------------------------------------
CREATE TABLE tbl_hero_banner (
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
CREATE TABLE tbl_audit_log (
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

-- ------------------------------------------------
-- Data Awal (Seed) — Akaun Super Admin Contoh
-- Kata laluan sebenar mesti dijana guna password_hash() dalam PHP, bukan hardcode.
-- ------------------------------------------------
-- INSERT INTO tbl_pengguna (nama_penuh, emel, kata_laluan, peranan)
-- VALUES ('Admin Sistem', 'admin@jts.sarawak.gov.my', '<HASH_DIJANA_PHP>', 'super_admin');
```

### 5.2 Rajah ERD (Ringkasan Perhubungan)

```
tbl_pengguna ──< tbl_audit_log
tbl_pengguna ──< tbl_galeri (upload_oleh)
tbl_pengguna ──< tbl_keputusan (direkod_oleh)

tbl_bahagian ──< tbl_pasukan >── tbl_sukan
tbl_bahagian ──< tbl_hero_banner (bahagian_juara_id)
tbl_bahagian ──< tbl_kedudukan_pingat

tbl_sukan ──< tbl_jadual_perlawanan
tbl_venue ──< tbl_jadual_perlawanan
tbl_pasukan ──< tbl_jadual_perlawanan (pasukan_a_id, pasukan_b_id)

tbl_jadual_perlawanan ──1:1── tbl_keputusan
tbl_pasukan ──< tbl_keputusan (pasukan_menang_id)

tbl_sukan ──< tbl_galeri
```

### 5.3 Nota Schema
- `vw_kedudukan_pingat` (VIEW) disyorkan sebagai **sumber utama** paparan kedudukan pingat kerana ia sentiasa tepat masa-nyata berdasarkan `tbl_keputusan`. Jadual `tbl_kedudukan_pingat` boleh dikekalkan sebagai cache/override manual jika prestasi menjadi isu apabila data membesar.
- Semua Foreign Key guna `ON DELETE CASCADE`/`SET NULL`/`RESTRICT` mengikut konteks — elak rekod anak yatim (orphan records) atau kehilangan data sejarah penting secara tidak sengaja.
- Lajur `status` guna `ENUM` untuk konsistensi data dan prestasi index yang lebih baik berbanding `VARCHAR` bebas.

---

## 6. Rujukan Silang

Dokumen ini melengkapi **PRD-Sistem-Sukan-JTS-Sarawak.md** (skop produk & senarai ciri penuh). Sebarang penambahan modul baharu mesti dikemaskini di kedua-dua dokumen secara serentak.
