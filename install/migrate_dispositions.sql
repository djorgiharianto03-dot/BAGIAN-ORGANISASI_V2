-- Migration: dispositions + prasyarat tabel surat (FK id_arsip)
-- Basis data: sesuai config/database.php
-- Charset: utf8mb4 (konsisten dengan install lain)

-- ---------------------------------------------------------------------------
-- Tabel surat: acuan arsip surat (satu baris per surat di sistem arsip).
-- Jika tabel `surat` sudah ada di lingkungan Anda, hapus atau komentari
-- blok berikut agar tidak bentrok dengan skema yang sudah dipakai.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `surat` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `meta_key` VARCHAR(384) NOT NULL COMMENT 'Kunci arsip, mis. masuk|namafile.pdf',
  `jenis` ENUM('masuk', 'keluar') NOT NULL DEFAULT 'masuk',
  `nama_file` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_surat_meta_key` (`meta_key`(191)),
  KEY `idx_surat_jenis` (`jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tabel dispositions: alur disposisi / lanjutan staf
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dispositions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_arsip` BIGINT UNSIGNED NOT NULL COMMENT 'FK ke surat.id',
  `parent_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Disposisi asal jika lanjutan/staf',
  `pengirim_username` VARCHAR(191) NOT NULL COMMENT 'Pemberi perintah',
  `penerima_username` VARCHAR(191) NOT NULL COMMENT 'Penerima perintah',
  `instruksi` TEXT NOT NULL COMMENT 'Isi perintah / tentang apa',
  `file_bukti` VARCHAR(1024) NULL DEFAULT NULL COMMENT 'Path file hasil kerja staf',
  `status` ENUM(
    'pending',
    'diterima',
    'dikerjakan',
    'selesai',
    'fix',
    'revisi'
  ) NOT NULL DEFAULT 'pending',
  `catatan_kabag` TEXT NULL DEFAULT NULL COMMENT 'Feedback Kabag untuk revisi',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dispositions_id_arsip` (`id_arsip`),
  KEY `idx_dispositions_parent_id` (`parent_id`),
  KEY `idx_dispositions_pengirim` (`pengirim_username`),
  KEY `idx_dispositions_penerima` (`penerima_username`),
  KEY `idx_dispositions_status` (`status`),
  CONSTRAINT `fk_dispositions_surat`
    FOREIGN KEY (`id_arsip`) REFERENCES `surat` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dispositions_parent`
    FOREIGN KEY (`parent_id`) REFERENCES `dispositions` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
