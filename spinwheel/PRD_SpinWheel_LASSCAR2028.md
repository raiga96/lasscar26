# PRD — Sistem Spin Wheel Penganjuran LASSCAR 2028

**Modul:** Spin Wheel Reveal (Majlis Pengumuman Penganjur)
**Sistem Induk:** Sistem Sukan JTS (`sistem_sukan_jts`)
**Versi:** 1.0
**Tarikh:** 7 Ogos 2026
**Disediakan untuk:** JTS Sarawak

---

## 1. Latar Belakang

Bahagian penganjur LASSCAR 2028 telah pun ditetapkan secara pentadbiran (bukan rawak). Walau bagaimanapun, majlis pengumuman perlu kekal berbentuk "lucky draw" secara visual — roda spin akan berputar dan mendarat di bahagian yang telah ditetapkan, bagi tujuan showmanship semasa majlis rasmi.

Sistem ini adalah modul tambahan kepada Sistem Sukan JTS sedia ada dan akan menggunakan data bahagian dari jadual `tbl_bahagian` yang sudah wujud.

## 2. Objektif

1. Memaparkan roda spin interaktif dengan logo dan nama setiap bahagian pada setiap jejari (segment).
2. Membenarkan admin menetapkan bahagian pemenang (penganjur) sebelum majlis, secara tersembunyi.
3. Animasi spin yang meyakinkan (realistic easing, pelbagai pusingan) dan **mesti** berhenti tepat pada bahagian yang telah ditetapkan.
4. Menyimpan rekod/log setiap draw untuk audit (siapa set, bila, hasil).
5. UI yang sesuai untuk paparan skrin besar / projektor semasa majlis rasmi.

## 3. Skop Projek

**Dalam skop:**
- Paparan wheel (canvas/SVG) dengan logo + nama bahagian dari `tbl_bahagian`
- Panel admin untuk tetapkan bahagian pemenang (hidden dari public view)
- Animasi spin dengan hasil predetermined
- Log sejarah draw
- Reveal effect (confetti/SweetAlert2) selepas wheel berhenti

**Luar skop:**
- Sistem pengurusan bahagian (CRUD `tbl_bahagian`) — guna sistem sukan sedia ada
- Live streaming / broadcast integration
- Multi-event draw serentak (fokus LASSCAR 2028 sahaja buat masa ini, tapi struktur DB dibuat generic untuk event akan datang)

## 4. Pengguna & Peranan

| Peranan | Akses |
|---|---|
| **Super Admin / Admin Sukan** | Tetapkan bahagian pemenang, mula/reset draw, lihat log |
| **Operator Majlis** | Trigger butang "SPIN" semasa majlis (tiada akses tetapkan pemenang) |
| **Public/Screen View** | Lihat wheel sahaja (read-only, tanpa kawalan) |

## 5. Keperluan Fungsian

### FR1 — Papar Wheel Interaktif
- Wheel dipaparkan sebagai bulatan dibahagi kepada N jejari (segment), N = jumlah bahagian aktif (`status = 'aktif'`).
- Setiap segment papar: logo (`logo_url`) + nama/singkatan bahagian (`nama_bahagian` / `singkatan`).
- Jika `logo_url` NULL, guna placeholder/avatar generik (inisial nama bahagian).
- Warna segment auto-generate (palette tetap, bukan random setiap load) supaya konsisten.

### FR2 — Ambil Data Bahagian
- Endpoint `get_bahagian_wheel.php` — return senarai bahagian aktif dalam JSON (id, nama_bahagian, singkatan, logo_url).
- Guna MySQLi prepared statement, filter `status = 'aktif'`.
- Boleh filter tambahan ikut `jenis` (dalaman/jemputan) jika hanya sebahagian bahagian layak jadi penganjur — **perlu confirm dengan Darmizi sama ada semua bahagian aktif layak, atau hanya jenis tertentu.**

### FR3 — Penetapan Pemenang (Predetermined Result)
- Jadual baharu `tbl_lasscar_draw` menyimpan:
  - `id_bahagian_menang` (FK ke `tbl_bahagian.id`)
  - `status_draw` (`belum_set`, `sedia`, `selesai`)
  - `nama_event` (cth: `LASSCAR 2028`)
- Admin pilih bahagian pemenang melalui dropdown (bukan wheel) di panel admin — disimpan dalam DB, **tidak dipaparkan** pada mana-mana UI public sebelum spin.
- Selepas draw `status_draw = 'selesai'`, tidak boleh spin/set semula tanpa reset eksplisit oleh Super Admin (elak pemenang tertukar secara tidak sengaja).

### FR4 — Animasi Spin (Server-Determined Rotation)
- Bila operator klik "SPIN", frontend call `spin_wheel.php` (POST, session-protected).
- **Backend** kira sudut rotation akhir berdasarkan kedudukan segment pemenang dalam array (bukan frontend yang tahu index pemenang secara terus), contoh:
  ```
  sudut_akhir = (360 * bilangan_pusingan_random(5-8)) 
              + (360 - sudut_tengah_segment_pemenang) 
              + offset_kecil_random(-5° hingga +5°)
  ```
- Backend hantar balik **hanya** `final_rotation_degrees` dan `duration_ms` — bukan `id_bahagian_menang` — supaya tidak mudah "intip" melalui Network tab devtools sebelum wheel berhenti. (Nota: ini bukan keselamatan mutlak, hanya elak spoiler mudah semasa majlis.)
- Frontend animate guna CSS `transition` / `requestAnimationFrame` dengan easing `cubic-bezier` (cepat → perlahan, macam wheel casino).
- Selepas animasi selesai, frontend baru call `get_hasil_draw.php` untuk dapatkan nama & logo pemenang sebenar bagi reveal.

### FR5 — Reveal Selepas Spin
- Apabila wheel berhenti, papar modal SweetAlert2 dengan logo besar + nama bahagian pemenang + confetti/animation.
- Auto-update `status_draw` kepada `selesai` dalam DB.

### FR6 — Log & Audit
- Setiap tindakan (set pemenang, spin, reveal) direkod dalam `tbl_lasscar_draw_log`:
  - `id`, `id_draw`, `tindakan`, `oleh_user_id`, `masa`

## 6. Keperluan Bukan Fungsian

| Aspek | Keperluan |
|---|---|
| Responsive | Wheel kekal proporsional pada skrin projektor (16:9) & tablet |
| Prestasi | Loading logo guna lazy-load / preload sebelum spin dimulakan (elak lag semasa animasi) |
| Keselamatan | MySQLi prepared statements sahaja, session check untuk endpoint admin, CSRF token pada borang set pemenang |
| Backward Compatibility | Tidak ubah struktur `tbl_bahagian` sedia ada |
| Browser | Chrome/Edge terkini (untuk keperluan majlis, tak perlu sokong browser lama) |

## 7. Reka Bentuk Pangkalan Data (Tambahan)

```sql
CREATE TABLE tbl_lasscar_draw (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_event VARCHAR(100) NOT NULL DEFAULT 'LASSCAR 2028',
    id_bahagian_menang INT UNSIGNED NULL,
    status_draw ENUM('belum_set','sedia','selesai') NOT NULL DEFAULT 'belum_set',
    dicipta_oleh INT UNSIGNED NULL,
    dicipta_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dikemaskini_pada TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_bahagian_menang) REFERENCES tbl_bahagian(id)
) ENGINE=InnoDB;

CREATE TABLE tbl_lasscar_draw_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_draw INT UNSIGNED NOT NULL,
    tindakan VARCHAR(50) NOT NULL, -- 'set_pemenang','mula_spin','reveal','reset'
    oleh_user_id INT UNSIGNED NULL,
    masa TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_draw) REFERENCES tbl_lasscar_draw(id)
) ENGINE=InnoDB;
```

> Nota: guna `InnoDB` (bukan MyISAM) untuk sokong foreign key constraint — selari dengan penemuan audit IMS v2 sebelum ini.

## 8. Seni Bina Teknikal

**Stack:** PHP procedural (page-controller), MySQLi prepared statements, Bootstrap 5, vanilla JS, SweetAlert2, Canvas API untuk wheel rendering.

**Struktur fail dicadangkan:**
```
/lasscar_wheel/
├── index.php                  # Paparan wheel public/screen
├── admin_draw.php             # Panel admin: pilih & kunci pemenang
├── actions/
│   ├── set_pemenang.php       # POST: admin set id_bahagian_menang
│   ├── spin_wheel.php         # POST: kira & pulang final_rotation_degrees
│   ├── get_hasil_draw.php     # GET: hasil sebenar (selepas spin selesai)
│   └── get_bahagian_wheel.php # GET: senarai bahagian aktif (JSON)
├── includes/
│   ├── db.php
│   └── auth_check.php
└── assets/
    ├── js/wheel.js            # Render canvas + animasi
    └── css/wheel.css
```

## 9. Reka Bentuk API/Endpoint

| Endpoint | Method | Auth | Fungsi |
|---|---|---|---|
| `get_bahagian_wheel.php` | GET | Public | Senarai bahagian aktif utk render wheel |
| `set_pemenang.php` | POST | Admin | Set `id_bahagian_menang` (CSRF protected) |
| `spin_wheel.php` | POST | Operator | Pulangkan `final_rotation_degrees`, `duration_ms` |
| `get_hasil_draw.php` | GET | Public | Nama + logo pemenang (hanya selepas `status_draw = selesai`) |

## 10. Alur Kerja (Workflow)

1. Admin log masuk → `admin_draw.php` → pilih bahagian pemenang → simpan (`status_draw = sedia`).
2. Semasa majlis, operator buka `index.php` di skrin utama.
3. Operator klik butang **"SPIN"** → call `spin_wheel.php`.
4. Backend kira sudut rotation berdasarkan pemenang tersimpan → hantar rotation degrees sahaja ke frontend.
5. Frontend animate wheel (5–8 saat, easing perlahan di akhir).
6. Apabila animasi tamat → frontend call `get_hasil_draw.php` → papar modal reveal + confetti.
7. `status_draw` bertukar `selesai`, log direkod.

## 11. Keperluan UI/UX

- Wheel besar di tengah skrin, logo bahagian kelihatan jelas walaupun dari jarak jauh (projektor).
- Butang SPIN besar, jelas, hanya aktif jika `status_draw = 'sedia'`.
- Loading state semasa fetch data bahagian (skeleton/spinner).
- Reveal modal: logo besar, nama penuh bahagian, animasi confetti, butang "Tutup".

## 12. Keselamatan

- Semua endpoint admin (`set_pemenang.php`) wajib session check + role check (Super Admin sahaja).
- CSRF token pada borang set pemenang.
- Prepared statements untuk semua query (tiada string concatenation).
- `id_bahagian_menang` tidak sekali-kali dihantar ke frontend sebelum spin selesai.
- Rate-limit/lock: `spin_wheel.php` hanya boleh dipanggil sekali per draw (`status_draw` guard) — elak spin berulang tukar hasil.

## 13. Pelan Pembangunan (Cadangan Milestone)

| Fasa | Kerja | Anggaran |
|---|---|---|
| 1 | Setup DB (`tbl_lasscar_draw`, log), endpoint data bahagian | 0.5 hari |
| 2 | Bina wheel rendering (canvas) + logo/nama per segment | 1 hari |
| 3 | Panel admin set pemenang + CSRF + auth | 0.5 hari |
| 4 | Logic rotation server-side + animasi frontend | 1 hari |
| 5 | Reveal modal + SweetAlert2 + confetti | 0.5 hari |
| 6 | Testing end-to-end + dry-run majlis | 0.5 hari |

**Anggaran keseluruhan:** ~4 hari kerja.

---

# Arahan Pembangunan (Step-by-Step)

1. **DB Setup**
   - Jalankan SQL di Seksyen 7 pada `sistem_sukan_jts`.
   - Pastikan `ENGINE=InnoDB` untuk sokong FK.

2. **Endpoint data bahagian**
   - `get_bahagian_wheel.php`: query `SELECT id, nama_bahagian, singkatan, logo_url FROM tbl_bahagian WHERE status = 'aktif'` guna prepared statement, return JSON.

3. **Wheel rendering (`wheel.js`)**
   - Fetch data dari `get_bahagian_wheel.php`.
   - Kira sudut per segment = `360 / bilangan_bahagian`.
   - Lukis setiap segment guna Canvas API (`arc`, `fillText`/`drawImage` untuk logo).
   - Preload semua logo (`Image()` object) sebelum render untuk elak flicker.

4. **Panel admin (`admin_draw.php`)**
   - Dropdown senarai bahagian aktif → pilih pemenang.
   - Submit ke `set_pemenang.php` dengan CSRF token.
   - Simpan `id_bahagian_menang`, set `status_draw = 'sedia'`, log tindakan.

5. **Logic spin (`spin_wheel.php`)**
   - Ambil `id_bahagian_menang` dari `tbl_lasscar_draw` (status mesti `sedia`).
   - Kira index segment pemenang berdasarkan urutan sama seperti frontend render (**penting: urutan mesti konsisten** — guna `ORDER BY id` di kedua-dua endpoint).
   - Kira `final_rotation_degrees` (rujuk formula di FR4).
   - Return JSON `{final_rotation_degrees, duration_ms}` sahaja.
   - Set `status_draw = 'selesai'` (guard: tolak jika bukan status `sedia`).

6. **Animasi frontend**
   - Guna CSS `transform: rotate()` dengan `transition: transform Xs cubic-bezier(0.17, 0.67, 0.12, 0.99)`.
   - Apabila `transitionend` event fired → call `get_hasil_draw.php`.

7. **Reveal (`get_hasil_draw.php` + modal)**
   - Return nama + logo bahagian pemenang (hanya jika `status_draw = 'selesai'`).
   - Papar guna SweetAlert2 dengan custom HTML (logo + nama) + confetti library ringkas (cth: canvas-confetti CDN).

8. **Testing**
   - Test urutan segment frontend vs backend SAMA (paling kritikal — silap urutan = wheel berhenti di segment salah walaupun rotation degree betul).
   - Dry-run penuh sebelum majlis sebenar.

---

**Soalan untuk pengesahan sebelum development bermula:**
1. Adakah semua bahagian `status = 'aktif'` layak jadi calon penganjur, atau hanya `jenis = 'dalaman'`? dalaman saja
2. Berapa ramai bahagian dijangka pada wheel (untuk optimize saiz segment/logo)? 13 Bahagian
3. Perlukah sistem ini standalone (folder berasingan) atau integrate terus dalam Sistem Sukan JTS sedia ada (guna session/auth yang sama)? tidak perlu, buat dalam folder spinwheel, nanti akan di akses domain/lasscar26/spinwheel/index.php dan tidak perlu admin. set akan hanya di atas index.php sahaja
