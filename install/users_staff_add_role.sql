-- =============================================================================
-- Tambah kolom level pada tabel users (jika tabel sudah ada tanpa kolom ini)
-- =============================================================================
-- Sesuaikan nama database dengan config/database.php (mis. db_organisasi).
USE `db_organisasi`;

ALTER TABLE `users`
  ADD COLUMN `level` VARCHAR(64) NULL DEFAULT NULL COMMENT 'super_admin, admin, sub_admin_eorganisasi, sub_admin_publikasi' AFTER `email_google`;

-- Jika masih memakai kolom lama nama_staf, jalankan sekali:
-- ALTER TABLE `users` CHANGE `nama_staf` `nama` VARCHAR(191) NOT NULL DEFAULT '';
