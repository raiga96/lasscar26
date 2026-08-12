-- ============================================
-- Skrip Migrasi SQL: Kemaskini View Kedudukan Pingat (vw_kedudukan_pingat)
-- Pangkalan Data: sistem_sukan_jts
-- Tujuan: Mengira Pingat Perak secara automatik untuk pasukan kalah (Naib Juara) dalam Perlawanan Akhir (Emas)
-- ============================================

USE sistem_sukan_jts;

CREATE OR REPLACE VIEW vw_kedudukan_pingat AS
SELECT 
    b.id AS bahagian_id,
    b.nama_bahagian AS nama_bahagian,
    b.singkatan AS singkatan,
    b.logo_url AS logo_url,
    b.jenis AS jenis,
    SUM(CASE 
        WHEN k.jenis_pingat = 'emas' AND p.id = k.pasukan_menang_id THEN 1 
        ELSE 0 
    END) AS emas,
    SUM(CASE 
        WHEN k.jenis_pingat = 'perak' AND p.id = k.pasukan_menang_id THEN 1 
        WHEN k.jenis_pingat = 'emas' AND (
            (j.pasukan_a_id = p.id AND k.pasukan_menang_id = j.pasukan_b_id) OR 
            (j.pasukan_b_id = p.id AND k.pasukan_menang_id = j.pasukan_a_id)
        ) THEN 1 
        ELSE 0 
    END) AS perak,
    SUM(CASE 
        WHEN k.jenis_pingat = 'gangsa' AND p.id = k.pasukan_menang_id THEN 1 
        ELSE 0 
    END) AS gangsa,
    (
        SUM(CASE WHEN k.jenis_pingat = 'emas' AND p.id = k.pasukan_menang_id THEN 1 ELSE 0 END) +
        SUM(CASE 
            WHEN k.jenis_pingat = 'perak' AND p.id = k.pasukan_menang_id THEN 1 
            WHEN k.jenis_pingat = 'emas' AND (
                (j.pasukan_a_id = p.id AND k.pasukan_menang_id = j.pasukan_b_id) OR 
                (j.pasukan_b_id = p.id AND k.pasukan_menang_id = j.pasukan_a_id)
            ) THEN 1 
            ELSE 0 
        END) +
        SUM(CASE WHEN k.jenis_pingat = 'gangsa' AND p.id = k.pasukan_menang_id THEN 1 ELSE 0 END)
    ) AS jumlah
FROM tbl_bahagian b
LEFT JOIN tbl_pasukan p ON p.bahagian_id = b.id
LEFT JOIN tbl_jadual_perlawanan j ON (j.pasukan_a_id = p.id OR j.pasukan_b_id = p.id)
LEFT JOIN tbl_keputusan k ON k.jadual_id = j.id
GROUP BY b.id, b.nama_bahagian, b.singkatan, b.logo_url, b.jenis
ORDER BY 
    emas DESC, 
    perak DESC, 
    gangsa DESC, 
    b.nama_bahagian ASC;
