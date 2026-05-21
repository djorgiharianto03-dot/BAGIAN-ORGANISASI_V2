-- =============================================================================
-- Tabel MySQL opsional: personel (selaras field aplikasi: id, nama, nip, jabatan)
-- =============================================================================
-- Catatan: aplikasi saat ini menyimpan personel di berkas personnel.json.
-- Skrip ini untuk integrasi database / migrasi ke MySQL di kemudian hari.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `personel` (
  `id` VARCHAR(64) NOT NULL,
  `nama` VARCHAR(255) NOT NULL,
  `nip` VARCHAR(20) NOT NULL DEFAULT '',
  `jabatan` VARCHAR(255) NOT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nip` (`nip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Personel Bagian Organisasi (mirror opsional ke JSON)';

-- -----------------------------------------------------------------------------
-- Jika tabel personel sudah ada dari versi lama tanpa kolom nip:
-- -----------------------------------------------------------------------------
-- ALTER TABLE `personel` ADD COLUMN `nip` VARCHAR(20) NOT NULL DEFAULT '' AFTER `nama`;
