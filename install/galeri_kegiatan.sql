-- =============================================================================
-- Galeri kegiatan (tabel `galeri`)
-- =============================================================================
-- Jalankan di phpMyAdmin setelah memilih basis data proyek Anda.
-- =============================================================================

USE `db_organisasi`;

CREATE TABLE IF NOT EXISTS `galeri` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `judul` VARCHAR(255) NOT NULL DEFAULT '',
  `nama_file` VARCHAR(255) NOT NULL DEFAULT '',
  `tgl_upload` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_galeri_tgl` (`tgl_upload`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Foto kegiatan — berkas di folder assets/img/galeri/';
