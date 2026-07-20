# Product Requirement Document (PRD)

## Maklumat Projek
* **Nama Projek:** Sistem Pengurusan Sukan Jabatan Tanah dan Survei Sarawak (SukanJTS)
* **Klien/Pemilik:** Jabatan Tanah dan Survei Ibupejabat (JTS) Sarawak
* **Pembangun Sistem:** Darmizi bin Dan
* **Asas Teknologi:** Web-based (HTML5, CSS Bootstrap 5, JavaScript / jQuery, PHP Native dengan MySQLi Object-Oriented, Tanpa PDO)

---

## 1. Objektif Projek
* Menyediakan pusat informasi sehenti (one-stop center) digital rasmi bagi kejohanan sukan anjuran JTS Sarawak.
* Memaparkan jadual perlawanan, keputusan skor, kedudukan pingat semasa, dan tentative majlis secara dinamik kepada semua kontinjen dan orang awam.
* Menyediakan panel pentadbir (Admin Dashboard) yang selamat untuk pengurusan data kejohanan (CRUD) secara *real-time* tanpa kebergantungan kepada struktur PDO.

---

## 2. Struktur Modul & Keperluan Sistem

Sistem ini terbahagi kepada dua komponen utama: **Paparan Awam (Landing Page)** dan **Panel Pengurusan Pentadbir (Admin Dashboard)**.

### A. Paparan Awam (Public Landing Page - `index.php`)
Satu halaman utama berasaskan reka bentuk Bootstrap 5 yang responsif dan mudah dilayari, mengandungi seksyen-seksyen berikut:

1. **Hero Section & Pengumuman Pemenang**
   * Paparan visual utama kejohanan menggunakan imej beresolusi tinggi.
   * Mod Dinamik: Apabila status kejohanan bertukar kepada 'Selesai', seksyen ini secara automatik bertukar untuk memaparkan **Hero Image Juara Keseluruhan** (Kontinjen yang memenangi pingat emas terbanyak).

2. **Profil Kontinjen & Jabatan Jemputan**
   * Paparan kad (Bootstrap Cards) atau senarai logo rasmi kontinjen.
   * Terbahagi kepada dua kategori:
     * **Pejabat Bahagian JTS:** Ibu Pejabat, Kuching, Samarahan, Sri Aman, Betong, Sarikei, Sibu, Mukah, Kapit, Bintulu, Miri, Limbang.
     * **Jabatan Jemputan:** Agensi luar seperti PBT, JKMR, atau jabatan kerajaan lain yang dijemput bertanding.

3. **Aturcara Kejohanan**
   * **Aturcara Pembukaan (Opening Ceremony):** Paparan jadual masa dan senarai aktiviti/protokol majlis perasmian.
   * **Aturcara Makan Malam & Penutupan (Closing Dinner):** Jadual masa, menu (jika perlu), dan susunan agenda kemuncak majlis penutupan.

4. **Pusat Perlawanan Semasa (Match Center)**
   * **Sedang Berlangsung (Live/Ongoing):** Perlawanan yang sedang dimainkan pada hari/jam semasa beserta paparan skor langsung.
   * **Perlawanan Seterusnya (Next Match):** Senarai perlawanan akan datang bagi memudahkan atlet bersiap sedia.
   * **Perlawanan Selesai (Past Match):** Keputusan akhir perlawanan yang telah tamat bagi menentukan kedudukan pasukan.

5. **Jadual & Tempat Pertandingan**
   * Senarai penuh sukan yang dipertandingkan (cth: Bola Sepak, Badminton, Netball, Karom, Dart, dll).
   * Maklumat lokasi/tempat perlawanan yang jelas bagi setiap acara sukan.

6. **Papan Kedudukan Pingat (Medal Standing)**
   * Jadual automatik yang menyusun kedudukan kontinjen berdasarkan jumlah **Emas (Gold)**, **Perak (Silver)**, dan seterusnya **Gangsa (Bronze)**.

7. **Galeri Media Kejohanan**
   * Paparan grid gambar dan video sepanjang kejohanan menggunakan pemformatan galeri responsif Bootstrap dengan kesan lightbox JavaScript.

---

### B. Panel Pengurusan (Admin Dashboard - CRUD Panel)
Halaman pengurusan bertapis keselamatan (Session-based login) untuk urus setia kejohanan melaksanakan operasi **Create, Read, Update, Delete (CRUD)** menggunakan skrip PHP Native dan penyata MySQLi.

| Modul Pengurusan | Operasi CRUD yang Diperlukan |
| :--- | :--- |
| **Urus Kontinjen & Jemputan** | Tambah bahagian/jabatan baru, muat naik logo bendera, kemaskini nama, dan padam rekod. |
| **Urus Acara Sukan & Lokasi** | Urus jenis sukan yang dipertandingkan, kategori (Lelaki/Wanita), dan nama tempat perlawanan. |
| **Urus Jadual & Skor** | Tambah jadual perlawanan baru (tarikh, masa, venue), kemaskini status perlawanan (`Belum`, `Sedang`, `Selesai`), dan kemaskini skor perlawanan untuk auto-kemaskini pingat kontinjen. |
| **Urus Tentatif Aturcara** | Mengemaskini susunan aktiviti bagi Majlis Pembukaan dan Majlis Penutupan/Dinner. |
| **Urus Media & Hero Setup** | Memuat naik gambar ke galeri kejohanan dan menetapkan imej juara untuk dipaparkan pada Hero Section. |

---

## 3. Reka Bentuk Pangkalan Data (Skema MySQL)

Semua operasi pangkalan data menggunakan pemandu **MySQLi (Object-Oriented)** dengan *Prepared Statements* untuk mengelakkan serangan SQL Injection. Penggunaan PDO adalah dilarang sama sekali mengikut spesifikasi pembangunan ini.

```sql
-- 1. Table: kontinjen
CREATE TABLE kontinjen (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_bahagian VARCHAR(100) NOT NULL,
    jenis ENUM('JTS', 'Jemputan') DEFAULT 'JTS',
    logo VARCHAR(255) DEFAULT 'default_logo.png',
    emas INT DEFAULT 0,
    perak INT DEFAULT 0,
    gangsa INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table: sukan
CREATE TABLE sukan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_sukan VARCHAR(50) NOT NULL,
    kategori VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table: perlawanan
CREATE TABLE perlawanan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sukan_id INT NOT NULL,
    kontinjen_a_id INT NOT NULL,
    kontinjen_b_id INT NOT NULL,
    skor_a INT DEFAULT NULL,
    skor_b INT DEFAULT NULL,
    tempat VARCHAR(150) NOT NULL,
    tarikh_masa DATETIME NOT NULL,
    status ENUM('Belum', 'Sedang', 'Selesai') DEFAULT 'Belum',
    pemenang_id INT DEFAULT NULL,
    FOREIGN KEY (sukan_id) REFERENCES sukan(id) ON DELETE CASCADE,
    FOREIGN KEY (kontinjen_a_id) REFERENCES kontinjen(id) ON DELETE CASCADE,
    FOREIGN KEY (kontinjen_b_id) REFERENCES kontinjen(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table: aturcara
CREATE TABLE aturcara (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jenis_majlis ENUM('Pembukaan', 'Penutupan/Dinner') NOT NULL,
    masa TIME NOT NULL,
    aktiviti TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 4. Contoh Implementasi Kod CRUD (Sintaks MySQLi)

Berikut adalah contoh standard penulisan kod PHP untuk operasi kemaskini data menggunakan MySQLi tanpa menggunakan PDO:

```php
<?php
// Sambungan ke Database menggunakan MySQLi
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "db_sukanjts";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Sambungan pangkalan data gagal: " . $conn->connect_error);
}

// Operasi UPDATE Skor & Status Perlawanan (CRUD - Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $perlawanan_id = $_POST['perlawanan_id'];
    $skor_a        = $_POST['skor_a'];
    $skor_b        = $_POST['skor_b'];
    $status        = 'Selesai';

    // Menggunakan Prepared Statement MySQLi
    $stmt = $conn->prepare("UPDATE perlawanan SET skor_a = ?, skor_b = ?, status = ? WHERE id = ?");
    $stmt->bind_param("iiii", $skor_a, $skor_b, $status, $perlawanan_id);

    if ($stmt->execute()) {
        echo "Keputusan perlawanan berjaya dikemaskini.";
    } else {
        echo "Ralat berlaku: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
```

---

## 5. Keperluan Bukan Fungsian (Non-Functional Requirements)
* **Kelebihan Responsif:** Antaramuka frontend berasaskan Bootstrap 5 wajib memaparkan susunan yang kemas apabila dibuka menggunakan peranti mudah alih (telefon pintar) oleh urus setia di lapangan sukan.
* **Keselamatan Maklumat:** Akses ke folder `admin/` mestilah disekat menggunakan validasi `session_start()` pada setiap permulaan fail PHP bagi menghalang akses tanpa kebenaran (*unauthorized access*).
* **Prestasi Query:** Pangkalan data perlu dioptimumkan menggunakan *foreign keys* yang betul agar pengiraan automatik jumlah pingat pada Papan Kedudukan (Medal Standing) berjalan dengan pantas tanpa melambatkan muatan halaman.
