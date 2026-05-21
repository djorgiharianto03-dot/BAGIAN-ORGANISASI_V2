-- Metadata dokumen publik + penghitung unduhan (Digital Library)
-- Basis data: db_organisasi (sesuai config/database.php)

CREATE TABLE IF NOT EXISTS `dokumen` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_file` VARCHAR(255) NOT NULL COMMENT 'Nama file di folder uploads',
  `kategori` VARCHAR(100) NOT NULL DEFAULT 'Umum',
  `jumlah_unduh` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_dokumen_nama_file` (`nama_file`),
  KEY `idx_dokumen_unduh` (`jumlah_unduh`),
  KEY `idx_dokumen_kategori` (`kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
