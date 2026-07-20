# PRD — Sistem Pengurusan Pertandingan Sukan
## Jabatan Tanah dan Survei Sarawak (JTS Sarawak)

**Versi:** 1.0
**Tarikh:** 20 Julai 2026
**Disediakan untuk:** Jabatan Tanah dan Survei Sarawak
**Status:** Draf untuk semakan

---

## 1. Ringkasan Eksekutif

JTS Sarawak memerlukan sebuah sistem web (web-based) untuk mengurus dan mempamerkan maklumat pertandingan sukan tahunan/berkala yang melibatkan pelbagai pejabat bahagian dan jabatan jemputan. Sistem ini akan berfungsi sebagai portal awam (landing page) sekaligus panel pentadbiran (admin CMS) untuk staf pengurusan sukan mengemas kini keputusan, jadual, dan media secara masa nyata (real-time).

**Tumpuan utama:**
- Landing page maklumat awam yang menarik dan responsif
- Papan pemarkahan pingat (medal standing) automatik
- Status perlawanan (live / selesai / akan datang)
- Pengurusan aturcara majlis (pembukaan & makan malam penutup)
- Galeri media dan hero image juara
- Panel admin CRUD penuh untuk semua modul
- Dibina dengan **PHP + MySQLi** (bukan PDO) mengikut keperluan klien

---

## 2. Latar Belakang & Objektif

### 2.1 Latar Belakang
Pada masa ini, maklumat pertandingan sukan JTS Sarawak (jadual, keputusan, kedudukan pingat) kemungkinan diuruskan secara manual/edaran surat pekeliling/Excel, menyukarkan penyebaran maklumat tepat pada masanya kepada peserta dan orang awam.

### 2.2 Objektif Projek
1. Menyediakan satu platform berpusat untuk semua maklumat pertandingan sukan.
2. Membolehkan pengemaskinian keputusan & jadual secara masa nyata oleh urus setia.
3. Meningkatkan ketelusan kedudukan pingat antara bahagian/jabatan.
4. Mempamerkan galeri media dan penghormatan kepada juara (hero banner).
5. Memudahkan pentadbir menguruskan data melalui panel CRUD yang selamat dan mesra pengguna.

### 2.3 Skop Projek
**Termasuk:**
- Portal awam (public-facing) — tiada log masuk diperlukan untuk melihat
- Panel admin (role-based) untuk CRUD semua modul
- Pangkalan data MySQL
- Reka bentuk responsif (mobile, tablet, desktop)

**Tidak termasuk (Fasa 1):**
- Aplikasi mobile native (iOS/Android)
- Sistem pendaftaran peserta secara online (boleh dipertimbang Fasa 2)
- Integrasi live streaming video
- Sistem e-mel/SMS automatik (boleh dipertimbang Fasa 2)

---

## 3. Pengguna Sasaran & Peranan (User Roles)

| Peranan | Penerangan | Akses |
|---|---|---|
| **Orang Awam / Peserta** | Kakitangan JTS, jabatan jemputan, orang awam | Lihat sahaja (read-only) semua kandungan awam |
| **Admin Utama (Super Admin)** | Urus setia sukan / IT JTS | Akses penuh — urus pengguna, semua modul CRUD, tetapan sistem |
| **Admin Modul (Editor)** | Wakil setiap sukan/pegawai teknikal | CRUD terhad kepada modul tertentu (cth: hanya keputusan & jadual) |
| **Admin Media** | Jurugambar/juruhebah rasmi | Upload & urus galeri media, hero image |

---

## 4. Senarai Modul & Ciri (Feature List)

### A. MODUL AWAM (Front-End / Landing Page)

| # | Modul | Penerangan |
|---|---|---|
| A1 | **Laman Utama (Hero Section)** | Banner utama dinamik memaparkan hero image juara pertandingan terkini, tarikh & tema pertandingan |
| A2 | **Info Am Pertandingan** | Tajuk, tema, tarikh, lokasi, sambutan/kata alu-aluan Pengarah/Pengerusi |
| A3 | **Pejabat Bahagian Mengambil Bahagian** | Senarai bahagian JTS yang bertanding (logo, nama, jumlah peserta) |
| A4 | **Jabatan Jemputan** | Senarai jabatan luar yang dijemput bertanding/hadir |
| A5 | **Senarai Pertandingan (Sports List)** | Semua acara sukan (bola sepak, badminton, catur dll.) dengan ikon & kategori |
| A6 | **Kedudukan Pingat (Medal Standing)** | Jadual automatik: Emas / Perak / Gangsa / Jumlah, disusun mengikut bahagian |
| A7 | **Perlawanan Sedang Berlangsung (Live)** | Senarai perlawanan status "LIVE" dengan skor terkini |
| A8 | **Perlawanan Telah Selesai** | Sejarah keputusan lepas, boleh tapis ikut sukan/tarikh |
| A9 | **Perlawanan Akan Datang** | Jadual perlawanan seterusnya (countdown opsyenal) |
| A10 | **Tempat Pertandingan (Venues)** | Senarai lokasi/dewan/padang berserta peta (Google Maps embed) |
| A11 | **Aturcara Umum** | Aturcara keseluruhan program sepanjang hari/minggu |
| A12 | **Jadual Pertandingan (Fixture/Schedule)** | Jadual penuh setiap sukan (tarikh, masa, tempat, pasukan berlawan) |
| A13 | **Aturcara Majlis Pembukaan** | Susunan atur cara majlis perasmian |
| A14 | **Aturcara Majlis Makan Malam Penutup** | Susunan atur cara dinner & penyampaian hadiah |
| A15 | **Galeri Media** | Koleksi gambar/video sepanjang pertandingan, kategori ikut sukan/hari |
| A16 | **Carian & Penapis (Search & Filter)** | Tapis ikut bahagian, sukan, tarikh, status |

### B. MODUL PENTADBIRAN (Admin Panel — CRUD)

| # | Modul Admin | Operasi CRUD |
|---|---|---|
| B1 | Log Masuk & Pengurusan Pengguna | Create/Read/Update/Delete admin, tetapan peranan (RBAC) |
| B2 | Urus Bahagian & Jabatan Jemputan | CRUD nama, logo, singkatan, jenis (dalaman/jemputan) |
| B3 | Urus Sukan/Acara | CRUD jenis sukan, peraturan, kategori (lelaki/wanita/berpasukan) |
| B4 | Urus Peserta/Pasukan | CRUD pendaftaran pasukan bagi setiap bahagian mengikut sukan |
| B5 | Urus Jadual Perlawanan | CRUD fixture — tarikh, masa, tempat, pasukan A vs B |
| B6 | Urus Keputusan & Skor | Update skor live, tukar status (Akan Datang → Live → Selesai), rekod pemenang |
| B7 | Urus Kedudukan Pingat | Auto-kira dari keputusan (dengan pilihan override manual) |
| B8 | Urus Tempat/Venue | CRUD nama tempat, alamat, kapasiti, koordinat peta |
| B9 | Urus Aturcara (Pembukaan/Penutup) | CRUD item aturcara — masa, aktiviti, pegawai bertanggungjawab |
| B10 | Urus Galeri Media | Upload/padam gambar & video, susun album ikut hari/sukan |
| B11 | Urus Hero Banner/Juara | CRUD gambar juara keseluruhan untuk paparan hero utama |
| B12 | Log Audit | Rekod siapa buat perubahan apa & bila (audit trail) |
| B13 | Dashboard Admin | Statistik ringkas: jumlah sukan, perlawanan live, jumlah peserta |

---

## 5. Aliran Pengguna (User Flow) — Ringkas

**Pelawat Awam:**
`Landing Page → Pilih Menu (Jadual/Pingat/Galeri/dll.) → Papar Info → (Opsyenal) Cari/Tapis`

**Admin:**
`Log Masuk → Dashboard → Pilih Modul → CRUD Data → Simpan → Paparan Awam Auto-Update`

---

## 6. Keperluan Fungsian (Functional Requirements)

- FR1: Sistem mesti memaparkan kedudukan pingat yang dikira secara automatik berdasarkan keputusan perlawanan yang direkod.
- FR2: Sistem mesti membenarkan admin menukar status perlawanan (Akan Datang / Live / Selesai) secara manual.
- FR3: Sistem mesti memaparkan senarai perlawanan mengikut status secara berasingan (3 tab/seksyen berbeza).
- FR4: Sistem mesti membenarkan upload pelbagai gambar/video ke dalam galeri dengan pengelasan album.
- FR5: Sistem mesti menyokong upload hero image berasingan untuk paparan banner juara di laman utama.
- FR6: Sistem admin mesti mempunyai fungsi CRUD penuh (Create, Read, Update, Delete) bagi **setiap** modul data (bahagian, sukan, jadual, keputusan, venue, aturcara, galeri).
- FR7: Semua borang input admin mesti disahkan (validation) di sisi klien (JavaScript) dan sisi pelayan (PHP).
- FR8: Sistem mesti menyokong carian dan penapisan (filter) di halaman jadual dan galeri.
- FR9: Sistem mesti mempunyai log audit bagi setiap tindakan CRUD oleh admin.
- FR10: Sistem mesti responsif — boleh diakses dengan baik di telefon pintar, tablet, dan desktop.

---

## 7. Keperluan Bukan Fungsian (Non-Functional Requirements)

| Kategori | Keperluan |
|---|---|
| **Prestasi** | Muka surat utama mesti dimuatkan < 3 saat pada sambungan 4G purata |
| **Kebolehcapaian** | Reka bentuk responsif penuh (Bootstrap grid), sokongan pelayar utama (Chrome, Edge, Safari, Firefox) |
| **Kebolehselenggaraan** | Kod PHP berstruktur MVC ringkas/modular, komen kod yang jelas |
| **Kebolehskalaan** | Struktur pangkalan data mesti mampu menampung >50 bahagian, >30 jenis sukan, ribuan rekod perlawanan |
| **Ketersediaan** | Sasaran uptime 99% semasa tempoh pertandingan aktif |
| **Kebolehgunaan (UI/UX)** | Antara muka moden, bersih, warna korporat JTS/kerajaan, navigasi mudah faham |

---

## 8. Keperluan Keselamatan (Security Requirements)

> ⚠️ **Nota penting mengenai "jangan guna PDO":** PDO dan MySQLi kedua-duanya selamat *jika* digunakan dengan betul (prepared statements). Oleh sebab keperluan projek menetapkan **MySQLi**, kami akan memastikan MySQLi digunakan dalam mod **Prepared Statements (mysqli_prepare / bind_param)** — BUKAN string concatenation terus — supaya risiko SQL Injection tetap dikawal setanding PDO.

| # | Keperluan Keselamatan |
|---|---|
| S1 | **Prepared Statements (MySQLi)** untuk semua query yang melibatkan input pengguna — elak SQL Injection |
| S2 | **Password Hashing** menggunakan `password_hash()` (bcrypt) untuk akaun admin — tiada password disimpan plain-text |
| S3 | **Session Management** selamat — session regenerate selepas log masuk, session timeout automatik |
| S4 | **CSRF Protection** — token tersembunyi pada setiap borang admin (create/update/delete) |
| S5 | **Input Validation & Sanitization** — semua input disahkan format (tarikh, nombor, teks) sebelum disimpan |
| S6 | **Output Escaping (XSS Protection)** — `htmlspecialchars()` pada semua output data pengguna ke HTML |
| S7 | **File Upload Security** — semak jenis fail (whitelist: jpg, png, mp4), had saiz, namakan semula fail secara rawak, simpan di luar root awam jika perlu |
| S8 | **Role-Based Access Control (RBAC)** — kawal akses modul mengikut peranan admin |
| S9 | **HTTPS/SSL** wajib untuk seluruh sistem (terutama panel admin) |
| S10 | **Rate Limiting / Login Attempt Lockout** — halang brute-force pada halaman log masuk |
| S11 | **Audit Log** — rekod IP, masa, pengguna, dan tindakan bagi setiap perubahan data |
| S12 | **Backup Pangkalan Data** berkala (harian semasa tempoh pertandingan aktif) |

---

## 9. Seni Bina Teknikal (Technical Architecture)

### 9.1 Tech Stack

| Lapisan | Teknologi |
|---|---|
| Front-End | HTML5, CSS3, **Bootstrap 5**, JavaScript (Vanilla JS / jQuery jika perlu), AOS.js (animasi ringan, opsyenal) |
| Back-End | **PHP 8.x** (prosedural terstruktur atau OOP ringkas — cadangan corak MVC ringkas) |
| Pangkalan Data | **MySQL / MariaDB**, akses melalui **MySQLi (Prepared Statements)** — *bukan PDO* |
| Pelayan Web | Apache/Nginx (XAMPP/LAMP untuk pembangunan) |
| Pengurusan Versi | Git (disyorkan, walaupun tidak dinyatakan wajib) |

### 9.2 Struktur Folder Cadangan

```
sistem-sukan-jts/
├── admin/
│   ├── auth/              (login, logout, session check)
│   ├── bahagian/          (CRUD bahagian & jabatan jemputan)
│   ├── sukan/              (CRUD jenis sukan)
│   ├── jadual/             (CRUD jadual perlawanan)
│   ├── keputusan/          (update skor & status)
│   ├── venue/              (CRUD tempat pertandingan)
│   ├── aturcara/           (CRUD aturcara pembukaan/penutup)
│   ├── galeri/             (upload/padam media)
│   └── dashboard.php
├── public/
│   ├── index.php           (landing page)
│   ├── jadual.php
│   ├── keputusan.php
│   ├── kedudukan-pingat.php
│   ├── galeri.php
│   ├── venue.php
│   └── aturcara.php
├── includes/
│   ├── db.php              (sambungan MySQLi)
│   ├── functions.php
│   ├── header.php / footer.php
│   └── auth-check.php
├── assets/
│   ├── css/
│   ├── js/
│   └── uploads/ (gambar/video — dengan kawalan akses)
└── config/
    └── config.php
```

### 9.3 Contoh Sambungan MySQLi (Bukan PDO)

```php
<?php
// includes/db.php
$conn = mysqli_connect("localhost", "db_user", "db_password", "sistem_sukan_jts");

if (!$conn) {
    die("Sambungan pangkalan data gagal: " . mysqli_connect_error());
}

// Contoh query selamat dengan Prepared Statement
function getJadualBySukan($conn, $sukan_id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM jadual_perlawanan WHERE sukan_id = ? ORDER BY tarikh ASC");
    mysqli_stmt_bind_param($stmt, "i", $sukan_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result;
}
?>
```

---

## 10. Reka Bentuk Pangkalan Data (Database Schema — Cadangan)

| Jadual | Medan Utama |
|---|---|
| **tbl_pengguna** | id, nama, emel, kata_laluan (hash), peranan (super_admin/editor/media), status |
| **tbl_bahagian** | id, nama_bahagian, jenis (dalaman/jemputan), logo, singkatan |
| **tbl_sukan** | id, nama_sukan, kategori (lelaki/wanita/campuran), ikon, jenis (individu/berpasukan) |
| **tbl_pasukan** | id, bahagian_id, sukan_id, nama_pasukan |
| **tbl_venue** | id, nama_tempat, alamat, latitude, longitude, kapasiti |
| **tbl_jadual_perlawanan** | id, sukan_id, pasukan_a_id, pasukan_b_id, venue_id, tarikh, masa, status (akan_datang/live/selesai) |
| **tbl_keputusan** | id, jadual_id, skor_a, skor_b, pasukan_menang_id, catatan |
| **tbl_kedudukan_pingat** | bahagian_id, emas, perak, gangsa, jumlah *(atau dikira secara dinamik via query)* |
| **tbl_aturcara** | id, jenis (pembukaan/penutup), masa, aktiviti, pegawai_bertanggungjawab, susunan |
| **tbl_galeri** | id, tajuk, jenis_fail (imej/video), url_fail, album/kategori, tarikh_upload |
| **tbl_hero_banner** | id, tajuk, url_imej, bahagian_juara_id, status_aktif |
| **tbl_audit_log** | id, pengguna_id, tindakan, jadual_disentuh, masa, ip_address |

**Perhubungan (Relationships) ringkas:**
`tbl_bahagian` 1—N `tbl_pasukan` → `tbl_jadual_perlawanan` (2 FK: pasukan_a & pasukan_b) → 1—1 `tbl_keputusan`
`tbl_sukan` 1—N `tbl_jadual_perlawanan`
`tbl_venue` 1—N `tbl_jadual_perlawanan`

---

## 11. Wireframe / Struktur Laman (Sitemap)

**Laman Awam:**
1. Laman Utama (Hero + Ringkasan)
2. Info Pertandingan
3. Bahagian & Jabatan Jemputan
4. Senarai Sukan
5. Jadual Pertandingan
6. Keputusan (Live / Selesai / Akan Datang — tab)
7. Kedudukan Pingat
8. Tempat Pertandingan
9. Aturcara (Umum / Pembukaan / Penutup)
10. Galeri Media
11. Hubungi Kami (opsyenal)

**Panel Admin:**
1. Log Masuk
2. Dashboard
3. Urus Bahagian
4. Urus Sukan
5. Urus Jadual & Keputusan
6. Urus Venue
7. Urus Aturcara
8. Urus Galeri & Hero Banner
9. Urus Pengguna Admin
10. Log Audit

---

## 12. Cadangan UI/UX

- **Palet warna:** Berdasarkan identiti korporat kerajaan (cth: biru gelap, emas/kuning aksen untuk pingat, putih bersih)
- **Font:** Bootstrap default (Segoe/Helvetica-based) atau Google Fonts moden seperti *Poppins*/*Inter* untuk kelihatan segar
- **Komponen Bootstrap dicadangkan:** Navbar sticky, Card, Badge (status live/selesai), Tabs, Carousel (hero), Modal (view gambar galeri), Accordion (aturcara), Table responsive (kedudukan pingat)
- **Indikator Live:** Badge merah berkelip/animasi CSS ringkas untuk perlawanan "LIVE"
- **Aksesibiliti:** Kontras warna mencukupi, alt-text untuk semua imej, saiz butang mesra sentuhan (mobile)

---

## 13. Pelan Pembangunan (Fasa Cadangan)

| Fasa | Aktiviti | Anggaran Tempoh |
|---|---|---|
| Fasa 1 | Reka bentuk UI/UX (wireframe & mockup) + persetujuan klien | 1–2 minggu |
| Fasa 2 | Reka bentuk & bina pangkalan data | 1 minggu |
| Fasa 3 | Pembangunan Panel Admin (CRUD semua modul) | 2–3 minggu |
| Fasa 4 | Pembangunan Portal Awam (Front-End) | 2–3 minggu |
| Fasa 5 | Integrasi keselamatan (CSRF, validation, RBAC) | 1 minggu |
| Fasa 6 | Ujian (UAT), pembetulan pepijat | 1–2 minggu |
| Fasa 7 | Deployment & latihan pengguna | 1 minggu |

*(Anggaran keseluruhan: ~9–13 minggu, tertakluk kepada skop akhir yang dipersetujui)*

---

## 14. Ujian & Jaminan Kualiti (Testing & QA)

- Ujian fungsian setiap modul CRUD (create/edit/delete/view)
- Ujian keselamatan asas: cubaan SQL Injection, XSS, akses tanpa kebenaran
- Ujian responsif merentasi peranti (mobile/tablet/desktop)
- Ujian beban ringan semasa waktu puncak (banyak pelawat semasa keputusan live diumumkan)
- User Acceptance Testing (UAT) bersama urus setia sukan JTS sebelum go-live

---

## 15. Risiko & Mitigasi

| Risiko | Kesan | Mitigasi |
|---|---|---|
| Kemasukan skor tidak konsisten oleh pelbagai admin modul | Data kedudukan pingat salah | RBAC ketat + log audit + validasi skor |
| Trafik tinggi semasa pertandingan live | Laman perlahan/down | Caching ringkas, optimize query, hosting mencukupi |
| Upload media besar-besaran | Storan penuh | Had saiz fail, kompres imej automatik, semakan storan berkala |
| SQL Injection (kerana guna MySQLi bukan PDO) | Kebocoran/kerosakan data | Wajib guna Prepared Statements MySQLi sepanjang sistem (rujuk Seksyen 8) |

---

## 16. Kriteria Kejayaan (Success Criteria)

- ✅ Semua 16 modul awam (A1–A16) berfungsi dan responsif
- ✅ Semua 13 modul admin (B1–B13) menyokong CRUD penuh
- ✅ Kedudukan pingat dikemas kini automatik selepas keputusan direkod
- ✅ Tiada isu keselamatan kritikal semasa ujian penembusan asas (SQLi/XSS)
- ✅ Sistem stabil (tiada downtime kritikal) sepanjang tempoh pertandingan sebenar

---

## 17. Lampiran — Cadangan Nama Domain/Sub-domain

- `sukan.jts.sarawak.gov.my` *(cadangan sahaja, tertakluk kelulusan IT JTS/SUKEM)*

---

**Nota Akhir:** Dokumen ini adalah draf PRD peringkat awal. Cadangan seterusnya ialah sesi bengkel bersama urus setia sukan JTS untuk mengesahkan senarai penuh sukan, bilangan bahagian yang terlibat, dan format aturcara sebenar sebelum reka bentuk wireframe visual (mockup) dibangunkan.
