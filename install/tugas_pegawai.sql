-- =============================================================================
-- Tabel tugas_pegawai — Manajemen Tugas E-Organisasi
-- =============================================================================
-- Jalankan di phpMyAdmin pada basis data proyek (sesuaikan USE jika perlu).
-- =============================================================================

USE `db_organisasi`;

CREATE TABLE IF NOT EXISTS `tugas_pegawai` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL COMMENT 'Pemilik / pengunggah (FK users.id)',
  `judul_tugas` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `file_tugas` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Nama file di uploads/tugas_pegawai/',
  `status` ENUM('pending', 'diterima', 'revisi', 'selesai', 'ditolak') NOT NULL DEFAULT 'pending',
  `catatan_kabag` TEXT NULL DEFAULT NULL COMMENT 'Umpan balik Kabag saat revisi / validasi',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tugas_user_id` (`user_id`),
  KEY `idx_tugas_status` (`status`),
  KEY `idx_tugas_created` (`created_at`),
  CONSTRAINT `fk_tugas_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tugas pegawai — visibilitas: pemilik + kabag_organisasi';
