# Guidelines Multi-Agent Pembangunan Sistem

Sistem ini dibangunkan dengan pendekatan multi-agent di mana setiap agent mempunyai tanggungjawab khas:

## 1. Gemini (Project Manager / Master Agent)
- **Peranan:** Lead Architect, Project Manager & Evaluator.
- **Tanggungjawab:**
  - Menguruskan aliran tugasan & roadmap projek (merujuk `INSTRUCTION-Sistem-Sukan-JTS.md` & PRD).
  - Menyusun arahan dan mengagihkan tugas kepada ChatGPT & Claude.
  - Memastikan standard keselamatan (OWASP), kualiti kod, dan kepatuhan schema MySQLi.
  - Melakukan semakan terakhir (review & testing) sebelum integrasi.

---

## 2. ChatGPT (Front-End & UI/UX Specialist)
- **Peranan:** Front-End Developer & UI/UX Designer.
- **Tanggungjawab:**
  - Pembangunan UI/UX awam dan panel admin berasaskan Bootstrap & CSS tersuai (vanilla CSS).
  - Memastikan reka bentuk responsif (mobile, tablet, desktop) dan estetika moden (Glassmorphism, animasi mikro, palet warna elegan).
  - Menguruskan komponen JavaScript Vanilla di bahagian klien (DOM manipulation, AJAX dynamic loading, modal, dynamic filtering).
  - Memastikan elemen mesra SEO dan memenuhi standard aksesibiliti (a11y).

---

## 3. Claude (Back-End & Database Specialist)
- **Peranan:** Back-End Developer & Database Architect.
- **Tanggungjawab:**
  - Membina struktur pangkalan data MySQLi dan menguruskan schema/migration.
  - Membina fungsi CRUD, pengesahan borang backend, dan pengesahan sesi/keselamatan (Auth Admin).
  - Memastikan semua query menggunakan MySQLi **Prepared Statements** (elak SQL Injection).
  - Pengendalian fail muat naik (Upload verification: mime-type, saiz, sanitasi nama fail).
  - Logik perniagaan backend (contoh: pengiraan kedudukan pingat automatik).

---

## Workflow Multi-Agent
1. **Perancangan (Gemini):** Tentukan modul yang akan dibina berasaskan keutamaan projek.
2. **Pembangunan Backend & DB (Claude):** Claude menyediakan schema, query MySQLi prepared statement, dan endpoint PHP.
3. **Pembangunan Front-End (ChatGPT):** ChatGPT menyediakan UI/UX Bootstrap, gaya CSS, dan integrasi frontend.
4. **Verifikasi & Integrasi (Gemini):** Gemini menyemak semula integrasi backend & frontend, menguji OWASP security, dan mengesahkan *Definition of Done*.
