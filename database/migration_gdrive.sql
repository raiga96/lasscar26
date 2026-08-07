-- ============================================
-- Skrip Migrasi SQL: Integrasi Google Drive API v3
-- Pangkalan Data: sistem_sukan_jts
-- Jadual Disentuh: tbl_galeri
-- ============================================

USE sistem_sukan_jts;

-- 1. Tambah lajur-lajur metadata Google Drive ke dalam tbl_galeri
ALTER TABLE tbl_galeri 
  ADD COLUMN IF NOT EXISTS gdrive_file_id VARCHAR(100) NULL AFTER url_fail,
  ADD COLUMN IF NOT EXISTS gdrive_folder_id VARCHAR(100) NULL AFTER gdrive_file_id,
  ADD COLUMN IF NOT EXISTS gdrive_thumbnail_url TEXT NULL AFTER gdrive_folder_id,
  ADD COLUMN IF NOT EXISTS gdrive_view_url TEXT NULL AFTER gdrive_thumbnail_url,
  ADD COLUMN IF NOT EXISTS gdrive_modified_time DATETIME NULL AFTER gdrive_view_url,
  ADD COLUMN IF NOT EXISTS is_gdrive TINYINT(1) NOT NULL DEFAULT 0 AFTER gdrive_modified_time;

-- 2. Tambah indeks unik untuk menggelakkan gambar bertindih
ALTER TABLE tbl_galeri 
  ADD UNIQUE INDEX IF NOT EXISTS idx_gdrive_file_id (gdrive_file_id);
