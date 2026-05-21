-- =============================================================================
-- Migrasi nilai kolom `level` ke: super_admin | admin | sub_admin_eorganisasi | sub_admin_publikasi
-- Jalankan pada database yang sama dengan config/database.php (mis. db_organisasi).
-- =============================================================================

ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `level` VARCHAR(64) NULL DEFAULT NULL
  COMMENT 'super_admin, admin, sub_admin_eorganisasi, sub_admin_publikasi';

UPDATE `users` SET `level` = COALESCE(`level`, `role`)
  WHERE (`level` IS NULL OR TRIM(`level`) = '');

UPDATE `users` SET `level` = 'sub_admin_eorganisasi'
  WHERE LOWER(TRIM(COALESCE(`level`, ''))) IN ('sub admin', 'sub_admin', 'subadmin');

UPDATE `users` SET `level` = 'admin'
  WHERE LOWER(TRIM(COALESCE(`level`, ''))) IN ('admin', 'administrator');

UPDATE `users` SET `level` = 'super_admin'
  WHERE LOWER(TRIM(COALESCE(`level`, ''))) IN ('super admin', 'super_admin');

UPDATE `users` SET `level` = 'admin'
  WHERE LOWER(`username`) = 'djorgi';
