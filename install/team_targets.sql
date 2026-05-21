-- Target tahunan per Tim Kerja (Kelembagaan, RB, Yanlik)
-- Buka admin/kelola_team_targets.php sekali untuk migrasi otomatis jika tabel belum ada.

CREATE TABLE IF NOT EXISTS `team_targets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tim_kerja` ENUM('kelembagaan', 'rb', 'yanlik') NOT NULL,
  `tahun` SMALLINT UNSIGNED NOT NULL,
  `kegiatan` VARCHAR(255) NOT NULL DEFAULT '',
  `status` ENUM('direncanakan', 'berjalan', 'selesai') NOT NULL DEFAULT 'direncanakan',
  `urutan` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_team_targets_tahun_tim` (`tahun`, `tim_kerja`, `urutan`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `team_targets_year` (
  `tahun` SMALLINT UNSIGNED NOT NULL,
  `tampil_beranda` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tahun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
