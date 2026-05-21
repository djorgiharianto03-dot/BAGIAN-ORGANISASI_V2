-- Widget dinamis di halaman beranda (progress bar & perbandingan nilai)
-- Jika tabel sudah ada dari versi lama, buka admin/kelola_dashboard_widgets.php sekali
-- (migrasi kolom `aktif` otomatis) atau jalankan:
-- ALTER TABLE `dashboard_widgets` ADD COLUMN `aktif` TINYINT(1) NOT NULL DEFAULT 1 AFTER `urutan`;

CREATE TABLE IF NOT EXISTS `dashboard_widgets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `judul` VARCHAR(255) NOT NULL DEFAULT '',
  `tipe_data` ENUM('progres_angka', 'perbandingan_nilai') NOT NULL DEFAULT 'progres_angka',
  `nilai_kiri` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Angka/nilai kiri (pembilang atau teks kiri)',
  `nilai_kanan` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Angka/nilai kanan (penyebut atau teks kanan)',
  `warna_tema` VARCHAR(32) NOT NULL DEFAULT 'primary' COMMENT 'primary|success|danger',
  `urutan` INT UNSIGNED NOT NULL DEFAULT 0,
  `aktif` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dashboard_widgets_aktif_urutan` (`aktif`, `urutan`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
