-- Kolom `role` untuk menyimpan staf_disposisi bila `level` bertipe ENUM tanpa nilai tersebut.
-- Aplikasi dapat menambahkan kolom ini otomatis; jika gagal, jalankan manual di phpMyAdmin (sesuaikan nama DB).

-- MariaDB 10.0.2+ / MySQL 8.0.12+ (sesuaikan jika pernyataan tidak didukung):
-- ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `role` VARCHAR(64) NULL DEFAULT NULL COMMENT 'Peran disposisi (staf_disposisi)' AFTER `level`;

-- Portabel (abaikan error jika kolom sudah ada):
ALTER TABLE `users`
    ADD COLUMN `role` VARCHAR(64) NULL DEFAULT NULL COMMENT 'Peran disposisi (staf_disposisi)' AFTER `level`;
