-- Opsional: kolom kategori bagian pada arsip_surat (Monitoring Disposisi).
-- Jalankan setelah tabel `arsip_surat` ada. Jika kolom sudah ada, lewati pernyataan yang error.

-- MariaDB 10.0.2+ (Laragon umumnya MariaDB):
ALTER TABLE `arsip_surat`
    ADD COLUMN IF NOT EXISTS `kategori_bagian` VARCHAR(64) NULL DEFAULT NULL
    COMMENT 'Slug: kelembagaan_anjab, kinerja_rb, pelayanan_tatalaksana, kepegawaian, keuangan, kabag';

-- MySQL tanpa IF NOT EXISTS: gunakan jika pernyataan di atas tidak didukung (hapus baris MariaDB lalu jalankan):
-- ALTER TABLE `arsip_surat`
--     ADD COLUMN `kategori_bagian` VARCHAR(64) NULL DEFAULT NULL
--     COMMENT 'Slug: kelembagaan_anjab, kinerja_rb, pelayanan_tatalaksana, kepegawaian, keuangan, kabag';
