-- ============================================================
-- Skrip SQL Modul Spin Wheel LASSCAR 2028
-- Pangkalan Data: sistem_sukan_jts
-- Enjin: InnoDB (Menyokong Foreign Key Constraints)
-- Tarikh: 7 Ogos 2026
-- ============================================================

-- 1. Jadual Utama Penganjuran / Draw
CREATE TABLE IF NOT EXISTS `tbl_lasscar_draw` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama_event` VARCHAR(100) NOT NULL DEFAULT 'LASSCAR 2028',
    `id_bahagian_menang` INT UNSIGNED NULL,
    `status_draw` ENUM('belum_set','sedia','selesai') NOT NULL DEFAULT 'belum_set',
    `dicipta_oleh` INT UNSIGNED NULL,
    `dicipta_pada` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `dikemaskini_pada` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_lasscar_draw_bahagian` 
        FOREIGN KEY (`id_bahagian_menang`) REFERENCES `tbl_bahagian` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Jadual Log Tindakan Majlis
CREATE TABLE IF NOT EXISTS `tbl_lasscar_draw_log` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_draw` INT UNSIGNED NOT NULL,
    `tindakan` VARCHAR(50) NOT NULL, -- 'set_pemenang', 'mula_spin', 'reveal', 'reset'
    `oleh_user_id` INT UNSIGNED NULL,
    `masa` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_lasscar_draw_log_draw` 
        FOREIGN KEY (`id_draw`) REFERENCES `tbl_lasscar_draw` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Inisialisasi Rekod Penganjur Lalai (ID 5: LANDAS MIRI)
INSERT INTO `tbl_lasscar_draw` (`nama_event`, `id_bahagian_menang`, `status_draw`) 
VALUES ('LASSCAR 2028', 5, 'sedia')
ON DUPLICATE KEY UPDATE `id_bahagian_menang` = 5, `status_draw` = 'sedia';
