-- Tabel arsip surat & surat disposisi (alur Sub Admin E-Organisasi → Kabag_organisasi)
-- Impor lewat phpMyAdmin / mysql CLI setelah memilih basis data aplikasi.
-- Charset: utf8mb4 (konsisten dengan install lain)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- arsip_surat: satu baris per berkas PDF di modul Arsip / uploads
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `arsip_surat` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jenis_surat` ENUM('masuk', 'keluar') NOT NULL DEFAULT 'masuk',
  `nama_file` VARCHAR(255) NOT NULL COMMENT 'Nama berkas di uploads/surat_masuk atau surat_keluar',
  `nomor_surat` VARCHAR(128) NOT NULL DEFAULT '',
  `perihal_ringkasan` TEXT NOT NULL,
  `instansi_asal` VARCHAR(255) NOT NULL DEFAULT '',
  `instansi_tujuan` VARCHAR(255) NOT NULL DEFAULT '',
  `kategori_bagian` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Slug kategori (org_arsip_kategori_bagian_map)',
  `ikut_monitoring_disposisi` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = surat masuk ikut alur disposisi Kabag',
  `tanggal_surat` DATE NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_arsip_jenis_nama_file` (`jenis_surat`, `nama_file`(191)),
  KEY `idx_arsip_jenis` (`jenis_surat`),
  KEY `idx_arsip_nomor` (`nomor_surat`),
  KEY `idx_arsip_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- surat_disposisi: alur disposisi (awal ke Kabag, tindak lanjut ke staf, dll.)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `surat_disposisi` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_arsip` BIGINT UNSIGNED NOT NULL COMMENT 'FK ke arsip_surat.id',
  `parent_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Disposisi induk (tindak lanjut dari Kabag ke staf)',
  `referensi_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Rujukan alternatif ke baris disposisi induk',
  `pengirim_username` VARCHAR(191) NOT NULL COMMENT 'Pemberi perintah (mis. Bupati, Sekda, atau username login)',
  `penerima_username` VARCHAR(191) NOT NULL COMMENT 'Penerima (mis. Kabag_organisasi atau username staf)',
  `instruksi` TEXT NOT NULL,
  `file_bukti` VARCHAR(1024) NULL DEFAULT NULL COMMENT 'Path/URL bukti penyelesaian staf',
  `status` VARCHAR(32) NOT NULL DEFAULT 'pending' COMMENT 'pending|diterima|dikerjakan|selesai|fix|revisi|…',
  `catatan_kabag` TEXT NULL DEFAULT NULL COMMENT 'Catatan internal / jejak input Sub Admin E-Org',
  `kabag_tandai_selesai` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Kabag verifikasi tugas staf selesai',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sd_id_arsip` (`id_arsip`),
  KEY `idx_sd_parent` (`parent_id`),
  KEY `idx_sd_referensi` (`referensi_id`),
  KEY `idx_sd_pengirim` (`pengirim_username`(64)),
  KEY `idx_sd_penerima` (`penerima_username`(64)),
  KEY `idx_sd_status` (`status`),
  CONSTRAINT `fk_surat_disposisi_arsip`
    FOREIGN KEY (`id_arsip`) REFERENCES `arsip_surat` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_surat_disposisi_parent`
    FOREIGN KEY (`parent_id`) REFERENCES `surat_disposisi` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
