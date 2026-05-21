-- Tabel saran & kritik pengunjung (AJAX dari footer situs)
-- Jalankan di phpMyAdmin pada basis data proyek Anda.

CREATE TABLE IF NOT EXISTS `saran_kritik` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(191) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `pesan` TEXT NOT NULL,
  `tgl_kirim` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_saran_kritik_tgl` (`tgl_kirim`),
  KEY `idx_saran_kritik_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
