-- ============================================
-- Skrip Migrasi SQL: YouTube Live Integration
-- Pangkalan Data: sistem_sukan_jts
-- Jadual Disentuh: tbl_jadual_perlawanan
-- ============================================

USE sistem_sukan_jts;

-- Tambah lajur youtube_url ke dalam tbl_jadual_perlawanan
ALTER TABLE tbl_jadual_perlawanan 
  ADD COLUMN youtube_url VARCHAR(255) NULL AFTER status;
