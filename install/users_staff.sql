-- =============================================================================
-- Tabel users (akun staf) — manajemen admin
-- =============================================================================
-- Jalankan di phpMyAdmin pada basis data proyek (sesuaikan USE jika perlu).
-- =============================================================================

-- Sesuaikan nama database dengan config/database.php (mis. db_organisasi).
USE `db_organisasi`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL COMMENT 'Login unik',
  `nama` VARCHAR(191) NOT NULL DEFAULT '' COMMENT 'Nama lengkap / tampilan',
  `email_google` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Gmail untuk integrasi Google Drive',
  `level` VARCHAR(64) NULL DEFAULT NULL COMMENT 'super_admin, admin, sub_admin_eorganisasi, sub_admin_publikasi',
  `password` VARCHAR(255) NOT NULL COMMENT 'password_hash()',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Akun staf (email Google + password ter-hash)';
