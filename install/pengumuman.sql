-- Pengumuman & brosur (gambar di uploads/pengumuman/, tidak masuk Perpustakaan Dokumen)

CREATE TABLE IF NOT EXISTS `pengumuman` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `judul` VARCHAR(255) NOT NULL DEFAULT '',
  `teks` TEXT NOT NULL,
  `nama_gambar` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Berkas di uploads/pengumuman/',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pengumuman_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
